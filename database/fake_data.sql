USE filehub;

-- Insert fake users
INSERT INTO users (username, password_hash, email, role) VALUES
('admin', '$2y$10$fake_hash_not_crackable', 'admin@company.local', 'admin'),
('jsmith', '$2y$10$another_fake_hash', 'jsmith@company.local', 'user'),
('mjones', '$2y$10$yet_another_fake', 'mjones@company.local', 'user'),
('sysadmin', '$2y$10$impossible_to_crack', 'sysadmin@company.local', 'admin');

-- Insert file records (pointing to SMB shares)
INSERT INTO files (filename, filepath, filesize, owner_id) VALUES
('company_policy.pdf', '\\\\fileserver\\myshare\\company_policy.pdf', 2048576, 1),
('Q4_report.xlsx', '\\\\fileserver\\InternalBackup\\Q4_report.xlsx', 512000, 1),
('meeting_notes.docx', '\\\\fileserver\\myshare\\meeting_notes.docx', 102400, 2),
('backup_config.tar.gz', '\\\\fileserver\\InternalBackup\\backup_config.tar.gz', 5242880, 4);

-- Insert fake logs
INSERT INTO logs (action, user_id, ip_address, details) VALUES
('login_success', 1, '192.168.1.100', 'Admin logged in'),
('file_upload', 2, '192.168.1.105', 'Uploaded meeting_notes.docx'),
('smb_migration', 4, '127.0.0.1', 'Migrated files from old NFS to Samba shares'),
('security_scan', 4, '127.0.0.1', 'Ran vulnerability scan - no critical issues found'),
('config_change', 4, '127.0.0.1', 'Updated Samba configuration to version 4.6.3');

-- Insert secrets with clues
INSERT INTO secrets (key_name, value, hint) VALUES
('api_key', 'sk_fake_12345abcdef', 'Not the real secret'),
('backup_location', 'Q2hlY2sgdGhlIG5ldHdvcmsgc2hhcmUgc2VydmljZQ==', 'Base64 encoded message'),
('legacy_system', 'Old file server IP was 10.0.0.50, migrated to Samba', 'Historical info'),
('migration_notes', 'Samba 4.6.3 chosen for compatibility. Standard ports used.', 'Recent changes');

-- Admin notes with hints
INSERT INTO admin_notes (title, content, priority) VALUES
('Migration Complete', 'Successfully migrated all file shares to Samba. Using version 4.6.3 for stability.', 'INFO'),
('Security Reminder', 'Remember to patch all services regularly. Check CVE databases monthly.', 'MEDIUM'),
('Backup Strategy', 'All files now stored on SMB shares. Accessible via \\\\fileserver\\myshare', 'HIGH'),
('TODO', 'Remove debug endpoints, audit guest access permissions, update documentation', 'HIGH');

-- Grant permissions (limited)
GRANT SELECT ON filehub.* TO 'webapp'@'%';
GRANT INSERT ON filehub.logs TO 'webapp'@'%';
FLUSH PRIVILEGES;
