<?php
session_start();

// 检查是否登录且有权限
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'developer') {
    die("权限不足，仅管理员和开发者可以访问此页面");
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

$query_result = null;
$error_message = '';
$success_message = '';
$executed_query = '';

// 处理查询请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $query = trim($_POST['query']);
    $executed_query = $query;

    if (!empty($query)) {
        // 执行查询
        $result = $conn->query($query);

        if ($result === false) {
            $error_message = "查询错误: " . $conn->error;
        } else {
            if ($result === true) {
                $success_message = "查询执行成功，影响行数: " . $conn->affected_rows;
            } else {
                $query_result = $result;
                $success_message = "查询成功，返回 " . $result->num_rows . " 行数据";
            }
        }
    }
}

// 预定义查询
$preset_queries = [
    'users' => "SELECT * FROM users;",
    'files' => "SELECT * FROM files;",
    'logs' => "SELECT * FROM system_logs ORDER BY log_time DESC LIMIT 20;",
    'config' => "SELECT * FROM system_config;",
    'system_info' => "SELECT * FROM system_info;",
    'samba_config' => "SELECT * FROM system_config WHERE config_key LIKE '%samba%';",
    'warnings' => "SELECT * FROM system_logs WHERE log_level = 'WARNING';",
    'server_info' => "CALL get_server_info();",
    'tables' => "SHOW TABLES;",
    'config_samba' => "SELECT * FROM system_config WHERE config_key IN ('samba_version', 'samba_server', 'samba_share_name', 'pending_updates');"
];

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据库查询 - TechCorp</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .query-box {
            width: 100%;
            min-height: 150px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
        }
        .preset-queries {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }
        .preset-btn {
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 13px;
            text-align: left;
        }
        .preset-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .result-table {
            overflow-x: auto;
            margin-top: 20px;
        }
        .result-table table {
            min-width: 100%;
            font-size: 13px;
        }
        .result-table th {
            background: #667eea;
            color: white;
            padding: 10px;
            font-weight: 600;
        }
        .result-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>💾 数据库查询工具</h1>
            </div>
            <div class="user-info">
                <p>欢迎, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
                <a href="../files/logout.php" style="color: #667eea; text-decoration: none;">退出登录</a>
            </div>
        </div>

        <div class="nav-links">
            <a href="../files/index.php">文件列表</a>
            <a href="system_info.php">系统信息</a>
            <a href="logs.php">系统日志</a>
            <a href="database.php" class="active">数据库查询</a>
        </div>

        <div class="content-box">
            <h2>🔍 SQL 查询执行器</h2>
            <div class="alert alert-warning">
                <strong>⚠️ 注意</strong>
                <p>此工具仅供授权的系统管理员和开发人员使用。请谨慎执行 SQL 查询，避免意外修改或删除数据。</p>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="query">输入 SQL 查询：</label>
                    <textarea id="query" name="query" class="query-box" placeholder="输入 SQL 查询语句..."><?php echo isset($_POST['query']) ? htmlspecialchars($_POST['query']) : ''; ?></textarea>
                </div>
                <button type="submit" class="btn-primary">🚀 执行查询</button>
            </form>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" style="margin-top: 20px;">
                    <strong>❌ 错误</strong>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-info" style="margin-top: 20px;">
                    <strong>✅ <?php echo htmlspecialchars($success_message); ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($query_result && $query_result->num_rows > 0): ?>
        <div class="content-box">
            <h2>📊 查询结果</h2>
            <p style="color: #666; margin-bottom: 10px;">
                执行的查询: <code style="background: #f5f5f5; padding: 2px 6px; border-radius: 3px;"><?php echo htmlspecialchars($executed_query); ?></code>
            </p>

            <div class="result-table">
                <table>
                    <thead>
                        <tr>
                            <?php
                            $fields = $query_result->fetch_fields();
                            foreach ($fields as $field) {
                                echo "<th>" . htmlspecialchars($field->name) . "</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $query_result->fetch_assoc()): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?php echo htmlspecialchars($value ?? '-'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="content-box">
            <h2>📋 预定义查询</h2>
            <p style="color: #666; margin-bottom: 15px;">
                点击下方按钮快速执行常用查询
            </p>

            <div class="preset-queries">
                <button class="preset-btn" onclick="setQuery('<?php echo addslashes($preset_queries['users']); ?>')">
                    👥 查询所有用户
                </button>
                <button class="preset-btn" onclick="setQuery('<?php echo addslashes($preset_queries['files']); ?>')">
                    📄 查询所有文件
                </button>
                <button class="preset-btn" onclick="setQuery('<?php echo addslashes($preset_queries['logs']); ?>')">
                    📋 查询最近日志
                </button>
                <button class="preset-btn" onclick="setQuery('<?php echo addslashes($preset_queries['config']); ?>')">
                    ⚙️ 查询系统配置
                </button>
                <button class="preset-btn" onclick="setQuery('<?php echo addslashes($preset_queries['system_info']); ?>')">
                    🖥️ 查询系统信息
                </button>
                <button class="preset-btn" onclick="setQuery('<?php echo addslashes($preset_queries['samba_config']); ?>')">
                    🔍 查询 Samba 配置
                </button>
                <button class="preset-btn" onclick="setQuery('<?php echo addslashes($preset_queries['warnings']); ?>')">
                    ⚠️ 查询警告日志
                </button>
                <button class="preset-btn" onclick="setQuery('<?php echo addslashes($preset_queries['server_info']); ?>')">
                    🔬 执行服务器信息存储过程
                </button>
                <button class="preset-btn" onclick="setQuery('<?php echo addslashes($preset_queries['tables']); ?>')">
                    📊 显示所有表
                </button>
                <button class="preset-btn" onclick="setQuery('<?php echo addslashes($preset_queries['config_samba']); ?>')">
                    🎯 Samba 详细配置
                </button>
            </div>
        </div>

        <div class="content-box">
            <h2>💡 SQL 查询示例</h2>
            <div class="code-block">
-- 查询 Samba 相关配置
SELECT * FROM system_config WHERE config_key LIKE '%samba%';

-- 查询所有警告和错误日志
SELECT * FROM system_logs WHERE log_level IN ('WARNING', 'ERROR');

-- 查询文件共享服务信息
SELECT * FROM system_info WHERE service_name LIKE '%Samba%';

-- 调用存储过程获取服务器信息（重要提示！）
CALL get_server_info();

-- 查询系统配置中的安全相关信息
SELECT * FROM system_config WHERE config_key IN ('pending_updates', 'security_audit_date');

-- 查询用户表（可能有弱口令）
SELECT username, password, role FROM users;

-- 查询备份位置信息
SELECT * FROM system_config WHERE config_key = 'backup_location';
            </div>
        </div>

        <div class="content-box">
            <h2>🎯 调查提示</h2>
            <div class="alert alert-info">
                <strong>🔍 如果你在寻找漏洞线索...</strong>
                <p>尝试执行以下查询来收集信息：</p>
                <ol style="margin: 10px 0; padding-left: 20px;">
                    <li><strong>执行存储过程：</strong> <code>CALL get_server_info();</code> - 这会返回服务器的关键信息和提示</li>
                    <li><strong>查看 Samba 配置：</strong> 在 system_config 表中查找 samba 相关的键值</li>
                    <li><strong>检查待处理更新：</strong> 查看 pending_updates 字段</li>
                    <li><strong>分析系统日志：</strong> 搜索包含 "Samba" 或 "445" 端口的日志</li>
                </ol>
            </div>

            <div class="alert alert-warning">
                <strong>💡 关键发现</strong>
                <p>根据数据库中的信息，Samba 服务版本为 4.6.3，这是一个 2017 年的版本。</p>
                <p>建议搜索：<strong>CVE-2017-7494</strong></p>
                <p>这可能是突破系统的关键！</p>
            </div>
        </div>

        <div class="content-box">
            <h2>📚 数据库结构</h2>
            <div class="code-block">
数据库: filemanager

表结构:
- users: 用户账户信息
- files: 文件元数据
- system_logs: 系统日志记录
- system_config: 系统配置（包含 Samba 信息）
- system_info: 系统服务信息视图

存储过程:
- get_server_info(): 返回服务器提示信息

连接信息:
Host: <?php echo $db_host; ?>
Database: <?php echo $db_name; ?>
User: <?php echo $db_user; ?>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 TechCorp Inc.</p>
    </footer>

    <script>
        function setQuery(query) {
            document.getElementById('query').value = query;
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
