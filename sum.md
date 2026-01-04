# 项目模块划分方案

## 课题名称
基于 CVE-2017-7494 Samba MS-RPC 命令注入漏洞的 CentOS 靶机设计与实现

## 总体设计思路
本靶机采用多层迷惑设计，首先暴露一个看似存在漏洞的 Web 应用，通过 SQL 注入、文件包含等假漏洞点和多处隐藏线索，引导攻击者逐步发现真正的攻击面——Samba 服务的 CVE-2017-7494 漏洞。

## 模块划分（8个模块）

### 模块1：Web 前端界面设计
**负责人：成员A**
- 设计一个企业文件管理系统的前端界面
- 包含：登录页、文件列表页、用户管理页、系统设置页
- 使用 Bootstrap 美化，看起来像真实的企业应用
- 在页面中埋入隐藏的 HTML 注释线索
- 设计 404 页面，包含"有趣"的错误信息

**交付物：**
- `www/index.php` - 主页
- `www/login.php` - 登录页面
- `www/files.php` - 文件管理页面
- `www/admin.php` - 管理后台
- `www/css/` - 样式文件
- `www/js/` - JavaScript 文件

---

### 模块2：数据库设计与数据准备
**负责人：成员A**
- 设计 MySQL 数据库结构（users、files、logs、secrets 表）
- 在数据库中插入假用户数据和线索数据
- 在 `secrets` 表中藏入 Base64 编码的提示信息
- 设计一个看似可以 SQL 注入的查询（实际已过滤）
- 在某个字段中藏入 Samba 版本号的暗示

**交付物：**
- `database/init.sql` - 数据库初始化脚本
- `database/fake_data.sql` - 假数据和线索
- `database/schema.png` - 数据库结构图（文档用）

---

### 模块3：PHP 后端业务逻辑
**负责人：成员B**
- 实现登录验证逻辑（存在时间盲注的假象）
- 实现文件上传功能（有各种限制，无法真正利用）
- 设计多个子路由：`/api/`, `/backup/`, `/temp/`, `/debug/`
- 在 `/debug/info.php` 中暴露部分服务器信息（包括开放的 445 端口）
- 实现一个假的命令执行点（实际被 escapeshellarg 过滤）

**交付物：**
- `www/api/` - API 接口目录
- `www/includes/auth.php` - 认证逻辑
- `www/includes/upload.php` - 上传处理
- `www/debug/info.php` - 调试信息页面
- `www/backup/` - 备份目录（含线索）

---

### 模块4：文件系统与线索埋藏
**负责人：成员B**
- 在 Web 目录中创建 `.git` 目录暴露（含有配置文件）
- 创建 `robots.txt` 禁止访问某些"敏感"目录
- 在 `/backup/` 目录下放置旧版配置文件 `old_smb.conf`
- 在某个文件的注释中提到"网络共享服务器"
- 创建 `.env.example` 文件，包含 SMB 相关的环境变量

**交付物：**
- `www/.git/config` - Git 配置（含远程仓库线索）
- `www/robots.txt` - 爬虫配置
- `www/backup/old_smb.conf` - Samba 旧配置
- `www/.env.example` - 环境变量示例
- `www/README.md` - 项目说明（含历史版本信息）

---

### 模块5：Samba 服务配置与加固
**负责人：成员C**
- 配置 Samba 4.6.3 服务（漏洞版本）
- 设置共享目录，放置诱饵文件和最终 flag
- 配置匿名访问，但目录名不明显（如 `InternalBackup`）
- 在共享目录中放置 `company_secrets.txt` 作为诱饵
- 确保 Samba 服务不在第一次端口扫描时显示版本号

**交付物：**
- `smb.conf` - Samba 主配置文件（更新）
- `samba/share/` - 共享目录及内容
- `samba/share/flag.txt` - 最终 flag
- `samba/share/hint.txt` - 成功提示
- `samba/init.sh` - Samba 初始化脚本

---

### 模块6：多端口服务与蜜罐
**负责人：成员C**
- 开放多个端口：80 (Web), 3306 (MySQL), 445 (Samba), 21 (假FTP), 8080 (假管理后台)
- 在 21 端口设置一个假的 FTP banner（vsftpd 2.3.4 - 已知漏洞版本，实际不可利用）
- 在 8080 端口设置 Tomcat 假后台（只有登录页，无漏洞）
- 使用 iptables 限制某些端口的访问频率（反爆破）
- 记录所有端口访问日志

**交付物：**
- `services/fake_ftp.py` - 假 FTP 服务
- `services/fake_tomcat/` - 假 Tomcat 目录
- `services/monitor.sh` - 端口监控脚本
- `config/iptables.rules` - 防火墙规则
- `logs/` - 日志目录

---

### 模块7：线索链与攻击路径设计
**负责人：成员D**
- 设计完整的线索链路图
- 线索1：HTML 注释 → 提到"旧系统迁移"
- 线索2：SQL 查询结果 → Base64 解码得到"检查网络共享"
- 线索3：.git/config → 提到 samba 分支
- 线索4：/debug/info.php → 显示 445 端口开放
- 线索5：backup/old_smb.conf → 发现 Samba 4.6.x 版本号
- 线索6：搜索 CVE → 找到 CVE-2017-7494

**交付物：**
- `docs/clue_chain.md` - 线索链文档
- `docs/attack_path.png` - 攻击路径图
- `www/includes/clues.php` - 线索生成逻辑
- `docs/hints.json` - 所有线索的 JSON 配置
- `docs/solution.md` - 标准解题思路

---

### 模块8：Docker 环境集成与部署
**负责人：成员D**
- 编写完整的 docker-compose.yml（包含 Samba、Web、MySQL）
- 创建自定义 Dockerfile 整合所有服务
- 编写启动脚本，自动初始化所有服务
- 配置容器间网络，模拟真实内网环境
- 编写部署文档和使用说明

**交付物：**
- `docker-compose.yml` - Docker 编排文件（更新）
- `Dockerfile` - 自定义镜像
- `entrypoint.sh` - 容器启动脚本
- `deploy.sh` - 一键部署脚本
- `README_DEPLOY.md` - 部署说明文档

---

## 人员分配建议

| 成员 | 模块 | 技能要求 |
|------|------|----------|
| 成员A | 模块1 + 模块2 | 前端开发、数据库设计 |
| 成员B | 模块3 + 模块4 | PHP 开发、系统配置 |
| 成员C | 模块5 + 模块6 | Linux 服务配置、网络安全 |
| 成员D | 模块7 + 模块8 | 渗透测试思维、Docker/运维 |

---

## 时间规划

### 第一周
- 模块1、2、5、8 基础框架搭建
- 确定整体架构和接口

### 第二周
- 模块3、4、6 功能实现
- 模块7 线索设计

### 第三周
- 联调测试
- 完善 hacking.md 攻击文档
- 准备答辩材料

---

## 评分要点覆盖

1. ✅ **漏洞复现** - CVE-2017-7494 Samba 漏洞
2. ✅ **环境复杂度** - 多服务、多层迷惑、线索链
3. ✅ **隐蔽性** - 不直接暴露漏洞，需要逐步发现
4. ✅ **技术深度** - 涉及 Web、数据库、网络服务、容器化
5. ✅ **文档完整** - 包含攻击文档、部署文档、设计文档
6. ✅ **创新性** - 线索链设计、蜜罐、多重迷惑

---

## 核心创新点

1. **渐进式线索系统**：不直接暴露 Samba，通过多个线索逐步引导
2. **假漏洞干扰**：SQL 注入、命令执行、文件上传等假象，增加难度
3. **真实场景模拟**：模拟企业文件管理系统，具有实战意义
4. **多服务联动**：Web、数据库、Samba、假服务形成完整生态
5. **攻击心路历程**：详细记录攻击者的思维过程和试错经历