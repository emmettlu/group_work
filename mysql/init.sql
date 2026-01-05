-- 公司数据库初始化脚本
-- 这个数据库看起来是一个普通的公司内部系统

CREATE DATABASE IF NOT EXISTS company_db;
USE company_db;

-- 用户表
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 文件管理表
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500),
    filesize INT,
    upload_by INT,
    upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    FOREIGN KEY (upload_by) REFERENCES users(id)
);

-- 系统日志表
CREATE TABLE system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    log_type VARCHAR(50),
    message TEXT,
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 配置表 (关键提示隐藏在这里)
CREATE TABLE system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 插入普通用户数据 (迷惑用)
INSERT INTO users (username, password, email, role) VALUES
('admin', MD5('admin123'), 'admin@company.local', 'admin'),
('john', MD5('john2023'), 'john@company.local', 'user'),
('alice', MD5('alice456'), 'alice@company.local', 'user'),
('bob', MD5('qwerty'), 'bob@company.local', 'user'),
('test', MD5('test'), 'test@company.local', 'user');

-- 插入文件记录 (迷惑和提示并存)
INSERT INTO files (filename, filepath, filesize, upload_by, description) VALUES
('company_policy.pdf', '/uploads/docs/policy.pdf', 2048576, 1, '公司规章制度'),
('salary_2023.xlsx', '/uploads/finance/salary.xlsx', 1024000, 1, '2023年工资表'),
('project_plan.docx', '/uploads/projects/plan.docx', 512000, 2, '项目计划书'),
('meeting_notes.txt', '/uploads/notes/meeting.txt', 10240, 3, '会议记录'),
('backup_guide.txt', '/uploads/docs/backup.txt', 4096, 1, '重要：服务器备份指南，请勿外传');

-- 插入系统日志 (制造真实感，同时埋下线索)
INSERT INTO system_logs (log_type, message, ip_address) VALUES
('LOGIN', 'User admin logged in successfully', '192.168.1.100'),
('LOGIN', 'User john logged in successfully', '192.168.1.105'),
('FILE_UPLOAD', 'File company_policy.pdf uploaded', '192.168.1.100'),
('ERROR', 'Failed to connect to file server at 445', '192.168.1.100'),
('WARNING', 'SMB service responding slowly', '192.168.1.1'),
('INFO', 'Backup completed to network share', '192.168.1.1'),
('ERROR', 'Web upload failed, check SMB connection', '192.168.1.105'),
('LOGIN', 'User alice logged in successfully', '192.168.1.108');

-- 插入系统配置 (关键提示藏在这里)
INSERT INTO system_config (config_key, config_value, description) VALUES
('site_name', 'TechCorp Internal Portal', '网站名称'),
('upload_max_size', '10485760', '最大上传文件大小（字节）'),
('maintenance_mode', 'false', '维护模式开关'),
('file_storage_type', 'smb', '文件存储类型：local/smb/ftp'),
('smb_server', 'fileserver', 'SMB文件服务器地址'),
('smb_port', '445', 'SMB服务端口'),
('smb_share', 'myshare', 'SMB共享名称'),
('backup_server', '//fileserver/myshare', '备份服务器路径'),
('admin_email', 'admin@company.local', '管理员邮箱'),
('debug_mode', 'true', '调试模式（开发环境）'),
('secret_note', 'All important files are now stored on our new file server. Web upload is just a frontend, real storage is on SMB share.', '系统备注');

-- 创建一个"隐藏"的管理员备忘表
CREATE TABLE admin_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    content TEXT,
    priority VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admin_notes (title, content, priority) VALUES
('服务器迁移完成', '已将所有文件迁移到新的Samba文件服务器，容器名：target_samba，端口：445', 'HIGH'),
('安全提醒', 'TODO: 升级Samba版本！当前版本4.6.3存在已知漏洞，但测试环境暂时未修复', 'CRITICAL'),
('备份策略', '每日自动备份到 //fileserver/myshare 路径，使用guest账户无密码访问', 'MEDIUM'),
('网络配置', 'Web服务(8080) -> MySQL(3306) -> Samba(445)，三层架构', 'LOW'),
('开发备注', 'PHPMyAdmin暂未部署，需要查看数据库请用命令行', 'LOW');

-- 创建一个看起来像是漏洞的表（实际是兔子洞）
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    token VARCHAR(100),
    expires_at TIMESTAMP,
    used TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO password_reset_tokens (user_id, token, expires_at, used) VALUES
(2, 'a3f5c8e9b2d1f4a6c7e8b9d0', DATE_ADD(NOW(), INTERVAL 1 HOUR), 0),
(3, 'x9y8z7w6v5u4t3s2r1q0p9o8', DATE_ADD(NOW(), INTERVAL -1 HOUR), 1);

-- 创建视图方便"发现"关键信息
CREATE VIEW server_info AS
SELECT
    config_key,
    config_value,
    description
FROM system_config
WHERE config_key LIKE '%smb%' OR config_key LIKE '%server%' OR config_key = 'secret_note';

-- 授权
GRANT ALL PRIVILEGES ON company_db.* TO 'webuser'@'%';
FLUSH PRIVILEGES;
