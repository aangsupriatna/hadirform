<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isOperator();
    }

    public function view(User $user, Event $event): bool
    {
        return $user->organization_id === $event->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Event $event): bool
    {
        return $user->isAdmin() && $user->organization_id === $event->organization_id;
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->isAdmin() && $user->organization_id === $event->organization_id;
    }
}
