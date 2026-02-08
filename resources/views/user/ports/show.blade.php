@extends('layouts.app')

@section('title', 'Detail Port #' . $port->id)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-ethernet me-2"></i>Detail Port #{{ $port->id }}</h5>
                <div>
                    @if($port->isActive())
                        <span class="badge bg-success">Aktif</span>
                    @elseif($port->isExpired())
                        <span class="badge bg-danger">Expired</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($port->status) }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Port Publik</td>
                                <td class="fw-bold">{{ $port->public_port }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Port Lokal</td>
                                <td>{{ $port->local_port }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Protocol</td>
                                <td><span class="badge bg-info">{{ strtoupper($port->protocol) }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">VPN Type</td>
                                <td><span class="badge bg-primary">{{ strtoupper($port->vpn_type) }}</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Node</td>
                                <td>{{ $port->node->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Lokasi</td>
                                <td>{{ $port->node->location }}, {{ $port->node->region }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">SSH</td>
                                <td>
                                    @if($port->ssh_enabled)
                                        <span class="badge bg-success">{{ $port->ssh_port }}</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak aktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Dibuat</td>
                                <td>{{ $port->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($port->ssh_enabled)
                    <div class="alert alert-info">
                        <h6><i class="bi bi-terminal me-2"></i>SSH Connection</h6>
                        <code>ssh -p {{ $port->ssh_port }} user@{{ $port->node->domain }}</code>
                    </div>
                @endif
            </div>
        </div>

        @if($port->wireguardConfig)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>WireGuard Config</h5>
                    <a href="{{ route('user.ports.download', [$port, 'wireguard']) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Client IP</label>
                        <input type="text" class="form-control" value="{{ $port->wireguardConfig->client_ip }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">DNS</label>
                        <input type="text" class="form-control" value="{{ $port->wireguardConfig->dns }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Server Endpoint</label>
                        <input type="text" class="form-control" value="{{ $port->wireguardConfig->server_endpoint }}:{{ $port->wireguardConfig->server_port }}" readonly>
                    </div>
                    <div class="alert alert-light border">
                        <h6 class="small fw-bold">Config File:</h6>
                        <pre class="mb-0 small"><code>{{ $port->wireguardConfig->config_file }}</code></pre>
                    </div>
                </div>
            </div>
        @endif

        @if($port->sstpConfig)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-globe me-2"></i>SSTP Config</h5>
                    <a href="{{ route('user.ports.download', [$port, 'sstp']) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-download me-1"></i>Download Script
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Username</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $port->sstpConfig->username }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $port->sstpConfig->username }}')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" value="{{ $port->sstpConfig->password }}" id="sstp-password" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="bi bi-eye" id="eye-icon"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $port->sstpConfig->password }}')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Server</label>
                        <input type="text" class="form-control" value="{{ $port->sstpConfig->server_address }}:{{ $port->sstpConfig->server_port }}" readonly>
                    </div>
                    <div class="alert alert-light border">
                        <h6 class="small fw-bold">Linux Connect Command:</h6>
                        <code>sstpc --user {{ $port->sstpConfig->username }} --password {{ $port->sstpConfig->password }} {{ $port->sstpConfig->server_address }}:{{ $port->sstpConfig->server_port }}</code>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi</h5>
            </div>
            <div class="card-body">
                <h6>Cara Menggunakan:</h6>
                <ol class="small">
                    @if($port->wireguardConfig)
                        <li class="mb-2">Download config WireGuard</li>
                        <li class="mb-2">Install WireGuard di device Anda</li>
                        <li class="mb-2">Import config file</li>
                        <li class="mb-2">Connect ke VPN</li>
                        <li class="mb-2">Akses port {{ $port->public_port }} via tunnel</li>
                    @endif
                    @if($port->sstpConfig)
                        <li class="mb-2">Download script SSTP (Linux)</li>
                        <li class="mb-2">Jalankan script atau copy command</li>
                        <li class="mb-2">Connect ke VPN</li>
                        <li class="mb-2">Akses port {{ $port->public_port }} via tunnel</li>
                    @endif
                </ol>

                <hr>

                <h6>Port Forwarding:</h6>
                <p class="small text-muted mb-0">
                    Port publik <strong>{{ $port->public_port }}</strong> akan di-forward ke 
                    port lokal <strong>{{ $port->local_port }}</strong> di server Anda melalui VPN tunnel.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Copied to clipboard!');
        });
    }

    function togglePassword() {
        const passwordInput = document.getElementById('sstp-password');
        const eyeIcon = document.getElementById('eye-icon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        }
    }
</script>
@endpush
