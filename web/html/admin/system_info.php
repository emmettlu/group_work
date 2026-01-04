<?php
session_start();

// 检查是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// 连接数据库
$db_host = getenv('MYSQL_HOST') ?: 'db';
$db_user = getenv('MYSQL_USER') ?: 'fileuser';
$db_pass = getenv('MYSQL_PASSWORD') ?: 'File@2024';
$db_name = getenv('MYSQL_DATABASE') ?: 'filemanager';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("数据库连接失败");
}

// 获取系统信息视图
$system_info_query = "SELECT * FROM system_info";
$system_info_result = $conn->query($system_info_query);

// 获取所有系统配置
$config_query = "SELECT * FROM system_config ORDER BY config_key";
$config_result = $conn->query($config_query);

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统信息 - TechCorp</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>⚙️ 系统信息</h1>
            </div>
            <div class="user-info">
                <p>欢迎, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
                <a href="../files/logout.php" style="color: #667eea; text-decoration: none;">退出登录</a>
            </div>
        </div>

        <div class="nav-links">
            <a href="../files/index.php">文件列表</a>
            <a href="system_info.php" class="active">系统信息</a>
            <a href="logs.php">系统日志</a>
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer'): ?>
                <a href="database.php">数据库查询</a>
            <?php endif; ?>
        </div>

        <div class="content-box">
            <h2>🖥️ 服务状态概览</h2>
            <table>
                <thead>
                    <tr>
                        <th>服务名称</th>
                        <th>版本</th>
                        <th>端口</th>
                        <th>状态</th>
                        <th>备注</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($info = $system_info_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($info['service_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($info['version']); ?></td>
                            <td><?php echo htmlspecialchars($info['port']); ?></td>
                            <td>
                                <?php
                                $badge_class = 'badge-info';
                                if ($info['status'] === 'VULNERABLE') {
                                    $badge_class = 'badge-vulnerable';
                                } elseif ($info['status'] === 'WARNING') {
                                    $badge_class = 'badge-warning';
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($info['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($info['notes']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="content-box">
            <h2>🔧 详细系统配置</h2>

            <div class="alert alert-danger">
                <strong>⚠️ 安全警告</strong>
                <p>检测到系统中存在过时的服务版本，可能存在已知安全漏洞。强烈建议立即更新！</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 30%;">配置键</th>
                        <th style="width: 35%;">配置值</th>
                        <th>说明</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($config = $config_result->fetch_assoc()): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($config['config_key']); ?></code></td>
                            <td>
                                <strong><?php echo htmlspecialchars($config['config_value']); ?></strong>
                                <?php if ($config['config_key'] === 'samba_version'): ?>
                                    <span class="badge badge-vulnerable">漏洞版本</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($config['description']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="content-box">
            <h2>🔍 Samba 服务详细信息</h2>

            <div class="alert alert-warning">
                <strong>💡 技术提示</strong>
                <p>当前 Samba 版本为 4.6.3，发布于 2017 年。该版本可能包含已披露的安全漏洞。</p>
                <p>建议访问 <a href="https://cve.mitre.org" target="_blank" style="color: #667eea;">CVE 数据库</a> 搜索相关漏洞信息。</p>
            </div>

            <div class="code-block">
# Samba 服务配置摘要
版本: 4.6.3
发布时间: 2017年3月
协议: SMB/CIFS (Server Message Block)
端口: 445 (SMB), 139 (NetBIOS)
共享路径: /home/share
访客访问: 已启用
只读模式: 否

# 网络信息
内部地址: 192.168.1.10
对外端口: 445 已开放
防火墙: 未配置

# 安全状态
最后安全审计: 2017-05-01
补丁状态: 未应用最新安全补丁
威胁级别: 高危 🔴
            </div>

            <div class="alert alert-info">
                <strong>🎯 渗透测试提示</strong>
                <p>如果你是安全研究人员，可以尝试以下方向：</p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>检查 Samba 4.6.3 版本的已知 CVE 漏洞</li>
                    <li>使用 <code>smbclient</code> 工具连接到共享服务</li>
                    <li>查找该版本是否存在远程代码执行漏洞</li>
                    <li>关键词：Samba 4.6.3 RCE exploit</li>
                </ul>
            </div>
        </div>

        <div class="content-box">
            <h2>📊 系统统计信息</h2>
            <table>
                <tr>
                    <td><strong>数据库版本</strong></td>
                    <td>MySQL 5.7</td>
                </tr>
                <tr>
                    <td><strong>Web 服务器</strong></td>
                    <td>Nginx 1.21 + PHP 7.4</td>
                </tr>
                <tr>
                    <td><strong>操作系统</strong></td>
                    <td>Linux (容器化环境)</td>
                </tr>
                <tr>
                    <td><strong>部署方式</strong></td>
                    <td>Docker Compose</td>
                </tr>
                <tr>
                    <td><strong>最后更新时间</strong></td>
                    <td>2024-01-15</td>
                </tr>
                <tr>
                    <td><strong>系统管理员</strong></td>
                    <td>admin, developer</td>
                </tr>
            </table>
        </div>

        <div class="content-box">
            <h2>🔐 安全建议</h2>
            <div class="alert alert-danger">
                <strong>紧急修复建议：</strong>
                <ol style="margin: 10px 0; padding-left: 20px;">
                    <li><strong>立即升级 Samba</strong> - 当前版本 4.6.3 存在严重的远程代码执行漏洞</li>
                    <li><strong>禁用访客访问</strong> - 共享文件夹应要求身份验证</li>
                    <li><strong>配置防火墙</strong> - 限制 445 端口仅对内网开放</li>
                    <li><strong>启用日志审计</strong> - 记录所有文件访问和修改操作</li>
                    <li><strong>定期更新补丁</strong> - 订阅安全公告，及时应用补丁</li>
                </ol>
            </div>
        </div>

        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer'): ?>
        <div class="content-box">
            <h2>🔬 高级调试信息</h2>
            <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
                以下信息仅供系统管理员和开发人员参考
            </p>

            <div class="code-block">
# 数据库连接信息
Host: <?php echo $db_host; ?>
Database: <?php echo $db_name; ?>
User: <?php echo $db_user; ?>

# 环境变量
MYSQL_HOST=<?php echo getenv('MYSQL_HOST') ?: 'db'; ?>
MYSQL_USER=<?php echo getenv('MYSQL_USER') ?: 'fileuser'; ?>
MYSQL_DATABASE=<?php echo getenv('MYSQL_DATABASE') ?: 'filemanager'; ?>

# 有用的数据库查询
CALL get_server_info();  -- 获取服务器信息存储过程
SELECT * FROM system_config WHERE config_key LIKE '%samba%';
SELECT * FROM system_logs WHERE log_level = 'WARNING';
            </div>
        </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2024 TechCorp Inc.</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
