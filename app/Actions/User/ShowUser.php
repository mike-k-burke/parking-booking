<?php

namespace App\Actions\User;

use App\Models\User;

class ShowUser
{
    public function handle(int $id): User
    {
        return User::query()->findOrFail($id);
    }
}
