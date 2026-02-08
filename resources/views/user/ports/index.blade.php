@extends('layouts.app')

@section('title', 'Port Saya - PortVPN Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Port Saya</h2>
    <a href="{{ route('user.ports.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Order Port Baru
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($ports->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-ethernet text-muted fs-1"></i>
                <h5 class="mt-3">Belum ada port</h5>
                <p class="text-muted">Order port pertama Anda sekarang</p>
                <a href="{{ route('user.ports.create') }}" class="btn btn-primary">Order Port</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Port Publik</th>
                            <th>Port Lokal</th>
                            <th>Node</th>
                            <th>VPN Type</th>
                            <th>Protocol</th>
                            <th>SSH</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ports as $port)
                            <tr>
                                <td>#{{ $port->id }}</td>
                                <td>
                                    <span class="badge bg-primary fs-6">{{ $port->public_port }}</span>
                                </td>
                                <td>{{ $port->local_port }}</td>
                                <td>{{ $port->node->name }}</td>
                                <td>
                                    <span class="badge bg-info">{{ strtoupper($port->vpn_type) }}</span>
                                </td>
                                <td>{{ strtoupper($port->protocol) }}</td>
                                <td>
                                    @if($port->ssh_enabled)
                                        <span class="badge bg-success">{{ $port->ssh_port }}</span>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($port->isActive())
                                        <span class="badge bg-success">Aktif</span>
                                    @elseif($port->isExpired())
                                        <span class="badge bg-danger">Expired</span>
                                    @elseif($port->status === 'suspended')
                                        <span class="badge bg-warning">Suspended</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($port->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('user.ports.show', $port) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $ports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
