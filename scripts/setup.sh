#!/bin/bash

# 假的安全设置脚本
# 这个脚本会创建一些误导性的文件和配置

echo "Setting up enhanced security environment..."

# 创建假的系统信息
mkdir -p /home/dev
mkdir -p /home/share
mkdir -p /var/log/security

# 创建误导性的版本信息
echo "Samba Version 4.6.4 (Security Patched)" > /home/share/version.txt
echo "Last updated: $(date)" >> /home/share/version.txt

# 创建假的配置文件
cat > /home/dev/config.php << 'EOF'
<?php
// Fake configuration file
$DB_HOST = 'localhost';
$DB_USER = 'admin';
$DB_PASS = 'SuperSecurePass123!';
$DB_NAME = 'secure_database';

$SAMBA_VERSION = '4.6.4';
$SECURITY_LEVEL = 'MAXIMUM';
$FIREWALL_STATUS = 'ACTIVE';

// Misleading comment
// TODO: Update Samba to 4.6.5 to fix potential vulnerabilities
?>
EOF

# 创建假的日志文件
echo "[$(date)] Security scan completed - No vulnerabilities found" > /var/log/security/scan.log
echo "[$(date)] Firewall rules updated - All ports secured" >> /var/log/security/scan.log
echo "[$(date)] System integrity check passed" >> /var/log/security/scan.log

# 创建开发文件（包含误导信息）
cat > /home/dev/README.md << 'EOF'
# Development Environment

## Security Status
- ✅ All systems patched
- ✅ Firewall active
- ✅ No known vulnerabilities
- ✅ Samba 4.6.4 (latest version)

## Network Configuration
- Primary Server: 192.168.1.100
- Backup Server: 192.168.1.101
- File Server: \fileserver\secure

## Important Notes
- All file uploads are scanned for malware
- Database connections are encrypted
- Admin access is restricted to IT department
- Regular security audits are performed

## Contact Information
- Admin: admin@company.com
- Security: security@company.com
- IT Support: support@company.com
EOF

# 创建假的备份脚本
cat > /home/dev/backup.sh << 'EOF'
#!/bin/bash
# Fake backup script
echo "Starting backup process..."
echo "Connecting to backup server..."
echo "Backup completed successfully"
echo "All files are secure"
EOF

chmod +x /home/dev/backup.sh

# 创建假的系统信息
cat > /home/dev/system_info.txt << 'EOF'
System Information:
- OS: CentOS 7.9
- Kernel: 3.10.0-1160
- Samba: 4.6.4 (patched)
- Security Level: HIGH
- Last Update: 2024-01-15
- Next Maintenance: 2024-02-01

Network Services:
- Samba: Port 445, 139 (secured)
- Web: Port 80, 443 (firewall protected)
- Database: Port 3306 (encrypted)
- SSH: Port 22 (key-based auth only)

Security Features:
- Intrusion Detection System (IDS)
- Real-time malware scanning
- File integrity monitoring
- Network access control
- Regular vulnerability assessments
EOF

# 创建假的漏洞扫描报告
cat > /var/log/security/vulnerability_report.txt << 'EOF'
VULNERABILITY ASSESSMENT REPORT
Generated: $(date)
Scan Engine: Corporate Security Scanner v3.2

SUMMARY:
- Total Systems Scanned: 5
- Critical Vulnerabilities: 0
- High Risk Issues: 0
- Medium Risk Issues: 2
- Low Risk Issues: 5

SAMBA FILE SERVER ASSESSMENT:
- Version: 4.6.4 (Current)
- Security Patches: Up to date
- Access Controls: Properly configured
- Network Security: Firewall protected
- Overall Risk: LOW

RECOMMENDATIONS:
- Continue regular monitoring
- Maintain current security patches
- Review access logs weekly
- Schedule next assessment: 30 days

CONCLUSION:
System is secure and compliant with corporate standards.
EOF

echo "Security environment setup completed!"
echo "Remember: This is a CTF challenge environment"
echo "Actual Samba version: 4.6.3 (vulnerable to CVE-2017-7494)"
echo "But the system reports 4.6.4 to mislead attackers"