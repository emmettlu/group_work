# 🎯 FileHub - CVE-2017-7494 Samba 渗透测试靶机

> 基于 CVE-2017-7494 (SambaCry) 的综合性渗透测试训练环境

[![Difficulty](https://img.shields.io/badge/难度-中高级-orange.svg)](https://github.com)
[![Time](https://img.shields.io/badge/预计时间-45--60分钟-blue.svg)](https://github.com)
[![Docker](https://img.shields.io/badge/Docker-Required-2496ED.svg)](https://www.docker.com/)
[![License](https://img.shields.io/badge/License-Educational-green.svg)](https://github.com)

---

## 📖 项目简介

这是一个精心设计的渗透测试靶机环境，模拟真实企业的文件管理系统。通过多层迷惑和渐进式线索，引导攻击者从 Web 应用逐步发现真正的攻击面——**Samba 服务的 CVE-2017-7494 远程代码执行漏洞**。

### 🎓 课题背景

**课题名称**: 基于 CVE-2017-7494 Samba MS-RPC 命令注入漏洞的 CentOS 靶机设计与实现

**项目特点**:
- ✨ 多服务联动（Web + Database + SMB + FTP）
- ✨ 真假漏洞混合，增加识别难度
- ✨ 完整的线索链设计
- ✨ 真实场景模拟（企业系统迁移）
- ✨ Docker 一键部署

---

## 🚀 快速开始

### 前置要求

```bash
Docker >= 20.10
Docker Compose >= 1.29
内存 >= 2GB
磁盘 >= 5GB
```

### 一键启动

```bash
# 克隆项目
cd group_work

# 启动环境
docker-compose up -d

# 查看服务状态
docker-compose ps
```

### 访问目标

- 🌐 Web 应用: http://localhost
- 📁 SMB 服务: `smb://localhost/myshare`
- 💾 MySQL: localhost:3306
- 📦 FTP: localhost:21

---

## 🎯 攻击目标

**主要目标**: 
- 发现并利用 Samba 服务的 CVE-2017-7494 漏洞
- 获取系统最高权限（root shell）
- 找到隐藏的 flag 文件

**次要目标**:
- 练习系统化信息收集
- 学会识别真假漏洞
- 掌握线索关联分析
- 理解历史 CVE 的危害

---

## 📊 系统架构

```
┌─────────────────────────────────────────┐
│          攻击者 (Attacker)               │
└──────────────┬──────────────────────────┘
               │
    ┌──────────┼──────────┐
    ▼          ▼          ▼
┌────────┐ ┌────────┐ ┌────────┐
│Web:80  │ │FTP:21  │ │TC:8080 │  ← 迷惑层
│(线索)  │ │(蜜罐)  │ │(假象)  │
└────────┘ └────────┘ └────────┘
    │          │          │
    └──────────┼──────────┘
               ▼
    ┌──────────────────────┐
    │   MySQL:3306         │  ← 数据层
    │   (线索存储)          │
    └──────────────────────┘
               │
               ▼
    ┌──────────────────────┐
    │   Samba:445 ⚠️       │  ← 真实漏洞
    │   CVE-2017-7494      │
    └──────────────────────┘
```

---

## 🗂️ 项目结构

```
group_work/
├── 📄 docker-compose.yml       # Docker 编排配置
├── 📄 smb.conf                 # Samba 配置文件
├── 📄 entrypoint.sh            # 启动脚本
├── 📘 README_DEPLOY.md         # 详细部署文档 ★
├── 📕 hacking.md               # 完整攻击实录（剧透！）
├── 📗 sum.md                   # 8模块划分方案
│
├── 📁 www/                     # Web 应用
│   ├── index.php               # 登录页面
│   ├── login.php               # 认证逻辑（假SQL注入）
│   ├── robots.txt              # 线索入口
│   ├── .git/config             # Git泄露（线索）
│   ├── backup/                 # 关键线索目录
│   │   ├── old_smb.conf        # ★ Samba版本号
│   │   └── README.txt          # 迁移说明
│   └── debug/info.php          # ★ 系统信息
│
├── 📁 database/                # 数据库
│   ├── init.sql                # 表结构
│   └── fake_data.sql           # 假数据+线索
│
├── 📁 samba/share/             # SMB共享
│   ├── welcome.txt             # 欢迎文件
│   └── flag.txt                # 🎯 最终目标
│
├── 📁 services/                # 辅助服务
│   ├── fake_ftp.py             # vsftpd 2.3.4 蜜罐
│   └── monitor.sh              # 监控脚本
│
└── 📁 docs/                    # 文档
    └── clue_chain.md           # 线索链设计文档
```

---

## 🔗 线索链概览

```
🔍 端口扫描
  ↓
🌐 Web 应用 (80)
  ├─► 📝 HTML 注释 → "网络共享迁移"
  ├─► 🤖 robots.txt → 暴露敏感目录
  ├─► 🗂️ .git/config → "samba-migration"分支
  ├─► 📦 backup/old_smb.conf → ★ 版本号 4.6.3
  ├─► 📄 backup/README.txt → 确认445端口
  └─► 🐛 debug/info.php → 服务详情
  ↓
🔎 线索汇总
  ↓
💡 搜索 "Samba 4.6.3 vulnerability"
  ↓
🎯 发现 CVE-2017-7494 (SambaCry)
  ↓
🔓 SMB 枚举 → 可写共享
  ↓
💣 漏洞利用 → Root Shell
  ↓
🚩 获取 Flag
```

---

## 🎓 模块划分（4人小组）

| 成员 | 模块编号 | 主要工作 |
|------|---------|---------|
| **成员A** | 模块1+2 | Web前端设计 + 数据库设计 |
| **成员B** | 模块3+4 | PHP后端逻辑 + 文件系统线索 |
| **成员C** | 模块5+6 | Samba配置 + 多端口服务 |
| **成员D** | 模块7+8 | 线索链设计 + Docker集成 |

详细分工请参考 [sum.md](sum.md)

---

## 📚 文档导航

| 文档 | 说明 | 适用对象 |
|------|------|---------|
| [README_DEPLOY.md](README_DEPLOY.md) | 详细部署和使用指南 | 所有人 ★ |
| [hacking.md](hacking.md) | 完整攻击实录（**剧透警告**） | 讲师/复盘 |
| [sum.md](sum.md) | 8模块划分方案 | 开发团队 |
| [docs/clue_chain.md](docs/clue_chain.md) | 线索链设计文档 | 讲师/设计者 |
| [prompt.md](prompt.md) | 原始项目需求 | 开发团队 |

---

## 🛡️ 安全警告

```
⚠️  警告：本环境包含已知的严重安全漏洞！

❌ 不要在生产环境部署
❌ 不要暴露到公网
❌ 不要使用真实数据
✅ 仅在隔离的实验环境中使用
✅ 用于教育和培训目的
```

---

## 🎮 攻击提示（无剧透）

<details>
<summary>💡 提示 1: 从哪里开始？</summary>

先进行全面的信息收集：
- 端口扫描（nmap -sV）
- Web目录枚举（gobuster）
- 查看常见文件（robots.txt, README等）
</details>

<details>
<summary>💡 提示 2: 被卡住了？</summary>

不要在一个"漏洞"上花太多时间：
- SQL注入可能不是答案
- FTP后门可能是蜜罐
- 试试整理所有线索
</details>

<details>
<summary>💡 提示 3: 关键是什么？</summary>

**版本号！** 找到具体的服务版本号，然后搜索对应的CVE。
</details>

<details>
<summary>💡 提示 4: 真正的目标？</summary>

不是Web应用，而是一个网络服务。所有线索都在指向它。
</details>

---

## 🔧 管理命令

```bash
# 启动环境
docker-compose up -d

# 查看日志
docker-compose logs -f [service_name]

# 停止环境
docker-compose down

# 完全清理（包括数据）
docker-compose down -v

# 重启某个服务
docker-compose restart samba

# 进入容器调试
docker exec -it filehub_samba /bin/bash
```

---

## 📈 难度调整

### 降低难度
- 在首页直接提示"Samba服务"
- 减少假漏洞点
- 在debug/info.php中显示CVE编号

### 提高难度  
- 隐藏具体版本号，只显示"4.x"
- 增加更多红鲱鱼（假线索）
- 要求共享认证
- 添加IDS/IPS检测

---

## 🎯 学习成果

完成本靶机后，你将掌握：

- ✅ 系统化的信息收集方法论
- ✅ 真假漏洞的识别技巧
- ✅ CVE研究和exploit使用
- ✅ SMB协议和Samba服务知识
- ✅ 历史漏洞的现实威胁
- ✅ 完整的攻击链思维

---

## 🌟 核心创新点

1. **渐进式线索系统** - 不直接暴露，需要拼图
2. **假漏洞干扰** - SQL注入、FTP后门等迷惑点
3. **真实场景** - 模拟企业系统迁移
4. **多服务联动** - Web、数据库、文件共享完整生态
5. **心路历程** - 详细记录攻击者的思维过程

---

## 🤝 贡献指南

欢迎改进本项目：

- 🐛 报告 Bug
- 💡 提出新的线索设计
- 📝 改进文档
- 🎨 优化界面
- 🔧 添加新服务

---

## 📞 技术支持

遇到问题？

1. 查看 [README_DEPLOY.md](README_DEPLOY.md) 的故障排除章节
2. 查看 Docker 日志: `docker-compose logs`
3. 检查端口占用: `netstat -tulpn`
4. 参考 [hacking.md](hacking.md) 的完整流程（剧透）

---

## 📄 许可证

本项目仅供教育和研究使用。

**免责声明**: 使用本靶机进行的任何测试活动应仅限于合法授权的环境。未经授权对真实系统进行渗透测试是违法行为。

---

## 🙏 致谢

- [Vulhub](https://github.com/vulhub/vulhub) - Samba 4.6.3 Docker镜像
- CVE-2017-7494 漏洞研究者
- 开源安全社区的所有贡献者

---

## 📊 项目信息

- **创建时间**: 2024-01
- **版本**: v1.0.0
- **难度**: ★★★★☆
- **类型**: 综合渗透测试靶机
- **核心漏洞**: CVE-2017-7494 (CVSS 10.0)

---

<div align="center">

### 🎯 准备好接受挑战了吗？

```bash
docker-compose up -d
```

**记住：真正的黑客不仅会攻击，更会防御！**

---

Made with ❤️ for Security Education

</div>