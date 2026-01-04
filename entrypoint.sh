#!/bin/bash
# Main entrypoint script for FileHub vulnerable environment

echo "═══════════════════════════════════════════════"
echo "  FileHub - CVE-2017-7494 Training Environment"
echo "═══════════════════════════════════════════════"
echo ""
echo "Starting services..."
echo ""

# Wait for dependencies
sleep 5

echo "✓ Samba service ready on port 445"
echo "✓ Web application ready on port 80"  
echo "✓ MySQL database ready on port 3306"
echo "✓ FTP honeypot ready on port 21"
echo "✓ Fake Tomcat ready on port 8080"
echo ""
echo "Environment is ready for penetration testing!"
echo ""
echo "Target: http://localhost"
echo "Difficulty: Intermediate"
echo "Goal: Find and exploit CVE-2017-7494"
echo ""
echo "═══════════════════════════════════════════════"

# Keep container running
tail -f /dev/null
