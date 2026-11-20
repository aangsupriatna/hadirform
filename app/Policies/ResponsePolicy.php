<?php

namespace App\Policies;

use App\Models\Response;
use App\Models\User;

class ResponsePolicy
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

    public function view(User $user, Response $response): bool
    {
        return $user->organization_id === $response->organization_id;
    }

    public function verify(User $user, Response $response): bool
    {
        return $user->isOperator() && $user->organization_id === $response->organization_id;
    }

    public function delete(User $user, Response $response): bool
    {
        return $user->isAdmin() && $user->organization_id === $response->organization_id;
    }
}
