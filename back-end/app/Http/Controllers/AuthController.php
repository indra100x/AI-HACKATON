<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = ($user instanceof User) ? $user->createToken('auth_token')->plainTextToken : null;

        $redirectUrl = $user->role === 'admin' ? '/admin' : '/dashboard';

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
            'redirectUrl' => $redirectUrl,
        ], 200);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        $token = ($user instanceof User) ? $user->createToken('auth_token')->plainTextToken : null;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => $user,
            'redirectUrl' => '/dashboard',
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $user,
            'stats' => [
                'documents_count' => $user->documents()->count(),
                'feedbacks_count' => $user->feedbacks()->count(),
                'approved_feedbacks' => $user->feedbacks()->where('approved', true)->count(),
            ],
            'recent_documents' => $user->documents()->latest()->take(5)->get(),
        ], 200);
    }
}

