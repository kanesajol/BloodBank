<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DonorController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $remarks = trim((string) $request->input('remarks', ''));
        $request->merge([
            'remarks' => $remarks !== '' ? $remarks : null,
        ]);

        return app(BloodCollectionController::class)->storeDonor($request);
    }

    public static function displayName(?string $last, ?string $first, ?string $middle, ?string $suffix, ?string $fallback = null): string
    {
        $clean = static function (string $value): string {
            $value = trim($value);

            return trim(preg_replace('/^[`\'’‘]+/u', '', $value) ?? $value);
        };
        $last = $clean((string) $last);
        $first = $clean((string) $first);
        $middle = $clean((string) $middle);
        $suffix = $clean((string) $suffix);

        if (strcasecmp($suffix, 'None') === 0) {
            $suffix = '';
        }

        if ($last === '' && $first === '') {
            $fallbackName = trim((string) $fallback);
            if ($fallbackName === '' || preg_match('/^(?:—|-)?\s*\d+$/u', $fallbackName)) {
                return '—';
            }

            return $fallbackName;
        }

        $rest = trim(implode(' ', array_filter([$first, $middle, $suffix], fn (string $part) => $part !== '')));
        $lastLabel = $last !== '' ? mb_strtoupper($last) : '';
        $restLabel = $rest !== '' ? mb_strtoupper($rest) : '';

        if ($lastLabel === '') {
            return $restLabel;
        }

        return $restLabel === '' ? $lastLabel : $lastLabel.', '.$restLabel;
    }

    public static function displayNameFor(Donor $donor): string
    {
        return self::displayName(
            $donor->getAttributes()['last_name'] ?? null,
            $donor->getAttributes()['first_name'] ?? null,
            $donor->getAttributes()['middle_name'] ?? null,
            $donor->getAttributes()['suffix'] ?? null,
            $donor->getAttributes()['full_name'] ?? null,
        );
    }
}
