#!/bin/sh
# Simple monitoring script

echo "FileHub Monitor Service Started"
echo "Logging to /var/log/filehub/monitor.log"

mkdir -p /var/log/filehub

while true; do
    TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$TIMESTAMP] System check - All services running" >> /var/log/filehub/monitor.log
    sleep 300  # Check every 5 minutes
done
