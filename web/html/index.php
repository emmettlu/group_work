<?php
session_start();

// 如果已登录，跳转到文件管理页面
if (isset($_SESSION['user_id'])) {
    header('Location: files/index.php');
    exit();
}

$error = '';

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 连接数据库
    $db_host = getenv('MYSQL_HOST') ?: 'db';
    $db_user = getenv('MYSQL_USER') ?: 'fileuser';
    $db_pass = getenv('MYSQL_PASSWORD') ?: 'File@2024';
    $db_name = getenv('MYSQL_DATABASE') ?: 'filemanager';

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        $error = "系统错误，请稍后再试";
    } else {
        // 简单的SQL查询（这里故意用参数化查询，让SQL注入不work）
        $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header('Location: files/index.php');
            exit();
        } else {
            $error = "用户名或密码错误";
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechCorp 企业文件管理系统</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <h1>🏢 TechCorp</h1>
                <p>企业文件管理系统</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-login">登录</button>
            </form>

            <div class="help-text">
                <p>💡 提示：如果忘记密码，请联系IT部门</p>
                <p style="margin-top: 10px; font-size: 12px; color: #888;">
                    测试账号参考：admin/admin123, guest/guest, developer/dev123
                </p>
            </div>
        </div>

        <div class="announcements">
            <h3>📢 系统公告</h3>
            <div class="announcement-item">
                <strong>2024-01-15</strong>
                <p>公司已部署新的文件共享服务器，所有重要文档请上传至共享目录。共享服务器地址将在内部系统中公布。</p>
            </div>
            <div class="announcement-item">
                <strong>2024-01-10</strong>
                <p>IT部门通知：由于预算限制，文件服务器软件版本暂未更新，请各部门注意数据安全。</p>
            </div>
            <div class="announcement-item">
                <strong>2024-01-05</strong>
                <p>安全提醒：请勿使用弱密码，定期更换密码以保护账户安全。</p>
            </div>
            <div class="announcement-item important">
                <strong>2023-12-20</strong>
                <p>⚠️ 紧急通知：网络安全部门发现部分服务存在潜在漏洞，技术团队正在评估中。相关信息已记录在系统日志中。</p>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 TechCorp Inc. | 技术支持: IT部门</p>
        <p style="font-size: 11px; color: #666;">系统版本: v1.0 | 文件存储: Samba Network Share</p>
    </footer>
</body>
</html>
