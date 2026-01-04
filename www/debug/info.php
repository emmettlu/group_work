<?php
// Debug information - REMOVE IN PRODUCTION!

header('Content-Type: text/plain');
?>
=== System Debug Information ===
Generated: <?php echo date('Y-m-d H:i:s'); ?>

PHP Version: <?php echo phpversion(); ?>

OS: <?php echo php_uname(); ?>

Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>


=== Network Services ===
Open Ports:
- 21: FTP Service (vsftpd)
- 80: Web Service (Apache)
- 445: SMB Service (Samba 4.x)
- 3306: MySQL Service
- 8080: Management Console (Tomcat)

Services Status:
✓ Apache - Running
✓ MySQL - Running  
✓ Samba - Running (4 shares active)
✓ FTP - Running

=== File Shares ===
Active SMB Shares:
- myshare (Public access)
- InternalBackup (Internal use)
- print$ (Print drivers)
- IPC$ (Inter-process communication)

=== Last Updates ===
System Update: 2023-11-20
Samba Migration: Completed
Database Migration: Completed
Security Audit: Pending

=== Notes ===
- Network share migration from old file server completed
- All legacy data transferred to SMB shares
- Web interface for file management in development
- Contact sysadmin@company.local for issues

WARNING: This debug endpoint should be disabled in production!
