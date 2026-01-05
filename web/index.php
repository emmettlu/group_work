<?php
// TechCorp Internal Portal - 公司内部门户系统
// 这是一个看起来正常的企业内部系统，用于迷惑攻击者

session_start();

// 数据库连接配置
$db_host = getenv('DB_HOST') ?: 'mysql';
$db_user = getenv('DB_USER') ?: 'webuser';
$db_pass = getenv('DB_PASS') ?: 'webpass123';
$db_name = getenv('DB_NAME') ?: 'company_db';

// 简单的路由系统
$page = $_GET['page'] ?? 'home';

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechCorp - 企业内部门户</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .nav {
            background: #34495e;
            padding: 15px 30px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .nav a:hover {
            background: #667eea;
        }

        .nav a.active {
            background: #667eea;
        }

        .content {
            padding: 40px;
            min-height: 400px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid;
        }

        .alert-info {
            background: #e3f2fd;
            border-color: #2196f3;
            color: #0d47a1;
        }

        .alert-warning {
            background: #fff3e0;
            border-color: #ff9800;
            color: #e65100;
        }

        .alert-danger {
            background: #ffebee;
            border-color: #f44336;
            color: #c62828;
        }

        .card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
        }

        .card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 0.9em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #667eea;
            color: white;
        }

        tr:hover {
            background: #f5f5f5;
        }

        .login-form {
            max-width: 400px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        .hidden-hint {
            font-size: 0.7em;
            color: #95a5a6;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏢 TechCorp</h1>
            <p>企业内部资源管理门户 | Internal Resource Portal</p>
        </div>

        <div class="nav">
            <a href="?page=home" class="<?= $page == 'home' ? 'active' : '' ?>">首页</a>
            <a href="?page=files" class="<?= $page == 'files' ? 'active' : '' ?>">文件管理</a>
            <a href="?page=upload" class="<?= $page == 'upload' ? 'active' : '' ?>">上传文件</a>
            <a href="?page=system" class="<?= $page == 'system' ? 'active' : '' ?>">系统信息</a>
            <a href="?page=login" class="<?= $page == 'login' ? 'active' : '' ?>">登录</a>
            <a href="admin/" style="opacity: 0.7;">管理后台</a>
        </div>

        <div class="content">
            <?php
            switch ($page) {
                case 'home':
                    include 'pages/home.php';
                    break;
                case 'files':
                    include 'pages/files.php';
                    break;
                case 'upload':
                    include 'pages/upload.php';
                    break;
                case 'system':
                    include 'pages/system.php';
                    break;
                case 'login':
                    include 'pages/login.php';
                    break;
                default:
                    echo "<h2>页面未找到</h2>";
                    echo "<p>您访问的页面不存在。</p>";
            }
            ?>
        </div>

        <div class="footer">
            <p>&copy; 2024 TechCorp. All Rights Reserved. | Version 1.2.3 | Powered by PHP & Samba</p>
            <p class="hidden-hint"><!-- Debug: Check /admin/debug.php for troubleshooting --></p>
        </div>
    </div>
</body>
</html>
