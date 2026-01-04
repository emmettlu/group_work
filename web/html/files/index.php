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

// 获取文件列表
$files_query = "SELECT * FROM files ORDER BY upload_time DESC";
$files_result = $conn->query($files_query);

// 获取系统配置
$config_query = "SELECT * FROM system_config WHERE config_key IN ('samba_version', 'samba_server', 'samba_share_name', 'admin_notes', 'pending_updates')";
$config_result = $conn->query($config_query);
$configs = [];
while ($row = $config_result->fetch_assoc()) {
    $configs[$row['config_key']] = $row['config_value'];
}

// 获取最近的系统日志
$logs_query = "SELECT * FROM system_logs ORDER BY log_time DESC LIMIT 5";
$logs_result = $conn->query($logs_query);

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文件管理 - TechCorp</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>📁 文件管理系统</h1>
            </div>
            <div class="user-info">
                <p>欢迎, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
                <a href="logout.php" style="color: #667eea; text-decoration: none;">退出登录</a>
            </div>
        </div>

        <div class="nav-links">
            <a href="index.php" class="active">文件列表</a>
            <a href="../admin/system_info.php">系统信息</a>
            <a href="../admin/logs.php">系统日志</a>
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer'): ?>
                <a href="../admin/database.php">数据库查询</a>
            <?php endif; ?>
        </div>

        <div class="content-box">
            <h2>📄 共享文件列表</h2>
            <p style="color: #666; margin-bottom: 20px;">
                所有文件存储在公司内部 Samba 文件共享服务器上，通过网络共享访问。
            </p>

            <?php if ($files_result->num_rows > 0): ?>
                <ul class="file-list">
                    <?php while ($file = $files_result->fetch_assoc()): ?>
                        <li class="file-item">
                            <strong>📄 <?php echo htmlspecialchars($file['filename']); ?></strong>
                            <p>路径: <?php echo htmlspecialchars($file['filepath']); ?></p>
                            <p>大小: <?php echo number_format($file['filesize'] / 1024, 2); ?> KB</p>
                            <p>上传者: <?php echo htmlspecialchars($file['upload_user']); ?> |
                               上传时间: <?php echo $file['upload_time']; ?></p>
                            <p style="color: #667eea; font-style: italic;">
                                <?php echo htmlspecialchars($file['description']); ?>
                            </p>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>暂无文件</p>
            <?php endif; ?>
        </div>

        <div class="content-box">
            <h2>⚙️ 文件服务器信息</h2>

            <div class="alert alert-warning">
                <strong>⚠️ 注意事项</strong>
                <p>文件服务器配置信息仅供技术人员参考。如需访问共享文件，请使用 SMB 协议连接到服务器。</p>
            </div>

            <table>
                <tr>
                    <th>配置项</th>
                    <th>值</th>
                </tr>
                <tr>
                    <td><strong>服务类型</strong></td>
                    <td>Samba 网络文件共享</td>
                </tr>
                <tr>
                    <td><strong>Samba 版本</strong></td>
                    <td>
                        <?php echo htmlspecialchars($configs['samba_version'] ?? '未知'); ?>
                        <span class="badge badge-vulnerable">需要更新</span>
                    </td>
                </tr>
                <tr>
                    <td><strong>服务器地址</strong></td>
                    <td><?php echo htmlspecialchars($configs['samba_server'] ?? '内部网络'); ?></td>
                </tr>
                <tr>
                    <td><strong>共享名称</strong></td>
                    <td><?php echo htmlspecialchars($configs['samba_share_name'] ?? 'myshare'); ?></td>
                </tr>
                <tr>
                    <td><strong>端口</strong></td>
                    <td>445 (SMB), 139 (NetBIOS)</td>
                </tr>
            </table>

            <?php if (isset($configs['pending_updates'])): ?>
                <div class="alert alert-danger" style="margin-top: 20px;">
                    <strong>🚨 待处理更新</strong>
                    <p><?php echo htmlspecialchars($configs['pending_updates']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($configs['admin_notes'])): ?>
                <div class="alert alert-info" style="margin-top: 20px;">
                    <strong>💡 管理员备注</strong>
                    <p><?php echo htmlspecialchars($configs['admin_notes']); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="content-box">
            <h2>📋 最近系统日志</h2>
            <table>
                <thead>
                    <tr>
                        <th>时间</th>
                        <th>级别</th>
                        <th>消息</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($log = $logs_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $log['log_time']; ?></td>
                            <td>
                                <?php
                                $badge_class = 'badge-info';
                                if ($log['log_level'] === 'WARNING') $badge_class = 'badge-warning';
                                if ($log['log_level'] === 'ERROR') $badge_class = 'badge-vulnerable';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($log['log_level']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($log['message']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <p style="margin-top: 15px; text-align: center;">
                <a href="../admin/logs.php" style="color: #667eea;">查看完整日志 →</a>
            </p>
        </div>

        <div class="content-box">
            <h2>💡 快速帮助</h2>
            <p><strong>如何访问共享文件？</strong></p>
            <p>在文件管理器中输入: <code style="background: #f5f5f5; padding: 2px 6px; border-radius: 3px;">\\192.168.1.10\myshare</code></p>
            <p style="margin-top: 10px;"><strong>Linux 用户：</strong></p>
            <p>使用 smbclient 连接: <code style="background: #f5f5f5; padding: 2px 6px; border-radius: 3px;">smbclient //192.168.1.10/myshare</code></p>
            <p style="margin-top: 15px; color: #888; font-size: 13px;">
                如有技术问题，请查看系统日志或联系 IT 部门 (developer 账户)
            </p>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 TechCorp Inc.</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
