<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\BloodBank;
use App\Support\StaffPresence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('blood-collection');
        }

        return view('auth', ['panel' => 'login']);
    }

    public function login(Request $request): RedirectResponse
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
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'Invalid username or password.']);
        }

        $request->session()->regenerate();
        StaffPresence::open($request->user());

        return redirect()->intended(route('blood-collection'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            StaffPresence::signOut($user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showSignup(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('blood-collection');
        }

        return view('auth', ['panel' => 'signup']);
    }

    public function signup(Request $request): RedirectResponse
    {
        $validated = $request->validate(self::signupRules());

        $user = self::createStaffAccount($validated);

        Auth::login($user);
        $request->session()->regenerate();
        StaffPresence::open($user);

        return redirect()->route('blood-collection');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function signupRules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._@+-]+$/', Rule::unique('users', 'username'), self::uniqueStaffEmail()],
            'role' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    /**
     * @param  array{full_name: string, username: string, role: string, contact: string, password: string}  $validated
     */
    public static function createStaffAccount(array $validated): User
    {
        $shift = BloodBank::shift('morning');
        $nextId = ((int) User::query()->max('id')) + 1;
        $username = trim($validated['username']);

        return User::query()->create([
            'name' => trim($validated['full_name']),
            'username' => $username,
            'email' => self::staffEmail($username),
            'password' => $validated['password'],
            'role' => trim($validated['role']),
            'assigned_shift' => $shift['assigned'],
            'shift_type' => $shift['id'],
            'station' => '',
            'contact' => trim($validated['contact']),
            'status' => 'Active',
            'avatar_initials' => BloodBank::initials($validated['full_name']),
            'avatar_color' => BloodBank::AVATAR_COLORS[$nextId % count(BloodBank::AVATAR_COLORS)],
            'last_login' => 'Never',
        ]);
    }

    public static function staffEmail(string $username): string
    {
        return str_contains($username, '@') ? $username : $username.'@staff.local';
    }

    public static function uniqueStaffEmail(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $email = self::staffEmail(trim((string) $value));
            if (User::query()->where('email', $email)->exists()) {
                $fail('The username has already been taken.');
            }
        };
    }
}
