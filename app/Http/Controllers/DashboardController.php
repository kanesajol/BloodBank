<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use App\Support\BloodBank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $colors = BloodBank::DONOR_AVATAR_COLORS;
        $donors = Donor::with('donations')->orderBy('id')->get()->values()->map(function (Donor $donor, int $index) use ($colors) {
            $last = $donor->donations->sortBy('donated_on')->last();

            return [
                'donor' => $donor,
                'initials' => BloodBank::initials($donor->full_name),
                'avatar' => $colors[$index % count($colors)],
                'lastDonationLabel' => $last ? BloodBank::formatDate($last->donated_on, 'M j, Y') : 'New Registrant',
                'search' => mb_strtolower(implode(' ', [
                    $donor->full_name,
                    $donor->blood_type,
                    $donor->formattedAddress(),
                    $donor->gender,
                ])),
            ];
        });

        $totalBags = $donors->sum(fn (array $row) => $row['donor']->donations->count());

        return view('dashboard', [
            'donors' => $donors,
            'totalDonors' => $donors->count(),
            'totalBags' => $totalBags,
            'bloodTypes' => BloodBank::BLOOD_TYPES,
            'openRegister' => old('form') === 'dashboard-donor',
        ]);
    }

    public function storeDonor(Request $request): RedirectResponse
    {
        $request->merge([
            'full_name' => preg_replace('/\s+/', ' ', trim((string) $request->input('full_name'))),
        ]);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'blood_type' => ['required', Rule::in(BloodBank::BLOOD_TYPES)],
            'age' => ['required', 'integer', 'min:16', 'max:70'],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'contact' => ['required', 'string', 'max:50'],
            'barangay' => ['required', 'string', 'max:255'],
            'municipality' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'initial_donation_date' => ['required', 'date'],
            'donation_location' => ['required', 'string', 'max:255'],
        ]);

        $result = Donor::recordProfileOrDonation($validated);
        $donor = $result['donor'];

        $message = $result['attached']
            ? "Donation attached to {$donor->full_name}. Their permanent address was kept."
            : "Donor {$donor->full_name} was registered.";

        return redirect()->route('dashboard')->with('success', $message);
    }

    public function destroyDonor(Request $request, Donor $donor): RedirectResponse
    {
        $validated = $request->validate([
            'return_to' => ['nullable', Rule::in(['dashboard', 'blood-collection'])],
        ]);

        $name = $donor->full_name;
        $donor->delete();

        $route = ($validated['return_to'] ?? 'dashboard') === 'blood-collection'
            ? 'blood-collection'
            : 'dashboard';

        return redirect()
            ->route($route)
            ->with('success', "Donor {$name} was deleted.");
    }
}
