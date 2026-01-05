<?php
// 系统信息页面 - 显示服务器配置和关键信息（包含通向漏洞的线索）

// 数据库连接
$db_host = getenv('DB_HOST') ?: 'mysql';
$db_user = getenv('DB_USER') ?: 'webuser';
$db_pass = getenv('DB_PASS') ?: 'webpass123';
$db_name = getenv('DB_NAME') ?: 'company_db';

$conn = null;
$config = [];
$logs = [];
$admin_notes = [];
$error = null;

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        throw new Exception("数据库连接失败: " . $conn->connect_error);
    }

    // 查询系统配置
    $sql = "SELECT * FROM system_config ORDER BY id";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $config[$row['config_key']] = $row;
        }
    }

    // 查询系统日志（最近10条）
    $sql = "SELECT * FROM system_logs ORDER BY timestamp DESC LIMIT 10";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }

    // 查询管理员备注
    $sql = "SELECT * FROM admin_notes ORDER BY priority DESC, created_at DESC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $admin_notes[] = $row;
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<h2>🔧 系统信息与配置</h2>

<?php if ($error): ?>
<div class="alert alert-danger">
    <strong>错误：</strong> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="alert alert-info">
    <strong>ℹ️ 说明：</strong> 本页面显示系统配置信息和运行状态。如需修改配置，请联系系统管理员。
</div>

<!-- PHP系统信息 -->
<div class="card">
    <h3>📊 PHP 环境信息</h3>
    <table style="width: auto;">
        <tr>
            <td><strong>PHP 版本</strong></td>
            <td><?= phpversion() ?></td>
        </tr>
        <tr>
            <td><strong>服务器软件</strong></td>
            <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td>
        </tr>
        <tr>
            <td><strong>服务器IP</strong></td>
            <td><?= $_SERVER['SERVER_ADDR'] ?? 'Unknown' ?></td>
        </tr>
        <tr>
            <td><strong>服务器端口</strong></td>
            <td><?= $_SERVER['SERVER_PORT'] ?? 'Unknown' ?></td>
        </tr>
        <tr>
            <td><strong>文档根目录</strong></td>
            <td><?= $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' ?></td>
        </tr>
        <tr>
            <td><strong>系统时间</strong></td>
            <td><?= date('Y-m-d H:i:s') ?></td>
        </tr>
    </table>
</div>

<!-- 系统配置信息 -->
<div class="card">
    <h3>⚙️ 系统配置</h3>
    <?php if (empty($config)): ?>
        <p style="color: #999;">无法读取配置信息</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>配置项</th>
                    <th>配置值</th>
                    <th>说明</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($config as $key => $item): ?>
                <tr <?= (strpos($key, 'smb') !== false || $key === 'secret_note') ? 'style="background: #fff9e6;"' : '' ?>>
                    <td><strong><?= htmlspecialchars($item['config_key']) ?></strong></td>
                    <td>
                        <?php if (strpos($key, 'password') !== false): ?>
                            <code>******</code>
                        <?php else: ?>
                            <code><?= htmlspecialchars($item['config_value']) ?></code>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- 文件服务器详细配置 -->
<div class="card" style="background: #fff3cd; border-color: #ffc107;">
    <h3>📁 文件服务器配置（重要）</h3>
    <p style="margin-bottom: 15px;">以下是后端文件服务器的详细配置信息：</p>

    <table style="width: auto;">
        <tr style="background: #ffe082;">
            <td><strong>服务类型</strong></td>
            <td>Samba/CIFS 文件共享服务</td>
        </tr>
        <tr>
            <td><strong>服务器地址</strong></td>
            <td><code><?= htmlspecialchars($config['smb_server']['config_value'] ?? 'fileserver') ?></code></td>
        </tr>
        <tr style="background: #ffe082;">
            <td><strong>服务端口</strong></td>
            <td><code><?= htmlspecialchars($config['smb_port']['config_value'] ?? '445') ?></code></td>
        </tr>
        <tr>
            <td><strong>共享名称</strong></td>
            <td><code><?= htmlspecialchars($config['smb_share']['config_value'] ?? 'myshare') ?></code></td>
        </tr>
        <tr style="background: #ffe082;">
            <td><strong>Samba 版本</strong></td>
            <td><code style="color: #d32f2f; font-weight: bold;">4.6.3</code> <span style="color: #d32f2f;">⚠️ 版本过旧</span></td>
        </tr>
        <tr>
            <td><strong>认证方式</strong></td>
            <td>Guest (map to guest = Bad User)</td>
        </tr>
        <tr>
            <td><strong>访问权限</strong></td>
            <td>Read/Write (guest ok = yes)</td>
        </tr>
        <tr>
            <td><strong>容器名称</strong></td>
            <td><code>target_samba</code></td>
        </tr>
        <tr style="background: #ffe082;">
            <td><strong>共享路径</strong></td>
            <td><code>/home/share</code></td>
        </tr>
    </table>

    <div style="margin-top: 20px; padding: 15px; background: #ffebee; border-radius: 5px; border-left: 4px solid #f44336;">
        <strong style="color: #c62828;">🔴 安全警告：</strong><br>
        <p style="margin-top: 10px; line-height: 1.8; color: #c62828;">
            检测到当前Samba版本 <strong>4.6.3</strong> 存在已知安全漏洞！<br>
            此版本发布于2017年，已有多个CVE安全公告。<br>
            <strong>强烈建议尽快升级到最新版本！</strong><br><br>
            <em style="font-size: 0.9em;">已通知IT部门，计划在下次维护窗口期间升级。</em>
        </p>
    </div>
</div>

<!-- 管理员备注 -->
<?php if (!empty($admin_notes)): ?>
<div class="card" style="background: #e3f2fd; border-color: #2196f3;">
    <h3>📝 管理员备注</h3>
    <table>
        <thead>
            <tr>
                <th>优先级</th>
                <th>标题</th>
                <th>内容</th>
                <th>创建时间</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admin_notes as $note): ?>
            <tr <?= $note['priority'] === 'CRITICAL' ? 'style="background: #ffcdd2;"' : '' ?>>
                <td>
                    <span style="
                        padding: 4px 8px;
                        border-radius: 3px;
                        font-weight: bold;
                        font-size: 0.85em;
                        <?php
                        switch($note['priority']) {
                            case 'CRITICAL':
                                echo 'background: #f44336; color: white;';
                                break;
                            case 'HIGH':
                                echo 'background: #ff9800; color: white;';
                                break;
                            case 'MEDIUM':
                                echo 'background: #ffc107; color: black;';
                                break;
                            default:
                                echo 'background: #9e9e9e; color: white;';
                        }
                        ?>
                    ">
                        <?= htmlspecialchars($note['priority']) ?>
                    </span>
                </td>
                <td><strong><?= htmlspecialchars($note['title']) ?></strong></td>
                <td style="max-width: 400px;"><?= htmlspecialchars($note['content']) ?></td>
                <td><?= date('Y-m-d H:i', strtotime($note['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- 系统日志 -->
<?php if (!empty($logs)): ?>
<div class="card">
    <h3>📜 系统日志（最近10条）</h3>
    <table>
        <thead>
            <tr>
                <th>时间</th>
                <th>类型</th>
                <th>消息</th>
                <th>IP地址</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr <?= $log['log_type'] === 'ERROR' ? 'style="background: #ffebee;"' : '' ?>>
                <td><?= date('Y-m-d H:i:s', strtotime($log['timestamp'])) ?></td>
                <td>
                    <span style="
                        padding: 4px 8px;
                        border-radius: 3px;
                        font-size: 0.85em;
                        font-weight: bold;
                        <?php
                        switch($log['log_type']) {
                            case 'ERROR':
                                echo 'background: #f44336; color: white;';
                                break;
                            case 'WARNING':
                                echo 'background: #ff9800; color: white;';
                                break;
                            case 'INFO':
                                echo 'background: #2196f3; color: white;';
                                break;
                            default:
                                echo 'background: #4caf50; color: white;';
                        }
                        ?>
                    ">
                        <?= htmlspecialchars($log['log_type']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($log['message']) ?></td>
                <td><?= htmlspecialchars($log['ip_address']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- 网络连接测试 -->
<div class="card">
    <h3>🌐 网络连接测试</h3>
    <p style="margin-bottom: 15px;">使用以下命令测试与文件服务器的连接：</p>

    <p><strong>测试端口连通性：</strong></p>
    <pre style="background: #2c3e50; color: #2ecc71; padding: 15px; border-radius: 5px; overflow-x: auto;">
# 使用 netcat
nc -zv <span style="color: #e74c3c;">TARGET_IP</span> 445

# 使用 nmap
nmap -p 445 <span style="color: #e74c3c;">TARGET_IP</span> -sV

# 使用 telnet
telnet <span style="color: #e74c3c;">TARGET_IP</span> 445
    </pre>

    <p><strong>列出SMB共享：</strong></p>
    <pre style="background: #2c3e50; color: #2ecc71; padding: 15px; border-radius: 5px; overflow-x: auto;">
smbclient -L //<span style="color: #e74c3c;">TARGET_IP</span> -N
    </pre>

    <p><strong>连接到共享目录：</strong></p>
    <pre style="background: #2c3e50; color: #2ecc71; padding: 15px; border-radius: 5px; overflow-x: auto;">
smbclient //<span style="color: #e74c3c;">TARGET_IP</span>/myshare -N
    </pre>

    <p><strong>获取Samba版本信息：</strong></p>
    <pre style="background: #2c3e50; color: #2ecc71; padding: 15px; border-radius: 5px; overflow-x: auto;">
# 使用 Metasploit
msfconsole -q -x "use auxiliary/scanner/smb/smb_version; set RHOSTS <span style="color: #e74c3c;">TARGET_IP</span>; run; exit"

# 使用 nmap 脚本
nmap -p 445 --script smb-protocols,smb-os-discovery <span style="color: #e74c3c;">TARGET_IP</span>
    </pre>
</div>

<!-- 安全建议 -->
<div class="card" style="background: #ffebee; border-color: #f44336;">
    <h3>🔒 安全建议</h3>
    <ol style="line-height: 2; margin-left: 20px;">
        <li><strong style="color: #c62828;">立即升级Samba版本！</strong> 当前版本4.6.3存在严重安全漏洞</li>
        <li>禁用Guest访问，改用基于用户名/密码的身份验证</li>
        <li>配置防火墙规则，限制SMB端口访问</li>
        <li>启用SMB签名和加密</li>
        <li>定期审计共享文件权限</li>
        <li>监控异常的SMB连接和文件访问</li>
    </ol>

    <div style="margin-top: 20px; padding: 15px; background: white; border-radius: 5px;">
        <strong style="color: #d32f2f;">⚠️ 关于 CVE-2017-7494：</strong><br>
        <p style="margin-top: 10px; line-height: 1.8; color: #555;">
            Samba 版本 3.5.0 至 4.6.4 之前的版本存在远程代码执行漏洞。<br>
            攻击者可以通过上传恶意共享库文件并触发加载，从而执行任意代码。<br>
            <strong style="color: #c62828;">当前版本 4.6.3 受此漏洞影响！</strong><br><br>
            <em style="font-size: 0.9em;">参考：<a href="https://www.samba.org/samba/security/CVE-2017-7494.html" target="_blank" style="color: #1976d2;">https://www.samba.org/samba/security/CVE-2017-7494.html</a></em>
        </p>
    </div>
</div>

<!-- 调试信息 -->
<div class="card" style="background: #f5f5f5; border-color: #9e9e9e;">
    <h3>🐛 调试信息</h3>
    <p style="margin-bottom: 15px; color: #666;">
        <em>以下信息仅在开发环境中显示，生产环境请关闭 debug_mode</em>
    </p>

    <table style="width: auto; font-family: monospace; font-size: 0.9em;">
        <tr>
            <td><strong>DEBUG_MODE</strong></td>
            <td><?= htmlspecialchars($config['debug_mode']['config_value'] ?? 'false') ?></td>
        </tr>
        <tr>
            <td><strong>DB_HOST</strong></td>
            <td><?= $db_host ?></td>
        </tr>
        <tr>
            <td><strong>DB_NAME</strong></td>
            <td><?= $db_name ?></td>
        </tr>
        <tr>
            <td><strong>SMB_SERVER</strong></td>
            <td><?= htmlspecialchars($config['smb_server']['config_value'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <td><strong>BACKUP_PATH</strong></td>
            <td><?= htmlspecialchars($config['backup_server']['config_value'] ?? 'N/A') ?></td>
        </tr>
    </table>

    <p style="margin-top: 20px; font-size: 0.9em; color: #666;">
        提示：更多调试信息可以访问 <code>/admin/debug.php</code>（需要管理员权限）
    </p>
</div>

<div style="margin-top: 20px;">
    <a href="?page=home" class="btn">🏠 返回首页</a>
    <a href="?page=files" class="btn" style="background: #27ae60;">📁 文件管理</a>
</div>

<?php
if ($conn) {
    $conn->close();
}
?>

<!-- 隐藏的线索和提示 -->
<!-- 关键信息：Samba 4.6.3, Guest访问, 445端口, CVE-2017-7494 -->
<!-- 攻击者应该已经收集到足够的信息来进行漏洞利用 -->
<!-- 下一步：查找 CVE-2017-7494 的 exploit 并尝试攻击 -->
