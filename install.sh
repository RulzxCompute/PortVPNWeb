#!/bin/bash

# PortVPN Manager Installation Script
# ===================================
# This script installs:
# 1. Web Management Panel (PHP/Laravel + Nginx + MariaDB)
# 2. Node Agent (Python Flask + WireGuard + SSTP)
# 3. Uninstall option

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

INSTALL_DIR="/var/www/portvpn"
DB_NAME="portvpn"
DB_USER="portvpn"

clear
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                                                            ║${NC}"
echo -e "${BLUE}║${NC}     ${CYAN}██████╗  ██████╗ ██████╗ ████████╗██╗   ██╗██████╗ ${NC}   ${BLUE}║${NC}"
echo -e "${BLUE}║${NC}     ${CYAN}██╔══██╗██╔═══██╗██╔══██╗╚══██╔══╝██║   ██║██╔══██╗${NC}   ${BLUE}║${NC}"
echo -e "${BLUE}║${NC}     ${CYAN}██████╔╝██║   ██║██████╔╝   ██║   ██║   ██║██████╔╝${NC}   ${BLUE}║${NC}"
echo -e "${BLUE}║${NC}     ${CYAN}██╔═══╝ ██║   ██║██╔══██╗   ██║   ╚██╗ ██╔╝██╔═══╝ ${NC}   ${BLUE}║${NC}"
echo -e "${BLUE}║${NC}     ${CYAN}██║     ╚██████╔╝██║  ██║   ██║    ╚████╔╝ ██║     ${NC}   ${BLUE}║${NC}"
echo -e "${BLUE}║${NC}     ${CYAN}╚═╝      ╚═════╝ ╚═╝  ╚═╝   ╚═╝     ╚═══╝  ╚═╝     ${NC}   ${BLUE}║${NC}"
echo -e "${BLUE}║                                                            ║${NC}"
echo -e "${BLUE}║${NC}              ${GREEN}PortVPN Manager Installer${NC}                    ${BLUE}║${NC}"
echo -e "${BLUE}║                                                            ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Error: Please run as root${NC}"
    exit 1
fi

# Detect OS
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$NAME
else
    echo -e "${RED}Cannot detect OS${NC}"
    exit 1
fi

echo -e "${CYAN}Detected OS: $OS${NC}"
echo ""

# Menu
echo -e "${YELLOW}Pilih opsi instalasi:${NC}"
echo ""
echo -e "  ${GREEN}[1]${NC} Install Web Management Panel"
echo -e "  ${GREEN}[2]${NC} Install Node Agent"
echo -e "  ${GREEN}[3]${NC} Uninstall"
echo ""
read -p "Pilih (1/2/3): " CHOICE

case $CHOICE in
    1)
        echo ""
        echo -e "${BLUE}============================================${NC}"
        echo -e "${BLUE}   Installing Web Management Panel${NC}"
        echo -e "${BLUE}============================================${NC}"
        echo ""
        
        # Get configuration
        read -p "Domain (contoh: panel.yourdomain.com): " DOMAIN
        read -p "Use SSL? (y/n) [y]: " USE_SSL
        USE_SSL=${USE_SSL:-y}
        read -p "Database password: " DB_PASS
        read -p "Admin email (for SSL): " ADMIN_EMAIL
        
        echo ""
        echo -e "${YELLOW}[1/10] Updating system packages...${NC}"
        apt-get update > /dev/null 2>&1
        apt-get install -y software-properties-common curl apt-transport-https ca-certificates gnupg > /dev/null 2>&1
        
        echo -e "${YELLOW}[2/10] Adding PHP repository...${NC}"
        LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php > /dev/null 2>&1 || true
        
        echo -e "${YELLOW}[3/10] Adding MariaDB repository...${NC}"
        curl -sSL https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | bash -s -- --mariadb-server-version="mariadb-10.11" > /dev/null 2>&1 || true
        
        apt-get update > /dev/null 2>&1
        
        echo -e "${YELLOW}[4/10] Installing dependencies...${NC}"
        apt-get install -y php8.3 php8.3-common php8.3-cli php8.3-gd php8.3-mysql php8.3-mbstring \
            php8.3-bcmath php8.3-xml php8.3-fpm php8.3-curl php8.3-zip php8.3-intl php8.3-redis \
            mariadb-server nginx tar unzip git redis-server > /dev/null 2>&1
        
        echo -e "${YELLOW}[5/10] Installing Composer...${NC}"
        if ! command -v composer &> /dev/null; then
            curl -sS https://getcomposer.org/installer | php
            mv composer.phar /usr/local/bin/composer
            chmod +x /usr/local/bin/composer
        fi
        
        echo -e "${YELLOW}[6/10] Creating database...${NC}"
        mysql -u root -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';"
        mysql -u root -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
        mysql -u root -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'127.0.0.1' WITH GRANT OPTION;"
        mysql -u root -e "FLUSH PRIVILEGES;"
        
        echo -e "${YELLOW}[7/10] Setting up application...${NC}"
        mkdir -p ${INSTALL_DIR}
        
        # Check if source exists
        if [ -d "$(dirname "$0")/app" ]; then
            cp -r $(dirname "$0")/* ${INSTALL_DIR}/
        else
            echo -e "${YELLOW}Downloading from GitHub...${NC}"
            cd /tmp
            curl -Lo portvpn-web.tar.gz https://github.com/RulzxCompute/PortVPNWeb/releases/latest/download/portvpn-web.tar.gz
            tar -xzf portvpn-web.tar.gz -C ${INSTALL_DIR} --strip-components=1
            rm -f portvpn-web.tar.gz
        fi
        
        cd ${INSTALL_DIR}
        mkdir storage
        cd bootstrap
        mkdir cache
        cd ../
        # Set permissions
        chmod -R 755 storage bootstrap/cache
        chown -R www-data:www-data ${INSTALL_DIR}
        sudo chown -R www-data:www-data storage
        sudo chown -R www-data:www-data bootstrap/cache

        sudo chmod -R 775 storage
        sudo chmod -R 775 bootstrap/cache

        
        # Install dependencies
        sudo -u www-data composer install --no-dev --optimize-autoloader > /dev/null 2>&1
        
        # Setup environment
        cp .env.example .env
        sudo chmod 664 .env
        sudo chmod -R 777 storage bootstrap/cache
        sudo chmod 777 .env
        sed -i "s/DB_DATABASE=portvpn/DB_DATABASE=${DB_NAME}/" .env
        sed -i "s/DB_USERNAME=portvpn/DB_USERNAME=${DB_USER}/" .env
        sed -i "s/DB_PASSWORD=your_secure_password/DB_PASSWORD=${DB_PASS}/" .env
        sed -i "s|APP_URL=https://your-domain.com|APP_URL=https://${DOMAIN}|" .env
        
        # Generate key
        sudo -u www-data php artisan key:generate --force
        sudo -u www-data php artisan storage:link
        
        echo -e "${YELLOW}[8/10] Running migrations...${NC}"
        sudo php artisan thinker
        sudo -u www-data php artisan migrate --force --seed
        sudo -u www-data php artisan app:init
        
        echo -e "${YELLOW}[9/10] Configuring Nginx...${NC}"
        
        if [ "$USE_SSL" = "y" ] || [ "$USE_SSL" = "Y" ]; then
            # Install Certbot
            apt-get install -y certbot python3-certbot-nginx > /dev/null 2>&1
            
            cat > /etc/nginx/sites-available/portvpn << EOF
server {
    listen 80;
    server_name ${DOMAIN};
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ${DOMAIN};
    root ${INSTALL_DIR}/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/${DOMAIN}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${DOMAIN}/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
            # Obtain SSL certificate
            certbot --nginx -d ${DOMAIN} --non-interactive --agree-tos -m ${ADMIN_EMAIL} || true
        else
            cat > /etc/nginx/sites-available/portvpn << EOF
server {
    listen 80;
    server_name ${DOMAIN};
    root ${INSTALL_DIR}/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
        fi
        
        ln -sf /etc/nginx/sites-available/portvpn /etc/nginx/sites-enabled/
        rm -f /etc/nginx/sites-enabled/default
        nginx -t && systemctl restart nginx
        
        echo -e "${YELLOW}[10/10] Setting up cron and queue worker...${NC}"
        
        # Cron job
        (crontab -u www-data -l 2>/dev/null; echo "* * * * * cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab -u www-data -
        
        # Queue worker service
        cat > /etc/systemd/system/portvpn-worker.service << EOF
[Unit]
Description=PortVPN Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php ${INSTALL_DIR}/artisan queue:work
StartLimitInterval=180
StartLimitBurst=30
RestartSec=5s

[Install]
WantedBy=multi-user.target
EOF
        
        systemctl daemon-reload
        systemctl enable --now portvpn-worker
        systemctl enable --now redis-server
        
        echo ""
        echo -e "${GREEN}============================================${NC}"
        echo -e "${GREEN}   Installation Complete!${NC}"
        echo -e "${GREEN}============================================${NC}"
        echo ""
        echo -e "${CYAN}Website URL: ${NC}https://${DOMAIN}"
        echo ""
        echo -e "${YELLOW}Next steps:${NC}"
        echo -e "  1. Create admin user: ${CYAN}cd ${INSTALL_DIR} && sudo -u www-data php artisan app:user:create${NC}"
        echo -e "  2. Login to admin panel"
        echo -e "  3. Add nodes in Admin > Nodes"
        echo -e "  4. Generate redeem codes in Admin > Redeem Codes"
        echo ""
        echo -e "${YELLOW}Useful commands:${NC}"
        echo -e "  ${CYAN}systemctl status portvpn-worker${NC} - Check queue worker"
        echo -e "  ${CYAN}journalctl -u portvpn-worker -f${NC} - View worker logs"
        echo -e "  ${CYAN}tail -f ${INSTALL_DIR}/storage/logs/laravel.log${NC} - View app logs"
        ;;
        
    2)
        echo ""
        echo -e "${BLUE}============================================${NC}"
        echo -e "${BLUE}   Installing Node Agent${NC}"
        echo -e "${BLUE}============================================${NC}"
        echo ""
        
        # Run node agent installer
        if [ -f "$(dirname "$0")/node-agent/install.sh" ]; then
            bash $(dirname "$0")/node-agent/install.sh
        else
            echo -e "${RED}Node agent installer not found!${NC}"
            echo -e "${YELLOW}Please download the full package.${NC}"
            exit 1
        fi
        ;;
        
    3)
        echo ""
        echo -e "${RED}============================================${NC}"
        echo -e "${RED}   Uninstall PortVPN${NC}"
        echo -e "${RED}============================================${NC}"
        echo ""
        
        # Detect what is installed
        WEB_INSTALLED=false
        NODE_INSTALLED=false
        
        if [ -d "$INSTALL_DIR" ]; then
            WEB_INSTALLED=true
        fi
        
        if [ -f "/etc/systemd/system/portvpn-node.service" ]; then
            NODE_INSTALLED=true
        fi
        
        if [ "$WEB_INSTALLED" = false ] && [ "$NODE_INSTALLED" = false ]; then
            echo -e "${YELLOW}PortVPN is not installed.${NC}"
            exit 0
        fi
        
        echo -e "${YELLOW}Detected installation:${NC}"
        if [ "$WEB_INSTALLED" = true ]; then
            echo -e "  - Web Management Panel"
        fi
        if [ "$NODE_INSTALLED" = true ]; then
            echo -e "  - Node Agent"
        fi
        echo ""
        
        if [ "$WEB_INSTALLED" = true ] && [ "$NODE_INSTALLED" = true ]; then
            echo -e "${YELLOW}Pilih yang ingin diuninstall:${NC}"
            echo "  [1] Web Management Panel saja"
            echo "  [2] Node Agent saja"
            echo "  [3] Keduanya"
            read -p "Pilih (1/2/3): " UNINSTALL_CHOICE
        else
            UNINSTALL_CHOICE=3
        fi
        
        read -p "Yakin ingin uninstall? Data akan hilang! (y/N): " CONFIRM
        if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
            echo -e "${YELLOW}Uninstall dibatalkan.${NC}"
            exit 0
        fi
        
        case $UNINSTALL_CHOICE in
            1|3)
                if [ "$WEB_INSTALLED" = true ]; then
                    echo -e "${YELLOW}Uninstalling Web Management Panel...${NC}"
                    systemctl stop portvpn-worker 2>/dev/null || true
                    systemctl disable portvpn-worker 2>/dev/null || true
                    rm -f /etc/systemd/system/portvpn-worker.service
                    
                    rm -f /etc/nginx/sites-enabled/portvpn
                    rm -f /etc/nginx/sites-available/portvpn
                    systemctl restart nginx
                    
                    (crontab -u www-data -l 2>/dev/null | grep -v "portvpn" || true) | crontab -u www-data -
                    
                    echo -e "${YELLOW}Hapus database? (y/N): ${NC}"
                    read DROP_DB
                    if [ "$DROP_DB" = "y" ] || [ "$DROP_DB" = "Y" ]; then
                        mysql -u root -e "DROP DATABASE IF EXISTS ${DB_NAME};"
                        mysql -u root -e "DROP USER IF EXISTS '${DB_USER}'@'127.0.0.1';"
                    fi
                    
                    rm -rf ${INSTALL_DIR}
                    echo -e "${GREEN}Web Management Panel diuninstall.${NC}"
                fi
                ;;
        esac
        
        case $UNINSTALL_CHOICE in
            2|3)
                if [ "$NODE_INSTALLED" = true ]; then
                    echo -e "${YELLOW}Uninstalling Node Agent...${NC}"
                    systemctl stop portvpn-node 2>/dev/null || true
                    systemctl disable portvpn-node 2>/dev/null || true
                    rm -f /etc/systemd/system/portvpn-node.service
                    
                    systemctl stop wg-quick@wg0 2>/dev/null || true
                    systemctl disable wg-quick@wg0 2>/dev/null || true
                    
                    rm -rf /opt/portvpn-node
                    rm -rf /etc/portvpn
                    
                    echo -e "${GREEN}Node Agent diuninstall.${NC}"
                fi
                ;;
        esac
        
        systemctl daemon-reload
        echo ""
        echo -e "${GREEN}Uninstall complete!${NC}"
        ;;
        
    *)
        echo -e "${RED}Pilihan tidak valid!${NC}"
        exit 1
        ;;
esac
