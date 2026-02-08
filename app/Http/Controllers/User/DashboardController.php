<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load(['ports.node', 'transactions']);

        $stats = [
            'active_ports' => $user->ports()->active()->count(),
            'total_ports' => $user->ports()->count(),
            'balance' => $user->balance,
        ];

        $recent_ports = $user->ports()
            ->with('node')
            ->latest()
            ->take(5)
            ->get();

        $recent_transactions = $user->transactions()
            ->latest()
            ->take(5)
            ->get();

        return view('user.dashboard', compact('stats', 'recent_ports', 'recent_transactions'));
    }
}
