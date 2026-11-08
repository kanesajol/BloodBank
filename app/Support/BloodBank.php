<?php

namespace App\Support;

use Carbon\Carbon;

class BloodBank
{
    public const BLOOD_TYPES = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

    public const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    public const LOCATIONS = ['Tubajon', 'San Jose', 'Basilisa', 'Cagdianao', 'Dinagat', 'Loreto', 'Libjo'];

    public const YEARS = [2024, 2025, 2026, 2027];

    public const ROLES = [
        'Medical Technologist',
        'Blood Collection Nurse',
        'Emergency Screening Officer',
        'Phlebotomist / Screener',
        'Blood Bank Administrator',
    ];

    public const AVATAR_COLORS = ['#2563eb', '#059669', '#d97706', '#7c3aed', '#dc2626', '#0891b2'];

    public const DONOR_AVATAR_COLORS = ['#dbeafe', '#dcfce7', '#ffedd5', '#f3e8ff', '#fce7f3', '#e0e7ff'];

    public const SHIFTS = [
        'morning' => [
            'id' => 'morning',
            'name' => 'Morning Shift',
            'time' => '6:00 AM – 2:00 PM',
            'assigned' => 'Morning Shift (6AM – 2PM)',
            'icon' => 'ti ti-sun',
            'badge' => 'shift-morning',
        ],
        'afternoon' => [
            'id' => 'afternoon',
            'name' => 'Afternoon Shift',
            'time' => '2:00 PM – 10:00 PM',
            'assigned' => 'Afternoon Shift (2PM – 10PM)',
            'icon' => 'ti ti-sunset',
            'badge' => 'shift-afternoon',
        ],
        'midnight' => [
            'id' => 'midnight',
            'name' => 'Midnight Shift',
            'time' => '10:00 PM – 6:00 AM',
            'assigned' => 'Midnight Shift (10PM – 6AM)',
            'icon' => 'ti ti-moon',
            'badge' => 'shift-midnight',
        ],
    ];

    public static function shift(string $type): array
    {
        return self::SHIFTS[$type] ?? self::SHIFTS['morning'];
    }

    public static function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $letters = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $letters !== '' ? $letters : 'ST';
    }

    public const PROVINCE_ACRONYM_MAP = [
        'SC' => 'Surigao City',
        'SDN' => 'Surigao del Norte',
        'PDI' => 'Dinagat Islands',
        'SDS' => 'Surigao del Sur',
        'ADN' => 'Agusan del Norte',
        'ADS' => 'Agusan del Sur',
    ];

    public const MUNICIPALITY_PROVINCE_MAP = [
        'Alegria' => 'Surigao del Norte',
        'Bacuag' => 'Surigao del Norte',
        'Burgos' => 'Surigao del Norte',
        'Claver' => 'Surigao del Norte',
        'Dapa' => 'Surigao del Norte',
        'Del Carmen' => 'Surigao del Norte',
        'General Luna' => 'Surigao del Norte',
        'Gigaquit' => 'Surigao del Norte',
        'Mainit' => 'Surigao del Norte',
        'Malimono' => 'Surigao del Norte',
        'Pilar' => 'Surigao del Norte',
        'Placer' => 'Surigao del Norte',
        'San Benito' => 'Surigao del Norte',
        'San Francisco' => 'Surigao del Norte',
        'San Isidro' => 'Surigao del Norte',
        'Santa Monica' => 'Surigao del Norte',
        'Sison' => 'Surigao del Norte',
        'Socorro' => 'Surigao del Norte',
        'Surigao City' => 'Surigao del Norte',
        'Tagana-an' => 'Surigao del Norte',
        'Tubod' => 'Surigao del Norte',
        'Barobo' => 'Surigao del Sur',
        'Bayabas' => 'Surigao del Sur',
        'Bislig City' => 'Surigao del Sur',
        'Cagwait' => 'Surigao del Sur',
        'Cantilan' => 'Surigao del Sur',
        'Carmen' => 'Surigao del Sur',
        'Carrascal' => 'Surigao del Sur',
        'Cortes' => 'Surigao del Sur',
        'Hinatuan' => 'Surigao del Sur',
        'Lanuza' => 'Surigao del Sur',
        'Lianga' => 'Surigao del Sur',
        'Lingig' => 'Surigao del Sur',
        'Madrid' => 'Surigao del Sur',
        'Marihatag' => 'Surigao del Sur',
        'San Agustin' => 'Surigao del Sur',
        'San Miguel' => 'Surigao del Sur',
        'Tagbina' => 'Surigao del Sur',
        'Tago' => 'Surigao del Sur',
        'Tandag City' => 'Surigao del Sur',
        'Basilisa' => 'Dinagat Islands',
        'Cagdianao' => 'Dinagat Islands',
        'Dinagat' => 'Dinagat Islands',
        'Libjo' => 'Dinagat Islands',
        'Loreto' => 'Dinagat Islands',
        'San Jose' => 'Dinagat Islands',
        'Tubajon' => 'Dinagat Islands',
        'Buenavista' => 'Agusan del Norte',
        'Cabadbaran City' => 'Agusan del Norte',
        'Jabonga' => 'Agusan del Norte',
        'Kitcharao' => 'Agusan del Norte',
        'Las Nieves' => 'Agusan del Norte',
        'Magallanes' => 'Agusan del Norte',
        'Nasipit' => 'Agusan del Norte',
        'Remedios T. Romualdez (RTR)' => 'Agusan del Norte',
        'Santiago' => 'Agusan del Norte',
        'Tubay' => 'Agusan del Norte',
        'Butuan City' => 'Agusan del Norte',
        'Bayugan City' => 'Agusan del Sur',
        'Bunawan' => 'Agusan del Sur',
        'Esperanza' => 'Agusan del Sur',
        'Prosperidad' => 'Agusan del Sur',
        'Rosario' => 'Agusan del Sur',
        'San Luis' => 'Agusan del Sur',
        'Santa Josefa' => 'Agusan del Sur',
        'Sibagat' => 'Agusan del Sur',
        'Talacogon' => 'Agusan del Sur',
        'Trento' => 'Agusan del Sur',
        'Veruela' => 'Agusan del Sur',
    ];

    public static function titleCaseText(string $value): string
    {
        return self::titleCaseAddressSegment($value);
    }

    protected static function titleCaseAddressSegment(string $segment): string
    {
        $segment = trim($segment);

        if ($segment === '') {
            return '';
        }

        $formatted = ucwords(mb_strtolower($segment), ' -');

        return preg_replace_callback('/\b(del|de|la|le|da|di|van|von|y|st|sa|el)\b/i', function ($matches) {
            return mb_strtolower($matches[0]);
        }, $formatted) ?? $formatted;
    }

    public static function expandLocationAbbreviations(string $value): string
    {
        $expanded = trim($value);

        if ($expanded === '') {
            return '';
        }

        foreach (self::PROVINCE_ACRONYM_MAP as $code => $provinceName) {
            $expanded = preg_replace('/\b'.preg_quote($code, '/').'\b/i', $provinceName, $expanded) ?? $expanded;
        }

        return preg_replace('/\s+/', ' ', $expanded) ?? $expanded;
    }

    public static function cleanAddressString(?string $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return '';
        }

        $expanded = self::expandLocationAbbreviations(trim(preg_replace('/\s+/', ' ', $value) ?? $value));

        $segments = preg_split('/\s*,\s*/u', $expanded, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $clean = [];
        $seen = [];

        foreach ($segments as $segment) {
            $formatted = self::titleCaseAddressSegment($segment);

            if ($formatted === '') {
                continue;
            }

            $key = preg_replace('/\s+/', ' ', mb_strtolower($formatted)) ?? $formatted;

            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $clean[] = $formatted;
            }
        }

        return implode(', ', $clean);
    }

    public static function summarizeCaragaAddress(?string $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return '';
        }

        $expanded = self::expandLocationAbbreviations($value);
        $segments = preg_split('/\s*,\s*/u', $expanded, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $municipalities = [];

        foreach (self::MUNICIPALITY_PROVINCE_MAP as $municipality => $province) {
            $municipalities[mb_strtolower($municipality)] = [$municipality, $province];
        }

        foreach (array_reverse($segments) as $segment) {
            $key = mb_strtolower(trim(preg_replace('/\s+/', ' ', $segment) ?? $segment));

            if (isset($municipalities[$key])) {
                [$municipality, $province] = $municipalities[$key];

                return $municipality.', '.$province;
            }
        }

        return '';
    }

    /**
     * @return array{province: string, municipality: string}
     */
    public static function resolveCaragaLocation(?string $value, ?string $municipalityValue = null): array
    {
        $segments = preg_split('/\s*,\s*/u', self::expandLocationAbbreviations((string) $value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $municipalities = [];

        foreach (self::MUNICIPALITY_PROVINCE_MAP as $municipality => $province) {
            $municipalities[mb_strtolower($municipality)] = [$municipality, $province];
        }

        $municipality = null;
        $municipalityKey = mb_strtolower(trim((string) $municipalityValue));

        if (isset($municipalities[$municipalityKey])) {
            [$municipality] = $municipalities[$municipalityKey];
        } else {
            foreach (array_reverse($segments) as $segment) {
                $key = mb_strtolower(trim(preg_replace('/\s+/', ' ', $segment) ?? $segment));

                if (isset($municipalities[$key])) {
                    [$municipality] = $municipalities[$key];
                    break;
                }
            }
        }

        $province = $municipality !== null ? self::MUNICIPALITY_PROVINCE_MAP[$municipality] : null;

        if ($province === null) {
            $knownProvinces = array_unique(array_values(self::MUNICIPALITY_PROVINCE_MAP));
            foreach ($segments as $segment) {
                $key = mb_strtolower(trim(preg_replace('/\s+/', ' ', $segment) ?? $segment));

                foreach ($knownProvinces as $knownProvince) {
                    if ($key === mb_strtolower($knownProvince)) {
                        $province = $knownProvince;
                        break 2;
                    }
                }
            }
        }

        return [
            'province' => $province ?? 'Unidentified Province',
            'municipality' => $municipality ?? 'Unidentified Municipality',
        ];
    }

    public static function officialProvinceName(string $province): string
    {
        $province = trim($province);
        foreach (array_unique(self::MUNICIPALITY_PROVINCE_MAP) as $knownProvince) {
            if (strcasecmp($knownProvince, $province) === 0) {
                return $knownProvince;
            }
        }

        return $province;
    }

    public static function provinceForMunicipality(?string $municipality): ?string
    {
        $province = self::resolveCaragaLocation(null, $municipality)['province'];

        return $province !== 'Unidentified Province' ? $province : null;
    }

    public static function formatAssignedAddress(?string $barangay, ?string $municipality, ?string $province = null): string
    {
        $province = trim((string) $province) !== ''
            ? $province
            : self::provinceForMunicipality($municipality);

        return self::formatAddress($barangay, $municipality, $province);
    }

    public static function normalizeAddressString(?string $value): ?string
    {
        if (! is_string($value)) {
            return $value;
        }

        $clean = self::cleanAddressString($value);

        return $clean === '' ? $value : $clean;
    }

    public static function formatAddress(?string $barangay, ?string $municipality, ?string $province = null): string
    {
        $parts = array_values(array_filter([
            self::cleanAddressString($barangay),
            self::cleanAddressString($municipality),
            self::cleanAddressString($province),
        ], fn (string $part) => $part !== ''));

        return self::cleanAddressString(implode(', ', $parts));
    }

    public static function formatDate(mixed $date, string $format = 'F j, Y'): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format($format);
        }

        return Carbon::parse($date)->format($format);
    }
}
