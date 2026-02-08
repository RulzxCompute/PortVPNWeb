<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Port;
use App\Policies\PortPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Port::class => PortPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
