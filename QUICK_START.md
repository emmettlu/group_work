# 🚀 快速开始指南

## 5分钟部署

### 1. 检查环境

```bash
# 检查 Docker
docker --version

# 检查 Docker Compose
docker-compose --version
```

### 2. 启动靶机

```bash
# 进入项目目录
cd group_work

# 一键部署
./deploy.sh

# 或者手动启动
docker-compose up -d
```

### 3. 验证部署

```bash
# 运行测试脚本
./test_environment.sh

# 或手动检查
curl http://localhost
```

---

## 10秒开始攻击

```bash
# 1. 端口扫描
nmap -sV -p- localhost

# 2. Web枚举
gobuster dir -u http://localhost/ -w /usr/share/wordlists/dirb/common.txt

# 3. 查看关键文件
curl http://localhost/robots.txt
curl http://localhost/backup/old_smb.conf

# 4. SMB枚举
smbclient -L //localhost/ -N
```

---

## 🎯 攻击目标

- 找到 Samba 服务的版本号
- 搜索对应的 CVE 漏洞
- 利用漏洞获取 root shell
- 读取 flag.txt

---

## 📚 关键文档

| 要看什么 | 看这个文件 |
|---------|-----------|
| 如何部署 | README_DEPLOY.md |
| 完整攻击过程（剧透！） | hacking.md |
| 线索在哪里 | docs/clue_chain.md |
| 模块怎么分 | sum.md |

---

## 💡 核心提示

1. **版本号很重要** - 找到具体版本号
2. **不只是Web** - 注意其他服务
3. **整理线索** - 把发现的东西记下来
4. **搜索CVE** - 版本号 + vulnerability

---

## 🛠️ 常用命令

```bash
# 查看日志
docker-compose logs -f

# 重启服务
docker-compose restart

# 停止环境
docker-compose down

# 完全清理
docker-compose down -v
```

---

## ❓ 卡住了？

1. 查看 [线索提示](docs/clue_chain.md)
2. 检查 backup 目录
3. 查看 debug 信息页面
4. 搜索 "Samba" + "版本号" + "vulnerability"

---

## ⚠️ 记住

- 这是训练环境，包含真实漏洞
- 不要暴露到公网
- 仅供学习使用

---

**准备好了吗？开始吧！** 🎯
