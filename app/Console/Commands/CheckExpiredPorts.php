<?php

namespace App\Console\Commands;

use App\Models\Port;
use Illuminate\Console\Command;

class CheckExpiredPorts extends Command
{
    protected $signature = 'app:ports:check-expired';
    protected $description = 'Check and suspend expired ports';

    public function handle(): int
    {
        $expiredPorts = Port::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredPorts as $port) {
            $port->update(['status' => 'expired']);
            $port->node->decrementUsedPorts();
            $count++;
        }

        $this->info("{$count} expired ports suspended.");
        return 0;
    }
}
