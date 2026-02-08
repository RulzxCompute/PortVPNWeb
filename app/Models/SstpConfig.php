<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SstpConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'port_id',
        'user_id',
        'username',
        'password',
        'server_address',
        'server_port',
        'client_ip',
        'config_script',
    ];

    protected $hidden = [
        'password',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function port()
    {
        return $this->belongsTo(Port::class);
    }

    public function getConnectionStringAttribute(): string
    {
        return "{$this->server_address}:{$this->server_port}";
    }

    public function getLinuxSetupScriptAttribute(): string
    {
        return <<<SCRIPT
#!/bin/bash
# SSTP Client Setup Script
# Server: {$this->server_address}
# Username: {$this->username}

echo "Installing SSTP client..."
apt-get update
apt-get install -y sstp-client

echo "Creating SSTP connection..."
cat > /etc/sstp/sstp-{$this->username}.conf << 'EOF'
plugin sstp-ppp.so
sstp-srv-addr {$this->server_address}
sstp-user {$this->username}
sstp-pass {$this->password}
EOF

echo "Setup complete! Connect with:"
echo "sstpc --user {$this->username} --password {$this->password} {$this->server_address}:{$this->server_port}"
SCRIPT;
    }
}
