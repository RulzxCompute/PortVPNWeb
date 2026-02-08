@extends('layouts.app')

@section('title', 'Kelola Redeem Codes - PortVPN Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Kelola Redeem Codes</h2>
    <div>
        <a href="{{ route('admin.redeem-codes.export') }}" class="btn btn-success me-2">
            <i class="bi bi-download me-2"></i>Export
        </a>
        <a href="{{ route('admin.redeem-codes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Buat Kode
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($codes->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-ticket-perforated text-muted fs-1"></i>
                <h5 class="mt-3">Belum ada kode redeem</h5>
                <a href="{{ route('admin.redeem-codes.create') }}" class="btn btn-primary">Buat Kode</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Digunakan Oleh</th>
                            <th>Tanggal</th>
                            <th>Kadaluarsa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($codes as $code)
                            <tr>
                                <td><code class="fs-6">{{ $code->code }}</code></td>
                                <td>Rp {{ number_format($code->amount, 0, ',', '.') }}</td>
                                <td>
                                    @if($code->is_used)
                                        <span class="badge bg-secondary">Digunakan</span>
                                    @elseif($code->expires_at && $code->expires_at->isPast())
                                        <span class="badge bg-danger">Kadaluarsa</span>
                                    @else
                                        <span class="badge bg-success">Tersedia</span>
                                    @endif
                                </td>
                                <td>
                                    @if($code->user)
                                        {{ $code->user->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($code->used_at)
                                        {{ $code->used_at->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($code->expires_at)
                                        {{ $code->expires_at->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">Tidak ada</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$code->is_used)
                                        <form action="{{ route('admin.redeem-codes.destroy', $code) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus kode ini?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $codes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
