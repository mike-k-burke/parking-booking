<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class SaveUser
{
    public function __construct(private ShowUser $showAction) {}

    public function handle(array $attributes, ?int $id = null): User
    {
        if ($id === null) {
            $user = new User();
        } else {
            $user = $this->showAction->handle($id);
        }

        $attributes = Arr::only($attributes, ['name', 'email', 'password']);
        if (isset($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
        }

        $user->fill($attributes);
        $user->save();

        return $user;
    }
}
