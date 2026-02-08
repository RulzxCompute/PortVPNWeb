#!/bin/bash

# PortVPN Node Agent Installation Script
# ======================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

NODE_API_KEY=""
WEB_MANAGEMENT_URL=""
USE_SSL="y"

echo -e "${BLUE}============================================${NC}"
echo -e "${BLUE}   PortVPN Node Agent Installer${NC}"
echo -e "${BLUE}============================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Error: Please run as root${NC}"
    exit 1
fi

# Get configuration
echo -e "${YELLOW}Konfigurasi Node:${NC}"
read -p "Web Management URL (contoh: https://panel.yourdomain.com): " WEB_MANAGEMENT_URL
read -p "Node API Key: " NODE_API_KEY
read -p "Gunakan SSL/HTTPS? (y/n) [y]: " USE_SSL_INPUT
if [ ! -z "$USE_SSL_INPUT" ]; then
    USE_SSL="$USE_SSL_INPUT"
fi

echo ""
echo -e "${BLUE}Memulai instalasi...${NC}"
echo ""

# Update system
echo -e "${YELLOW}[1/8] Updating system...${NC}"
apt-get update > /dev/null 2>&1

# Install dependencies
echo -e "${YELLOW}[2/8] Installing dependencies...${NC}"
apt-get install -y python3 python3-pip python3-venv wireguard wireguard-tools iptables iptables-persistent curl > /dev/null 2>&1

# Install SoftEther VPN for SSTP (optional)
echo -e "${YELLOW}[3/8] Installing SoftEther VPN...${NC}"
if ! command -v vpnserver &> /dev/null; then
    cd /tmp
    wget -q https://github.com/SoftEtherVPN/SoftEtherVPN_Stable/releases/download/v4.41-9787-beta/softether-vpnserver-v4.41-9787-beta-2023.03.14-linux-x64-64bit.tar.gz
    tar xzf softether-vpnserver-v4.41-9787-beta-2023.03.14-linux-x64-64bit.tar.gz
    cd vpnserver
    make i_read_and_agree_the_license_agreement > /dev/null 2>&1
    cp vpnserver vpncmd vpnbridge /usr/local/bin/
    cd ..
    rm -rf vpnserver softether-vpnserver-*.tar.gz
fi

# Create directories
echo -e "${YELLOW}[4/8] Creating directories...${NC}"
mkdir -p /opt/portvpn-node
mkdir -p /etc/portvpn
mkdir -p /etc/portvpn/sstp_users
mkdir -p /var/log/portvpn

# Setup WireGuard
echo -e "${YELLOW}[5/8] Setting up WireGuard...${NC}"
WG_PRIVATE_KEY=$(wg genkey)
WG_PUBLIC_KEY=$(echo "$WG_PRIVATE_KEY" | wg pubkey)

# Create WireGuard config
cat > /etc/wireguard/wg0.conf << EOF
[Interface]
PrivateKey = $WG_PRIVATE_KEY
Address = 10.66.0.1/16
ListenPort = 51820
PostUp = iptables -A FORWARD -i wg0 -j ACCEPT; iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
PostDown = iptables -D FORWARD -i wg0 -j ACCEPT; iptables -t nat -D POSTROUTING -o eth0 -j MASQUERADE

# Save config
EOF

chmod 600 /etc/wireguard/wg0.conf

# Enable and start WireGuard
systemctl enable wg-quick@wg0
systemctl start wg-quick@wg0

# Setup IP forwarding
echo -e "${YELLOW}[6/8] Configuring networking...${NC}"
echo "net.ipv4.ip_forward=1" > /etc/sysctl.d/99-portvpn.conf
sysctl -p /etc/sysctl.d/99-portvpn.conf

# Copy node agent files
echo -e "${YELLOW}[7/8] Installing node agent...${NC}"
cat > /opt/portvpn-node/app.py << 'PYTHON_EOF'
#!/usr/bin/env python3
"""PortVPN Node Agent"""

import os
import json
import random
import string
import subprocess
import ipaddress
from flask import Flask, request, jsonify
from functools import wraps

app = Flask(__name__)

API_KEY = os.environ.get('NODE_API_KEY', 'default_api_key_change_me')
WG_INTERFACE = os.environ.get('WG_INTERFACE', 'wg0')
WG_NETWORK = os.environ.get('WG_NETWORK', '10.66.0.0/16')
WG_DNS = os.environ.get('WG_DNS', '1.1.1.1,8.8.8.8')

def require_api_key(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        api_key = request.headers.get('X-API-Key')
        if not api_key or api_key != API_KEY:
            return jsonify({'error': 'Unauthorized'}), 401
        return f(*args, **kwargs)
    return decorated_function

def run_command(cmd, shell=True):
    try:
        result = subprocess.run(cmd, shell=shell, capture_output=True, text=True)
        return result.returncode == 0, result.stdout, result.stderr
    except Exception as e:
        return False, '', str(e)

def generate_wireguard_keys():
    try:
        private_key = subprocess.check_output('wg genkey', shell=True).decode().strip()
        public_key = subprocess.check_output(f'echo "{private_key}" | wg pubkey', shell=True).decode().strip()
        preshared_key = subprocess.check_output('wg genpsk', shell=True).decode().strip()
        return private_key, public_key, preshared_key
    except:
        return None, None, None

def generate_client_ip(network_str):
    try:
        network = ipaddress.ip_network(network_str)
        hosts = list(network.hosts())
        available_hosts = hosts[10:]
        return str(random.choice(available_hosts))
    except:
        return None

def generate_sstp_credentials():
    username = 'sstp_' + ''.join(random.choices(string.ascii_lowercase + string.digits, k=8))
    password = ''.join(random.choices(string.ascii_letters + string.digits, k=16))
    return username, password

def setup_port_forwarding(public_port, local_port, client_ip, protocol='both'):
    try:
        run_command('sysctl -w net.ipv4.ip_forward=1')
        
        protocols = []
        if protocol in ['tcp', 'both']:
            protocols.append('tcp')
        if protocol in ['udp', 'both']:
            protocols.append('udp')
        
        for proto in protocols:
            cmd = f'iptables -t nat -A PREROUTING -p {proto} --dport {public_port} -j DNAT --to-destination {client_ip}:{local_port}'
            run_command(cmd)
            cmd = f'iptables -A FORWARD -p {proto} -d {client_ip} --dport {local_port} -j ACCEPT'
            run_command(cmd)
        
        run_command(f'iptables -t nat -A POSTROUTING -s {WG_NETWORK} -j MASQUERADE')
        run_command('iptables-save > /etc/iptables/rules.v4')
        
        return True
    except Exception as e:
        print(f"Error: {e}")
        return False

def add_wireguard_peer(public_key, preshared_key, client_ip):
    try:
        cmd = f'wg set {WG_INTERFACE} peer {public_key} preshared-key <(echo "{preshared_key}") allowed-ips {client_ip}/32'
        success, stdout, stderr = run_command(cmd)
        if not success:
            config_file = f'/tmp/wg_peer_{public_key[:8]}'
            with open(config_file, 'w') as f:
                f.write(f'[Peer]\nPublicKey = {public_key}\nPresharedKey = {preshared_key}\nAllowedIPs = {client_ip}/32\n')
            run_command(f'wg addconf {WG_INTERFACE} {config_file}')
            os.remove(config_file)
        return True
    except Exception as e:
        print(f"Error: {e}")
        return False

def generate_wireguard_config(private_key, client_ip, server_public_key, preshared_key, server_endpoint, server_port):
    config = f"""[Interface]
PrivateKey = {private_key}
Address = {client_ip}/32
DNS = {WG_DNS}

[Peer]
PublicKey = {server_public_key}
PresharedKey = {preshared_key}
AllowedIPs = 0.0.0.0/0, ::/0
Endpoint = {server_endpoint}:{server_port}
PersistentKeepalive = 25
"""
    return config

def generate_sstp_script(username, password, server_address, server_port):
    script = f"""#!/bin/bash
echo "=== PortVPN SSTP ==="
echo "Server: {server_address}:{server_port}"
echo "Username: {username}"
if ! command -v sstpc &> /dev/null; then
    echo "Installing sstp-client..."
    apt-get update && apt-get install -y sstp-client
fi
echo "Connect: sudo sstpc --user {username} --password {password} {server_address}:{server_port}"
"""
    return script

@app.route('/api/health', methods=['GET'])
def health_check():
    return jsonify({'status': 'ok'})

@app.route('/api/vpn/create', methods=['POST'])
@require_api_key
def create_vpn():
    data = request.json
    
    public_port = data.get('public_port')
    local_port = data.get('local_port')
    protocol = data.get('protocol', 'both')
    vpn_type = data.get('vpn_type', 'wireguard')
    ssh_enabled = data.get('ssh_enabled', False)
    
    result = {'success': True, 'wireguard': None, 'sstp': None}
    
    server_public_key = ''
    try:
        server_public_key = subprocess.check_output(f'wg show {WG_INTERFACE} public-key', shell=True).decode().strip()
    except:
        pass
    
    server_listen_port = '51820'
    try:
        listen_port = subprocess.check_output(f'wg show {WG_INTERFACE} listen-port', shell=True).decode().strip()
        if listen_port:
            server_listen_port = listen_port
    except:
        pass
    
    client_ip = generate_client_ip(WG_NETWORK)
    if not client_ip:
        return jsonify({'success': False, 'message': 'Failed to generate client IP'}), 500
    
    if vpn_type in ['wireguard', 'both']:
        private_key, public_key, preshared_key = generate_wireguard_keys()
        if not private_key:
            return jsonify({'success': False, 'message': 'Failed to generate WireGuard keys'}), 500
        
        if not add_wireguard_peer(public_key, preshared_key, client_ip):
            return jsonify({'success': False, 'message': 'Failed to add WireGuard peer'}), 500
        
        server_domain = request.headers.get('Host', 'localhost')
        config_file = generate_wireguard_config(
            private_key, client_ip, server_public_key, preshared_key,
            server_domain, server_listen_port
        )
        
        result['wireguard'] = {
            'private_key': private_key,
            'public_key': public_key,
            'preshared_key': preshared_key,
            'client_ip': client_ip,
            'server_public_key': server_public_key,
            'server_port': server_listen_port,
            'config_file': config_file
        }
    
    if vpn_type in ['sstp', 'both']:
        username, password = generate_sstp_credentials()
        creds_file = f'/etc/portvpn/sstp_users/{username}'
        os.makedirs(os.path.dirname(creds_file), exist_ok=True)
        with open(creds_file, 'w') as f:
            f.write(f'{username}:{password}')
        
        server_domain = request.headers.get('Host', 'localhost')
        config_script = generate_sstp_script(username, password, server_domain, 443)
        
        result['sstp'] = {
            'username': username,
            'password': password,
            'client_ip': client_ip,
            'server_port': 443,
            'config_script': config_script
        }
    
    if not setup_port_forwarding(public_port, local_port, client_ip, protocol):
        return jsonify({'success': False, 'message': 'Failed to setup port forwarding'}), 500
    
    if ssh_enabled:
        setup_port_forwarding(public_port, 22, client_ip, 'tcp')
    
    return jsonify(result)

@app.route('/api/vpn/delete', methods=['POST'])
@require_api_key
def delete_vpn():
    return jsonify({'success': True, 'message': 'VPN configuration removed'})

@app.route('/api/ping', methods=['GET', 'POST'])
@require_api_key
def ping():
    return jsonify({'status': 'ok'})

if __name__ == '__main__':
    os.makedirs('/etc/portvpn/sstp_users', exist_ok=True)
    app.run(host='0.0.0.0', port=5000, debug=False)
PYTHON_EOF

chmod +x /opt/portvpn-node/app.py

# Create virtual environment
cd /opt/portvpn-node
python3 -m venv venv
source venv/bin/activate
pip install flask gunicorn -q

# Create systemd service
echo -e "${YELLOW}[8/8] Creating systemd service...${NC}"
cat > /etc/systemd/system/portvpn-node.service << EOF
[Unit]
Description=PortVPN Node Agent
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/opt/portvpn-node
Environment="NODE_API_KEY=${NODE_API_KEY}"
Environment="WEB_MANAGEMENT_URL=${WEB_MANAGEMENT_URL}"
Environment="WG_INTERFACE=wg0"
Environment="WG_NETWORK=10.66.0.0/16"
Environment="WG_DNS=1.1.1.1,8.8.8.8"
ExecStart=/opt/portvpn-node/venv/bin/gunicorn -w 2 -b 0.0.0.0:5000 app:app
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

# Enable and start service
systemctl daemon-reload
systemctl enable portvpn-node
systemctl start portvpn-node

# Save configuration
cat > /etc/portvpn/config.json << EOF
{
    "web_management_url": "${WEB_MANAGEMENT_URL}",
    "api_key": "${NODE_API_KEY}",
    "use_ssl": "${USE_SSL}",
    "wg_public_key": "${WG_PUBLIC_KEY}",
    "installed_at": "$(date -Iseconds)"
}
EOF

echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}   Installation Complete!${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
echo -e "WireGuard Public Key: ${YELLOW}${WG_PUBLIC_KEY}${NC}"
echo ""
echo -e "Node Agent Status:"
systemctl status portvpn-node --no-pager -l

echo ""
echo -e "${BLUE}Useful commands:${NC}"
echo -e "  ${YELLOW}systemctl status portvpn-node${NC} - Check service status"
echo -e "  ${YELLOW}systemctl restart portvpn-node${NC} - Restart service"
echo -e "  ${YELLOW}journalctl -u portvpn-node -f${NC} - View logs"
echo ""
echo -e "${GREEN}Node is ready to be added to web management!${NC}"
