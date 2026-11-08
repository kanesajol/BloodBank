<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Models\StaffShiftRecord;
use App\Models\User;
use App\Support\BloodBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(): JsonResponse
    {
        $staff = User::query()
            ->orderBy('id')
            ->get()
            ->map(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'username' => $member->username,
                'role' => $member->role,
                'contact' => $member->contact,
                'status' => $member->status,
                'assigned_shift' => $member->assigned_shift,
            ])
            ->values();

        return response()->json(['staff' => $staff]);
    }

    public function log(Request $request): JsonResponse
    {
        $logDate = $request->query('date', now()->toDateString());
        if (! is_string($logDate) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $logDate)) {
            $logDate = now()->toDateString();
        }

        $dayLogs = StaffShiftRecord::query()
            ->with('user')
            ->whereDate('login_date', $logDate)
            ->get()
            ->sortBy(function (StaffShiftRecord $record) {
                return $record->logged_in_at?->getTimestamp()
                    ?? strtotime(trim($record->login_date?->format('Y-m-d').' '.$record->login_time))
                    ?: $record->id;
            })
            ->values()
            ->map(fn (StaffShiftRecord $record) => [
                'id' => $record->id,
                'staff' => $record->user?->name,
                'username' => $record->user?->username,
                'login' => $record->loginLabel(),
                'logout' => $record->isOpen() ? null : $record->logoutLabel(),
            ]);

        return response()->json([
            'logDate' => $logDate,
            'entries' => $dayLogs,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._@+-]+$/', Rule::unique('users', 'username'), AuthController::uniqueStaffEmail()],
            'role' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:50'],
        ]);

        $shift = BloodBank::shift('morning');
        $nextId = ((int) User::query()->max('id')) + 1;
        $username = trim($validated['username']);

        $user = User::query()->create([
            'name' => trim($validated['full_name']),
            'username' => $username,
            'email' => AuthController::staffEmail($username),
            'password' => 'password',
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

        return response()->json([
            'message' => 'Staff account created.',
            'staff' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
            ],
        ], 201);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        if (! $actor || $actor->is($user)) {
            return response()->json([
                'message' => 'You cannot delete the account you are currently using.',
            ], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Staff account deleted.']);
    }

    public function roles(): JsonResponse
    {
        return response()->json(['roles' => BloodBank::ROLES]);
    }
}
