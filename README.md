✨ Fitur Utama
Web Management Panel
Auth: Login, Register, Forgot Password (SMTP support)
User Dashboard: Statistik port, saldo, riwayat transaksi
Order Port: Pilih node berdasarkan ping, konfigurasi VPN
Redeem Code: Isi saldo dengan kode redeem
Admin Panel: Kelola nodes, users, ports, redeem codes, transaksi
Node Agent
WireGuard: Auto-generate keys & config
SSTP: Auto-generate credentials
Port Forwarding: iptables automation
SSH Tunnel: Optional support
Harga
Port pertama: Rp 5.000
Port tambahan: Rp 3.000
Maksimal: 50 port per user
🚀 Cara Install
bash
Copy
# Download & extract
tar -xzf portvpn-web.tar.gz
cd portvpn-web

# Jalankan installer
sudo ./install.sh

# Pilih opsi:
# 1 = Install Web Management Panel
# 2 = Install Node Agent
# 3 = Uninstall
📋 Setelah Install
bash
Copy
# Buat admin user
cd /var/www/portvpn
sudo -u www-data php artisan app:user:create

# Inisialisasi app
sudo -u www-data php artisan app:init
