===============================================
  TechCorp File Server - Shared Directory
===============================================

Welcome to the company file server!

This is the shared storage location for all company files.
All files uploaded through the web interface are stored here.

Server Information:
-------------------
- Server: Samba 4.6.3
- Share Name: myshare
- Path: /home/share
- Access: Guest (No authentication required)
- Permissions: Read/Write

Usage Instructions:
-------------------
You can access this share from:

Linux/Unix:
  smbclient //SERVER_IP/myshare -N
  mount -t cifs //SERVER_IP/myshare /mnt/share -o guest

Windows:
  \\SERVER_IP\myshare

Important Notes:
----------------
⚠️ WARNING: This server is running an outdated version of Samba
⚠️ Known security vulnerabilities exist in version 4.6.3
⚠️ Please contact IT department to schedule an upgrade

For more information, visit the web portal at:
http://SERVER_IP:8080

---
Last Updated: 2024-01-15
Maintained by: IT Department
Contact: admin@company.local
