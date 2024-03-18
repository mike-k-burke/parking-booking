<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticateUser
{
    public function handle(string $email, string $password):? User
    {
        $user = User::query()->where('email', $email)->first();

        if(!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }
}
