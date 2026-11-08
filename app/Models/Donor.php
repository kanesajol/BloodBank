<?php

namespace App\Models;

use App\Support\BloodBank;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'full_name',
    'first_name',
    'middle_name',
    'last_name',
    'suffix',
    'blood_type',
    'age',
    'gender',
    'barangay',
    'municipality',
    'province',
    'contact',
    'birthdate',
    'registered_date',
])]
class Donor extends Model
{
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn () => BloodBank::formatAddress($this->barangay, $this->municipality, $this->province),
        );
    }

    public function getFullAddressAttribute(): string
    {
        return $this->full_address;
    }

    public function formattedAddress(): string
    {
        return BloodBank::formatAddress($this->barangay, $this->municipality, $this->province);
    }

    /**
     * Display name: First, middle name or initial, last, then suffix.
     * Older records that only have full_name keep that stored value.
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(function (?string $value, array $attributes): string {
            $composed = static::composeName(
                $attributes['first_name'] ?? null,
                $attributes['middle_name'] ?? null,
                $attributes['last_name'] ?? null,
                $attributes['suffix'] ?? null,
            );

            if ($composed !== '') {
                return $composed;
            }

            $stored = trim((string) $value);
            if ($stored === '' || preg_match('/^(?:—|-)?\s*\d+$/u', $stored)) {
                return '—';
            }

            return $stored;
        });
    }

    public static function composeName(?string $first, ?string $middle, ?string $last, ?string $suffix): string
    {
        $parts = array_filter([
            trim((string) $first),
            static::displayMiddle($middle),
            trim((string) $last),
            static::displaySuffix($suffix),
        ], fn (string $part) => $part !== '');

        return preg_replace('/\s+/', ' ', trim(implode(' ', $parts))) ?? '';
    }

    /**
     * @return array{first_name: string, middle_name: string, last_name: string, suffix: string}
     */
    public function nameParts(): array
    {
        $first = trim((string) ($this->attributes['first_name'] ?? ''));
        $last = trim((string) ($this->attributes['last_name'] ?? ''));

        if ($first !== '' || $last !== '') {
            return [
                'first_name' => $first,
                'middle_name' => trim((string) ($this->attributes['middle_name'] ?? '')),
                'last_name' => $last,
                'suffix' => trim((string) ($this->attributes['suffix'] ?? '')),
            ];
        }

        return static::splitFullName((string) ($this->attributes['full_name'] ?? ''));
    }

    /**
     * @return array{first_name: string, middle_name: string, last_name: string, suffix: string}
     */
    public static function splitFullName(string $name): array
    {
        $tokens = preg_split('/\s+/', trim($name)) ?: [];
        $tokens = array_values(array_filter($tokens, fn ($token) => $token !== ''));
        $suffix = '';
        $known = [
            'jr' => 'Jr.',
            'jr.' => 'Jr.',
            'sr' => 'Sr.',
            'sr.' => 'Sr.',
            'ii' => 'II',
            'iii' => 'III',
            'iv' => 'IV',
        ];

        if ($tokens !== []) {
            $tail = mb_strtolower((string) end($tokens));
            if (isset($known[$tail])) {
                $suffix = $known[$tail];
                array_pop($tokens);
            }
        }

        $first = '';
        $middle = '';
        $last = '';

        if (count($tokens) === 1) {
            $first = $tokens[0];
        } elseif (count($tokens) === 2) {
            [$first, $last] = $tokens;
        } elseif (count($tokens) > 2) {
            $first = (string) array_shift($tokens);
            $last = (string) array_pop($tokens);
            $middle = implode(' ', $tokens);
        }

        return [
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
            'suffix' => $suffix,
        ];
    }

    private static function displayMiddle(?string $middle): string
    {
        $middle = trim((string) $middle);
        if ($middle === '') {
            return '';
        }

        $bare = rtrim($middle, '.');
        if (mb_strlen($bare) === 1) {
            return mb_strtoupper($bare).'.';
        }

        return $middle;
    }

    private static function displaySuffix(?string $suffix): string
    {
        $suffix = trim((string) $suffix);

        return strcasecmp($suffix, 'None') === 0 ? '' : $suffix;
    }

    protected function casts(): array
    {
        return [
            'registered_date' => 'date',
            'birthdate' => 'date',
            'age' => 'integer',
        ];
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * One profile per donor name. A later donation at another facility is attached
     * to that profile and does not replace the permanent address.
     *
     * @param  array<string, mixed>  $validated
     * @return array{donor: self, attached: bool}
     */
    public static function recordProfileOrDonation(array $validated): array
    {
        $name = preg_replace('/\s+/', ' ', trim((string) $validated['full_name'])) ?? '';

        try {
            return DB::transaction(fn () => static::storeProfileOrDonation($validated, $name));
        } catch (UniqueConstraintViolationException $exception) {
            return DB::transaction(function () use ($validated, $name, $exception) {
                $donor = static::findByName($name, lock: true);
                if ($donor === null) {
                    throw $exception;
                }

                static::attachDonation($donor, $validated);

                return ['donor' => $donor->fresh(), 'attached' => true];
            });
        }
    }

    public static function findByName(string $name, bool $lock = false): ?self
    {
        $query = static::query()->whereRaw('LOWER(full_name) = ?', [mb_strtolower($name)]);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{donor: self, attached: bool}
     */
    private static function storeProfileOrDonation(array $validated, string $name): array
    {
        $existing = static::findByName($name, lock: true);
        if ($existing !== null) {
            static::attachDonation($existing, $validated);

            return ['donor' => $existing->fresh(), 'attached' => true];
        }

        $donor = static::query()->create([
            'full_name' => $name,
            'first_name' => static::storedNamePart($validated['first_name'] ?? null),
            'middle_name' => static::storedNamePart($validated['middle_name'] ?? null),
            'last_name' => static::storedNamePart($validated['last_name'] ?? null),
            'suffix' => static::displaySuffix($validated['suffix'] ?? null) ?: null,
            'blood_type' => $validated['blood_type'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'contact' => trim((string) $validated['contact']),
            'barangay' => trim((string) $validated['barangay']),
            'municipality' => trim((string) $validated['municipality']),
            'province' => trim((string) ($validated['province'] ?? ''))
                ?: BloodBank::provinceForMunicipality((string) $validated['municipality']),
            'birthdate' => $validated['birthdate'] ?? null,
            'registered_date' => $validated['initial_donation_date'],
        ]);

        static::attachDonation($donor, $validated, $donor->blood_type);

        return ['donor' => $donor, 'attached' => false];
    }

    public static function storedNamePart(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private static function attachDonation(self $donor, array $validated, ?string $bloodType = null): void
    {
        $passed = ($validated['status'] ?? 'passed') !== 'deferred';

        $donor->donations()->create([
            'donated_on' => $validated['initial_donation_date'],
            'blood_type' => $bloodType ?: $donor->blood_type,
            'location' => trim((string) $validated['donation_location']),
            'bags' => $passed ? 1 : 0,
            'mbd_date' => static::storedNamePart($validated['mbd_date'] ?? null),
            'serial_number' => $passed ? static::storedNamePart($validated['serial_number'] ?? null) : null,
            'status' => isset($validated['status']) ? (string) $validated['status'] : null,
            'reason_for_deferral' => $passed ? null : static::storedNamePart($validated['reason_for_deferral'] ?? null),
            'reserved_for_px' => $passed ? static::storedNamePart($validated['reserved_for_px'] ?? null) : null,
            'remarks' => static::storedNamePart($validated['remarks'] ?? null),
        ]);
    }
}
