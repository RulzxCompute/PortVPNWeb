<?php

namespace App\Services;

use App\Models\Node;
use App\Models\Port;
use App\Models\WireguardConfig;
use App\Models\SstpConfig;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NodeService
{
    protected $timeout;

    public function __construct()
    {
        $this->timeout = config('app.node_api_timeout', 30);
    }

    public function getPingToClient(Node $node): ?int
    {
        try {
            $start = microtime(true);
            $port = $node->ssl_enabled ? 443 : 80;
            $connection = @fsockopen($node->ip_address, $port, $errno, $errstr, 3);
            $ping = round((microtime(true) - $start) * 1000);

            if ($connection) {
                fclose($connection);
                return $ping;
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }

    public function createPorts(
        Node $node,
        User $user,
        int $localPort,
        string $protocol,
        string $vpnType,
        bool $sshEnabled,
        int $quantity
    ): array {
        $createdPorts = [];
        $sshPortAssigned = false;

        for ($i = 0; $i < $quantity; $i++) {
            // Get random public port
            $publicPort = $this->getAvailablePort($node);
            
            if (!$publicPort) {
                // Rollback created ports
                foreach ($createdPorts as $port) {
                    $this->deletePortFromNode($node, $port);
                    $port->delete();
                }
                return [
                    'success' => false,
                    'message' => 'Tidak ada port publik yang tersedia.',
                ];
            }

            // Determine SSH port
            $currentSshPort = null;
            if ($sshEnabled && !$sshPortAssigned) {
                $currentSshPort = $publicPort;
                $sshPortAssigned = true;
            }

            // Create port in database
            $port = Port::create([
                'user_id' => $user->id,
                'node_id' => $node->id,
                'public_port' => $publicPort,
                'local_port' => $localPort,
                'protocol' => $protocol,
                'vpn_type' => $vpnType,
                'ssh_enabled' => $currentSshPort !== null,
                'ssh_port' => $currentSshPort,
                'status' => 'active',
            ]);

            // Create VPN configurations on node
            $result = $this->createVpnOnNode($node, $port, $user);

            if (!$result['success']) {
                // Rollback
                foreach ($createdPorts as $p) {
                    $this->deletePortFromNode($node, $p);
                    $p->delete();
                }
                $port->delete();
                return [
                    'success' => false,
                    'message' => $result['message'],
                ];
            }

            // Save VPN configs
            if (in_array($vpnType, ['wireguard', 'both'])) {
                WireguardConfig::create([
                    'port_id' => $port->id,
                    'user_id' => $user->id,
                    'private_key' => $result['wireguard']['private_key'],
                    'public_key' => $result['wireguard']['public_key'],
                    'preshared_key' => $result['wireguard']['preshared_key'],
                    'client_ip' => $result['wireguard']['client_ip'],
                    'server_public_key' => $result['wireguard']['server_public_key'],
                    'server_endpoint' => $node->domain,
                    'server_port' => $result['wireguard']['server_port'],
                    'config_file' => $result['wireguard']['config_file'],
                    'dns' => config('app.wg_dns', '1.1.1.1,8.8.8.8'),
                ]);
            }

            if (in_array($vpnType, ['sstp', 'both'])) {
                SstpConfig::create([
                    'port_id' => $port->id,
                    'user_id' => $user->id,
                    'username' => $result['sstp']['username'],
                    'password' => $result['sstp']['password'],
                    'server_address' => $node->domain,
                    'server_port' => $result['sstp']['server_port'],
                    'client_ip' => $result['sstp']['client_ip'],
                    'config_script' => $result['sstp']['config_script'],
                ]);
            }

            $node->incrementUsedPorts();
            $createdPorts[] = $port;
        }

        return [
            'success' => true,
            'ports' => $createdPorts,
        ];
    }

    protected function getAvailablePort(Node $node): ?int
    {
        $usedPorts = Port::where('node_id', $node->id)
            ->pluck('public_port')
            ->toArray();

        $availablePorts = range($node->port_start, $node->port_end);
        $availablePorts = array_diff($availablePorts, $usedPorts);

        if (empty($availablePorts)) {
            return null;
        }

        return $availablePorts[array_rand($availablePorts)];
    }

    protected function createVpnOnNode(Node $node, Port $port, User $user): array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $node->api_key,
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->post("{$node->api_url}/vpn/create", [
                'port_id' => $port->id,
                'user_id' => $user->id,
                'public_port' => $port->public_port,
                'local_port' => $port->local_port,
                'protocol' => $port->protocol,
                'vpn_type' => $port->vpn_type,
                'ssh_enabled' => $port->ssh_enabled,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'wireguard' => $response->json('wireguard'),
                    'sstp' => $response->json('sstp'),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message', 'Gagal membuat VPN di node.'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    protected function deletePortFromNode(Node $node, Port $port): bool
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $node->api_key,
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->post("{$node->api_url}/vpn/delete", [
                'port_id' => $port->id,
                'public_port' => $port->public_port,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deletePort(Port $port): bool
    {
        $node = $port->node;
        $result = $this->deletePortFromNode($node, $port);
        
        if ($result) {
            $node->decrementUsedPorts();
            $port->delete();
        }

        return $result;
    }
}
