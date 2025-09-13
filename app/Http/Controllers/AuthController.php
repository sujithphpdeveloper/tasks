<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Login function
    */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Response for the success login with the JWTToken
    */
    protected function respondWithToken($token)
    {
        return response()->json([
            'message' => 'User Login successfully',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }

    /**
     * Logout function
    */
    public function logout(Request $request)
    {
        auth()->logout();

        return response()->json([
            'message' => 'User Successfully Logged Out'
        ]);
    }

    /**
     * Get the logged User.
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Refreshing the token
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
}
