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

// 获取日志级别过滤
$filter = $_GET['level'] ?? 'all';
$logs_query = "SELECT * FROM system_logs";
if ($filter !== 'all') {
    $logs_query .= " WHERE log_level = '" . $conn->real_escape_string($filter) . "'";
}
$logs_query .= " ORDER BY log_time DESC LIMIT 50";

$logs_result = $conn->query($logs_query);

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统日志 - TechCorp</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .log-filters {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .log-filters a {
            padding: 8px 16px;
            background: #f8f9fa;
            color: #667eea;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .log-filters a:hover,
        .log-filters a.active {
            background: #667eea;
            color: white;
        }
        .log-details {
            font-size: 13px;
            color: #666;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-top: 5px;
        }
        .log-row-info {
            background: #e7f3ff !important;
        }
        .log-row-warning {
            background: #fff8e1 !important;
        }
        .log-row-error {
            background: #ffebee !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>📋 系统日志</h1>
            </div>
            <div class="user-info">
                <p>欢迎, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
                <a href="../files/logout.php" style="color: #667eea; text-decoration: none;">退出登录</a>
            </div>
        </div>

        <div class="nav-links">
            <a href="../files/index.php">文件列表</a>
            <a href="system_info.php">系统信息</a>
            <a href="logs.php" class="active">系统日志</a>
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer'): ?>
                <a href="database.php">数据库查询</a>
            <?php endif; ?>
        </div>

        <div class="content-box">
            <h2>🔍 日志筛选</h2>
            <div class="log-filters">
                <a href="?level=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">
                    全部日志
                </a>
                <a href="?level=INFO" class="<?php echo $filter === 'INFO' ? 'active' : ''; ?>">
                    📘 INFO
                </a>
                <a href="?level=WARNING" class="<?php echo $filter === 'WARNING' ? 'active' : ''; ?>">
                    ⚠️ WARNING
                </a>
                <a href="?level=ERROR" class="<?php echo $filter === 'ERROR' ? 'active' : ''; ?>">
                    🚨 ERROR
                </a>
            </div>
        </div>

        <div class="content-box">
            <h2>📊 日志记录 (最近50条)</h2>

            <?php if ($logs_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 18%;">时间</th>
                            <th style="width: 12%;">级别</th>
                            <th style="width: 35%;">消息</th>
                            <th style="width: 30%;">详细信息</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $logs_result->fetch_assoc()): ?>
                            <?php
                            $row_class = '';
                            $badge_class = 'badge-info';
                            if ($log['log_level'] === 'INFO') {
                                $row_class = 'log-row-info';
                                $badge_class = 'badge-info';
                            } elseif ($log['log_level'] === 'WARNING') {
                                $row_class = 'log-row-warning';
                                $badge_class = 'badge-warning';
                            } elseif ($log['log_level'] === 'ERROR') {
                                $row_class = 'log-row-error';
                                $badge_class = 'badge-vulnerable';
                            }
                            ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td><?php echo $log['id']; ?></td>
                                <td><?php echo $log['log_time']; ?></td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($log['log_level']); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo htmlspecialchars($log['message']); ?></strong></td>
                                <td>
                                    <?php if (!empty($log['details'])): ?>
                                        <?php echo htmlspecialchars($log['details']); ?>
                                    <?php else: ?>
                                        <span style="color: #999;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">
                    <p>没有找到符合条件的日志记录</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="content-box">
            <h2>💡 日志分析提示</h2>
            <div class="alert alert-warning">
                <strong>⚠️ 关键发现</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>检测到 Samba 服务版本警告 - 建议检查是否存在安全漏洞</li>
                    <li>系统配置已备份至 SMB 共享服务器</li>
                    <li>多个账户使用弱密码 - 已成功登录</li>
                    <li>文件共享服务端口 445 对外开放</li>
                </ul>
            </div>

            <div class="alert alert-info">
                <strong>🔍 安全审计建议</strong>
                <p>从日志中可以看出，Samba 文件共享服务可能是安全隐患的来源。建议：</p>
                <ol style="margin: 10px 0; padding-left: 20px;">
                    <li>检查 Samba 4.6.3 版本的 CVE 漏洞列表</li>
                    <li>验证共享目录的访问权限配置</li>
                    <li>测试未授权访问的可能性</li>
                    <li>审查备份文件的存储位置和权限</li>
                </ol>
            </div>
        </div>

        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer'): ?>
        <div class="content-box">
            <h2>🔧 日志管理工具</h2>
            <p style="color: #666; margin-bottom: 15px;">
                高级用户可以使用数据库查询工具进行更复杂的日志分析
            </p>
            <div class="code-block">
# 有用的 SQL 查询示例
SELECT * FROM system_logs WHERE message LIKE '%Samba%';
SELECT log_level, COUNT(*) as count FROM system_logs GROUP BY log_level;
SELECT * FROM system_logs WHERE log_time >= DATE_SUB(NOW(), INTERVAL 7 DAY);
SELECT * FROM system_logs WHERE details LIKE '%SMB%' OR details LIKE '%445%';
            </div>
            <p style="margin-top: 15px;">
                <a href="database.php" class="btn-secondary">前往数据库查询工具 →</a>
            </p>
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
