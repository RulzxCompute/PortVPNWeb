@extends('layouts.app')

@section('title', 'Dashboard - PortVPN Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Dashboard</h2>
    <a href="{{ route('user.ports.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Order Port Baru
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Port Aktif</h6>
                        <h3 class="mb-0">{{ $stats['active_ports'] }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-ethernet text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Port</h6>
                        <h3 class="mb-0">{{ $stats['total_ports'] }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-hdd-network text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Saldo</h6>
                        <h3 class="mb-0">Rp {{ number_format($stats['balance'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="bi bi-wallet2 text-warning fs-4"></i>
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
                <h5 class="mb-0"><i class="bi bi-ethernet me-2"></i>Port Terbaru</h5>
                <a href="{{ route('user.ports.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @if($recent_ports->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted fs-1"></i>
                        <p class="text-muted mt-2">Belum ada port</p>
                        <a href="{{ route('user.ports.create') }}" class="btn btn-primary btn-sm">Order Port</a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Port</th>
                                    <th>Node</th>
                                    <th>VPN</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_ports as $port)
                                    <tr>
                                        <td>
                                            <a href="{{ route('user.ports.show', $port) }}" class="text-decoration-none fw-semibold">
                                                {{ $port->public_port }}
                                            </a>
                                        </td>
                                        <td>{{ $port->node->name }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ strtoupper($port->vpn_type) }}</span>
                                        </td>
                                        <td>
                                            @if($port->isActive())
                                                <span class="badge bg-success">Aktif</span>
                                            @elseif($port->isExpired())
                                                <span class="badge bg-danger">Expired</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($port->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Transaksi Terbaru</h5>
                <a href="{{ route('user.transactions.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @if($recent_transactions->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted fs-1"></i>
                        <p class="text-muted mt-2">Belum ada transaksi</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
