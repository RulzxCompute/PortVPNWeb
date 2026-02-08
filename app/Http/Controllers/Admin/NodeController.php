<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NodeController extends Controller
{
    public function index()
    {
        $nodes = Node::withCount('ports')->latest()->paginate(20);
        return view('admin.nodes.index', compact('nodes'));
    }

    public function create()
    {
        return view('admin.nodes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'location' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'port_start' => 'required|integer|min:1000|max:65535',
            'port_end' => 'required|integer|min:1000|max:65535|gt:port_start',
            'ssl_enabled' => 'boolean',
        ]);

        $validated['api_key'] = Str::random(64);
        $validated['total_ports'] = $validated['port_end'] - $validated['port_start'];
        $validated['used_ports'] = 0;
        $validated['is_active'] = true;

        Node::create($validated);

        return redirect()->route('admin.nodes.index')
            ->with('success', 'Node berhasil ditambahkan.');
    }

    public function edit(Node $node)
    {
        return view('admin.nodes.edit', compact('node'));
    }

    public function update(Request $request, Node $node)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'location' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'is_active' => 'boolean',
            'ssl_enabled' => 'boolean',
        ]);

        $node->update($validated);

        return redirect()->route('admin.nodes.index')
            ->with('success', 'Node berhasil diupdate.');
    }

    public function destroy(Node $node)
    {
        if ($node->ports()->count() > 0) {
            return back()->with('error', 'Node masih memiliki port aktif.');
        }

        $node->delete();
        return redirect()->route('admin.nodes.index')
            ->with('success', 'Node berhasil dihapus.');
    }

    public function regenerateApiKey(Node $node)
    {
        $node->update(['api_key' => Str::random(64)]);
        return back()->with('success', 'API Key berhasil diregenerate.');
    }

    public function testPing(Node $node)
    {
        $start = microtime(true);
        $port = $node->ssl_enabled ? 443 : 80;
        $connection = @fsockopen($node->ip_address, $port, $errno, $errstr, 5);
        $ping = round((microtime(true) - $start) * 1000);

        if ($connection) {
            fclose($connection);
            $node->update([
                'ping_ms' => $ping,
                'last_ping_at' => now(),
            ]);
            return back()->with('success', "Ping berhasil: {$ping}ms");
        }

        return back()->with('error', 'Node tidak merespon.');
    }
}
