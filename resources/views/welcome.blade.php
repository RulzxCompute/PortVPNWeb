<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PortVPN Manager - Solusi Port Forwarding & VPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #7c3aed;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
        }
        
        .feature-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            height: 100%;
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .pricing-card {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .price {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .btn-hero {
            background: #fff;
            color: var(--primary);
            padding: 12px 32px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-hero:hover {
            background: rgba(255,255,255,0.9);
            transform: scale(1.05);
        }
        
        .btn-outline-hero {
            border: 2px solid #fff;
            color: #fff;
            padding: 12px 32px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-outline-hero:hover {
            background: #fff;
            color: var(--primary);
        }
        
        .tech-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            margin: 4px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark fixed-top" style="background: rgba(79, 70, 229, 0.95);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-shield-lock me-2"></i>PortVPN Manager
            </a>
            <div>
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm me-2">Login</a>
                <a href="{{ route('register') }}" class="btn btn-light btn-sm">Register</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 text-white">
                    <div class="hero-content">
                        <h1 class="mb-4">Port Forwarding & VPN Solution</h1>
                        <p class="lead mb-4">
                            Akses port publik dengan mudah menggunakan WireGuard atau SSTP VPN. 
                            Solusi terbaik untuk homelab, VPS, dan server lokal Anda.
                        </p>
                        <div class="mb-4">
                            <span class="tech-badge"><i class="bi bi-check-circle me-1"></i>WireGuard</span>
                            <span class="tech-badge"><i class="bi bi-check-circle me-1"></i>SSTP VPN</span>
                            <span class="tech-badge"><i class="bi bi-check-circle me-1"></i>SSH Tunnel</span>
                            <span class="tech-badge"><i class="bi bi-check-circle me-1"></i>TCP/UDP</span>
                        </div>
                        <div>
                            <a href="{{ route('register') }}" class="btn btn-hero me-3">Mulai Sekarang</a>
                            <a href="#pricing" class="btn btn-outline-hero">Lihat Harga</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="feature-card text-white">
                                <i class="bi bi-shield-check feature-icon"></i>
                                <h4>Aman & Terenkripsi</h4>
                                <p>Koneksi aman dengan enkripsi WireGuard dan SSTP</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="feature-card text-white">
                                <i class="bi bi-lightning feature-icon"></i>
                                <h4>Cepat & Stabil</h4>
                                <p>Pilih node terdekat untuk ping terbaik</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="feature-card text-white">
                                <i class="bi bi-globe feature-icon"></i>
                                <h4>Port Publik</h4>
                                <p>Akses dari mana saja dengan port 1000-10000</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="feature-card text-white">
                                <i class="bi bi-terminal feature-icon"></i>
                                <h4>SSH Support</h4>
                                <p>Opsional SSH tunnel untuk setiap port</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Harga Terjangkau</h2>
                <p class="text-muted">Mulai dari Rp 5.000 per port</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 mb-4">
                    <div class="pricing-card">
                        <h4 class="mb-3">Port Pertama</h4>
                        <div class="price">Rp 5.000</div>
                        <p class="text-muted">/port/bulan</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>1 Port Publik</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>WireGuard / SSTP</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>TCP/UDP Support</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>SSH Opsional</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="pricing-card border border-primary">
                        <span class="badge bg-primary mb-2">Hemat</span>
                        <h4 class="mb-3">Port Tambahan</h4>
                        <div class="price">Rp 3.000</div>
                        <p class="text-muted">/port/bulan</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Port Tambahan</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Semua Fitur</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Maksimal 50 Port</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Lebih Hemat</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Cara Kerja</h2>
            </div>
            <div class="row">
                <div class="col-md-3 text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <h3 class="m-0">1</h3>
                    </div>
                    <h5>Register</h5>
                    <p class="text-muted">Buat akun dan isi saldo dengan redeem code</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <h3 class="m-0">2</h3>
                    </div>
                    <h5>Pilih Node</h5>
                    <p class="text-muted">Pilih node terdekat dengan ping terbaik</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <h3 class="m-0">3</h3>
                    </div>
                    <h5>Order Port</h5>
                    <p class="text-muted">Pilih port lokal dan konfigurasi VPN</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <h3 class="m-0">4</h3>
                    </div>
                    <h5>Connect</h5>
                    <p class="text-muted">Download config dan connect ke VPN</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} PortVPN Manager. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
