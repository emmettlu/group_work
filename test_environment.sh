#!/bin/bash
# 环境测试脚本

echo "🧪 FileHub 环境测试"
echo "═══════════════════════════════════════════"
echo ""

# 测试 Web 服务
echo "📝 测试 Web 服务 (80)..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200"; then
    echo "   ✅ Web 服务正常"
else
    echo "   ❌ Web 服务异常"
fi

# 测试 robots.txt
echo "📝 测试 robots.txt..."
if curl -s http://localhost/robots.txt | grep -q "Disallow"; then
    echo "   ✅ robots.txt 可访问"
else
    echo "   ❌ robots.txt 不可访问"
fi

# 测试 backup 目录
echo "📝 测试 backup 目录..."
if curl -s http://localhost/backup/old_smb.conf | grep -q "4.6.3"; then
    echo "   ✅ backup/old_smb.conf 包含版本号"
else
    echo "   ❌ backup/old_smb.conf 异常"
fi

# 测试 debug 页面
echo "📝 测试 debug 页面..."
if curl -s http://localhost/debug/info.php | grep -q "Samba"; then
    echo "   ✅ debug/info.php 可访问"
else
    echo "   ❌ debug/info.php 异常"
fi

# 测试 SMB 服务
echo "📝 测试 SMB 服务 (445)..."
if command -v smbclient &> /dev/null; then
    if timeout 5 smbclient -L //localhost/ -N &> /dev/null; then
        echo "   ✅ SMB 服务正常"
    else
        echo "   ⚠️  SMB 服务可能未就绪（或需要安装 smbclient）"
    fi
else
    echo "   ⚠️  未安装 smbclient，跳过测试"
    echo "      安装: apt install smbclient (Ubuntu/Debian)"
fi

# 测试 MySQL
echo "📝 测试 MySQL 服务 (3306)..."
if nc -zv localhost 3306 2>&1 | grep -q "succeeded"; then
    echo "   ✅ MySQL 端口开放"
else
    echo "   ❌ MySQL 端口未开放"
fi

# 测试 FTP
echo "📝 测试 FTP 服务 (21)..."
if nc -zv localhost 21 2>&1 | grep -q "succeeded"; then
    echo "   ✅ FTP 端口开放"
else
    echo "   ❌ FTP 端口未开放"
fi

echo ""
echo "═══════════════════════════════════════════"
echo "测试完成！"
echo ""
echo "如果所有测试都通过，环境已准备就绪。"
echo "开始你的渗透测试之旅吧！ 🎯"
echo ""

