<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AuthController as StaffAuthController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\BloodBank;
use App\Support\StaffPresence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('username', $credentials['username'])->first();

        if (! $user || $user->status !== 'Active' || ! Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ])) {
            throw ValidationException::withMessages([
                'username' => ['Invalid username or password.'],
            ]);
        }

        StaffPresence::open($user);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function signup(Request $request): JsonResponse
    {
        $validated = $request->validate(StaffAuthController::signupRules());
        $user = StaffAuthController::createStaffAccount($validated);

        StaffPresence::open($user);

        return response()->json([
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            StaffPresence::signOut($user);
            $user->currentAccessToken()?->delete();
        }

        return response()->json(['message' => 'Logged out.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'contact' => ['required', 'string', 'max:50'],
            'role' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'contact' => trim($validated['contact']),
            'role' => isset($validated['role']) ? trim($validated['role']) : $user->role,
            'avatar_initials' => BloodBank::initials($validated['name']),
        ]);

        return response()->json([
            'message' => 'Profile updated.',
            'user' => $this->userPayload($user->refresh()),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update(['password' => $validated['password']]);

        return response()->json(['message' => 'Password changed.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
            'email' => $user->email,
            'contact' => $user->contact,
            'station' => $user->station,
            'assigned_shift' => $user->assigned_shift,
            'shift_type' => $user->shift_type,
            'avatar_initials' => $user->avatar_initials,
        ];
    }
}
