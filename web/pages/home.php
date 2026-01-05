<?php
// 首页内容 - 显示欢迎信息和系统公告
?>

<div class="alert alert-info">
    <strong>📢 系统公告：</strong> 欢迎使用TechCorp内部门户系统！本系统用于公司内部文件管理和资源共享。
</div>

<div class="alert alert-warning">
    <strong>⚠️ 维护通知：</strong> 文件上传功能目前正在维护中，部分上传可能失败。请联系IT部门或尝试直接访问文件服务器。
</div>

<div class="card">
    <h3>🎯 系统功能</h3>
    <ul style="line-height: 2; margin-left: 20px;">
        <li><strong>文件管理</strong> - 浏览和下载公司共享文件</li>
        <li><strong>文件上传</strong> - 上传文档到公司文件服务器</li>
        <li><strong>系统信息</strong> - 查看服务器状态和配置</li>
        <li><strong>用户管理</strong> - 管理员可访问后台进行用户管理</li>
    </ul>
</div>

<div class="card">
    <h3>📊 系统状态</h3>
    <table>
        <tr>
            <th>服务</th>
            <th>状态</th>
            <th>说明</th>
        </tr>
        <tr>
            <td>🌐 Web服务</td>
            <td style="color: green; font-weight: bold;">● 正常</td>
            <td>Apache + PHP 运行正常</td>
        </tr>
        <tr>
            <td>💾 数据库服务</td>
            <td style="color: green; font-weight: bold;">● 正常</td>
            <td>MySQL 5.7 运行正常</td>
        </tr>
        <tr>
            <td>📁 文件服务器</td>
            <td style="color: orange; font-weight: bold;">⚠ 警告</td>
            <td>Samba服务响应缓慢，建议检查</td>
        </tr>
        <tr>
            <td>🔐 认证服务</td>
            <td style="color: green; font-weight: bold;">● 正常</td>
            <td>Guest模式已启用</td>
        </tr>
    </table>
</div>

<div class="card">
    <h3>📝 快速链接</h3>
    <p style="margin-bottom: 15px;">常用功能快速访问：</p>
    <a href="?page=files" class="btn">浏览文件</a>
    <a href="?page=upload" class="btn" style="background: #27ae60;">上传文件</a>
    <a href="?page=system" class="btn" style="background: #e74c3c;">系统信息</a>
</div>

<div class="card" style="background: #fff9e6; border-color: #ffc107;">
    <h3>💡 提示</h3>
    <p style="line-height: 1.8;">
        <strong>新员工须知：</strong><br>
        1. 首次使用请先访问"系统信息"页面了解服务器配置<br>
        2. 重要文件已迁移至新的文件服务器，使用SMB协议访问<br>
        3. 如遇到上传问题，可以直接连接到后端文件服务器<br>
        4. 管理员账号：admin / admin123（仅供测试环境）<br>
        5. 更多信息请查看系统配置或联系IT支持
    </p>
</div>

<div style="margin-top: 30px; padding: 20px; background: #f0f0f0; border-radius: 5px; font-family: monospace; font-size: 0.85em;">
    <strong>系统信息：</strong><br>
    服务器时间: <?= date('Y-m-d H:i:s') ?><br>
    PHP版本: <?= phpversion() ?><br>
    服务器IP: <?= $_SERVER['SERVER_ADDR'] ?? 'Unknown' ?><br>
    客户端IP: <?= $_SERVER['REMOTE_ADDR'] ?? 'Unknown' ?><br>
</div>

<!-- 隐藏的HTML注释线索 -->
<!-- TODO: 修复文件上传接口，当前直接写入到 //fileserver/myshare -->
<!-- FIXME: Samba 4.6.3版本过旧，存在安全风险，待升级 -->
<!-- NOTE: 生产环境请关闭 debug_mode -->
