#!/usr/bin/env python3
"""
Fake FTP server that mimics vsftpd 2.3.4 (backdoor version)
This is a honeypot - it won't actually execute the backdoor
"""

import socket
import time

BANNER = "220 (vsFTPd 2.3.4)\r\n"
PORT = 21

def handle_client(conn, addr):
    print(f"[+] Connection from {addr}")
    
    # Send banner
    conn.send(BANNER.encode())
    
    backdoor_triggered = False
    
    while True:
        try:
            data = conn.recv(1024).decode('utf-8', errors='ignore').strip()
            if not data:
                break
                
            print(f"[*] Received: {data}")
            
            # Check for backdoor trigger :)
            if 'USER' in data and ':)' in data:
                backdoor_triggered = True
                conn.send(b"331 Please specify the password.\r\n")
                print(f"[!] Backdoor trigger detected from {addr}!")
                # In real backdoor, would open port 6200
                # Here we just log it
                time.sleep(2)  # Simulate delay
                
            elif data.startswith('USER'):
                conn.send(b"331 Please specify the password.\r\n")
                
            elif data.startswith('PASS'):
                # Always fail
                conn.send(b"530 Login incorrect.\r\n")
                time.sleep(1)
                
            elif data.startswith('QUIT'):
                conn.send(b"221 Goodbye.\r\n")
                break
                
            else:
                conn.send(b"500 Unknown command.\r\n")
                
        except Exception as e:
            print(f"[!] Error: {e}")
            break
    
    conn.close()
    print(f"[-] Connection from {addr} closed")

def main():
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    server.bind(('0.0.0.0', PORT))
    server.listen(5)
    
    print(f"[*] Fake FTP Server (vsFTPd 2.3.4) listening on port {PORT}")
    print("[*] This is a honeypot - backdoor won't work!")
    
    while True:
        try:
            conn, addr = server.accept()
            handle_client(conn, addr)
        except KeyboardInterrupt:
            print("\n[*] Shutting down...")
            break
        except Exception as e:
            print(f"[!] Error: {e}")
    
    server.close()

if __name__ == '__main__':
    main()
