<?php
// 登录页面 - 伪装的登录功能（用于迷惑攻击者）

$login_error = null;
$login_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 数据库连接
    $db_host = getenv('DB_HOST') ?: 'mysql';
    $db_user = getenv('DB_USER') ?: 'webuser';
    $db_pass = getenv('DB_PASS') ?: 'webpass123';
    $db_name = getenv('DB_NAME') ?: 'company_db';

    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        if ($conn->connect_error) {
            throw new Exception("数据库连接失败");
        }

        // 查询用户（使用MD5，故意制造弱密码提示）
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = MD5(?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            $login_message = "登录成功！欢迎回来，" . htmlspecialchars($user['username']);

            // 记录登录日志
            $log_stmt = $conn->prepare("INSERT INTO system_logs (log_type, message, ip_address) VALUES (?, ?, ?)");
            $log_type = 'LOGIN';
            $log_message = "User {$user['username']} logged in successfully";
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $log_stmt->bind_param("sss", $log_type, $log_message, $ip_address);
            $log_stmt->execute();
        } else {
            $login_error = "用户名或密码错误！提示：可以尝试 admin/admin123 或 test/test";
        }

        $conn->close();
    } catch (Exception $e) {
        $login_error = "登录失败：" . $e->getMessage();
    }
}

$is_logged_in = isset($_SESSION['user_id']);
?>

<?php if ($is_logged_in): ?>
    <!-- 已登录状态 -->
    <div class="alert alert-info">
        <strong>✅ 您已登录</strong><br>
        用户名：<?= htmlspecialchars($_SESSION['username']) ?><br>
        角色：<?= htmlspecialchars($_SESSION['role']) ?>
    </div>

    <div class="card">
        <h3>👤 个人信息</h3>
        <p>欢迎，<?= htmlspecialchars($_SESSION['username']) ?>！</p>
        <p>您可以访问以下功能：</p>
        <ul style="line-height: 2; margin-left: 20px; margin-top: 15px;">
            <li>📁 <a href="?page=files">浏览和下载文件</a></li>
            <li>📤 <a href="?page=upload">上传文件到服务器</a></li>
            <li>🔧 <a href="?page=system">查看系统配置</a></li>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <li>⚙️ <a href="admin/">访问管理后台</a></li>
            <?php endif; ?>
        </ul>

        <div style="margin-top: 20px;">
            <a href="?page=logout" class="btn" style="background: #e74c3c;">退出登录</a>
        </div>
    </div>

    <?php if ($_SESSION['role'] === 'admin'): ?>
    <div class="card" style="background: #e8f5e9; border-color: #4caf50;">
        <h3>🔑 管理员特权</h3>
        <p style="line-height: 1.8;">
            作为管理员，您拥有以下额外权限：<br>
            • 访问数据库管理界面<br>
            • 查看完整系统日志<br>
            • 修改系统配置<br>
            • 管理用户账户<br>
            • 直接访问文件服务器配置<br>
        </p>

        <div style="margin-top: 15px; padding: 15px; background: #fff9e6; border-radius: 5px;">
            <strong>📌 快速访问：</strong><br>
            <code style="display: block; margin-top: 10px; background: white; padding: 10px; border-radius: 3px;">
                数据库: mysql -h <?= getenv('DB_HOST') ?: 'mysql' ?> -u webuser -pwebpass123 company_db
            </code>
            <code style="display: block; margin-top: 10px; background: white; padding: 10px; border-radius: 3px;">
                SMB配置: /usr/local/samba/etc/smb.conf (容器: target_samba)
            </code>
        </div>
    </div>
    <?php endif; ?>

<?php else: ?>
    <!-- 登录表单 -->
    <h2>🔐 用户登录</h2>

    <?php if ($login_error): ?>
    <div class="alert alert-danger">
        <strong>❌ 登录失败：</strong><br>
        <?= htmlspecialchars($login_error) ?>
    </div>
    <?php endif; ?>

    <?php if ($login_message): ?>
    <div class="alert alert-info">
        <strong>✅ <?= htmlspecialchars($login_message) ?></strong><br>
        正在跳转...
        <script>
            setTimeout(function() {
                window.location.href = '?page=home';
            }, 2000);
        </script>
    </div>
    <?php endif; ?>

    <div class="alert alert-info">
        <strong>ℹ️ 说明：</strong> 请使用您的公司账号登录。首次使用请联系IT部门开通账号。
    </div>

    <div class="card">
        <h3>登录表单</h3>
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username">用户名：</label>
                <input type="text" name="username" id="username" placeholder="请输入用户名" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">密码：</label>
                <input type="password" name="password" id="password" placeholder="请输入密码" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn" style="width: 100%;">登录</button>
            </div>

            <div style="text-align: center; margin-top: 15px;">
                <a href="?page=reset" style="color: #667eea; text-decoration: none;">忘记密码？</a>
            </div>
        </form>
    </div>

    <div class="card" style="background: #fff3cd; border-color: #ffc107;">
        <h3>💡 测试账号</h3>
        <p style="margin-bottom: 15px;">以下是测试环境可用的账号（生产环境请勿使用）：</p>
        <table style="width: auto;">
            <thead>
                <tr>
                    <th>用户名</th>
                    <th>密码</th>
                    <th>角色</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background: #ffe082;">
                    <td><code>admin</code></td>
                    <td><code>admin123</code></td>
                    <td><strong>管理员</strong></td>
                </tr>
                <tr>
                    <td><code>john</code></td>
                    <td><code>john2023</code></td>
                    <td>普通用户</td>
                </tr>
                <tr>
                    <td><code>alice</code></td>
                    <td><code>alice456</code></td>
                    <td>普通用户</td>
                </tr>
                <tr>
                    <td><code>bob</code></td>
                    <td><code>qwerty</code></td>
                    <td>普通用户</td>
                </tr>
                <tr>
                    <td><code>test</code></td>
                    <td><code>test</code></td>
                    <td>普通用户</td>
                </tr>
            </tbody>
        </table>

        <p style="margin-top: 15px; font-size: 0.9em; color: #856404;">
            ⚠️ 注意：密码使用MD5加密存储（不安全，仅用于测试）
        </p>
    </div>

    <div class="card" style="background: #f5f5f5;">
        <h3>🔍 SQL注入测试？</h3>
        <p style="line-height: 1.8; color: #666;">
            看起来像是有SQL注入漏洞？其实并没有，我们使用了预编译语句。<br>
            不过，如果你想测试其他漏洞，可以尝试：<br><br>

            <strong>提示 1：</strong> Web层面的攻击可能是兔子洞...<br>
            <strong>提示 2：</strong> 真正的问题不在Web服务器上<br>
            <strong>提示 3：</strong> 看看系统信息页面，那里有更有趣的东西<br>
            <strong>提示 4：</strong> 文件服务器才是关键！<br>
        </p>
    </div>

    <div class="card" style="background: #e3f2fd; border-color: #2196f3;">
        <h3>📚 相关资源</h3>
        <ul style="line-height: 2; margin-left: 20px;">
            <li><a href="?page=home" style="color: #1976d2;">返回首页</a></li>
            <li><a href="?page=files" style="color: #1976d2;">浏览文件（无需登录）</a></li>
            <li><a href="?page=system" style="color: #1976d2;">查看系统信息（重要！）</a></li>
            <li><a href="admin/" style="color: #1976d2;">管理后台入口</a></li>
        </ul>
    </div>
<?php endif; ?>

<!-- 隐藏线索：登录功能本身不是重点，引导到文件服务器 -->
<!-- 真正的漏洞在Samba服务，不在Web应用 -->
