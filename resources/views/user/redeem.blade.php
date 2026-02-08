@extends('layouts.app')

@section('title', 'Redeem Saldo - PortVPN Manager')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-center">
                <h4 class="mb-0"><i class="bi bi-ticket-perforated me-2"></i>Redeem Saldo</h4>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-gift text-primary" style="font-size: 4rem;"></i>
                    <p class="mt-3 text-muted">Masukkan kode redeem untuk menambah saldo Anda</p>
                </div>

                <form method="POST" action="{{ route('user.redeem.submit') }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="code" class="form-label">Kode Redeem</label>
                        <input type="text" class="form-control form-control-lg text-center @error('code') is-invalid @enderror" 
                            id="code" name="code" value="{{ old('code') }}" 
                            placeholder="XXXXXXXXXXXXXXXX" maxlength="16" required
                            style="letter-spacing: 4px; text-transform: uppercase;">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-center">16 karakter alphanumeric</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Redeem Sekarang
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle me-2"></i>Informasi</h6>
                    <ul class="mb-0 small">
                        <li>Kode redeem bersifat satu kali pakai</li>
                        <li>Saldo akan langsung ditambahkan ke akun</li>
                        <li>Hubungi admin untuk membeli kode redeem</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
