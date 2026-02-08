@extends('layouts.app')

@section('title', 'Kelola Nodes - PortVPN Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Kelola Nodes</h2>
    <a href="{{ route('admin.nodes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Tambah Node
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($nodes->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-hdd-network text-muted fs-1"></i>
                <h5 class="mt-3">Belum ada node</h5>
                <a href="{{ route('admin.nodes.create') }}" class="btn btn-primary">Tambah Node</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Domain/IP</th>
                            <th>Lokasi</th>
                            <th>Port</th>
                            <th>Ping</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nodes as $node)
                            <tr>
                                <td>#{{ $node->id }}</td>
                                <td>{{ $node->name }}</td>
                                <td>
                                    <div>{{ $node->domain }}</div>
                                    <small class="text-muted">{{ $node->ip_address }}</small>
                                </td>
                                <td>{{ $node->location }}, {{ $node->region }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $node->used_ports }}/{{ $node->total_ports }}</span>
                                </td>
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
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.nodes.edit', $node) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.nodes.ping', $node) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-info" title="Test Ping">
                                                <i class="bi bi-wifi"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.nodes.regenerate-key', $node) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning" title="Regenerate API Key" onclick="return confirm('Yakin ingin regenerate API key?')">
                                                <i class="bi bi-key"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.nodes.destroy', $node) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus node ini?')">
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
                {{ $nodes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
