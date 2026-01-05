<?php
// 文件管理页面 - 显示公司共享文件列表

// 数据库连接
$db_host = getenv('DB_HOST') ?: 'mysql';
$db_user = getenv('DB_USER') ?: 'webuser';
$db_pass = getenv('DB_PASS') ?: 'webpass123';
$db_name = getenv('DB_NAME') ?: 'company_db';

$conn = null;
$files = [];
$error = null;

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        throw new Exception("数据库连接失败: " . $conn->connect_error);
    }

    // 查询文件列表
    $sql = "SELECT f.*, u.username FROM files f
            LEFT JOIN users u ON f.upload_by = u.id
            ORDER BY f.upload_time DESC";

    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<h2>📁 文件管理中心</h2>

<?php if ($error): ?>
<div class="alert alert-danger">
    <strong>错误：</strong> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="alert alert-info">
    <strong>ℹ️ 说明：</strong> 以下是存储在公司文件服务器上的共享文件。所有文件实际存储位置：<code>//fileserver/myshare</code>
</div>

<div class="card">
    <h3>共享文件列表</h3>

    <?php if (empty($files)): ?>
        <p style="text-align: center; padding: 40px; color: #999;">
            📂 暂无文件，请先上传文件
        </p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>文件名</th>
                    <th>大小</th>
                    <th>上传者</th>
                    <th>上传时间</th>
                    <th>描述</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($file['filename']) ?></strong>
                    </td>
                    <td>
                        <?= number_format($file['filesize'] / 1024, 2) ?> KB
                    </td>
                    <td>
                        <?= htmlspecialchars($file['username'] ?? 'Unknown') ?>
                    </td>
                    <td>
                        <?= date('Y-m-d H:i', strtotime($file['upload_time'])) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($file['description']) ?>
                    </td>
                    <td>
                        <a href="<?= htmlspecialchars($file['filepath']) ?>" class="btn" style="font-size: 0.85em; padding: 5px 10px;">
                            下载
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card" style="background: #fff3cd; border-color: #ffc107;">
    <h3>⚠️ 访问提示</h3>
    <p style="line-height: 1.8;">
        <strong>如果Web下载不可用，您可以：</strong><br><br>

        <strong>方案一：直接访问SMB共享</strong><br>
        <code style="background: white; padding: 5px 10px; display: inline-block; margin: 5px 0;">
            smbclient //&lt;服务器IP&gt;/myshare -N
        </code><br><br>

        <strong>方案二：挂载到本地</strong><br>
        <code style="background: white; padding: 5px 10px; display: inline-block; margin: 5px 0;">
            mount -t cifs //&lt;服务器IP&gt;/myshare /mnt/share -o guest
        </code><br><br>

        <strong>服务器信息：</strong><br>
        • 主机名: <code>fileserver</code> (或使用目标IP)<br>
        • 端口: <code>445</code> (SMB默认端口)<br>
        • 共享名: <code>myshare</code><br>
        • 认证方式: Guest访问（无需密码）<br><br>

        <em style="color: #856404;">提示：系统日志中有更多关于文件服务器的信息</em>
    </p>
</div>

<div class="card">
    <h3>🔍 文件服务器详情</h3>
    <table style="width: auto;">
        <tr>
            <td><strong>服务类型</strong></td>
            <td>Samba/CIFS 文件共享</td>
        </tr>
        <tr>
            <td><strong>服务器版本</strong></td>
            <td>Samba 4.6.3</td>
        </tr>
        <tr>
            <td><strong>协议</strong></td>
            <td>SMB/CIFS (Port 445)</td>
        </tr>
        <tr>
            <td><strong>共享路径</strong></td>
            <td>/home/share</td>
        </tr>
        <tr>
            <td><strong>访问权限</strong></td>
            <td>Read/Write (Guest)</td>
        </tr>
        <tr>
            <td><strong>容器名称</strong></td>
            <td>target_samba</td>
        </tr>
    </table>
</div>

<div style="margin-top: 20px;">
    <a href="?page=upload" class="btn" style="background: #27ae60;">
        ➕ 上传新文件
    </a>
    <a href="?page=system" class="btn" style="background: #3498db;">
        🔧 查看系统配置
    </a>
</div>

<?php
if ($conn) {
    $conn->close();
}
?>

<!-- 隐藏线索：SMB版本有已知漏洞 -->
<!-- CVE提示：Samba 4.6.3 是一个存在安全问题的版本 -->
