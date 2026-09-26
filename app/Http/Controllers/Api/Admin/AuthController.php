<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle admin login request.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
                'errors' => [
                    'email' => ['Invalid email or password.']
                ]
            ], 422);
        }

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have administrator permissions.'
            ], 403);
        }

        // Revoke older tokens if needed or issue a fresh one
        $token = $user->createToken('admin-auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Admin authentication successful.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    /**
     * Get the authenticated admin user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Administrator privileges required.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    /**
     * Log out the current admin user (revoke current access token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
