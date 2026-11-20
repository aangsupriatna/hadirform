<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;

class FormPolicy
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

    public function view(User $user, Form $form): bool
    {
        return $user->organization_id === $form->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Form $form): bool
    {
        return $user->isAdmin() && $user->organization_id === $form->organization_id;
    }

    public function publish(User $user, Form $form): bool
    {
        return $user->isAdmin() && $user->organization_id === $form->organization_id;
    }

    public function delete(User $user, Form $form): bool
    {
        return $user->isAdmin() && $user->organization_id === $form->organization_id;
    }

    public function export(User $user, Form $form): bool
    {
        return $user->organization_id === $form->organization_id;
    }
}
