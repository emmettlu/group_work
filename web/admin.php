<?php
session_start();

// 假的安全头部
header("X-Security-Level: ADMINISTRATIVE");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Server: AdminPanel/1.0");

// 简单的认证绕过（存在漏洞）
$admin_logged_in = false;
$auth_error = "";

if(isset($_POST['admin_login'])) {
    $username = $_POST['admin_user'] ?? '';
    $password = $_POST['admin_pass'] ?? '';
    
    // 故意的弱认证 - 存在逻辑漏洞
    if($username == "admin" || $password == "admin123") {
        // 这是故意的漏洞 - 只需要用户名 OR 密码匹配
        $_SESSION['admin_user'] = $username;
        $admin_logged_in = true;
    } else {
        // 检查是否为开发账户（后门）
        if($username == "dev" && strlen($password) > 3) {
            $_SESSION['admin_user'] = "developer";
            $admin_logged_in = true;
        } else {
            $auth_error = "Administrative access denied. Security team has been notified.";
        }
    }
}

// 如果已登录，处理管理操作
if(isset($_SESSION['admin_user'])) {
    $admin_logged_in = true;
    
    // 系统命令执行（严重漏洞）
    if(isset($_POST['system_command'])) {
        $command = $_POST['command'] ?? '';
        // 假的安全检查
        $dangerous_commands = ['rm', 'del', 'format', 'dd'];
        $safe = true;
        foreach($dangerous_commands as $dangerous) {
            if(stripos($command, $dangerous) !== false) {
                $safe = false;
                break;
            }
        }
        
        if($safe && !empty($command)) {
            // 执行命令（故意的漏洞）
            $output = shell_exec($command . " 2>&1");
        } else {
            $output = "Command blocked for security reasons.";
        }
    }
    
    // 文件管理
    if(isset($_POST['file_operation'])) {
        $operation = $_POST['operation'] ?? '';
        $file_path = $_POST['file_path'] ?? '';
        
        if($operation == 'read' && !empty($file_path)) {
            if(file_exists($file_path) && is_readable($file_path)) {
                $file_content = file_get_contents($file_path);
            } else {
                $file_content = "File not found or not readable: " . htmlspecialchars($file_path);
            }
        }
    }
}

// 获取系统信息
$system_info = [
    'php_version' => phpversion(),
    'os' => php_uname(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'admin_contact' => 'admin@company.com',
    'security_level' => 'ADMINISTRATIVE'
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>System Administration Panel</title>
    <style>
        body { 
            font-family: 'Courier New', monospace;
            background-color: #1a1a1a; 
            color: #00ff00;
            margin: 0; 
            padding: 20px;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><rect width="20" height="20" fill="none" stroke="%2300ff00" stroke-width="0.5" opacity="0.3"/></svg>');
        }
        .container { 
            max-width: 1000px; 
            margin: 20px auto; 
            padding: 30px; 
            background: #0d0d0d; 
            border-radius: 10px; 
            border: 2px solid #00ff00;
            box-shadow: 0 0 20px rgba(0,255,0,0.3);
        }
        h1 { 
            text-align: center; 
            color: #00ff00; 
            margin-bottom: 10px;
            text-shadow: 0 0 10px #00ff00;
        }
        .security-badge {
            text-align: center;
            background: linear-gradient(135deg, #ff0000, #cc0000);
            color: white;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #ff6666;
        }
        .login-form {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #333;
        }
        input { 
            display: block; 
            width: 100%; 
            margin-bottom: 15px; 
            padding: 12px; 
            border: 2px solid #333; 
            border-radius: 5px; 
            background: #0d0d0d;
            color: #00ff00;
            font-family: monospace;
        }
        input:focus {
            border-color: #00ff00;
            outline: none;
            box-shadow: 0 0 5px rgba(0,255,0,0.3);
        }
        input[type="submit"] { 
            background: linear-gradient(135deg, #ff0000, #cc0000);
            color: white; 
            border: none; 
            cursor: pointer; 
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        input[type="submit"]:hover { 
            background: linear-gradient(135deg, #cc0000, #990000);
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(255,0,0,0.3);
        }
        .error {
            background: #220000;
            color: #ff6666;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #660000;
        }
        .admin-panel {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #00ff00;
            margin-top: 20px;
        }
        .system-info {
            background: #001100;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #00aa00;
            font-size: 13px;
        }
        .command-output {
            background: #000;
            color: #00ff00;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #333;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
        .file-content {
            background: #000;
            color: #00ffff;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #0088aa;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
        .warning {
            background: #332200;
            color: #ffaa66;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #ff8800;
            font-size: 13px;
        }
        .nav-menu {
            text-align: center;
            margin: 20px 0;
        }
        .nav-menu a {
            color: #00ff00;
            text-decoration: none;
            margin: 0 10px;
            padding: 5px 10px;
            border: 1px solid #00ff00;
            border-radius: 3px;
            font-size: 12px;
        }
        .nav-menu a:hover {
            background: #00ff00;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="security-badge">
            🔴 ADMINISTRATIVE ACCESS - AUTHORIZED PERSONNEL ONLY 🔴
        </div>
        <h1>System Administration Panel</h1>
        
        <?php if(!$admin_logged_in): ?>
            <?php if($auth_error): ?>
                <div class="error"><?php echo htmlspecialchars($auth_error); ?></div>
            <?php endif; ?>
            
            <div class="warning">
                <strong>⚠️ WARNING:</strong> Unauthorized access will be prosecuted to the fullest extent of the law.
                All login attempts are monitored and logged.
            </div>
            
            <div class="login-form">
                <form method="post">
                    <h3>Administrative Login</h3>
                    <input type="text" name="admin_user" placeholder="Admin Username" required>
                    <input type="password" name="admin_pass" placeholder="Admin Password" required>
                    <input type="submit" name="admin_login" value="ADMIN LOGIN">
                </form>
            </div>
            
            <div class="system-info">
                <strong>System Information:</strong><br>
                PHP Version: <?php echo $system_info['php_version']; ?><br>
                Server: <?php echo htmlspecialchars($system_info['server_software']); ?><br>
                OS: <?php echo htmlspecialchars($system_info['os']); ?><br>
                Security Level: <?php echo htmlspecialchars($system_info['security_level']); ?><br>
                Contact: <?php echo htmlspecialchars($system_info['admin_contact']); ?>
            </div>
            
        <?php else: ?>
            <div class="admin-panel">
                <h3>Welcome, <?php echo htmlspecialchars($_SESSION['admin_user']); ?>!</h3>
                <div class="nav-menu">
                    <a href="#system">System</a>
                    <a href="#files">Files</a>
                    <a href="#network">Network</a>
                    <a href="#logs">Logs</a>
                    <a href="admin.php?logout=1">Logout</a>
                </div>
                
                <!-- System Command Execution -->
                <div id="system">
                    <h4>🔧 System Command Execution</h4>
                    <div class="warning">
                        <strong>⚠️ CAUTION:</strong> System commands are monitored. Unauthorized commands will be logged.
                    </div>
                    <form method="post">
                        <input type="text" name="command" placeholder="Enter system command (e.g., ls, pwd, whoami)" style="width: 70%; display: inline-block;">
                        <input type="submit" name="system_command" value="EXECUTE" style="width: 25%; display: inline-block; margin-left: 10px;">
                    </form>
                    
                    <?php if(isset($output)): ?>
                        <div class="command-output"><?php echo htmlspecialchars($output); ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- File Management -->
                <div id="files">
                    <h4>📁 File Management</h4>
                    <form method="post">
                        <input type="text" name="file_path" placeholder="Enter file path (e.g., /etc/passwd, /var/log/samba/log.smbd)" style="width: 70%;">
                        <input type="hidden" name="operation" value="read">
                        <input type="submit" name="file_operation" value="READ FILE" style="width: 25%; margin-left: 10px;">
                    </form>
                    
                    <?php if(isset($file_content)): ?>
                        <div class="file-content"><?php echo htmlspecialchars($file_content); ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Quick System Checks -->
                <div id="network">
                    <h4>🌐 Network Information</h4>
                    <div class="system-info">
                        <strong>Quick System Checks:</strong><br>
                        <?php
                        $checks = [
                            'Current Directory' => getcwd(),
                            'PHP Process User' => get_current_user(),
                            'Server IP' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
                            'Client IP' => $_SERVER['REMOTE_ADDR'],
                            'Server Port' => $_SERVER['SERVER_PORT'] ?? 'Unknown',
                            'Request Method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
                            'Script Filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown'
                        ];
                        foreach($checks as $label => $value): ?>
                            <?php echo htmlspecialchars($label); ?>: <?php echo htmlspecialchars($value); ?><br>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Security Logs -->
                <div id="logs">
                    <h4>🔒 Recent Security Events</h4>
                    <div class="system-info">
                        <strong>Latest Events:</strong><br>
                        [<?php echo date('Y-m-d H:i:s'); ?>] Admin panel accessed by <?php echo htmlspecialchars($_SESSION['admin_user']); ?><br>
                        [<?php echo date('Y-m-d H:i:s', time()-300); ?>] System integrity check completed<br>
                        [<?php echo date('Y-m-d H:i:s', time()-600); ?>] Security scan initiated<br>
                        [<?php echo date('Y-m-d H:i:s', time()-900); ?>] Network monitoring active<br>
                        [<?php echo date('Y-m-d H:i:s', time()-1200); ?>] File system checked
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="nav-menu">
            <a href="index.php">Main Portal</a>
            <a href="search.php">Employee Directory</a>
            <a href="fileupload.php">File Upload</a>
        </div>
    </div>
    
    <?php if(isset($_GET['logout'])): ?>
        <?php session_destroy(); ?>
        <script>window.location.href='admin.php';</script>
    <?php endif; ?>
</body>
</html>