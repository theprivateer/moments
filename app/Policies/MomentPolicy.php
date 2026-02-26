<?php

namespace App\Policies;

use App\Models\Moment;
use App\Models\User;

class MomentPolicy
{
    public function update(User $user, Moment $moment): bool
    {
        return $user->id === $moment->user_id;
    }

    public function delete(User $user, Moment $moment): bool
    {
        return $user->id === $moment->user_id;
    }
}
