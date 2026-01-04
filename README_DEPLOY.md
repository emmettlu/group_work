# FileHub - CVE-2017-7494 Samba 靶机部署指南

## 📋 项目简介

这是一个基于 CVE-2017-7494 (SambaCry) 漏洞的综合渗透测试靶机环境。通过多层迷惑设计，模拟真实企业网络环境，让攻击者在探索过程中逐步发现真正的攻击面。

**难度等级**: ★★★★☆ (中高级)  
**预计完成时间**: 45-60 分钟  
**技能要求**: 信息收集、Web 渗透、网络服务枚举、CVE 研究

---

## 🎯 学习目标

- 掌握系统化的信息收集方法
- 学会识别真假漏洞点
- 理解历史 CVE 的利用方法
- 体验从线索到漏洞的完整攻击链
- 了解 Samba 服务的安全风险

---

## 🏗️ 架构设计

```
┌─────────────────────────────────────────────────────┐
│                    攻击者                             │
└───────────────┬─────────────────────────────────────┘
                │
                ▼
┌───────────────────────────────────────────────────────┐
│              DMZ / 边界网络                            │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐           │
│  │ FTP:21   │  │ Web:80   │  │ Tomcat   │           │
│  │ (蜜罐)    │  │ (迷惑)    │  │ :8080    │           │
│  └──────────┘  └──────────┘  └──────────┘           │
└───────────────────────────────────────────────────────┘
                │
                ▼
┌───────────────────────────────────────────────────────┐
│              内部服务网络                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐           │
│  │ MySQL    │  │ Samba    │  │ Monitor  │           │
│  │ :3306    │  │ :445 ⚠️  │  │ Service  │           │
│  └──────────┘  └──────────┘  └──────────┘           │
└───────────────────────────────────────────────────────┘
```

---

## 🚀 快速部署

### 前置要求

- Docker >= 20.10
- Docker Compose >= 1.29
- 至少 2GB 可用内存
- 至少 5GB 可用磁盘空间

### 一键启动

```bash
# 克隆或进入项目目录
cd group_work

# 启动所有服务
docker-compose up -d

# 查看服务状态
docker-compose ps

# 查看日志
docker-compose logs -f
```

### 验证部署

```bash
# 检查端口是否开放
nmap -sV -p 21,80,445,3306,8080 localhost

# 访问 Web 界面
curl http://localhost/

# 枚举 SMB 共享
smbclient -L //localhost/ -N
```

---

## 🗂️ 项目结构

```
group_work/
├── docker-compose.yml          # Docker 编排配置
├── smb.conf                    # Samba 配置文件
├── entrypoint.sh               # 启动脚本
├── README_DEPLOY.md            # 本文档
├── hacking.md                  # 攻击实录（剧透警告！）
├── sum.md                      # 模块划分文档
├── prompt.md                   # 项目需求
│
├── www/                        # Web 应用目录
│   ├── index.php               # 登录页面
│   ├── login.php               # 登录处理（假漏洞）
│   ├── robots.txt              # 爬虫配置（含线索）
│   ├── .git/                   # Git 泄露（线索）
│   │   └── config              # 包含 samba-migration 分支
│   ├── backup/                 # 备份目录（关键线索）
│   │   ├── old_smb.conf        # 旧 Samba 配置
│   │   └── README.txt          # 迁移说明
│   ├── debug/                  # 调试目录（信息泄露）
│   │   └── info.php            # 系统信息页面
│   ├── api/                    # API 接口
│   ├── css/                    # 样式文件
│   │   └── style.css
│   └── js/                     # JavaScript 文件
│
├── database/                   # 数据库脚本
│   ├── init.sql                # 表结构
│   └── fake_data.sql           # 假数据和线索
│
├── samba/                      # Samba 共享目录
│   └── share/                  # 共享文件
│       ├── welcome.txt         # 欢迎文件
│       └── flag.txt            # 最终 flag
│
├── services/                   # 辅助服务
│   ├── fake_ftp.py             # 假 FTP 服务（vsftpd 2.3.4 蜜罐）
│   └── monitor.sh              # 监控脚本
│
├── config/                     # 配置文件
├── logs/                       # 日志目录
└── docs/                       # 文档目录
```

---

## 🎮 攻击指南（无剧透版）

### 第一步：信息收集

1. **端口扫描**
   ```bash
   nmap -sV -p- <target_ip>
   ```

2. **Web 目录枚举**
   ```bash
   gobuster dir -u http://<target_ip>/ -w /usr/share/wordlists/dirb/common.txt
   ```

3. **查看常见文件**
   - robots.txt
   - README
   - .git/
   - backup/

### 第二步：分析线索

- 注意所有提到"网络共享"、"迁移"、"Samba"的地方
- 收集版本号信息
- 整理开放的服务

### 第三步：漏洞研究

- 根据收集到的版本信息搜索 CVE
- 使用 searchsploit 或 Google
- 查找可用的 exploit

### 第四步：漏洞利用

- 使用找到的 exploit 工具
- 或者使用 Metasploit Framework

### 提示

- 💡 不是所有看起来的"漏洞"都是真的
- 💡 版本号是关键信息
- 💡 有时候最简单的服务藏着最严重的漏洞
- 💡 耐心和系统化思维很重要

---

## 🛠️ 管理命令

### 启动和停止

```bash
# 启动所有服务
docker-compose up -d

# 停止所有服务
docker-compose down

# 重启特定服务
docker-compose restart samba

# 查看实时日志
docker-compose logs -f samba
```

### 调试

```bash
# 进入 Samba 容器
docker exec -it filehub_samba /bin/bash

# 进入 Web 容器
docker exec -it filehub_web /bin/bash

# 查看 Samba 配置
docker exec filehub_samba cat /usr/local/samba/etc/smb.conf

# 查看共享文件
docker exec filehub_samba ls -la /home/share
```

### 重置环境

```bash
# 完全清理环境（包括数据卷）
docker-compose down -v

# 重新构建并启动
docker-compose up -d --build
```

---

## 🔧 自定义配置

### 修改 Flag

编辑 `samba/share/flag.txt`，然后重启：

```bash
docker-compose restart samba
```

### 添加更多线索

1. 编辑 `database/fake_data.sql` 添加数据库线索
2. 在 `www/` 下创建新的 PHP 文件
3. 修改 `www/debug/info.php` 添加信息

### 调整难度

**降低难度**:
- 在 `www/index.php` 注释中直接提示 Samba
- 在 `robots.txt` 中添加更明显的提示
- 在 debug 页面显示具体 CVE 编号

**增加难度**:
- 移除 `backup/old_smb.conf` 文件
- 隐藏版本号信息
- 添加更多假漏洞点

---

## 📊 服务端口映射

| 服务 | 容器端口 | 主机端口 | 用途 |
|------|----------|----------|------|
| Samba | 445 | 445 | **真实漏洞点** - CVE-2017-7494 |
| Apache | 80 | 80 | Web 界面 - 迷惑和线索 |
| MySQL | 3306 | 3306 | 数据库 - 线索存储 |
| FTP | 21 | 21 | 蜜罐 - vsftpd 2.3.4 假象 |
| Tomcat | 8080 | 8080 | 假管理后台 |

---

## 🔒 安全说明

### ⚠️ 警告

**此环境包含已知的严重安全漏洞，仅供教育和培训使用！**

- ❌ **不要**在生产环境部署
- ❌ **不要**暴露到公网
- ❌ **不要**使用真实数据
- ✅ **仅在**隔离的实验环境中使用
- ✅ **建议**在虚拟机或 Docker 网络中运行

### 网络隔离建议

```bash
# 创建独立的 Docker 网络
docker network create --subnet=172.20.0.0/24 filehub_isolated

# 使用防火墙限制访问
iptables -A INPUT -p tcp --dport 445 -s 172.20.0.0/24 -j ACCEPT
iptables -A INPUT -p tcp --dport 445 -j DROP
```

---

## 🐛 故障排除

### 问题：Samba 服务启动失败

```bash
# 查看日志
docker logs filehub_samba

# 检查配置文件
docker exec filehub_samba cat /usr/local/samba/etc/smb.conf

# 测试配置
docker exec filehub_samba /usr/local/samba/bin/testparm
```

### 问题：无法访问 Web 界面

```bash
# 检查 Apache 状态
docker exec filehub_web apachectl status

# 检查 PHP 配置
docker exec filehub_web php -v

# 查看错误日志
docker exec filehub_web cat /var/log/apache2/error.log
```

### 问题：MySQL 连接失败

```bash
# 检查 MySQL 状态
docker exec filehub_mysql mysqladmin -u root -p status

# 测试连接
docker exec filehub_mysql mysql -u webapp -p -e "SHOW DATABASES;"
```

### 问题：端口被占用

```bash
# 检查端口占用
sudo netstat -tulpn | grep -E ':(80|445|3306|8080|21)'

# 修改 docker-compose.yml 中的端口映射
# 例如：将 80:80 改为 8000:80
```

---

## 📚 相关资源

### CVE-2017-7494 资料

- **官方公告**: https://www.samba.org/samba/security/CVE-2017-7494.html
- **POC/Exploit**: https://github.com/joxeankoret/CVE-2017-7494
- **Metasploit 模块**: `exploit/linux/samba/is_known_pipename`

### 推荐工具

- **nmap**: 端口扫描和服务识别
- **gobuster**: 目录枚举
- **smbclient**: SMB 共享枚举和访问
- **msfconsole**: Metasploit Framework
- **Burp Suite**: Web 应用测试

### 学习路径

1. 完成本靶机
2. 阅读 `hacking.md` 查看详细攻击过程
3. 研究 CVE-2017-7494 的技术细节
4. 尝试手动编写 exploit
5. 学习如何防御此类攻击

---

## 👥 模块分工（供团队参考）

根据 `sum.md` 文档，项目分为 8 个模块：

| 成员 | 模块 | 主要工作 |
|------|------|----------|
| 成员A | 1+2 | Web 前端 + 数据库设计 |
| 成员B | 3+4 | PHP 后端 + 文件系统线索 |
| 成员C | 5+6 | Samba 配置 + 多端口服务 |
| 成员D | 7+8 | 线索设计 + Docker 集成 |

详细分工请参考 `sum.md` 文档。

---

## 🎓 教学建议

### 作为讲师

1. **课前准备**
   - 提前部署并测试环境
   - 准备网络拓扑图
   - 设置时间限制（建议 60-90 分钟）

2. **课程流程**
   - 介绍背景和目标
   - 学生独立或分组进行渗透测试
   - 收集并讨论不同的攻击路径
   - 讲解 CVE-2017-7494 原理
   - 演示防御措施

3. **评分要点**
   - 信息收集的完整性
   - 线索关联能力
   - 是否被假漏洞迷惑
   - 文档记录质量
   - 最终是否成功利用

### 作为学生

1. **准备工作**
   - 复习端口扫描和服务枚举
   - 了解常见 Web 漏洞
   - 学习 SMB 协议基础
   - 准备渗透测试工具

2. **记录要求**
   - 记录所有发现的端口和服务
   - 记录每个线索的位置
   - 记录尝试过的攻击方法
   - 记录思路转变的过程

3. **注意事项**
   - 不要只盯着 Web 应用
   - 版本号是重要信息
   - 系统化整理线索
   - 及时搜索 CVE 数据库

---

## 🔄 更新日志

### v1.0.0 (2024-01-XX)
- ✨ 初始版本发布
- ✨ 包含 5 个服务容器
- ✨ 设计 8 个模块的线索链
- ✨ 完整的攻击文档

### 未来计划
- 🚧 添加更多假漏洞点
- 🚧 集成 ELK 日志分析
- 🚧 添加 IDS/IPS 检测演示
- 🚧 制作视频教程

---

## 📞 支持与反馈

如有问题或建议，请：

1. 查看 `hacking.md` 获取完整攻击流程（剧透！）
2. 检查本文档的故障排除部分
3. 查看 Docker 日志排查问题
4. 联系项目维护者

---

## 📄 许可证

本项目仅供教育和研究使用。

**免责声明**: 使用本靶机环境进行的任何测试活动应仅限于合法授权的环境。未经授权对真实系统进行渗透测试是违法行为。

---

## 🌟 致谢

- Vulhub 项目提供的 Samba 4.6.3 Docker 镜像
- CVE-2017-7494 漏洞的发现者和研究者
- 所有开源安全工具的贡献者

---

**祝你攻击愉快！记住：在真实世界中，要做有道德的黑客。** 🎯