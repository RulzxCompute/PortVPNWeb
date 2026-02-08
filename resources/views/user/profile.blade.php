@extends('layouts.app')

@section('title', 'Profil - PortVPN Manager')

@section('content')
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Informasi Profil</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('user.profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                            id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                            id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lock me-2"></i>Ubah Password</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('user.profile.password') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                            id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                            id="password" name="password" required>
                        <div class="form-text">Minimal 8 karakter</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" 
                            id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-key me-2"></i>Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Akun</h5>
    </div>
    <div class="card-body">
        <table class="table table-borderless">
            <tr>
                <td class="text-muted" style="width: 200px;">ID User</td>
                <td>{{ auth()->user()->id }}</td>
            </tr>
            <tr>
                <td class="text-muted">Tipe Akun</td>
                <td>
                    @if(auth()->user()->isAdmin())
                        <span class="badge bg-danger">Administrator</span>
                    @else
                        <span class="badge bg-primary">User</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="text-muted">Terdaftar</td>
                <td>{{ auth()->user()->created_at->format('d F Y H:i') }}</td>
            </tr>
            <tr>
                <td class="text-muted">Email Terverifikasi</td>
                <td>
                    @if(auth()->user()->email_verified_at)
                        <span class="text-success"><i class="bi bi-check-circle me-1"></i>Ya</span>
                    @else
                        <span class="text-warning"><i class="bi bi-exclamation-circle me-1"></i>Belum</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
