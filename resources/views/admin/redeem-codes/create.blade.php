@extends('layouts.app')

@section('title', 'Buat Redeem Codes - PortVPN Manager')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-plus-lg me-2"></i>Buat Redeem Codes</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.redeem-codes.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="amount" class="form-label">Nominal (Rp)</label>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                            id="amount" name="amount" value="{{ old('amount', '50000') }}" min="1000" required>
                        <div class="form-text">Jumlah saldo yang akan ditambahkan</div>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Jumlah Kode</label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                            id="quantity" name="quantity" value="{{ old('quantity', '10') }}" min="1" max="100" required>
                        <div class="form-text">Berapa banyak kode yang akan dibuat (1-100)</div>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="expires_at" class="form-label">Kadaluarsa (opsional)</label>
                        <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" 
                            id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                        <div class="form-text">Kosongkan jika tidak ada batas waktu</div>
                        @error('expires_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle me-2"></i>Informasi</h6>
                        <ul class="mb-0 small">
                            <li>Kode akan digenerate otomatis (16 karakter)</li>
                            <li>Kode bersifat case-insensitive</li>
                            <li>User dapat redeem di halaman Redeem Saldo</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.redeem-codes.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-magic me-2"></i>Buat Kode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
