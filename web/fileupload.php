<?php
session_start();

// 假的安全头部
header("X-Security-Level: MAXIMUM");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Server: SecureFileServer/3.0");

$upload_message = "";
$security_scan = "";

if(isset($_FILES['file'])) {
    $upload_dir = "uploads/";
    if(!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = basename($_FILES['file']['name']);
    $file_size = $_FILES['file']['size'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_type = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // 假的安全检查
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'csv'];
    $max_size = 10 * 1024 * 1024; // 10MB
    
    // 文件类型检查（存在漏洞）
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_tmp);
    finfo_close($finfo);
    
    if($file_size > $max_size) {
        $upload_message = "❌ File too large. Maximum size: 10MB";
    } elseif(!in_array($file_type, $allowed_types)) {
        $upload_message = "❌ File type not allowed. Allowed types: " . implode(', ', $allowed_types);
    } else {
        // 假的安全扫描
        $security_scan = "🔍 Scanning file for malware...";
        
        // 模拟扫描过程
        sleep(2);
        
        // 文件重命名（但保留原始扩展名）
        $new_filename = uniqid() . '_' . $filename;
        $target_file = $upload_dir . $new_filename;
        
        if(move_uploaded_file($file_tmp, $target_file)) {
            chmod($target_file, 0644);
            
            $upload_message = "✅ File uploaded successfully: " . htmlspecialchars($filename);
            $upload_message .= "<br>📁 Stored as: " . htmlspecialchars($new_filename);
            $upload_message .= "<br>📊 File size: " . number_format($file_size / 1024, 2) . " KB";
            
            // 假的安全扫描结果
            $security_scan = "✅ Security scan completed: No threats detected<br>";
            $security_scan .= "🔒 File quarantined for 24-hour monitoring<br>";
            $security_scan .= "📋 MD5: " . md5_file($target_file) . "<br>";
            $security_scan .= "🔐 Access restricted to authorized personnel only";
            
            // 如果是PHP文件，创建可访问的副本（故意的漏洞）
            if($file_type == 'php') {
                $php_copy = $upload_dir . 'exec_' . uniqid() . '.php';
                copy($target_file, $php_copy);
                chmod($php_copy, 0755);
                
                $upload_message .= "<br><br>⚠️ PHP file detected. Executable copy created for system integration.";
                $upload_message .= "<br>🔗 Access URL: <a href='uploads/" . basename($php_copy) . "' target='_blank'>Execute File</a>";
            }
            
            // 记录上传日志
            $log_file = "uploads/upload_log.txt";
            $log_entry = date('Y-m-d H:i:s') . " - File: $filename -> $new_filename, Size: $file_size, IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
            
        } else {
            $upload_message = "❌ Upload failed. Please contact system administrator.";
        }
    }
}

// 获取已上传的文件列表
$uploaded_files = [];
if(is_dir($upload_dir)) {
    $files = scandir($upload_dir);
    foreach($files as $file) {
        if($file != '.' && $file != '..' && $file != 'upload_log.txt') {
            $file_path = $upload_dir . $file;
            if(is_file($file_path)) {
                $uploaded_files[] = [
                    'name' => $file,
                    'size' => filesize($file_path),
                    'time' => filemtime($file_path)
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Secure File Upload Portal</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f0f0f0; 
            margin: 0; 
            padding: 20px;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="none" stroke="%23ccc" stroke-width="0.5"/></svg>');
        }
        .container { 
            max-width: 900px; 
            margin: 20px auto; 
            padding: 30px; 
            background: white; 
            border-radius: 10px; 
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border: 2px solid #dc3545;
        }
        h1 { 
            text-align: center; 
            color: #dc3545; 
            margin-bottom: 10px;
        }
        .security-badge {
            text-align: center;
            background: #dc3545;
            color: white;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .upload-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px solid #dee2e6;
        }
        input[type="file"] {
            display: block;
            margin: 15px 0;
            padding: 10px;
            border: 2px dashed #ccc;
            border-radius: 5px;
            width: 100%;
            background: white;
        }
        input[type="submit"] { 
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white; 
            border: none; 
            cursor: pointer; 
            padding: 15px 30px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        input[type="submit"]:hover { 
            background: linear-gradient(135deg, #c82333, #bd2130);
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 14px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .security-scan {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            font-family: monospace;
        }
        .file-list {
            margin-top: 20px;
        }
        .file-item {
            background: #f8f9fa;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border-left: 4px solid #6c757d;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .file-info {
            font-size: 14px;
            color: #495057;
        }
        .file-actions {
            font-size: 12px;
        }
        .file-actions a {
            color: #007bff;
            text-decoration: none;
            margin-left: 10px;
        }
        .file-actions a:hover {
            text-decoration: underline;
        }
        .security-info {
            background: #e3f2fd;
            color: #1565c0;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #bbdefb;
            font-size: 13px;
        }
        .warning {
            background: #fff3e0;
            color: #e65100;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #ffe0b2;
            font-size: 13px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="security-badge">
            🔒 SECURE FILE UPLOAD - MAXIMUM PROTECTION 🔒
        </div>
        <h1>Corporate File Upload Portal</h1>
        
        <div class="security-info">
            <strong>🔐 Security Features:</strong><br>
            • Real-time malware scanning<br>
            • File type validation<br>
            • Size restrictions (10MB max)<br>
            • 24-hour quarantine period<br>
            • MD5 checksum verification<br>
            • Access logging and monitoring
        </div>
        
        <div class="warning">
            <strong>⚠️ Upload Policy:</strong><br>
            • Only authorized file types are permitted<br>
            • All uploads are subject to security review<br>
            • Suspicious files will be automatically quarantined<br>
            • Violations will be reported to security team
        </div>
        
        <div class="upload-form">
            <form method="post" enctype="multipart/form-data">
                <h3>Select File to Upload:</h3>
                <input type="file" name="file" required>
                <input type="submit" value="UPLOAD FILE SECURELY">
            </form>
        </div>
        
        <?php if($upload_message): ?>
            <div class="message success">
                <?php echo $upload_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if($security_scan): ?>
            <div class="message security-scan">
                <?php echo $security_scan; ?>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($uploaded_files)): ?>
            <div class="file-list">
                <h3>Recently Uploaded Files:</h3>
                <?php foreach($uploaded_files as $file): ?>
                    <div class="file-item">
                        <div class="file-info">
                            <strong><?php echo htmlspecialchars($file['name']); ?></strong>
                            <br>
                            Size: <?php echo number_format($file['size'] / 1024, 2); ?> KB | 
                            Uploaded: <?php echo date('Y-m-d H:i:s', $file['time']); ?>
                        </div>
                        <div class="file-actions">
                            <a href="uploads/<?php echo urlencode($file['name']); ?>" target="_blank">View</a>
                            <a href="uploads/<?php echo urlencode($file['name']); ?>" download>Download</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            <a href="index.php">← Back to Login</a> | 
            <a href="search.php">Employee Directory</a> | 
            <a href="admin.php">System Administration</a>
        </div>
    </div>
</body>
</html>