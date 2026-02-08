@extends('layouts.app')

@section('title', 'Order Port - PortVPN Manager')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-cart-plus me-2"></i>Order Port Baru</h4>
            </div>
            <div class="card-body p-4">
                @if($nodes->isEmpty())
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Sedang tidak ada VPN yang tersedia, Silahkan hubungi admin.
                    </div>
                @else
                    <form method="POST" action="{{ route('user.ports.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Pilih Node</label>
                            <div class="row g-3">
                                @foreach($nodes as $node)
                                    <div class="col-md-6">
                                        <div class="form-check card p-3 h-100">
                                            <input class="form-check-input" type="radio" name="node_id" 
                                                id="node_{{ $node->id }}" value="{{ $node->id }}" 
                                                {{ $loop->first ? 'checked' : '' }} required>
                                            <label class="form-check-label w-100" for="node_{{ $node->id }}">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong>{{ $node->name }}</strong>
                                                        <div class="text-muted small">{{ $node->location }}, {{ $node->region }}</div>
                                                        <div class="mt-1">
                                                            <span class="badge bg-info">{{ $node->available_ports }} port tersedia</span>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        @if($node->ping)
                                                            <span class="ping-indicator ping-{{ $node->ping < 50 ? 'good' : ($node->ping < 100 ? 'medium' : 'bad') }}"></span>
                                                            <small>{{ $node->ping }}ms</small>
                                                        @else
                                                            <span class="badge bg-secondary">No ping</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="local_port" class="form-label">Port Lokal Anda</label>
                                <input type="number" class="form-control @error('local_port') is-invalid @enderror" 
                                    id="local_port" name="local_port" value="{{ old('local_port', '5000') }}" 
                                    min="1" max="65535" required>
                                <div class="form-text">Port di server lokal/VPS Anda</div>
                                @error('local_port')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Jumlah Port</label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                    id="quantity" name="quantity" value="{{ old('quantity', '1') }}" 
                                    min="1" max="50" required>
                                <div class="form-text">Maksimal {{ $maxPorts - $userPortsCount }} port lagi</div>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="protocol" class="form-label">Protocol</label>
                                <select class="form-select @error('protocol') is-invalid @enderror" 
                                    id="protocol" name="protocol" required>
                                    <option value="tcp">TCP</option>
                                    <option value="udp">UDP</option>
                                    <option value="both" selected>TCP & UDP</option>
                                </select>
                                @error('protocol')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="vpn_type" class="form-label">Tipe VPN</label>
                                <select class="form-select @error('vpn_type') is-invalid @enderror" 
                                    id="vpn_type" name="vpn_type" required>
                                    <option value="wireguard">WireGuard</option>
                                    <option value="sstp">SSTP</option>
                                    <option value="both" selected>WireGuard & SSTP</option>
                                </select>
                                @error('vpn_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ssh_enabled" name="ssh_enabled" value="1">
                                <label class="form-check-label" for="ssh_enabled">
                                    <i class="bi bi-terminal me-1"></i>Allow SSH (opsional)
                                </label>
                            </div>
                            <div class="form-text">Jika dicentang, 1 port akan digunakan untuk SSH tunnel</div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="bi bi-calculator me-2"></i>Perkiraan Harga</h6>
                            <div id="price-calculation">
                                @php
                                    $priceFirst = config('app.price_first_port', 5000);
                                    $priceAdditional = config('app.price_additional_port', 3000);
                                @endphp
                                <p class="mb-1">Port pertama: Rp {{ number_format($priceFirst, 0, ',', '.') }}</p>
                                <p class="mb-1">Port tambahan: Rp {{ number_format($priceAdditional, 0, ',', '.') }} /port</p>
                                <hr class="my-2">
                                <p class="mb-0 fw-bold">Total: Rp <span id="total-price">{{ number_format($priceFirst, 0, ',', '.') }}</span></p>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-cart-check me-2"></i>Order Sekarang
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const priceFirst = {{ config('app.price_first_port', 5000) }};
    const priceAdditional = {{ config('app.price_additional_port', 3000) }};
    
    document.getElementById('quantity').addEventListener('input', function() {
        const quantity = parseInt(this.value) || 1;
        const total = quantity === 1 ? priceFirst : priceFirst + ((quantity - 1) * priceAdditional);
        document.getElementById('total-price').textContent = total.toLocaleString('id-ID');
    });
</script>
@endpush
