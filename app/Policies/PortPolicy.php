<?php

namespace App\Policies;

use App\Models\Port;
use App\Models\User;

class PortPolicy
{
    public function view(User $user, Port $port): bool
    {
        return $user->id === $port->user_id || $user->isAdmin();
    }

    public function update(User $user, Port $port): bool
    {
        return $user->id === $port->user_id || $user->isAdmin();
    }

    public function delete(User $user, Port $port): bool
    {
        return $user->id === $port->user_id || $user->isAdmin();
    }
}
