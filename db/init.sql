-- 文件管理系统数据库初始化脚本
-- 创建用户表

CREATE DATABASE IF NOT EXISTS filemanager;
USE filemanager;

-- 用户表（包含弱口令）
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 文件信息表
CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    filesize BIGINT,
    upload_user VARCHAR(50),
    upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT
);

-- 系统日志表
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    log_level VARCHAR(20),
    message TEXT,
    details TEXT
);

-- 系统配置表（隐藏提示信息）
CREATE TABLE IF NOT EXISTS system_config (
    config_key VARCHAR(100) PRIMARY KEY,
    config_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 插入测试用户（弱口令）
INSERT INTO users (username, password, email, role) VALUES
('admin', 'admin123', 'admin@techcorp.local', 'admin'),
('guest', 'guest', 'guest@techcorp.local', 'user'),
('john', 'john2024', 'john@techcorp.local', 'user'),
('developer', 'dev123', 'dev@techcorp.local', 'developer');

-- 插入文件信息
INSERT INTO files (filename, filepath, filesize, upload_user, description) VALUES
('company_report_2024.pdf', '/shared/documents/report.pdf', 2048576, 'admin', '2024年度公司报告'),
('employee_handbook.docx', '/shared/documents/handbook.docx', 524288, 'admin', '员工手册'),
('backup_config.txt', '/shared/backup/config.txt', 4096, 'developer', '系统配置备份文件'),
('server_notes.txt', '/shared/notes/server.txt', 8192, 'developer', '服务器维护笔记 - 重要！'),
('README.md', '/shared/README.md', 2048, 'admin', '共享文件系统使用说明');

-- 插入系统日志（提供线索）
INSERT INTO system_logs (log_level, message, details) VALUES
('INFO', '系统启动', 'Web服务器启动成功，端口: 80'),
('INFO', '文件共享服务启动', 'Samba服务已启动，共享目录: /home/share'),
('WARNING', '安全扫描提醒', '检测到 Samba 版本较旧，建议升级到最新版本'),
('INFO', '用户登录', '用户 developer 从 192.168.1.100 登录成功'),
('ERROR', '文件访问失败', '尝试访问 /shared/confidential/ 失败，权限不足'),
('WARNING', '弱密码警告', '检测到多个账户使用弱密码，请尽快修改'),
('INFO', '备份任务', '系统配置已备份至共享文件服务器 SMB://192.168.1.10/myshare');

-- 插入系统配置（关键提示信息）
INSERT INTO system_config (config_key, config_value, description) VALUES
('file_storage_type', 'samba_share', '文件存储类型：Samba网络共享'),
('samba_server', '192.168.1.10', 'Samba服务器IP地址（内部网络）'),
('samba_version', '4.6.3', 'Samba服务版本号 - 注意：此版本存在已知问题'),
('samba_share_name', 'myshare', 'Samba共享名称'),
('samba_path', '/home/share', 'Samba共享路径'),
('backup_location', 'smb://fileserver/myshare/backup', '备份文件存储位置'),
('admin_notes', '最近服务器有点不稳定，IT部门说是文件共享服务的问题，但一直没时间修...', '管理员备注'),
('security_audit_date', '2017-05-01', '上次安全审计日期 - 已过期'),
('pending_updates', 'Samba服务需要紧急更新！CVE补丁尚未应用。', '待处理的更新列表'),
('it_contact', 'developer', 'IT技术支持联系人');

-- 创建一个视图，方便查询系统信息
CREATE VIEW system_info AS
SELECT
    'Samba File Server' AS service_name,
    '4.6.3' AS version,
    '445' AS port,
    'VULNERABLE' AS status,
    '需要立即更新到最新版本' AS notes
UNION ALL
SELECT
    'Web Application' AS service_name,
    '1.0' AS version,
    '80' AS port,
    'RUNNING' AS status,
    '文件管理系统正常运行' AS notes
UNION ALL
SELECT
    'MySQL Database' AS service_name,
    '5.7' AS version,
    '3306' AS port,
    'RUNNING' AS status,
    '数据库服务正常' AS notes;

-- 创建存储过程（额外的隐藏信息）
DELIMITER //
CREATE PROCEDURE get_server_info()
BEGIN
    SELECT
        'Samba共享服务器运行在445端口' AS hint1,
        '当前Samba版本: 4.6.3 (2017年发布)' AS hint2,
        '建议检查该版本是否存在安全漏洞' AS hint3,
        'CVE数据库可能有相关信息' AS hint4;
END //
DELIMITER ;

-- 授权
GRANT ALL PRIVILEGES ON filemanager.* TO 'fileuser'@'%';
FLUSH PRIVILEGES;
