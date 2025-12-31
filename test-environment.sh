#!/bin/bash

# 测试环境设置脚本
echo "🧪 Testing Enhanced SambaCry Environment..."
echo "=================================="

# 检查 Docker 容器状态
echo "📋 Checking container status..."
docker-compose ps

echo ""
echo "🔍 Testing service connectivity..."
echo "=================================="

# 等待服务启动
echo "⏳ Waiting for services to start..."
sleep 10

# 测试 Web 服务器
echo "🌐 Testing Web Server (Port 8080)..."
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/
echo " - Web server response"

# 测试安全监控
echo "📊 Testing Security Monitor (Port 8081)..."
curl -s -o /dev/null -w "%{http_code}" http://localhost:8081/
echo " - Security monitor response"

# 测试数据库连接
echo "🗄️ Testing Database (Port 3306)..."
timeout 5 bash -c "</dev/tcp/localhost/3306" && echo "✅ Database port open" || echo "❌ Database port closed"

# 测试 SSH 蜜罐
echo "🍯 Testing SSH Honeypot (Port 2222)..."
timeout 5 bash -c "</dev/tcp/localhost/2222" && echo "✅ SSH honeypot port open" || echo "❌ SSH honeypot port closed"

# 测试 Samba 端口
echo "📁 Testing Samba (Port 445)..."
timeout 5 bash -c "</dev/tcp/localhost/445" && echo "✅ Samba port open" || echo "❌ Samba port closed"

echo ""
echo "🎯 Testing Samba functionality..."
echo "=================================="

# 测试 Samba 匿名访问
echo "🔍 Testing anonymous Samba access..."
smbclient -L //localhost -N 2>/dev/null && echo "✅ Anonymous listing successful" || echo "❌ Anonymous listing failed"

# 测试认证访问
echo "🔑 Testing authenticated Samba access..."
(echo "sambapass"; echo "sambapass") | smbclient //localhost/private -U sambauser -c "ls" 2>/dev/null && echo "✅ Authenticated access successful" || echo "❌ Authenticated access failed"

echo ""
echo "🌐 Testing Web Application vulnerabilities..."
echo "=================================="

# 测试 SQL 注入
echo "💉 Testing SQL injection..."
curl -s -X POST "http://localhost:8080/" -d "username=' OR '1'='1&password=' OR '1'='1&login=1" | grep -q "Login successful" && echo "⚠️  SQL injection working" || echo "✅ SQL injection patched"

# 测试文件上传
echo "📤 Testing file upload..."
echo "Test file content" > /tmp/test.txt
curl -s -X POST "http://localhost:8080/fileupload.php" -F "file=@/tmp/test.txt" | grep -q "uploaded successfully" && echo "⚠️  File upload working" || echo "✅ File upload restricted"
rm -f /tmp/test.txt

echo ""
echo "🔒 Checking for misdirection and false security..."
echo "=================================="

# 检查假版本信息
echo "🔍 Checking version misdirection..."
curl -s http://localhost:8080/ | grep -q "4.6.4" && echo "⚠️  Found false version info" || echo "✅ No false version info"

# 检查假安全头部
echo "🔍 Checking security headers..."
curl -s -I http://localhost:8080/ | grep -q "X-Security-Level" && echo "⚠️  Found false security headers" || echo "✅ No false security headers"

echo ""
echo "🏁 Environment Status Summary"
echo "=================================="
echo "✅ Enhanced SambaCry environment is ready!"
echo "🎯 Main vulnerabilities to find:"
echo "   1. CVE-2017-7494 Samba vulnerability (despite false 4.6.4 version)"
echo "   2. SQL injection in web application"
echo "   3. File upload vulnerabilities"
echo "   4. Admin panel authentication bypass"
echo "   5. Information disclosure"
echo ""
echo "🧭 Attack paths:"
echo "   • Direct Samba exploitation (primary)"
echo "   • Web app -> file upload -> Samba execution"
echo "   • SQL injection -> credential harvesting -> authenticated access"
echo "   • Admin panel -> system command execution"
echo ""
echo "📚 Documentation:"
echo "   • README.md - Complete attack guide"
echo "   • README.zh-cn.md - Chinese version"
echo "   • Security monitor: http://localhost:8081"
echo ""
echo "🎉 Happy hacking!"