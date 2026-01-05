<?php
// 文件上传页面 - 伪装成文件上传功能，实际提示SMB访问

$upload_error = null;
$upload_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // 模拟文件上传处理（实际不会真正上传）
    $upload_error = "上传失败：无法连接到文件服务器 (SMB://fileserver:445/myshare)。<br>
                     请检查网络连接或直接使用SMB客户端上传文件。<br>
                     错误代码: SMB_CONNECTION_REFUSED";
}
?>

<h2>📤 文件上传</h2>

<?php if ($upload_error): ?>
<div class="alert alert-danger">
    <strong>❌ 上传失败：</strong><br>
    <?= $upload_error ?>
</div>
<?php endif; ?>

<?php if ($upload_success): ?>
<div class="alert alert-info">
    <strong>✅ 上传成功！</strong> 文件已保存到文件服务器。
</div>
<?php endif; ?>

<div class="alert alert-warning">
    <strong>⚠️ 功能维护中</strong><br>
    Web上传功能目前不稳定，建议直接使用SMB协议上传文件到文件服务器。
</div>

<div class="card">
    <h3>Web上传（当前不可用）</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="file">选择文件：</label>
            <input type="file" name="file" id="file" required>
        </div>

        <div class="form-group">
            <label for="description">文件描述：</label>
            <input type="text" name="description" id="description" placeholder="请输入文件描述">
        </div>

        <button type="submit" class="btn">上传文件</button>
    </form>

    <p style="margin-top: 20px; color: #e74c3c; font-size: 0.9em;">
        ⚠️ 注意：由于后端文件服务器配置问题，Web上传功能暂时无法使用。
    </p>
</div>

<div class="card" style="background: #e8f5e9; border-color: #4caf50;">
    <h3>✅ 推荐方案：直接使用SMB上传</h3>

    <p style="margin-bottom: 20px; line-height: 1.8;">
        由于Web上传功能不稳定，我们<strong>强烈建议</strong>您直接使用SMB协议连接到文件服务器进行文件操作。
        这是目前最可靠的方式。
    </p>

    <h4 style="margin-top: 20px; margin-bottom: 10px;">🐧 Linux/Unix 用户：</h4>

    <p><strong>方法1：使用 smbclient 命令</strong></p>
    <pre style="background: #2c3e50; color: #2ecc71; padding: 15px; border-radius: 5px; overflow-x: auto;">
# 连接到SMB共享（无需密码）
smbclient //<span style="color: #e74c3c;">TARGET_IP</span>/myshare -N

# 进入后可以使用以下命令：
smb: \> ls          # 列出文件
smb: \> put file    # 上传文件
smb: \> get file    # 下载文件
smb: \> help        # 查看帮助
    </pre>

    <p><strong>方法2：挂载SMB共享</strong></p>
    <pre style="background: #2c3e50; color: #2ecc71; padding: 15px; border-radius: 5px; overflow-x: auto;">
# 创建挂载点
mkdir -p /mnt/share

# 挂载共享目录
mount -t cifs //<span style="color: #e74c3c;">TARGET_IP</span>/myshare /mnt/share -o guest,rw

# 现在可以像本地目录一样操作
cd /mnt/share
cp /path/to/file .
    </pre>

    <h4 style="margin-top: 20px; margin-bottom: 10px;">🪟 Windows 用户：</h4>

    <p><strong>使用文件资源管理器</strong></p>
    <pre style="background: #2c3e50; color: #2ecc71; padding: 15px; border-radius: 5px;">
1. 打开文件资源管理器
2. 在地址栏输入：\\<span style="color: #e74c3c;">TARGET_IP</span>\myshare
3. 按回车键访问共享文件夹
4. 直接拖拽文件进行上传/下载
    </pre>

    <div style="background: #fff9e6; padding: 15px; margin-top: 20px; border-radius: 5px; border-left: 4px solid #ffc107;">
        <strong>📌 连接信息：</strong><br>
        • 服务器地址: 使用目标服务器的IP地址<br>
        • 端口: 445 (SMB默认端口，通常不需要指定)<br>
        • 共享名称: <code>myshare</code><br>
        • 用户名: 无需提供（Guest访问）<br>
        • 密码: 无需提供<br>
    </div>
</div>

<div class="card">
    <h3>🔧 故障排查</h3>

    <p style="line-height: 1.8;"><strong>如果连接失败，请检查：</strong></p>
    <ol style="line-height: 2; margin-left: 20px;">
        <li>目标服务器IP是否正确（可以从docker容器或系统信息页面获取）</li>
        <li>防火墙是否阻止了445端口</li>
        <li>SMB服务是否正在运行</li>
        <li>网络连接是否正常</li>
    </ol>

    <p style="margin-top: 15px;"><strong>查看容器IP地址：</strong></p>
    <pre style="background: #2c3e50; color: #2ecc71; padding: 15px; border-radius: 5px;">
docker inspect target_samba | grep IPAddress
    </pre>

    <p style="margin-top: 15px;"><strong>测试端口连通性：</strong></p>
    <pre style="background: #2c3e50; color: #2ecc71; padding: 15px; border-radius: 5px;">
nc -zv <span style="color: #e74c3c;">TARGET_IP</span> 445
nmap -p 445 <span style="color: #e74c3c;">TARGET_IP</span>
    </pre>

    <p style="margin-top: 15px;"><strong>查看SMB共享列表：</strong></p>
    <pre style="background: #2c3e50; color: #2ecc71; padding: 15px; border-radius: 5px;">
smbclient -L //<span style="color: #e74c3c;">TARGET_IP</span> -N
    </pre>
</div>

<div class="card" style="background: #ffebee; border-color: #f44336;">
    <h3>🔐 安全提示</h3>
    <p style="line-height: 1.8;">
        <strong>请注意：</strong>当前文件服务器配置为Guest访问模式，任何人都可以无密码访问。
        这仅用于测试环境，生产环境中请务必配置适当的访问控制和身份验证！<br><br>

        <em>当前Samba版本：4.6.3</em><br>
        <span style="font-size: 0.85em; color: #c62828;">
            ⚠️ IT部门提醒：此版本存在已知安全问题，已计划升级。在升级完成前，请勿在此服务器存储敏感数据。
        </span>
    </p>
</div>

<div style="margin-top: 20px;">
    <a href="?page=files" class="btn">📁 返回文件列表</a>
    <a href="?page=system" class="btn" style="background: #e74c3c;">🔍 查看系统配置</a>
</div>

<!-- 重要线索：明确指出Samba版本和Guest访问 -->
<!-- 攻击者看到这里应该开始考虑Samba漏洞利用 -->
