# Enhanced Samba Authenticated RCE (CVE-2017-7494, Aka SambaCry)

[中文版本(Chinese version)](README.zh-cn.md)

## 🎯 Challenge Overview

This is an **enhanced and more complex** version of the classic SambaCry vulnerability challenge. The original vulnerability was too straightforward - just run exploit.py and you get root access. This version adds multiple layers of complexity, misdirection, and realistic enterprise security measures to create a more engaging CTF experience.

## 🔍 What's New

### Enhanced Complexity Features:
1. **Multi-layered Infrastructure** - Web app, database, security monitoring, SSH honeypot
2. **Realistic Enterprise Environment** - Multiple services, complex network topology
3. **Misdirection & False Security** - Fake security headers, misleading version info
4. **Multiple Attack Vectors** - SQL injection, file upload vulnerabilities, information disclosure
5. **Security Monitoring Simulation** - Fake IDS/IPS alerts to confuse attackers
6. **Complex Authentication** - Multiple user accounts with different privilege levels

## 🏗️ Environment Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    ATTACKER MACHINE                        │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    NETWORK LAYER                           │
├─────────────────┬─────────────────┬─────────────────────────┤
│   Web Server   │   Database     │   Security Monitor     │
│   (Port 8080)  │  (Port 3306)   │    (Port 8081)         │
├─────────────────┼─────────────────┼─────────────────────────┤
│   SSH Honeypot │   File Server  │   Multiple Services    │
│   (Port 2222)  │  (Port 445)    │   (139, etc.)          │
└─────────────────┴─────────────────┴─────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│              INTERNAL NETWORK (Bridge)                     │
└─────────────────────────────────────────────────────────────┘
```

## 🚀 Quick Start

```bash
# Start the enhanced environment
docker-compose up -d

# Check service status
docker-compose ps

# View logs (for debugging)
docker-compose logs -f
```

## 🎭 Service Details

### 1. Samba File Server (Target)
- **IP**: Container IP (check with `docker inspect`)
- **Ports**: 445, 139
- **Version**: 4.6.3 (vulnerable) but reports 4.6.4
- **Shares**: Multiple shares with different access levels

### 2. Web Application (Decoy & Attack Vector)
- **URL**: http://localhost:8080
- **Features**: 
  - SQL injection vulnerabilities
  - File upload with intentional flaws
  - Fake security monitoring
  - Multiple user accounts

### 3. Database Server
- **Port**: 3306
- **Credentials**: Check docker-compose.yml
- **Contains**: User data, system configs, fake logs

### 4. Security Monitoring Dashboard
- **URL**: http://localhost:8081
- **Purpose**: Shows fake security alerts to misdirect attackers

### 5. SSH Honeypot
- **Port**: 2222
- **Purpose**: Captures SSH brute force attempts

## 🕵️ Attack Methodology

### Phase 1: Reconnaissance
```bash
# Port scanning (be subtle to avoid "detection")
nmap -sS -p- target_ip

# Service enumeration
nmap -sV -p 445,139,8080,3306,8081,2222 target_ip

# Web application discovery
dirb http://target:8080
nikto -h http://target:8080
```

### Phase 2: Web Application Analysis
1. **Check login page**: http://target:8080/
2. **Test SQL injection**: Try `' OR '1'='1` in login form
3. **Explore file upload**: http://target:8080/fileupload.php
4. **Search for users**: http://target:8080/search.php
5. **Check for admin panel**: http://target:8080/admin.php

### Phase 3: Information Gathering
```bash
# Connect to Samba shares
smbclient -L //target_ip -N
smbclient //target_ip/public -N
smbclient //target_ip/private -U sambauser

# Check for readable shares
smbclient //target_ip/secure -U admin
```

### Phase 4: Exploit Development
1. **Find the real vulnerability**: Despite all the misdirection, CVE-2017-7494 still works
2. **Identify the correct share**: Not all shares are equally vulnerable
3. **Determine physical path**: Use information from web app and file shares
4. **Craft the exploit**: Create or modify the exploit for this specific environment

### Phase 5: Exploitation
```bash
# Method 1: Direct Samba exploit (classic)
./exploit.py -t target_ip -e malicious.so -s SHARENAME -r /physical/path/malicious.so

# Method 2: Web app to file upload to Samba
# 1. Upload malicious file via web app
# 2. Find file location
# 3. Use Samba to execute it
```

## 🧩 Key Challenges

### 1. Information Overload
- Multiple services running
- Fake security alerts
- Misleading version information
- Complex network topology

### 2. False Security Indicators
- "Security Level: HIGH" headers
- Fake vulnerability scan results
- Misleading Samba version (reports 4.6.4, actually 4.6.3)
- Simulated IDS/IPS alerts

### 3. Multiple Red Herrings
- SSH honeypot on port 2222
- Fake admin panels
- SQL injection that doesn't lead to the goal
- File upload that seems promising but isn't the main path

### 4. Complex Authentication
- Multiple user accounts with different privileges
- Some shares require authentication
- Guest access limited to specific shares

## 💡 Hints for Attackers

1. **Don't trust the version numbers** - They're designed to mislead
2. **The web app is mostly a decoy** - Fun to exploit but not the main path
3. **Focus on Samba** - That's where the real vulnerability lies
4. **Check all shares** - Some are more interesting than others
5. **Look for configuration files** - They might reveal the real version
6. **The security dashboard is fake** - It's meant to confuse you

## 🔓 Solution Path

<details>
<summary>Click for spoiler hints</summary>

1. **Identify the real Samba version**: Check configuration files in shares
2. **Find the vulnerable share**: Not all shares allow write access
3. **Determine physical paths**: Use the web app and file listings
4. **Craft your payload**: The classic SambaCry exploit still works
5. **Execute strategically**: Choose the right share and path

The final exploit will look something like:
```bash
./exploit.py -t target_ip -e your_shell.so -s VULNERABLE_SHARE -r /correct/path/your_shell.so
```

</details>

## 🛠️ Technical Details

### Samba Configuration
- Multiple shares with different permissions
- Intentional misconfiguration to allow exploitation
- Fake security settings to appear secure

### Web Application Vulnerabilities
- SQL injection in login and search functions
- File upload with insufficient validation
- Information disclosure in error messages
- Session management issues

### Database Security
- Weak credentials intentionally exposed
- Sensitive data stored in plaintext
- SQL injection vulnerabilities
- Excessive logging of sensitive operations

## 📝 Notes

- This environment is designed for educational purposes
- The complexity is intentional to simulate real-world scenarios
- Multiple attack paths exist but only one leads to the flag
- Fake security measures are meant to teach about misdirection
- All vulnerabilities are intentional for CTF purposes

## 🚧 Troubleshooting

### Services not starting
```bash
docker-compose down
docker-compose up -d
```

### Port conflicts
Edit docker-compose.yml to use different ports

### Permission issues
```bash
chmod +x scripts/setup.sh
sudo chown -R $USER:$USER .
```

## 🎉 Success Indicators

When you successfully exploit the system:
1. You'll get a shell on the Samba server
2. Look for the flag in `/root/flag.txt`
3. The flag format is: `flag{exploit_name_or_description}`

## 📚 Learning Objectives

1. **Information Gathering**: How to navigate complex environments
2. **Misdirection Recognition**: Identifying fake security measures
3. **Persistence**: Not giving up when faced with complexity
4. **Exploit Adaptation**: Modifying exploits for specific environments
5. **Real-world Skills**: Handling enterprise-like infrastructure

---

**Happy Hacking!** 🚀 Remember: the journey is more important than the destination. Enjoy the complexity!