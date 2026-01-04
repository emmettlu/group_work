# CVE-2017-7494 Samba 漏洞靶机

## 项目简介

这是一个基于 **CVE-2017-7494 Samba MS-RPC 远程代码执行漏洞**的渗透测试靶机环境。

通过精心设计的多层迷惑和引导机制，让渗透测试人员体验从信息收集、Web应用测试、数据库探索到最终发现并利用 Samba 漏洞的完整攻击链。

## 核心特性

- ✅ **真实漏洞环境**: 基于真实的 CVE-2017-7494 高危漏洞
- ✅ **多层迷惑设计**: Web应用、数据库、文件共享服务层层递进
- ✅ **引导式学习**: 通过日志、配置、文档逐步引导攻击者
- ✅ **完整攻击链**: 涵盖信息收集、漏洞发现、漏洞利用全流程
- ✅ **容器化部署**: 基于 Docker Compose，一键启动
- ✅ **教育性强**: 适合网络安全培训和渗透测试练习

## 快速开始

### 前置要求

- Docker Engine 20.10+
- Docker Compose 1.29+
- 至少 4GB 内存

### 启动靶机

```bash
# 克隆项目
git clone <repository_url>
cd group_work

# 启动所有服务
docker-compose up -d

# 查看服务状态
docker-compose ps
```

### 访问靶机

- **Web界面**: http://<靶机IP>
- **测试账号**: admin/admin123, developer/dev123
- **SMB共享**: //<靶机IP>/myshare

详细使用说明请查看 [quick_start.md](quick_start.md)

## 攻击路径概览

```
1. 端口扫描 → 发现 Web(80) 和 SMB(445) 服务
2. Web登录 → 弱密码突破（迷惑层1）
3. 信息收集 → 系统日志、配置页面发现 Samba 线索
4. 数据库查询 → 执行存储过程获取关键提示（迷惑层2）
5. SMB探索 → 匿名访问共享目录，下载维护文档
6. 恍然大悟 → 发现 CVE-2017-7494 漏洞详情
7. 漏洞利用 → 下载 exploit，获取 root shell
```

完整攻击过程和心路历程请查看 [hacking.md](hacking.md)

## 项目结构

```
group_work/
├── docker-compose.yml          # Docker 编排配置
├── web/                        # Web 应用
│   ├── nginx.conf             # Nginx 配置
│   ├── html/                  # PHP 应用代码
│   │   ├── index.php         # 登录页面
│   │   ├── files/            # 文件管理
│   │   ├── admin/            # 管理页面
│   │   └── css/              # 样式文件
│   └── php/                   # PHP 配置
├── db/                         # 数据库
│   └── init.sql              # 初始化脚本
├── samba/                      # Samba 服务
│   ├── smb.conf              # Samba 配置
│   └── share/                # 共享目录
│       ├── README.txt        # 共享说明
│       ├── server_notes.txt  # 维护笔记（关键线索）
│       └── backup_config.txt # 配置备份
├── hacking.md                  # 攻击过程详解
├── quick_start.md              # 快速开始指南
├── distribution.md             # 模块分工说明
└── convert_to_ova.sh          # OVA 转换脚本
```

## 技术架构

- **Web 层**: Nginx + PHP 7.4 + MySQL 5.7
- **漏洞层**: Samba 4.6.3 (CVE-2017-7494)
- **容器化**: Docker + Docker Compose
- **网络**: 自定义 bridge 网络

## CVE-2017-7494 漏洞说明

### 漏洞信息

- **CVE编号**: CVE-2017-7494
- **漏洞名称**: Samba Remote Code Execution (SambaCry)
- **CVSS评分**: 10.0 (Critical)
- **影响版本**: Samba 3.5.0 - 4.6.4
- **发现日期**: 2017年5月

### 漏洞原理

攻击者可以通过上传恶意共享库文件到可写的 Samba 共享目录，然后利用特制的 SMB 请求触发加载并执行该库文件，从而获得远程代码执行权限。

### 利用条件

✓ 目标运行易受攻击的 Samba 版本  
✓ 共享目录可写 (read only = no)  
✓ 允许访问 SMB 服务 (445 端口)  
✓ 匿名访问或弱认证 (可选)

## 学习目标

通过本靶机，你将学习到：

1. **信息收集技术** - 端口扫描、服务枚举、指纹识别
2. **Web应用测试** - 弱密码、信息泄露、权限管理
3. **数据库安全** - SQL查询、敏感信息存储、配置管理
4. **SMB协议** - 匿名访问、共享枚举、文件操作
5. **CVE研究** - 漏洞查询、exploit使用、漏洞验证
6. **后渗透技术** - Shell操作、权限维持、痕迹清理
7. **攻击链构建** - 信息关联、攻击路径规划

## 模块分工

本项目由4人小组完成，共8个模块：

| 成员 | 负责模块 |
|------|---------|
| 成员A | 环境配置与Docker编排、Web服务器配置 |
| 成员B | PHP前端开发、PHP后端逻辑 |
| 成员C | 数据库设计、Samba服务配置 |
| 成员D | 漏洞利用验证、文档编写与整合 |

详细分工请查看 [distribution.md](distribution.md)

## 常见问题

### 如何停止服务？

```bash
docker-compose stop
```

### 如何重启服务？

```bash
docker-compose restart
```

### 如何查看日志？

```bash
docker-compose logs -f
```

### 如何完全清理？

```bash
docker-compose down -v
```

### SMB连接失败？

检查 445 端口是否被占用：
```bash
netstat -tlnp | grep 445
```

Windows 用户可能需要禁用 SMB 服务。

## 安全警告

⚠️ **重要提示**：

1. **仅供学习使用** - 本靶机包含已知的严重安全漏洞
2. **隔离环境部署** - 不要暴露到公网或生产网络
3. **合法授权使用** - 仅在获得授权的测试环境中使用
4. **禁止非法攻击** - 不得用于未授权的系统攻击
5. **数据安全注意** - 不要存储任何真实敏感数据

## 参考资源

### 官方资源
- [Samba Security Advisory](https://www.samba.org/samba/security/CVE-2017-7494.html)
- [CVE-2017-7494 Details](https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2017-7494)
- [NVD Entry](https://nvd.nist.gov/vuln/detail/CVE-2017-7494)

### Exploit资源
- [ExploitDB](https://www.exploit-db.com/exploits/42084)
- [Metasploit Module](https://www.rapid7.com/db/modules/exploit/linux/samba/is_known_pipename/)

## 贡献指南

欢迎提交问题报告和改进建议：

1. Fork 本项目
2. 创建特性分支
3. 提交更改
4. 发起 Pull Request

## 许可证

本项目仅供教育和研究目的使用。

## 致谢

- Samba 开源项目
- Vulhub 项目（提供漏洞镜像）
- 网络安全社区

---

**免责声明**: 本项目仅用于授权的网络安全培训和测试。使用者需对自己的行为负责，项目作者不承担任何法律责任。

**祝你学习愉快！Happy Hacking! 🎯**