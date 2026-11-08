<?php

namespace App\Http\Controllers;

use App\Models\StaffShiftRecord;
use App\Models\User;
use App\Support\BloodBank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffAccountController extends Controller
{
    public function index(): View
    {
        $staff = User::query()
            ->with(['shiftRecords' => fn ($query) => $query->orderByDesc('id')])
            ->orderBy('id')
            ->get();

        return view('staff-accounts', ['staff' => $staff]);
    }

    public function log(Request $request): View
    {
        $logDate = $request->query('date', now()->toDateString());
        if (! is_string($logDate) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $logDate)) {
            $logDate = now()->toDateString();
        }

        $selectedStaff = $request->filled('staff')
            ? User::query()->findOrFail((int) $request->query('staff'))
            : null;
        $logsQuery = StaffShiftRecord::query()
            ->with('user')
            ->when(
                $selectedStaff !== null,
                fn ($query) => $query->where('user_id', $selectedStaff->id),
                fn ($query) => $query->whereDate('login_date', $logDate)
            );
        $dayLogs = $logsQuery->get()
            ->sortBy(function (StaffShiftRecord $record) {
                return $record->logged_in_at?->getTimestamp()
                    ?? strtotime(trim($record->login_date?->format('Y-m-d').' '.$record->login_time))
                    ?: $record->id;
            })
            ->when($selectedStaff !== null, fn ($logs) => $logs->reverse())
            ->values();

        return view('staff-log', [
            'logDate' => $logDate,
            'dayLogs' => $dayLogs,
            'selectedStaff' => $selectedStaff,
        ]);
    }

    public function store(Request $request): RedirectResponse
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

        User::query()->create([
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

        return redirect()->route('staff-accounts');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor || $actor->is($user)) {
            return redirect()
                ->route('staff-accounts')
                ->with('error', 'You cannot delete the account you are currently using.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('staff-accounts')
            ->with('success', "Staff account {$name} was deleted.");
    }

    public function profile(Request $request): View
    {
        $staff = $request->user();
        $records = $staff->shiftRecords()->orderByDesc('login_date')->orderByDesc('id')->get();

        return view('staff-profile', [
            'staff' => $staff,
            'records' => $records,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $staff = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff->id)],
            'contact' => ['required', 'string', 'max:50'],
            'role' => ['required', 'string', 'max:255'],
        ]);

        $staff->update([
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'contact' => trim($validated['contact']),
            'role' => trim($validated['role']),
            'avatar_initials' => BloodBank::initials($validated['name']),
        ]);

        return redirect()->route('staff-profile')->with('success', 'Profile updated.');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        $staff = $request->user();

        if (! Hash::check($validated['current_password'], $staff->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $staff->update(['password' => $validated['password']]);

        return redirect()->route('staff-profile')->with('success', 'Password changed.');
    }
}
