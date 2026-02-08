@extends('layouts.app')

@section('title', 'Tambah Node - PortVPN Manager')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-plus-lg me-2"></i>Tambah Node Baru</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.nodes.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Node</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name') }}" required>
                            <div class="form-text">Contoh: Node Jakarta 1</div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="domain" class="form-label">Domain</label>
                            <input type="text" class="form-control @error('domain') is-invalid @enderror" 
                                id="domain" name="domain" value="{{ old('domain') }}" required>
                            <div class="form-text">Contoh: node1.yourdomain.com</div>
                            @error('domain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ip_address" class="form-label">IP Address</label>
                            <input type="text" class="form-control @error('ip_address') is-invalid @enderror" 
                                id="ip_address" name="ip_address" value="{{ old('ip_address') }}" required>
                            @error('ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="ssl_enabled" name="ssl_enabled" value="1" checked>
                                <label class="form-check-label" for="ssl_enabled">
                                    SSL Enabled (HTTPS)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Lokasi</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                id="location" name="location" value="{{ old('location') }}" required>
                            <div class="form-text">Contoh: Jakarta</div>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="region" class="form-label">Region</label>
                            <input type="text" class="form-control @error('region') is-invalid @enderror" 
                                id="region" name="region" value="{{ old('region') }}" required>
                            <div class="form-text">Contoh: Indonesia, Singapore</div>
                            @error('region')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="port_start" class="form-label">Port Range Start</label>
                            <input type="number" class="form-control @error('port_start') is-invalid @enderror" 
                                id="port_start" name="port_start" value="{{ old('port_start', '1000') }}" min="1000" max="65535" required>
                            @error('port_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="port_end" class="form-label">Port Range End</label>
                            <input type="number" class="form-control @error('port_end') is-invalid @enderror" 
                                id="port_end" name="port_end" value="{{ old('port_end', '10000') }}" min="1000" max="65535" required>
                            @error('port_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle me-2"></i>Informasi</h6>
                        <ul class="mb-0 small">
                            <li>API Key akan digenerate otomatis</li>
                            <li>Pastikan node sudah terinstall dan running</li>
                            <li>Domain harus sudah diarahkan ke IP node</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.nodes.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Simpan Node
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
