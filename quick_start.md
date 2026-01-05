# 快速开始指南

## 项目简介

本项目是一个基于 CVE-2017-7494 Samba 漏洞的靶机环境，用于网络安全教学和渗透测试训练。环境包含三层架构：Web 前端、MySQL 数据库和 Samba 文件服务器，通过层层提示引导攻击者发现并利用 Samba 远程代码执行漏洞。

**⚠️ 警告：** 本环境仅用于教学和授权测试，请勿用于非法用途！

## 系统要求

### 硬件要求
- CPU: 2核心或以上
- 内存: 4GB RAM 或以上
- 磁盘: 10GB 可用空间

### 软件要求
- 操作系统: Linux (推荐 Ubuntu 20.04+, CentOS 7+)
- Docker: 20.10+
- Docker Compose: 1.29+
- 网络: 能够访问 Docker Hub

### 可选工具（攻击端）
- nmap - 端口扫描
- smbclient - SMB 客户端
- Metasploit Framework - 渗透测试框架
- Python 3.x - 运行 exploit 脚本

## 快速部署

### 1. 检查 Docker 环境

```bash
# 检查 Docker 版本
docker --version

# 检查 Docker Compose 版本
docker-compose --version

# 确保 Docker 服务正在运行
sudo systemctl status docker
```

### 2. 启动靶机环境

```bash
# 进入项目目录
cd /home/lym/Repos/group_work

# 启动所有服务（后台运行）
sudo docker-compose up -d

# 查看容器状态
docker-compose ps
```

**期望输出：**
```
Name                 Command               State                    Ports
-------------------------------------------------------------------------------------
target_mysql    docker-entrypoint.sh mysqld    Up      0.0.0.0:3306->3306/tcp
target_samba    /bin/sh -c /usr/local/sam...   Up      0.0.0.0:139->139/tcp,
                                                        0.0.0.0:445->445/tcp,
                                                        0.0.0.0:6699->6699/tcp
target_web      docker-php-entrypoint apac...  Up      0.0.0.0:8080->8080/tcp
```

### 3. 验证服务状态

```bash
# 查看容器日志（可选）
docker-compose logs -f

# 测试 Web 服务
curl http://localhost:8080

# 测试 MySQL 连接
docker exec -it target_mysql mysql -uwebuser -pwebpass123 -e "SHOW DATABASES;"

# 测试 SMB 服务
smbclient -L //localhost -N
```

### 4. 获取目标 IP 地址

```bash
# 获取 Samba 容器的 IP 地址
docker inspect target_samba | grep IPAddress

# 或者使用过滤命令
docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' target_samba
```

**记录下这个 IP 地址，攻击时需要使用！**

## 访问入口

### Web 界面（主要入口）
- **URL:** http://localhost:8080 或 http://YOUR_HOST_IP:8080
- **说明:** 这是攻击者的第一接触点，包含大量线索和提示

### 关键页面路径
- 首页: `http://localhost:8080/?page=home`
- 文件管理: `http://localhost:8080/?page=files`
- 文件上传: `http://localhost:8080/?page=upload`
- 系统信息: `http://localhost:8080/?page=system` ⭐ **重要**
- 登录页面: `http://localhost:8080/?page=login`
- 调试页面: `http://localhost:8080/admin/debug.php` ⭐ **关键线索**

### 数据库访问
```bash
# 从宿主机连接
mysql -h localhost -P 3306 -u webuser -pwebpass123 company_db

# 从容器内连接
docker exec -it target_mysql mysql -uwebuser -pwebpass123 company_db
```

**测试账号：**
- admin / admin123 (管理员)
- test / test (普通用户)

### Samba 文件服务（漏洞点）
```bash
# 列出共享
smbclient -L //localhost -N

# 连接到 myshare
smbclient //localhost/myshare -N

# 查看共享文件
smb: \> ls
smb: \> get README.txt
```

## 服务架构

```
┌─────────────────────────────────────────┐
│   攻击者 (Attacker)                      │
│   IP: YOUR_LOCAL_IP                     │
└────────────┬────────────────────────────┘
             │
             │ Port Scanning & Exploitation
             ▼
┌─────────────────────────────────────────┐
│   Docker Host (Target)                  │
│                                          │
│  ┌──────────────────────────────────┐   │
│  │  Web Server (Port 8080)          │   │
│  │  Container: target_web           │   │
│  │  Tech: Apache + PHP 7.4          │   │
│  └──────────┬───────────────────────┘   │
│             │                            │
│             ▼                            │
│  ┌──────────────────────────────────┐   │
│  │  Database (Port 3306)            │   │
│  │  Container: target_mysql         │   │
│  │  Tech: MySQL 5.7                 │   │
│  └──────────┬───────────────────────┘   │
│             │                            │
│             ▼                            │
│  ┌──────────────────────────────────┐   │
│  │  File Server (Port 445) 🎯       │   │
│  │  Container: target_samba         │   │
│  │  Tech: Samba 4.6.3 (VULNERABLE)  │   │
│  │  CVE: CVE-2017-7494              │   │
│  └──────────────────────────────────┘   │
│                                          │
│  Network: target_network (bridge)       │
└─────────────────────────────────────────┘
```

## 攻击流程概览

### 第一阶段：信息收集
1. 扫描目标主机，发现开放端口
2. 访问 Web 界面（8080）
3. 浏览各个页面，收集线索

### 第二阶段：漏洞发现
1. 在"系统信息"页面发现 Samba 版本 4.6.3
2. 在数据库或调试页面找到 CVE-2017-7494 提示
3. 确认 Guest 访问和可写权限

### 第三阶段：漏洞利用
1. 下载 CVE-2017-7494 exploit
2. 生成恶意 payload
3. 执行攻击获取 shell
4. 读取 flag.txt 文件

**详细攻击过程请参考 `hacking.md` 文档。**

## 常用管理命令

### 查看日志
```bash
# 查看所有容器日志
docker-compose logs

# 查看特定容器日志
docker-compose logs target_samba
docker-compose logs target_web

# 实时跟踪日志
docker-compose logs -f
```

### 重启服务
```bash
# 重启所有服务
docker-compose restart

# 重启特定服务
docker-compose restart target_samba
```

### 停止和清理
```bash
# 停止所有服务
docker-compose stop

# 停止并删除容器
docker-compose down

# 删除所有资源（包括卷）
docker-compose down -v
```

### 进入容器
```bash
# 进入 Samba 容器
docker exec -it target_samba /bin/sh

# 进入 Web 容器
docker exec -it target_web /bin/bash

# 进入 MySQL 容器
docker exec -it target_mysql /bin/bash
```

## 故障排查

### 问题1：容器无法启动
```bash
# 检查端口占用
sudo netstat -tulpn | grep -E '8080|3306|445'

# 如果端口被占用，修改 docker-compose.yml 中的端口映射
# 或停止占用端口的服务
```

### 问题2：Web 界面无法访问
```bash
# 检查 Web 容器状态
docker-compose ps target_web

# 查看 Web 容器日志
docker-compose logs target_web

# 重启 Web 服务
docker-compose restart target_web
```

### 问题3：无法连接 MySQL
```bash
# 等待 MySQL 初始化完成（首次启动需要时间）
docker-compose logs target_mysql | grep "ready for connections"

# 手动连接测试
docker exec -it target_mysql mysql -uroot -proot123456
```

### 问题4：SMB 服务无响应
```bash
# 检查 Samba 容器状态
docker-compose ps target_samba

# 查看 Samba 日志
docker-compose logs target_samba

# 测试端口连通性
nc -zv localhost 445
nmap -p 445 localhost
```

### 问题5：数据库初始化失败
```bash
# 删除数据卷并重新初始化
docker-compose down -v
docker-compose up -d

# 手动执行初始化脚本
docker exec -i target_mysql mysql -uroot -proot123456 < mysql/init.sql
```

## 安全注意事项

⚠️ **重要提醒：**

1. **仅用于教学环境：** 本靶机包含已知的严重漏洞，绝对不能在生产环境部署！
2. **网络隔离：** 建议在隔离的测试网络中运行，避免影响其他系统
3. **防火墙规则：** 如果在云服务器上运行，请配置防火墙限制访问来源
4. **及时关闭：** 测试完成后应立即停止服务
5. **合法授权：** 只能在自己的环境或经过授权的环境中进行测试

## 学习建议

### 适合人群
- 网络安全专业学生
- 渗透测试初学者
- CTF 参赛选手
- 安全运维人员

### 学习路径
1. **基础知识：** 了解 SMB/CIFS 协议、Samba 服务、Linux 权限
2. **信息收集：** 学习使用 nmap, smbclient 等工具
3. **漏洞研究：** 研究 CVE-2017-7494 的技术细节
4. **漏洞利用：** 学习 Metasploit 或手动编写 exploit
5. **防御加固：** 思考如何修复漏洞和加强防护

### 推荐资源
- Samba 官方安全公告: https://www.samba.org/samba/security/CVE-2017-7494.html
- ExploitDB: https://www.exploit-db.com/exploits/42084
- Metasploit 文档: https://www.rapid7.com/db/modules/exploit/linux/samba/is_known_pipename/

## 技术支持

### 获取帮助
- 查看 `hacking.md` 了解完整攻击流程
- 查看 `distribution.md` 了解项目架构
- 访问 `/admin/debug.php` 页面获取详细技术信息

### 常见问题
Q: 找不到漏洞在哪？
A: 访问 Web 界面的"系统信息"页面和 `/admin/debug.php`

Q: 如何获取目标 IP？
A: 使用 `docker inspect target_samba | grep IPAddress`

Q: Exploit 执行失败？
A: 确保目标 IP 正确，445 端口可达，Samba 服务正常运行

Q: 拿到 shell 后做什么？
A: 查看 `/home/share/flag.txt` 文件

## 附录：快速命令参考

```bash
# 一键启动
docker-compose up -d

# 获取目标 IP
docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' target_samba

# 测试 SMB
smbclient -L //TARGET_IP -N

# 连接共享
smbclient //TARGET_IP/myshare -N

# 查看日志
docker-compose logs -f

# 停止环境
docker-compose down
```

---

**祝您学习愉快！Good Luck! 🚀**

如有问题或建议，请查阅其他文档或联系项目维护者。
