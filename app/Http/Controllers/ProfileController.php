<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ProfileController extends Controller
{

    public function viewProfile()
    {
        $user = auth('api')->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => '✅ Profile fetched successfully!',
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'phone'  => $user->phone,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'role'   => $user->role->role_name ?? null,
                'status' => $user->status,
            ]
        ]);
    }

    /**
     * 🔹 Update Authenticated User Profile
     */
    public function updateProfile(Request $request)
    {
        $user = JWTAuth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // 🔸 Validation
        $validated = $request->validate([
            'name'   => ['sometimes', 'string', 'max:100'],
            'phone'  => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        // 🔸 Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar (if exists)
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // 🔸 Update other fields
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['phone'])) {
            $user->phone = $validated['phone'];
        }

        $user->save();

        return response()->json([
            'message' => '✅ Profile updated successfully!',
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'phone'  => $user->phone,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'role'   => $user->role->role_name ?? null,
                'status' => $user->status,
            ],
        ], 200);
    }
}