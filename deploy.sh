#!/bin/bash
# FileHub - 一键部署脚本

set -e

echo "═══════════════════════════════════════════════"
echo "  FileHub CVE-2017-7494 靶机环境部署脚本"
echo "═══════════════════════════════════════════════"
echo ""

# 检查 Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker 未安装，请先安装 Docker"
    exit 1
fi

# 检查 Docker Compose
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose 未安装，请先安装 Docker Compose"
    exit 1
fi

echo "✓ Docker 环境检查通过"
echo ""

# 检查端口占用
echo "🔍 检查端口占用..."
PORTS=(21 80 445 3306 8080)
OCCUPIED=()

for port in "${PORTS[@]}"; do
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1 ; then
        OCCUPIED+=($port)
    fi
done

if [ ${#OCCUPIED[@]} -gt 0 ]; then
    echo "⚠️  以下端口已被占用: ${OCCUPIED[*]}"
    echo "   请关闭占用这些端口的进程，或修改 docker-compose.yml 中的端口映射"
    read -p "是否继续部署？(y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
else
    echo "✓ 所有端口可用"
fi

echo ""
echo "🚀 开始部署..."
echo ""

# 停止旧容器
if docker-compose ps -q | grep -q .; then
    echo "📦 停止旧容器..."
    docker-compose down
fi

# 启动服务
echo "📦 启动服务容器..."
docker-compose up -d

# 等待服务启动
echo ""
echo "⏳ 等待服务启动（约15秒）..."
sleep 15

# 检查服务状态
echo ""
echo "📊 服务状态："
docker-compose ps

echo ""
echo "═══════════════════════════════════════════════"
echo "  ✅ 部署完成！"
echo "═══════════════════════════════════════════════"
echo ""
echo "🎯 访问信息："
echo "   Web 应用:  http://localhost"
echo "   SMB 共享:  smb://localhost/myshare"
echo "   MySQL:     localhost:3306"
echo "   FTP:       localhost:21"
echo ""
echo "📚 文档："
echo "   部署文档:  README_DEPLOY.md"
echo "   攻击实录:  hacking.md (剧透警告！)"
echo "   线索设计:  docs/clue_chain.md"
echo ""
echo "🛠️  常用命令："
echo "   查看日志:  docker-compose logs -f"
echo "   停止环境:  docker-compose down"
echo "   重启服务:  docker-compose restart"
echo ""
echo "⚠️  安全提醒："
echo "   本环境包含严重漏洞，仅供学习使用"
echo "   不要暴露到公网，不要用于生产环境"
echo ""
echo "═══════════════════════════════════════════════"
echo ""
echo "祝你攻击愉快！ 🎯"
echo ""
