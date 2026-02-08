<?php

namespace App\Services;

class WireguardService
{
    public function generateKeyPair(): array
    {
        $privateKey = trim(shell_exec('wg genkey'));
        $publicKey = trim(shell_exec("echo '{$privateKey}' | wg pubkey"));
        $presharedKey = trim(shell_exec('wg genpsk'));

        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey,
            'preshared_key' => $presharedKey,
        ];
    }

    public function generateClientIp(string $network): string
    {
        $parts = explode('/', $network);
        $baseIp = $parts[0];
        $baseParts = explode('.', $baseIp);
        
        // Generate random IP in the network
        $octet3 = rand(1, 254);
        $octet4 = rand(2, 254);
        
        return "{$baseParts[0]}.{$baseParts[1]}.{$octet3}.{$octet4}";
    }

    public function generateConfig(
        string $privateKey,
        string $clientIp,
        string $serverPublicKey,
        string $serverEndpoint,
        int $serverPort,
        string $presharedKey,
        string $dns = '1.1.1.1,8.8.8.8'
    ): string {
        return <<<CONFIG
[Interface]
PrivateKey = {$privateKey}
Address = {$clientIp}/32
DNS = {$dns}

[Peer]
PublicKey = {$serverPublicKey}
PresharedKey = {$presharedKey}
AllowedIPs = 0.0.0.0/0, ::/0
Endpoint = {$serverEndpoint}:{$serverPort}
PersistentKeepalive = 25
CONFIG;
    }

    public function generateServerConfig(
        string $privateKey,
        int $listenPort,
        string $network
    ): string {
        return <<<CONFIG
[Interface]
PrivateKey = {$privateKey}
Address = {$network}
ListenPort = {$listenPort}
PostUp = iptables -A FORWARD -i %i -j ACCEPT; iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
PostDown = iptables -D FORWARD -i %i -j ACCEPT; iptables -t nat -D POSTROUTING -o eth0 -j MASQUERADE
CONFIG;
    }

    public function generatePeerConfig(
        string $publicKey,
        string $presharedKey,
        string $clientIp
    ): string {
        return <<<CONFIG

[Peer]
PublicKey = {$publicKey}
PresharedKey = {$presharedKey}
AllowedIPs = {$clientIp}/32
CONFIG;
    }
}
