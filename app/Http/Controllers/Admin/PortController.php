<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Port;
use App\Models\Node;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index()
    {
        $ports = Port::with(['user', 'node'])
            ->latest()
            ->paginate(20);
        return view('admin.ports.index', compact('ports'));
    }

    public function show(Port $port)
    {
        $port->load(['user', 'node', 'wireguardConfig', 'sstpConfig']);
        return view('admin.ports.show', compact('port'));
    }

    public function suspend(Port $port)
    {
        $port->update(['status' => 'suspended']);
        return back()->with('success', 'Port berhasil disuspend.');
    }

    public function activate(Port $port)
    {
        $port->update(['status' => 'active']);
        return back()->with('success', 'Port berhasil diaktifkan.');
    }

    public function destroy(Port $port)
    {
        $port->node->decrementUsedPorts();
        $port->delete();
        return redirect()->route('admin.ports.index')
            ->with('success', 'Port berhasil dihapus.');
    }
}
