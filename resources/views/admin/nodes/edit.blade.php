@extends('layouts.app')

@section('title', 'Edit Node - PortVPN Manager')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Node</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.nodes.update', $node) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Node</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name', $node->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="domain" class="form-label">Domain</label>
                            <input type="text" class="form-control @error('domain') is-invalid @enderror" 
                                id="domain" name="domain" value="{{ old('domain', $node->domain) }}" required>
                            @error('domain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ip_address" class="form-label">IP Address</label>
                            <input type="text" class="form-control @error('ip_address') is-invalid @enderror" 
                                id="ip_address" name="ip_address" value="{{ old('ip_address', $node->ip_address) }}" required>
                            @error('ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="ssl_enabled" name="ssl_enabled" value="1" {{ $node->ssl_enabled ? 'checked' : '' }}>
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
                                id="location" name="location" value="{{ old('location', $node->location) }}" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="region" class="form-label">Region</label>
                            <input type="text" class="form-control @error('region') is-invalid @enderror" 
                                id="region" name="region" value="{{ old('region', $node->region) }}" required>
                            @error('region')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $node->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Node Aktif
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border">
                        <h6 class="small fw-bold">API Key:</h6>
                        <code>{{ $node->api_key }}</code>
                        <form action="{{ route('admin.nodes.regenerate-key', $node) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Yakin ingin regenerate API key?')">
                                <i class="bi bi-key me-1"></i>Regenerate API Key
                            </button>
                        </form>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.nodes.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Update Node
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
