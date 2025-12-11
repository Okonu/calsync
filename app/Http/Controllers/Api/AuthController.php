<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoogleAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'sometimes|string',
            'device_name' => 'required|string',
            'google_token' => 'sometimes|string',
        ]);

        // If Google token is provided, authenticate via Google
        if ($request->has('google_token')) {
            return $this->loginWithGoogle($request);
        }

        // Traditional email/password login (if implemented)
        if (!$request->has('password')) {
            return response()->json([
                'message' => 'Password or Google token required'
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'user' => $user->load('googleAccounts'),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    protected function loginWithGoogle(Request $request)
    {
        try {
            // In a real implementation, you would verify the Google token
            // For now, we'll assume the token is valid and contains user info
            $googleUser = $this->validateGoogleToken($request->google_token);

            if (!$googleUser) {
                return response()->json([
                    'message' => 'Invalid Google token'
                ], 401);
            }

            $user = User::where('email', $googleUser['email'])->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found. Please login via web first.'
                ], 404);
            }

            $token = $user->createToken($request->device_name)->plainTextToken;

            return response()->json([
                'user' => $user->load('googleAccounts'),
                'token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (\Exception $e) {
            Log::error('Google login error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Authentication failed'
            ], 500);
        }
    }

    protected function validateGoogleToken($token)
    {
        // TODO: Implement actual Google token validation
        // This would typically involve calling Google's tokeninfo endpoint
        // For now, return a mock response
        return [
            'email' => 'user@example.com',
            'name' => 'John Doe',
        ];
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string',
        ]);

        // Delete current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $request->user()->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load([
            'googleAccounts',
            'communities' => function($query) {
                $query->where('is_active', true)->withCount('events');
            },
            'bookingPage'
        ]);

        return response()->json([
            'user' => $user,
        ]);
    }
}