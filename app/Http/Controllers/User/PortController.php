<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Port;
use App\Models\Node;
use App\Services\NodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortController extends Controller
{
    protected $nodeService;

    public function __construct(NodeService $nodeService)
    {
        $this->nodeService = $nodeService;
    }

    public function index()
    {
        $ports = Auth::user()->ports()
            ->with('node')
            ->latest()
            ->paginate(10);
        return view('user.ports.index', compact('ports'));
    }

    public function show(Port $port)
    {
        $this->authorize('view', $port);
        $port->load(['node', 'wireguardConfig', 'sstpConfig']);
        return view('user.ports.show', compact('port'));
    }

    public function create()
    {
        $nodes = Node::where('is_active', true)
            ->get()
            ->map(function ($node) {
                $node->ping = $this->nodeService->getPingToClient($node);
                return $node;
            })
            ->sortBy('ping');

        if ($nodes->isEmpty()) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Sedang tidak ada VPN yang tersedia, Silahkan hubungi admin.');
        }

        $userPortsCount = Auth::user()->ports()->active()->count();
        $maxPorts = config('app.max_ports_per_user', 50);

        if ($userPortsCount >= $maxPorts) {
            return redirect()->route('user.ports.index')
                ->with('error', 'Anda sudah mencapai batas maksimal port.');
        }

        return view('user.ports.create', compact('nodes', 'userPortsCount', 'maxPorts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'node_id' => 'required|exists:nodes,id',
            'local_port' => 'required|integer|min:1|max:65535',
            'protocol' => 'required|in:tcp,udp,both',
            'vpn_type' => 'required|in:wireguard,sstp,both',
            'ssh_enabled' => 'boolean',
            'quantity' => 'required|integer|min:1|max:50',
        ]);

        $node = Node::findOrFail($validated['node_id']);
        $user = Auth::user();

        if (!$node->isAvailable()) {
            return back()->with('error', 'Node tidak tersedia atau port habis.');
        }

        $quantity = $validated['quantity'];
        $currentPorts = $user->ports()->active()->count();
        $maxPorts = config('app.max_ports_per_user', 50);

        if ($currentPorts + $quantity > $maxPorts) {
            return back()->with('error', 'Melebihi batas maksimal port.');
        }

        // Calculate price
        $priceFirst = config('app.price_first_port', 5000);
        $priceAdditional = config('app.price_additional_port', 3000);
        $totalPrice = $priceFirst + (($quantity - 1) * $priceAdditional);

        if ($user->balance < $totalPrice) {
            return back()->with('error', 'Saldo tidak cukup. Total: Rp ' . number_format($totalPrice));
        }

        // Create ports via node service
        $result = $this->nodeService->createPorts(
            $node,
            $user,
            $validated['local_port'],
            $validated['protocol'],
            $validated['vpn_type'],
            $validated['ssh_enabled'] ?? false,
            $quantity
        );

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        // Deduct balance
        $user->deductBalance($totalPrice);

        // Create transaction
        $user->transactions()->create([
            'type' => 'purchase',
            'amount' => $totalPrice,
            'description' => "Pembelian {$quantity} port di {$node->name}",
            'reference_type' => 'port',
            'reference_id' => $result['ports'][0]->id ?? null,
            'balance_after' => $user->balance,
        ]);

        return redirect()->route('user.ports.index')
            ->with('success', "Berhasil membeli {$quantity} port.");
    }

    public function downloadConfig(Port $port, string $type)
    {
        $this->authorize('view', $port);

        if ($type === 'wireguard' && $port->wireguardConfig) {
            $config = $port->wireguardConfig->config_file;
            $filename = "portvpn-{$port->public_port}.conf";
            return response($config)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', "attachment; filename=\"$filename\"");
        }

        if ($type === 'sstp' && $port->sstpConfig) {
            $script = $port->sstpConfig->linux_setup_script;
            $filename = "sstp-setup-{$port->sstpConfig->username}.sh";
            return response($script)
                ->header('Content-Type', 'text/x-shellscript')
                ->header('Content-Disposition', "attachment; filename=\"$filename\"");
        }

        return back()->with('error', 'Konfigurasi tidak ditemukan.');
    }
}
