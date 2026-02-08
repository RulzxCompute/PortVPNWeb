@extends('layouts.app')

@section('title', 'Kelola Ports - PortVPN Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Kelola Ports</h2>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Node</th>
                        <th>Port</th>
                        <th>VPN</th>
                        <th>Protocol</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ports as $port)
                        <tr>
                            <td>#{{ $port->id }}</td>
                            <td>{{ $port->user->name }}</td>
                            <td>{{ $port->node->name }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $port->public_port }}</span>
                                <small class="text-muted">-> {{ $port->local_port }}</small>
                            </td>
                            <td><span class="badge bg-info">{{ strtoupper($port->vpn_type) }}</span></td>
                            <td>{{ strtoupper($port->protocol) }}</td>
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
                                <div class="btn-group">
                                    <a href="{{ route('admin.ports.show', $port) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($port->isActive())
                                        <form action="{{ route('admin.ports.suspend', $port) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning" title="Suspend">
                                                <i class="bi bi-pause-fill"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.ports.activate', $port) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Activate">
                                                <i class="bi bi-play-fill"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.ports.destroy', $port) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus port ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $ports->links() }}
        </div>
    </div>
</div>
@endsection
