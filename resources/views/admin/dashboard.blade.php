@extends('layouts.app')

@section('title', 'Admin Dashboard - PortVPN Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Admin Dashboard</h2>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Users</h6>
                        <h3 class="mb-0">{{ $stats['total_users'] }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-people text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Nodes Aktif</h6>
                        <h3 class="mb-0">{{ $stats['active_nodes'] }}/{{ $stats['total_nodes'] }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-hdd-network text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Port Aktif</h6>
                        <h3 class="mb-0">{{ $stats['active_ports'] }}/{{ $stats['total_ports'] }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="bi bi-ethernet text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Revenue</h6>
                        <h3 class="mb-0">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="bi bi-cash-stack text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-hdd-network me-2"></i>Status Nodes</h5>
                <a href="{{ route('admin.nodes.index') }}" class="btn btn-sm btn-outline-primary">Kelola</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Node</th>
                                <th>Port Terpakai</th>
                                <th>Ping</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nodes_status as $node)
                                <tr>
                                    <td>{{ $node->name }}</td>
                                    <td>{{ $node->used_ports }}/{{ $node->total_ports }}</td>
                                    <td>
                                        @if($node->ping_ms)
                                            <span class="ping-indicator ping-{{ $node->ping_ms < 50 ? 'good' : ($node->ping_ms < 100 ? 'medium' : 'bad') }}"></span>
                                            {{ $node->ping_ms }}ms
                                        @else
                                            <span class="badge bg-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($node->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Nonaktif</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Transaksi Terbaru</h5>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Tipe</th>
                                <th>Jumlah</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->user->name }}</td>
                                    <td>
                                        @if($transaction->isDeposit())
                                            <span class="badge bg-success">Deposit</span>
                                        @elseif($transaction->isPurchase())
                                            <span class="badge bg-primary">Pembelian</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($transaction->type) }}</span>
                                        @endif
                                    </td>
                                    <td class="{{ $transaction->isPurchase() ? 'text-danger' : 'text-success' }}">
                                        {{ $transaction->formatted_amount }}
                                    </td>
                                    <td>{{ $transaction->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>User Terbaru</h5>
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Saldo</th>
                        <th>Port</th>
                        <th>Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>Rp {{ number_format($user->balance, 0, ',', '.') }}</td>
                            <td>{{ $user->ports_count }}</td>
                            <td>{{ $user->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
