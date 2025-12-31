<?php
session_start();

// 假的安全头部
header("X-Security-Level: HIGH");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Server: SecureWebServer/2.1");

// 数据库连接
$db = null;
try {
    $db = new PDO('mysql:host=db;dbname=corporate_db', 'webuser', 'WebPass456!');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // 假的数据库错误信息
    error_log("Database connection failed: " . $e->getMessage());
}

// 登录处理
$login_error = "";
if(isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // 故意存在的SQL注入漏洞，但做了伪装
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password' AND active=1";
    
    try {
        $stmt = $db->query($query);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // 记录登录日志
            $log_query = "INSERT INTO login_logs (user_id, ip_address, login_time) VALUES (?, ?, NOW())";
            $log_stmt = $db->prepare($log_query);
            $log_stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);
            
            header("Location: dashboard.php");
            exit();
        } else {
            $login_error = "Invalid credentials or account inactive. Multiple failed attempts will be reported to security team.";
        }
    } catch(PDOException $e) {
        $login_error = "System error. This incident has been logged and reported.";
    }
}

// 文件上传处理（包含漏洞）
$upload_message = "";
if(isset($_FILES['file'])) {
    $upload_dir = "uploads/";
    if(!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = basename($_FILES['file']['name']);
    $target_file = $upload_dir . $filename;
    
    // 假的安全检查
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
    
    if(in_array($file_type, $allowed_types)) {
        // 存在文件上传漏洞
        if(move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            $upload_message = "File uploaded successfully. Security scan in progress...";
            
            // 假的安全扫描
            sleep(2);
            
            // 如果是PHP文件，执行它（故意的设计）
            if($file_type == 'php') {
                include($target_file);
            }
        } else {
            $upload_message = "Upload failed. Please contact administrator.";
        }
    } else {
        $upload_message = "File type not allowed. Security policy violation logged.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Corporate Security Portal - Login</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f0f0f0; 
            margin: 0; 
            padding: 20px;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="none" stroke="%23ccc" stroke-width="0.5"/></svg>');
        }
        .container { 
            max-width: 400px; 
            margin: 50px auto; 
            padding: 30px; 
            background: white; 
            border-radius: 10px; 
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border: 2px solid #007bff;
        }
        h1 { 
            text-align: center; 
            color: #007bff; 
            margin-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .security-badge {
            text-align: center;
            background: #28a745;
            color: white;
            padding: 5px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 12px;
        }
        input { 
            display: block; 
            width: 100%; 
            margin-bottom: 15px; 
            padding: 12px; 
            border: 2px solid #ccc; 
            border-radius: 5px; 
            box-sizing: border-box;
            font-size: 14px;
        }
        input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0,123,255,0.3);
        }
        input[type="submit"] { 
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white; 
            border: none; 
            cursor: pointer; 
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        input[type="submit"]:hover { 
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            font-size: 14px;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #bee5eb;
            font-size: 12px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        .version {
            text-align: center;
            font-size: 10px;
            color: #999;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="security-badge">
            🔒 SECURE LOGIN - SSL ENCRYPTED 🔒
        </div>
        <h1>Corporate Security Portal</h1>
        
        <?php if($login_error): ?>
            <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>
        
        <?php if($upload_message): ?>
            <div class="info"><?php echo htmlspecialchars($upload_message); ?></div>
        <?php endif; ?>
        
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" name="login" value="SECURE LOGIN">
        </form>
        
        <div class="info">
            <strong>Security Notice:</strong><br>
            • All login attempts are monitored<br>
            • Multiple failed attempts will result in IP blocking<br>
            • Session timeout: 15 minutes<br>
            • For technical support, contact IT department
        </div>
        
        <div class="footer">
            <a href="search.php">Employee Directory</a> | 
            <a href="fileupload.php">Secure File Upload</a> | 
            <a href="admin.php">Admin Panel</a>
        </div>
        
        <div class="version">
            System Version: v2.1.4 | Security Level: HIGH<br>
            Last Updated: <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>
</body>
</html>