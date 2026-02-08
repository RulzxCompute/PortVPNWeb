<?php

namespace App\Services;

use Illuminate\Support\Str;

class SstpService
{
    public function generateCredentials(): array
    {
        return [
            'username' => 'sstp_' . Str::random(8),
            'password' => Str::random(16),
        ];
    }

    public function generateClientIp(string $network): string
    {
        $parts = explode('/', $network);
        $baseIp = $parts[0];
        $baseParts = explode('.', $baseIp);
        
        $octet3 = rand(1, 254);
        $octet4 = rand(2, 254);
        
        return "{$baseParts[0]}.{$baseParts[1]}.{$octet3}.{$octet4}";
    }

    public function generateLinuxScript(
        string $username,
        string $password,
        string $serverAddress,
        int $serverPort
    ): string {
        return <<<SCRIPT
#!/bin/bash
# SSTP Client Setup Script
# Server: {$serverAddress}:{$serverPort}
# Username: {$username}

echo "=== PortVPN SSTP Setup ==="
echo "Server: {$serverAddress}:{$serverPort}"
echo "Username: {$username}"
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
echo "sudo sstpc --user {$username} --password {$password} {$serverAddress}:{$serverPort} --log-stdout --log-level 2"
echo ""
echo "Or create a persistent connection:"
echo "sudo nano /etc/ppp/peers/sstp-{$username}"
echo ""
echo "Add these lines:"
echo "plugin sstp-ppp.so"
echo "sstp-srv-addr {$serverAddress}"
echo "sstp-user {$username}"
echo "sstp-pass {$password}"
echo "sstp-port {$serverPort}"
echo ""
echo "Then connect with: sudo pon sstp-{$username}"
echo "Disconnect with: sudo poff sstp-{$username}"
SCRIPT;
    }

    public function generateWindowsScript(
        string $username,
        string $password,
        string $serverAddress
    ): string {
        return <<<SCRIPT
@echo off
echo === PortVPN SSTP Windows Setup ===
echo Server: {$serverAddress}
echo Username: {$username}
echo.
echo Creating VPN connection...
echo.

rasdial "PortVPN-SSTP" /disconnect >nul 2>&1
rasdial "PortVPN-SSTP" {$username} {$password} /phonebook:"%USERPROFILE%\PortVPN.pbk"

if %errorlevel% == 0 (
    echo Connected successfully!
) else (
    echo Creating new connection...
    powershell -Command "Add-VpnConnection -Name 'PortVPN-SSTP' -ServerAddress '{$serverAddress}' -TunnelType SSTP -AuthenticationMethod MSChapv2 -EncryptionLevel Required -Force"
    echo.
    echo Connection created. Please connect manually:
    echo 1. Open Settings > Network & Internet > VPN
    echo 2. Click on 'PortVPN-SSTP'
    echo 3. Click Connect
    echo.
    echo Username: {$username}
    echo Password: {$password}
)
pause
SCRIPT;
    }
}
