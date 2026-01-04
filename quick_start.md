# 快速开始指南

## 项目简介

本项目是一个基于 CVE-2017-7494 Samba 漏洞的渗透测试靶机环境。通过多层迷惑和引导，让渗透测试人员体验从信息收集到漏洞利用的完整过程。

## 环境要求

### 靶机环境
- Docker Engine 20.10+
- Docker Compose 1.29+
- 至少 4GB 可用内存
- 至少 10GB 可用磁盘空间

### 攻击机环境
- Linux 操作系统（推荐 Kali Linux）
- 基本渗透测试工具：
  - nmap
  - smbclient
  - gobuster/dirb
  - netcat
  - Python 3.x

---

## 部署步骤

### 1. 克隆或下载项目

```bash
git clone <repository_url>
cd group_work
```

### 2. 启动靶机环境

```bash
# 构建并启动所有服务
docker-compose up -d

# 查看服务状态
docker-compose ps

# 预期输出：
# NAME                SERVICE   STATUS    PORTS
# group_work-web-1    web       running   0.0.0.0:80->80/tcp
# group_work-php-1    php       running   9000/tcp
# group_work-db-1     db        running   0.0.0.0:3306->3306/tcp
# group_work-samba-1  samba     running   0.0.0.0:139->139/tcp, 0.0.0.0:445->445/tcp
```

### 3. 等待服务初始化

```bash
# 等待数据库初始化完成（约30-60秒）
docker-compose logs db | grep "ready for connections"

# 确认所有服务正常运行
docker-compose logs --tail=50
```

### 4. 验证服务可用性

```bash
# 测试Web服务
curl http://localhost

# 测试SMB服务
smbclient -L //localhost -N

# 测试数据库
mysql -h localhost -u fileuser -pFile@2024 -e "SHOW DATABASES;"
```

---

## 靶机访问信息

### Web 应用
- **URL**: `http://<靶机IP>`
- **测试账号**:
  - `admin / admin123` (管理员)
  - `developer / dev123` (开发者，可访问数据库)
  - `guest / guest` (访客)
  - `john / john2024` (普通用户)

### SMB 文件共享
- **服务地址**: `//<靶机IP>/myshare`
- **端口**: 445, 139
- **认证**: 匿名访问（无需密码）
- **共享名**: `myshare`

### MySQL 数据库
- **主机**: `<靶机IP>`
- **端口**: 3306
- **数据库**: `filemanager`
- **用户**: `fileuser`
- **密码**: `File@2024`

---

## 攻击提示（剧透警告！）

<details>
<summary>点击展开攻击路径提示（建议先自己尝试）</summary>

### 第一步：信息收集
1. 使用 nmap 扫描端口
2. 访问 Web 应用
3. 尝试弱密码登录

### 第二步：内部探索
1. 查看系统信息页面
2. 阅读系统日志
3. 使用数据库查询工具

### 第三步：发现线索
1. 注意 Samba 版本号
2. 查看系统配置表
3. 执行 `CALL get_server_info();`

### 第四步：SMB 探索
1. 匿名连接到 SMB 共享
2. 下载共享目录中的文件
3. 阅读 `server_notes.txt`

### 第五步：漏洞利用
1. 搜索 CVE-2017-7494
2. 下载对应的 exploit
3. 执行攻击获取 shell

</details>

---

## 故障排除

### 服务无法启动

```bash
# 查看详细日志
docker-compose logs

# 重启服务
docker-compose restart

# 完全重建
docker-compose down -v
docker-compose up -d --build
```

### 数据库连接失败

```bash
# 检查数据库是否就绪
docker-compose exec db mysql -u root -prootpass123 -e "SELECT 1;"

# 重新初始化数据库
docker-compose exec db mysql -u root -prootpass123 filemanager < db/init.sql
```

### SMB 服务无法访问

```bash
# 检查 Samba 服务状态
docker-compose exec samba ps aux | grep smbd

# 测试端口是否开放
nmap -p 445 <靶机IP>

# 重启 Samba 服务
docker-compose restart samba
```

### Web 页面显示异常

```bash
# 检查 PHP 日志
docker-compose logs php

# 检查 Nginx 日志
docker-compose logs web

# 重启 Web 服务
docker-compose restart web php
```

---

## 管理命令

### 查看日志
```bash
# 查看所有服务日志
docker-compose logs -f

# 查看特定服务日志
docker-compose logs -f samba
docker-compose logs -f web
docker-compose logs -f db
```

### 停止和清理
```bash
# 停止所有服务
docker-compose stop

# 停止并删除容器
docker-compose down

# 删除容器和数据卷（清除所有数据）
docker-compose down -v

# 删除容器、数据卷和镜像
docker-compose down -v --rmi all
```

### 进入容器调试
```bash
# 进入 Web 容器
docker-compose exec web sh

# 进入 Samba 容器
docker-compose exec samba bash

# 进入数据库容器
docker-compose exec db mysql -u root -p
```

---

## 转换为 OVA 文件

如需将靶机转换为虚拟机文件（OVA格式），可以使用提供的脚本：

```bash
# 使用 d2vm 工具转换
chmod +x convert_to_ova.sh
./convert_to_ova.sh

# 转换过程会生成：
# 1. vm.qcow2 - QEMU 虚拟机镜像
# 2. vm.vmdk - VMware 虚拟机磁盘
# 3. vm.ova - 最终的 OVA 文件
```

转换完成后，可以将 `vm.ova` 导入到 VMware 或 VirtualBox 中使用。

---

## 安全警告

⚠️ **重要提示**：

1. **仅用于学习**: 本靶机仅供网络安全学习和渗透测试训练使用
2. **隔离环境**: 请在隔离的测试网络中运行，不要暴露到公网
3. **已知漏洞**: 靶机包含故意设置的安全漏洞，切勿用于生产环境
4. **合法使用**: 仅在获得授权的环境中使用，不得用于非法攻击
5. **数据安全**: 不要在靶机中存储真实的敏感数据

---

## 学习目标

通过完成本靶机，你将学习到：

1. ✅ 完整的渗透测试流程（信息收集 → 漏洞发现 → 漏洞利用）
2. ✅ Web 应用安全测试（弱密码、信息泄露）
3. ✅ 数据库安全（SQL 查询、敏感信息存储）
4. ✅ 网络服务安全（SMB 协议、匿名访问）
5. ✅ CVE 漏洞研究与利用（CVE-2017-7494）
6. ✅ 后渗透技术（持久化、痕迹清理）
7. ✅ 信息关联能力（从多个来源拼凑完整攻击链）

---

## 技术支持

### 常见问题

**Q: 为什么 PHP 扩展安装失败？**
A: PHP 容器需要编译安装 mysqli 扩展，首次启动可能需要较长时间。查看日志确认安装进度。

**Q: 数据库初始化失败？**
A: 确保 `db/init.sql` 文件存在且格式正确。可以手动执行初始化脚本。

**Q: SMB 连接被拒绝？**
A: 检查防火墙设置，确保 445 端口未被占用。Windows 系统可能需要禁用内置的 SMB 服务。

**Q: 攻击不成功？**
A: 确保使用正确的 exploit 版本，注意目标 IP 和共享名配置。查看 `hacking.md` 获取详细步骤。

### 反馈与改进

如果发现问题或有改进建议，欢迎反馈：
- 检查日志文件
- 提供错误信息
- 描述复现步骤

---

## 参考资源

### CVE-2017-7494 相关资源
- [Official Samba Security Advisory](https://www.samba.org/samba/security/CVE-2017-7494.html)
- [CVE Details](https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2017-7494)
- [NVD Entry](https://nvd.nist.gov/vuln/detail/CVE-2017-7494)
- [ExploitDB](https://www.exploit-db.com/exploits/42084)

### 学习资源
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Metasploit Unleashed](https://www.offensive-security.com/metasploit-unleashed/)
- [Penetration Testing Execution Standard](http://www.pentest-standard.org/)

---

## 致谢

- Samba 项目团队
- Vulhub 项目（提供 Samba 镜像）
- 安全研究社区

---

## 许可证

本项目仅供教育和研究目的使用。

---

**祝你攻击愉快！Good Luck Hacking! 🎯**