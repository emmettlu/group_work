-- 创建用户表
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    department VARCHAR(50),
    role VARCHAR(50),
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- 创建登录日志表
CREATE TABLE login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 创建搜索日志表
CREATE TABLE search_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    search_term VARCHAR(255),
    ip_address VARCHAR(45),
    search_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    results_count INT DEFAULT 0
);

-- 创建文件上传日志表
CREATE TABLE upload_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255),
    file_size INT,
    upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploader_ip VARCHAR(45),
    security_scan_result VARCHAR(50) DEFAULT 'PASSED'
);

-- 创建系统配置表
CREATE TABLE system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    description TEXT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 插入测试数据
INSERT INTO users (username, password, email, department, role, active) VALUES
('admin', 'AdminPass123!', 'admin@company.com', 'IT', 'Administrator', 1),
('johndoe', 'JohnDoe456', 'john.doe@company.com', 'HR', 'HR Manager', 1),
('janesmith', 'JaneSmith789', 'jane.smith@company.com', 'Finance', 'Accountant', 1),
('mikejohnson', 'MikeJ101', 'mike.johnson@company.com', 'IT', 'Developer', 1),
('sarahwilliams', 'SarahW202', 'sarah.williams@company.com', 'Security', 'Security Analyst', 1),
('robertbrown', 'RobertB303', 'robert.brown@company.com', 'Operations', 'Manager', 1),
('lindadavis', 'LindaD404', 'linda.davis@company.com', 'IT', 'System Admin', 1),
('davidwilson', 'DavidW505', 'david.wilson@company.com', 'Finance', 'CFO', 1),
('susanjones', 'SusanJ606', 'susan.jones@company.com', 'HR', 'Recruiter', 1),
('paulmartinez', 'PaulM707', 'paul.martinez@company.com', 'Security', 'Penetration Tester', 1),
('guest', 'guest', 'guest@company.com', 'Guest', 'Guest User', 1),
('testuser', 'test123', 'test@company.com', 'Testing', 'Test Account', 1),
('backup_user', 'BackupPass789', 'backup@company.com', 'IT', 'Backup Admin', 1),
('sambatest', 'sambapass', 'samba@company.com', 'File Services', 'Samba User', 1);

-- 插入系统配置数据
INSERT INTO system_config (config_key, config_value, description) VALUES
('system_version', '2.1.4', 'Current system version'),
('security_level', 'HIGH', 'Current security level setting'),
('max_login_attempts', '3', 'Maximum failed login attempts before lockout'),
('session_timeout', '900', 'Session timeout in seconds'),
('file_upload_max_size', '10485760', 'Maximum file upload size in bytes'),
('allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx,txt,csv', 'Allowed file types for upload'),
('samba_server_path', '//fileserver/secure', 'Primary Samba server path'),
('backup_server_path', '//fileserver/backup', 'Backup server path'),
('admin_contact', 'admin@company.com', 'Administrator contact email'),
('security_team_contact', 'security@company.com', 'Security team contact email'),
('database_backup_schedule', 'daily_02:00', 'Database backup schedule'),
('system_maintenance_window', 'sunday_02:00-04:00', 'System maintenance window'),
('vulnerability_scan_schedule', 'weekly_friday_18:00', 'Vulnerability scan schedule'),
('incident_response_team', 'security@company.com,admin@company.com', 'Incident response team contacts'),
('file_server_version', '4.6.4', 'File server version (misleading - actually 4.6.3)'),
('last_security_update', '2024-01-15', 'Last security update date'),
('next_penetration_test', '2024-03-01', 'Next scheduled penetration test');

-- 插入一些误导性的数据
INSERT INTO users (username, password, email, department, role, active) VALUES
('ctf_flag', 'flag{sambacry_exploit_2017}', 'flag@ctf.com', 'CTF', 'Flag User', 1),
('vulnerable_user', 'password123', 'vuln@company.com', 'Legacy', 'Old System', 1),
('system_admin', 'admin123', 'sysadmin@company.com', 'IT', 'System Administrator', 1),
('backup_admin', 'backup123', 'backupadmin@company.com', 'IT', 'Backup Administrator', 1),
('file_server_admin', 'fileadmin123', 'fileadmin@company.com', 'File Services', 'File Server Admin', 1);

-- 创建一些假的日志条目
INSERT INTO login_logs (user_id, ip_address, login_time, success) VALUES
(1, '192.168.1.100', DATE_SUB(NOW(), INTERVAL 1 DAY), 1),
(2, '192.168.1.101', DATE_SUB(NOW(), INTERVAL 2 DAY), 1),
(3, '192.168.1.102', DATE_SUB(NOW(), INTERVAL 3 DAY), 0),
(4, '192.168.1.103', DATE_SUB(NOW(), INTERVAL 4 DAY), 1),
(5, '192.168.1.104', DATE_SUB(NOW(), INTERVAL 5 DAY), 1);

INSERT INTO search_logs (search_term, ip_address, search_time, results_count) VALUES
('admin', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 1 HOUR), 2),
('john', '192.168.1.101', DATE_SUB(NOW(), INTERVAL 2 HOUR), 1),
('sql', '192.168.1.105', DATE_SUB(NOW(), INTERVAL 3 HOUR), 0),
('samba', '192.168.1.106', DATE_SUB(NOW(), INTERVAL 4 HOUR), 1),
('file server', '192.168.1.107', DATE_SUB(NOW(), INTERVAL 5 HOUR), 3);

INSERT INTO upload_logs (filename, file_size, upload_time, uploader_ip, security_scan_result) VALUES
('report.pdf', 2048576, DATE_SUB(NOW(), INTERVAL 1 DAY), '192.168.1.100', 'PASSED'),
('image.jpg', 524288, DATE_SUB(NOW(), INTERVAL 2 DAY), '192.168.1.101', 'PASSED'),
('data.csv', 1048576, DATE_SUB(NOW(), INTERVAL 3 DAY), '192.168.1.102', 'PASSED'),
('presentation.pptx', 4194304, DATE_SUB(NOW(), INTERVAL 4 DAY), '192.168.1.103', 'PASSED'),
('backup.zip', 8388608, DATE_SUB(NOW(), INTERVAL 5 DAY), '192.168.1.104', 'QUARANTINED');