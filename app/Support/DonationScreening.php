<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class DonationScreening
{
    public static function parseMbdDate(string $value): ?string
    {
        if (! preg_match('/(\d{4}-\d{2}-\d{2}|\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/', $value, $match)) {
            return null;
        }

        $token = $match[1];
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $token, $iso)) {
            return checkdate((int) $iso[2], (int) $iso[3], (int) $iso[1]) ? $token : null;
        }

        if (! preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/', $token, $parts)) {
            return null;
        }

        $year = strlen($parts[3]) === 2 ? (int) ('20'.$parts[3]) : (int) $parts[3];
        $month = (int) $parts[1];
        $day = (int) $parts[2];
        if (! checkdate($month, $day, $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    public static function venueFromMbdDate(string $value): string
    {
        $venue = trim(preg_replace('/\d{4}-\d{2}-\d{2}|\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/', '', $value) ?? '');
        $venue = preg_replace('/\s+/', ' ', $venue) ?? '';

        return $venue !== '' ? $venue : trim($value);
    }

    public static function normalizeMbdDate(string $value): ?string
    {
        $date = self::parseMbdDate($value);

        return $date === null ? null : Carbon::parse($date)->format('m/d/Y');
    }

    public static function mbdTypeFrom(string $value): string
    {
        return preg_match('/\bWI\b/i', $value) === 1 ? 'WI' : 'MBD';
    }

    public static function composeMbdDate(string $type, string $isoDate): string
    {
        $type = strtoupper(trim($type)) === 'WI' ? 'WI' : 'MBD';

        return $type.' '.Carbon::parse($isoDate)->format('m/d/Y');
    }

    /**
     * @return array{type: string, date: string}
     */
    public static function splitMbdDate(?string $value): array
    {
        $value = trim((string) $value);

        return [
            'type' => $value === '' ? 'MBD' : self::mbdTypeFrom($value),
            'date' => self::parseMbdDate($value) ?? '',
        ];
    }
}
