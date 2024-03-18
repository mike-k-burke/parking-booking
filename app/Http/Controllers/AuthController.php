<?php

namespace App\Http\Controllers;

use App\Actions\User\SaveUser;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function register(RegisterUserRequest $request, SaveUser $action)
    {
        $user = $action->handle($request->validated());
        $token = $user->createToken($user->email)->plainTextToken;

        return response()->json(compact('token', 'user'), 201);
    }

    public function login(LoginRequest $request, AuthenticateUser $action)
    {
        $user = $action->handle($request->validated());
        if ($user === null) {
            throw new AuthenticationException();
        }

        $token = $user->createToken($user->email)->plainTextToken;

        return response()->json(compact('token', 'user'), 200);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'Tokens deleted'], 200);
    }
}
