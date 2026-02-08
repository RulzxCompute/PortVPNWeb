<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Node;
use App\Models\Port;
use App\Models\Transaction;
use App\Models\RedeemCode;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_nodes' => Node::count(),
            'active_nodes' => Node::where('is_active', true)->count(),
            'total_ports' => Port::count(),
            'active_ports' => Port::active()->count(),
            'total_revenue' => Transaction::where('type', 'purchase')->sum('amount'),
            'pending_redeems' => RedeemCode::valid()->count(),
        ];

        $recent_transactions = Transaction::with('user')
            ->latest()
            ->take(10)
            ->get();

        $recent_users = User::latest()
            ->take(5)
            ->get();

        $nodes_status = Node::withCount('ports')
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_transactions', 'recent_users', 'nodes_status'));
    }
}
