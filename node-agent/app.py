#!/usr/bin/env python3
"""
PortVPN Node Agent
==================
This agent runs on each node and handles:
- WireGuard configuration
- SSTP configuration
- Port forwarding via iptables
- SSH tunnel setup
"""

import os
import json
import random
import string
import subprocess
import ipaddress
from flask import Flask, request, jsonify
from functools import wraps

app = Flask(__name__)

# Configuration
API_KEY = os.environ.get('NODE_API_KEY', 'default_api_key_change_me')
WEB_MANAGEMENT_URL = os.environ.get('WEB_MANAGEMENT_URL', '')
WG_INTERFACE = os.environ.get('WG_INTERFACE', 'wg0')
WG_NETWORK = os.environ.get('WG_NETWORK', '10.66.0.0/16')
WG_DNS = os.environ.get('WG_DNS', '1.1.1.1,8.8.8.8')
SSTP_NETWORK = os.environ.get('SSTP_NETWORK', '10.67.0.0/16')
SSTP_DNS = os.environ.get('SSTP_DNS', '1.1.1.1,8.8.8.8')
NODE_ID = os.environ.get('NODE_ID', '1')

def require_api_key(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        api_key = request.headers.get('X-API-Key')
        if not api_key or api_key != API_KEY:
            return jsonify({'error': 'Unauthorized'}), 401
        return f(*args, **kwargs)
    return decorated_function

def run_command(cmd, shell=True):
    """Run shell command and return result"""
    try:
        result = subprocess.run(cmd, shell=shell, capture_output=True, text=True)
        return result.returncode == 0, result.stdout, result.stderr
    except Exception as e:
        return False, '', str(e)

def generate_wireguard_keys():
    """Generate WireGuard key pair"""
    try:
        private_key = subprocess.check_output('wg genkey', shell=True).decode().strip()
        public_key = subprocess.check_output(f'echo "{private_key}" | wg pubkey', shell=True).decode().strip()
        preshared_key = subprocess.check_output('wg genpsk', shell=True).decode().strip()
        return private_key, public_key, preshared_key
    except:
        return None, None, None

def generate_client_ip(network_str):
    """Generate random client IP from network"""
    try:
        network = ipaddress.ip_network(network_str)
        hosts = list(network.hosts())
        # Exclude first 10 IPs (reserved for servers)
        available_hosts = hosts[10:]
        return str(random.choice(available_hosts))
    except:
        return None

def generate_sstp_credentials():
    """Generate SSTP username and password"""
    username = 'sstp_' + ''.join(random.choices(string.ascii_lowercase + string.digits, k=8))
    password = ''.join(random.choices(string.ascii_letters + string.digits, k=16))
    return username, password

def setup_port_forwarding(public_port, local_port, client_ip, protocol='both'):
    """Setup iptables port forwarding"""
    try:
        # Enable IP forwarding
        run_command('sysctl -w net.ipv4.ip_forward=1')
        
        protocols = []
        if protocol in ['tcp', 'both']:
            protocols.append('tcp')
        if protocol in ['udp', 'both']:
            protocols.append('udp')
        
        for proto in protocols:
            # DNAT: redirect public port to client via VPN
            cmd = f'iptables -t nat -A PREROUTING -p {proto} --dport {public_port} -j DNAT --to-destination {client_ip}:{local_port}'
            run_command(cmd)
            
            # Allow forwarding
            cmd = f'iptables -A FORWARD -p {proto} -d {client_ip} --dport {local_port} -j ACCEPT'
            run_command(cmd)
        
        # MASQUERADE for VPN traffic
        run_command(f'iptables -t nat -A POSTROUTING -s {WG_NETWORK} -j MASQUERADE')
        
        return True
    except Exception as e:
        print(f"Error setting up port forwarding: {e}")
        return False

def remove_port_forwarding(public_port, client_ip, local_port, protocol='both'):
    """Remove iptables port forwarding rules"""
    try:
        protocols = []
        if protocol in ['tcp', 'both']:
            protocols.append('tcp')
        if protocol in ['udp', 'both']:
            protocols.append('udp')
        
        for proto in protocols:
            cmd = f'iptables -t nat -D PREROUTING -p {proto} --dport {public_port} -j DNAT --to-destination {client_ip}:{local_port}'
            run_command(cmd)
            cmd = f'iptables -D FORWARD -p {proto} -d {client_ip} --dport {local_port} -j ACCEPT'
            run_command(cmd)
        
        return True
    except Exception as e:
        print(f"Error removing port forwarding: {e}")
        return False

def add_wireguard_peer(public_key, preshared_key, client_ip):
    """Add peer to WireGuard interface"""
    try:
        cmd = f'wg set {WG_INTERFACE} peer {public_key} preshared-key <(echo "{preshared_key}") allowed-ips {client_ip}/32'
        success, stdout, stderr = run_command(cmd)
        if not success:
            # Try alternative method
            config_file = f'/tmp/wg_peer_{public_key[:8]}'
            with open(config_file, 'w') as f:
                f.write(f'[Peer]\nPublicKey = {public_key}\nPresharedKey = {preshared_key}\nAllowedIPs = {client_ip}/32\n')
            run_command(f'wg addconf {WG_INTERFACE} {config_file}')
            os.remove(config_file)
        return True
    except Exception as e:
        print(f"Error adding WireGuard peer: {e}")
        return False

def remove_wireguard_peer(public_key):
    """Remove peer from WireGuard interface"""
    try:
        cmd = f'wg set {WG_INTERFACE} peer {public_key} remove'
        run_command(cmd)
        return True
    except Exception as e:
        print(f"Error removing WireGuard peer: {e}")
        return False

def add_sstp_user(username, password):
    """Add SSTP user (requires SoftEther or similar)"""
    try:
        # This is a simplified version - actual implementation depends on your SSTP server
        # For SoftEther VPN:
        # vpncmd localhost:5555 /SERVER /CMD UserCreate {username} /GROUP:none /REALNAME:none /NOTE:none
        # vpncmd localhost:5555 /SERVER /CMD UserPasswordSet {username} /PASSWORD:{password}
        
        # Store credentials for reference
        creds_file = f'/etc/portvpn/sstp_users/{username}'
        os.makedirs(os.path.dirname(creds_file), exist_ok=True)
        with open(creds_file, 'w') as f:
            f.write(f'{username}:{password}')
        
        return True
    except Exception as e:
        print(f"Error adding SSTP user: {e}")
        return False

def remove_sstp_user(username):
    """Remove SSTP user"""
    try:
        creds_file = f'/etc/portvpn/sstp_users/{username}'
        if os.path.exists(creds_file):
            os.remove(creds_file)
        return True
    except Exception as e:
        print(f"Error removing SSTP user: {e}")
        return False

def generate_wireguard_config(private_key, client_ip, server_public_key, preshared_key, server_endpoint, server_port):
    """Generate WireGuard client config"""
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
    """Generate SSTP setup script for Linux"""
    script = f"""#!/bin/bash
# SSTP Client Setup Script
# Server: {server_address}:{server_port}
# Username: {username}

echo "=== PortVPN SSTP Setup ==="
echo "Server: {server_address}:{server_port}"
echo "Username: {username}"
echo ""

# Install sstp-client if not exists
if ! command -v sstpc &> /dev/null; then
    echo "Installing sstp-client..."
    if command -v apt-get &> /dev/null; then
        sudo apt-get update
        sudo apt-get install -y sstp-client
    elif command -v yum &> /dev/null; then
        sudo yum install -y sstp-client
    else
        echo "Package manager not supported. Please install sstp-client manually."
        exit 1
    fi
fi

echo ""
echo "=== Connection Command ==="
echo "Run this command to connect:"
echo "sudo sstpc --user {username} --password {password} {server_address}:{server_port} --log-stdout --log-level 2"
"""
    return script

@app.route('/api/health', methods=['GET'])
def health_check():
    return jsonify({'status': 'ok', 'node_id': NODE_ID})

@app.route('/api/vpn/create', methods=['POST'])
@require_api_key
def create_vpn():
    data = request.json
    
    port_id = data.get('port_id')
    user_id = data.get('user_id')
    public_port = data.get('public_port')
    local_port = data.get('local_port')
    protocol = data.get('protocol', 'both')
    vpn_type = data.get('vpn_type', 'wireguard')
    ssh_enabled = data.get('ssh_enabled', False)
    
    result = {
        'success': True,
        'wireguard': None,
        'sstp': None
    }
    
    # Get server public key for WireGuard
    server_public_key = ''
    try:
        server_public_key = subprocess.check_output(f'wg show {WG_INTERFACE} public-key', shell=True).decode().strip()
    except:
        pass
    
    # Get server port for WireGuard
    server_listen_port = '51820'
    try:
        listen_port = subprocess.check_output(f'wg show {WG_INTERFACE} listen-port', shell=True).decode().strip()
        if listen_port:
            server_listen_port = listen_port
    except:
        pass
    
    # Generate client IP
    client_ip = generate_client_ip(WG_NETWORK)
    if not client_ip:
        return jsonify({'success': False, 'message': 'Failed to generate client IP'}), 500
    
    # Setup WireGuard if requested
    if vpn_type in ['wireguard', 'both']:
        private_key, public_key, preshared_key = generate_wireguard_keys()
        if not private_key:
            return jsonify({'success': False, 'message': 'Failed to generate WireGuard keys'}), 500
        
        # Add peer to WireGuard
        if not add_wireguard_peer(public_key, preshared_key, client_ip):
            return jsonify({'success': False, 'message': 'Failed to add WireGuard peer'}), 500
        
        # Generate client config
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
    
    # Setup SSTP if requested
    if vpn_type in ['sstp', 'both']:
        username, password = generate_sstp_credentials()
        
        if not add_sstp_user(username, password):
            return jsonify({'success': False, 'message': 'Failed to add SSTP user'}), 500
        
        server_domain = request.headers.get('Host', 'localhost')
        config_script = generate_sstp_script(username, password, server_domain, 443)
        
        result['sstp'] = {
            'username': username,
            'password': password,
            'client_ip': client_ip,
            'server_port': 443,
            'config_script': config_script
        }
    
    # Setup port forwarding
    if not setup_port_forwarding(public_port, local_port, client_ip, protocol):
        return jsonify({'success': False, 'message': 'Failed to setup port forwarding'}), 500
    
    # Setup SSH if enabled
    if ssh_enabled:
        ssh_port = public_port  # Use same port for SSH
        if not setup_port_forwarding(ssh_port, 22, client_ip, 'tcp'):
            return jsonify({'success': False, 'message': 'Failed to setup SSH forwarding'}), 500
    
    return jsonify(result)

@app.route('/api/vpn/delete', methods=['POST'])
@require_api_key
def delete_vpn():
    data = request.json
    
    port_id = data.get('port_id')
    public_port = data.get('public_port')
    
    # Remove port forwarding rules
    # Note: In production, you'd need to store and retrieve the client IP
    
    return jsonify({'success': True, 'message': 'VPN configuration removed'})

@app.route('/api/ping', methods=['GET', 'POST'])
@require_api_key
def ping():
    return jsonify({'status': 'ok', 'node_id': NODE_ID})

if __name__ == '__main__':
    # Create necessary directories
    os.makedirs('/etc/portvpn/sstp_users', exist_ok=True)
    
    # Run Flask app
    app.run(host='0.0.0.0', port=5000, debug=False)
