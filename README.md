# PortVPN Manager

Sistem manajemen port forwarding berbasis VPN yang memungkinkan pengguna untuk mengakses port lokal mereka melalui port publik menggunakan WireGuard atau SSTP VPN.

## Fitur

### User Features
- **Register & Login** dengan verifikasi email (SMTP support)
- **Order Port** dengan pilihan node berdasarkan lokasi dan ping
- **WireGuard & SSTP VPN** support
- **SSH Tunnel** opsional untuk setiap port
- **Redeem Code** untuk isi saldo
- **Riwayat Transaksi** lengkap

### Admin Features
- **Dashboard** dengan statistik lengkap
- **Node Management** dengan ping monitoring
- **User Management** dengan kontrol saldo
- **Port Management** (suspend/activate/delete)
- **Redeem Code Generator**
- **Transaction History**

### Pricing
- Port pertama: **Rp 5.000**
- Port tambahan: **Rp 3.000** per port
- Maksimal: **50 port** per user

## System Requirements

### Web Management Panel
- Ubuntu 22.04/24.04 atau Debian 12
- PHP 8.3+
- MariaDB 10.11+
- Nginx
- Redis
- Composer

### Node Agent
- Ubuntu 22.04/24.04 atau Debian 12
- Python 3.8+
- WireGuard
- SoftEther VPN (untuk SSTP)
- iptables

## Instalasi

### Opsi 1: Install Web Management Panel

```bash
# Download installer
curl -Lo install.sh https://github.com/yourusername/portvpn/releases/latest/download/install.sh
chmod +x install.sh

# Jalankan installer
sudo ./install.sh

# Pilih opsi 1 (Install Web Management Panel)
```

Installer akan meminta:
- Domain (contoh: panel.yourdomain.com)
- SSL (y/n)
- Database password
- Admin email

Setelah instalasi selesai:
```bash
# Buat admin user
cd /var/www/portvpn
sudo -u www-data php artisan app:user:create
```

### Opsi 2: Install Node Agent

```bash
# Jalankan installer
sudo ./install.sh

# Pilih opsi 2 (Install Node Agent)
```

Installer akan meminta:
- Web Management URL
- Node API Key (dari admin panel)
- SSL (y/n)

### Opsi 3: Uninstall

```bash
sudo ./install.sh
# Pilih opsi 3 (Uninstall)
```

## Konfigurasi

### Environment Variables (.env)

```env
# App
APP_NAME="PortVPN Manager"
APP_URL=https://your-domain.com

# Database
DB_DATABASE=portvpn
DB_USERNAME=portvpn
DB_PASSWORD=your_password

# Mail (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password

# Pricing
PRICE_FIRST_PORT=5000
PRICE_ADDITIONAL_PORT=3000
MAX_PORTS_PER_USER=50

# Node API
NODE_API_KEY=your_random_api_key
```

### Setup SMTP (Gmail)

1. Buka [Google Account Settings](https://myaccount.google.com/)
2. Ke **Security** > **2-Step Verification** > Aktifkan
3. Ke **Security** > **App passwords**
4. Generate app password untuk "Mail"
5. Copy password ke `.env` (MAIL_PASSWORD)

## Penggunaan

### Admin Panel

1. Login ke panel admin
2. **Tambah Node**: Admin > Nodes > Add Node
   - Masukkan nama, domain, IP, lokasi, region
   - API key akan digenerate otomatis
3. **Generate Redeem Codes**: Admin > Redeem Codes > Create
   - Tentukan nominal dan jumlah kode
4. **Monitor**: Dashboard menampilkan statistik real-time

### User Panel

1. **Register/Login** ke website
2. **Redeem Saldo**: Masukkan kode redeem
3. **Order Port**:
   - Pilih node (lihat ping untuk pilih yang terbaik)
   - Masukkan port lokal Anda
   - Pilih protocol (TCP/UDP/Both)
   - Pilih VPN type (WireGuard/SSTP/Both)
   - Centang SSH jika diperlukan
4. **Download Config** dan connect ke VPN
5. Akses port publik Anda!

### Node Agent Commands

```bash
# Check status
systemctl status portvpn-node

# View logs
journalctl -u portvpn-node -f

# Restart service
systemctl restart portvpn-node

# Regenerate WireGuard keys
wg genkey | tee /etc/wireguard/privatekey | wg pubkey > /etc/wireguard/publickey
```

## Arsitektur

```
┌─────────────────────────────────────────────────────────────┐
│                    WEB MANAGEMENT PANEL                      │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────────┐  │
│  │  Users   │  │  Nodes   │  │  Ports   │  │ Redeem     │  │
│  │  Auth    │  │  Manage  │  │  Order   │  │ Codes      │  │
│  └──────────┘  └──────────┘  └──────────┘  └────────────┘  │
│                                                              │
│  Laravel + PHP 8.3 + MariaDB + Nginx + Redis                │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ HTTPS API
                              │
┌─────────────────────────────────────────────────────────────┐
│                        NODE AGENT #1                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐  │
│  │  WireGuard   │  │    SSTP      │  │  Port Forwarding │  │
│  │   Server     │  │   Server     │  │    (iptables)    │  │
│  └──────────────┘  └──────────────┘  └──────────────────┘  │
│                                                              │
│  Python Flask + WireGuard + SoftEther VPN                   │
└─────────────────────────────────────────────────────────────┘
```

## API Endpoints

### Node API

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/health` | GET | Health check |
| `/api/ping` | POST | Update ping status |
| `/api/vpn/create` | POST | Create VPN config |
| `/api/vpn/delete` | POST | Delete VPN config |

Headers required:
```
X-API-Key: your_node_api_key
```

## Troubleshooting

### Web Panel tidak bisa diakses
```bash
# Check Nginx
nginx -t
systemctl status nginx

# Check PHP-FPM
systemctl status php8.3-fpm

# Check Laravel logs
tail -f /var/www/portvpn/storage/logs/laravel.log
```

### Node tidak terkoneksi
```bash
# Check node service
systemctl status portvpn-node

# Test API
curl -H "X-API-Key: your_api_key" http://node-domain:5000/api/health

# Check WireGuard
wg show
```

### Port forwarding tidak berfungsi
```bash
# Check iptables
iptables -t nat -L PREROUTING -n -v
iptables -L FORWARD -n -v

# Enable IP forwarding
sysctl net.ipv4.ip_forward=1

# Check WireGuard interface
ip addr show wg0
```

## Security Notes

1. **Ganti default API key** segera setelah instalasi
2. **Backup .env file** (terutama APP_KEY)
3. **Gunakan firewall** (UFW/iptables) untuk membatasi akses
4. **Update regularly** untuk patch keamanan
5. **Monitor logs** untuk aktivitas mencurigakan

## License

MIT License - See LICENSE file for details

## Support

- GitHub Issues: [github.com/yourusername/portvpn/issues](https://github.com/yourusername/portvpn/issues)
- Email: support@yourdomain.com

---

**Made with ❤️ for the homelab community**
