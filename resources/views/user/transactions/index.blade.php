@extends('layouts.app')

@section('title', 'Riwayat Transaksi - PortVPN Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Riwayat Transaksi</h2>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($transactions->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-clock-history text-muted fs-1"></i>
                <h5 class="mt-3">Belum ada transaksi</h5>
                <p class="text-muted">Transaksi Anda akan muncul di sini</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Deskripsi</th>
                            <th>Jumlah</th>
                            <th>Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>#{{ $transaction->id }}</td>
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
                                <td>{{ $transaction->description }}</td>
                                <td class="{{ $transaction->isPurchase() ? 'text-danger' : 'text-success' }} fw-semibold">
                                    {{ $transaction->formatted_amount }}
                                </td>
                                <td>Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
