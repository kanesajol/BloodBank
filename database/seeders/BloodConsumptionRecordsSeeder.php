<?php

namespace Database\Seeders;

use App\Http\Controllers\BloodConsumptionController;
use App\Support\BloodBank;
use DateTimeImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

class BloodConsumptionRecordsSeeder extends Seeder
{
    private const OUTSIDE = [
        'AGUSAN DEL NORTE',
        'AGUSAN DEL SUR',
        'SURIGAO DEL SUR',
        'OUTSIDE',
    ];

    public function run(): void
    {
        $path = 'C:/Users/sajol/Downloads/HWAITING! _D(AutoRecovered).xlsx';
        if (! is_file($path)) {
            throw new RuntimeException('Blood consumption workbook was not found.');
        }

        DB::disableQueryLog();
        $places = BloodConsumptionController::places();
        $aliases = $this->municipalityAliases($places);
        $barangayIndex = $this->barangayIndex($places);
        $existing = $this->existingSignatures();

        $inserted = 0;
        $skippedExisting = 0;
        $skippedName = 0;
        $skippedDate = 0;
        $pending = [];
        $lastDate = null;

        foreach ($this->rows($path) as $cells) {
            $name = $this->patientName($cells['B'] ?? '');
            if ($name === null) {
                $skippedName++;

                continue;
            }

            $rawDate = trim($cells['A'] ?? '');
            $date = $this->excelDate($rawDate);
            if ($date === null && $lastDate !== null && ($rawDate === '' || (is_numeric($rawDate) && (float) $rawDate > 0 && (float) $rawDate < 1))) {
                $date = $lastDate;
            }
            if ($date === null) {
                $skippedDate++;

                continue;
            }
            $lastDate = $date;

            $components = $this->components($cells);
            $units = $this->units($cells['J'] ?? '', $components !== []);
            $age = $this->age($cells['C'] ?? '');
            $sex = $this->sex($cells);
            $original = trim($cells['K'] ?? '');
            [$municipality, $barangay] = $this->parseAddress($original, $aliases, $barangayIndex);
            $location = $original !== '' ? $original : ($barangay ? $barangay.', '.$municipality : (string) $municipality);
            $submitted = DateTimeImmutable::createFromFormat('Y-m-d', $date);
            $month = (int) $submitted->format('n');
            $year = (int) $submitted->format('Y');
            $now = now()->toDateTimeString();
            $targets = $components !== [] ? $components : [null];

            foreach ($targets as $component) {
                $signature = $this->signature($name, $date, $component, $units, mb_substr($location, 0, 255));
                if (isset($existing[$signature])) {
                    $skippedExisting++;

                    continue;
                }
                $existing[$signature] = true;
                $pending[] = [
                    'patient_name' => mb_substr($name, 0, 255),
                    'age' => $age,
                    'sex' => $sex,
                    'blood_component' => $component,
                    'municipality' => $municipality,
                    'barangay' => $barangay,
                    'location' => mb_substr($location, 0, 255),
                    'blood_type' => null,
                    'number_of_bags' => $units,
                    'month' => $month,
                    'year' => $year,
                    'reporting_period' => BloodBank::MONTHS[$month - 1].' '.$year,
                    'date_submitted' => $date,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (count($pending) >= 300) {
                    DB::table('consumption_records')->insert($pending);
                    $inserted += count($pending);
                    $pending = [];
                    $this->command?->info('Inserted '.$inserted.' consumption rows...');
                }
            }
        }

        if ($pending !== []) {
            DB::table('consumption_records')->insert($pending);
            $inserted += count($pending);
        }

        $this->command?->info("Consumption import finished. Inserted {$inserted}, already stored {$skippedExisting}, blank names {$skippedName}, bad dates {$skippedDate}.");
    }

    /**
     * @return \Generator<int, array<string, string>>
     */
    private function rows(string $path): \Generator
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Blood consumption workbook could not be opened.');
        }

        $shared = [];
        $sharedXml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
        if ($sharedXml !== false) {
            foreach ($sharedXml->si as $si) {
                $text = isset($si->t) ? (string) $si->t : '';
                if ($text === '' && isset($si->r)) {
                    foreach ($si->r as $run) {
                        $text .= (string) $run->t;
                    }
                }
                $shared[] = $text;
            }
        }

        $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet2.xml'));
        $zip->close();
        if ($sheet === false) {
            throw new RuntimeException('BLOOD CONSUMPTION DATA sheet was not found.');
        }

        foreach ($sheet->sheetData->row as $row) {
            if ((int) $row['r'] === 1) {
                continue;
            }
            $cells = [];
            foreach ($row->c as $cell) {
                $column = preg_replace('/\d+/', '', (string) $cell['r']) ?? '';
                $type = (string) $cell['t'];
                $value = isset($cell->v) ? (string) $cell->v : '';
                if ($type === 's') {
                    $value = $shared[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = isset($cell->is->t) ? (string) $cell->is->t : '';
                }
                $cells[$column] = trim($value);
            }
            yield $cells;
        }
    }

    private function patientName(string $raw): ?string
    {
        $name = strtoupper(trim($raw));
        $name = preg_replace('/^[\s\'`]+/u', '', $name) ?? $name;
        $name = BloodConsumptionController::displayName($name);
        if ($name === '' || preg_match('/\p{L}/u', $name) !== 1) {
            return null;
        }

        return $name;
    }

    private function excelDate(string $raw): ?string
    {
        $raw = trim($raw);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $raw, $match) === 1) {
            $year = (int) $match[1];
            if ($year < 2020 || $year > 2030) {
                return null;
            }

            return $match[1].'-'.$match[2].'-'.$match[3];
        }
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{3,4})$/', $raw, $match) === 1
            || preg_match('/^(\d{1,2})\/(\d{1,2})\s+(\d{3,4})$/', $raw, $match) === 1
            || preg_match('/^(\d{1,2})\/(\d{2})(\d{4})$/', $raw, $match) === 1) {
            $month = (int) $match[1];
            $day = (int) $match[2];
            $year = $this->repairYear($match[3]);
            if ($year !== null && checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }
        if (! is_numeric($raw)) {
            return null;
        }

        $serial = (int) floor((float) $raw);
        if ($serial < 43831 || $serial > 47850) {
            return null;
        }

        $date = (new DateTimeImmutable('1899-12-30'))->modify('+'.$serial.' days');
        $year = (int) $date->format('Y');
        if ($year < 2020 || $year > 2030) {
            return null;
        }

        return $date->format('Y-m-d');
    }

    private function repairYear(string $year): ?int
    {
        return match ($year) {
            '2024' => 2024,
            '2025' => 2025,
            '2026' => 2026,
            '2027' => 2027,
            '0206', '0296', '206', '296' => 2026,
            default => null,
        };
    }

    /**
     * @param  array<string, string>  $cells
     * @return list<string>
     */
    private function components(array $cells): array
    {
        $found = [];
        foreach (['F', 'G', 'H', 'I'] as $column) {
            $raw = $cells[$column] ?? '';
            if ($raw === '') {
                continue;
            }
            foreach (preg_split('/[\/;]+/u', $raw) ?: [] as $part) {
                $mapped = $this->mapComponent($part);
                if ($mapped !== null) {
                    $found[$mapped] = $mapped;
                }
            }
        }

        return array_values($found);
    }

    private function mapComponent(string $raw): ?string
    {
        $token = strtoupper(trim($raw));
        $token = str_replace(['`', '’', "'", '"'], '', $token);
        $token = trim(preg_replace('/\s+/', ' ', $token) ?? $token);
        if ($token === '' || $token === '1' || $token === 'O POS' || $token === 'OPOS') {
            return null;
        }
        if (str_contains($token, 'WASH')) {
            return 'Washed RBC';
        }
        if (str_contains($token, 'CRYO')) {
            return 'Cryoprecipitate';
        }
        if (str_contains($token, 'APHER')) {
            return 'Platelet Apheresis';
        }
        if (str_contains($token, 'FFP')) {
            return 'Fresh Frozen Plasma';
        }
        if (preg_match('/\b(FWB|FBW|WFB|WB)\b/', $token) === 1 || str_contains($token, 'WB-ALI')) {
            return 'Fresh Whole Blood';
        }
        if (
            str_contains($token, 'PRB')
            || str_contains($token, 'PBR')
            || str_contains($token, 'PRC')
            || str_contains($token, 'PRN')
            || str_contains($token, 'PACKED')
            || str_contains($token, 'ALIQ')
            || str_contains($token, 'RBC')
        ) {
            return 'Packed RBC';
        }
        if (in_array($token, ['PC', 'P', 'PH', 'PHIC', 'WP'], true) || preg_match('/^PC\b/', $token) === 1) {
            return 'Platelet Concentrate';
        }

        return null;
    }

    private function units(string $raw, bool $hasComponent): int
    {
        if (preg_match('/\d+/', $raw, $match) === 1) {
            $units = (int) $match[0];
            if ($units >= 1 && $units <= 99) {
                return $units;
            }
        }

        return $hasComponent ? 1 : 0;
    }

    private function age(string $raw): ?int
    {
        if (! is_numeric(trim($raw))) {
            return null;
        }
        $age = (int) $raw;

        return $age >= 0 && $age <= 120 ? $age : null;
    }

    /**
     * @param  array<string, string>  $cells
     */
    private function sex(array $cells): ?string
    {
        foreach (['D', 'E'] as $column) {
            $value = strtoupper(trim($cells[$column] ?? ''));
            if ($value === 'M' || $value === 'F') {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, list<string>>  $places
     * @return array<string, string>
     */
    private function municipalityAliases(array $places): array
    {
        $aliases = [
            'SC' => 'Surigao City',
            'S C' => 'Surigao City',
            'SURIGAO CITY' => 'Surigao City',
            'GL' => 'General Luna',
            'GEN LUNA' => 'General Luna',
            'GENERAL LUNA' => 'General Luna',
            'MAINT' => 'Mainit',
            'MAINIT' => 'Mainit',
            'STA MONICA' => 'Santa Monica',
            'SANTA MONICA' => 'Santa Monica',
            'TAGANAAN' => 'Tagana-an',
            'TAGANA AN' => 'Tagana-an',
            'TAGANA-AN' => 'Tagana-an',
            'DEL CARMEN' => 'Del Carmen',
        ];
        foreach (array_keys($places) as $name) {
            if (in_array($name, self::OUTSIDE, true)) {
                continue;
            }
            $aliases[strtoupper($name)] = $name;
        }

        return $aliases;
    }

    /**
     * @param  array<string, list<string>>  $places
     * @return array<string, list<array{name: string, upper: string, base: string}>>
     */
    private function barangayIndex(array $places): array
    {
        $index = [];
        foreach ($places as $municipality => $barangays) {
            $index[$municipality] = [];
            foreach ($barangays as $barangay) {
                $upper = strtoupper($barangay);
                $base = trim(preg_replace('/\s+/', ' ', preg_replace('/\([^)]*\)/', ' ', $upper)) ?? $upper);
                $index[$municipality][] = [
                    'name' => $barangay,
                    'upper' => $upper,
                    'base' => $base,
                ];
            }
        }

        return $index;
    }

    /**
     * @param  array<string, string>  $aliases
     * @param  array<string, list<array{name: string, upper: string, base: string}>>  $barangayIndex
     * @return array{0: ?string, 1: ?string}
     */
    private function parseAddress(string $original, array $aliases, array $barangayIndex): array
    {
        $norm = $this->normalizeAddress($original);
        if ($norm === '') {
            return [null, null];
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $norm)), fn (string $part) => $part !== ''));
        $consumed = [];
        $municipality = null;
        $outside = null;

        foreach ($parts as $index => $part) {
            $outsideHit = $this->outsidePlace($part);
            if ($outsideHit !== null) {
                $outside = $outsideHit;
                $consumed[$index] = true;

                continue;
            }
            $stripped = $this->stripProvince($part);
            if ($stripped === '') {
                $consumed[$index] = true;

                continue;
            }
            if (isset($aliases[$stripped])) {
                $municipality = $aliases[$stripped];
                $consumed[$index] = true;
            }
        }

        if ($municipality === null) {
            for ($index = count($parts) - 1; $index >= 0; $index--) {
                if (isset($consumed[$index])) {
                    continue;
                }
                $found = $this->municipalityInside($parts[$index], $aliases);
                if ($found === null) {
                    continue;
                }
                $municipality = $found['name'];
                $parts[$index] = trim(preg_replace('/\b'.preg_quote($found['needle'], '/').'\b/', ' ', $parts[$index]) ?? $parts[$index]);
                break;
            }
        }

        if ($municipality === null) {
            $municipality = $outside ?? $this->outsideInside($norm);
        }

        $barangay = null;
        if ($municipality !== null && ($barangayIndex[$municipality] ?? []) !== []) {
            $bestScore = 0;
            foreach ($parts as $index => $part) {
                if (isset($consumed[$index])) {
                    continue;
                }
                $cleaned = $this->stripNoise($part);
                if ($cleaned === '') {
                    continue;
                }
                foreach ($barangayIndex[$municipality] as $item) {
                    $score = $this->barangayScore($cleaned, $item);
                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $barangay = $item['name'];
                    }
                }
            }
        }

        return [$municipality, $barangay];
    }

    private function normalizeAddress(string $original): string
    {
        $text = strtoupper(trim($original));
        $text = str_replace(['S.C.', 'S. C.', 'S.C', 'S. C'], ' SC ', $text);
        $text = str_replace('.', ' ', $text);
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);

        return $text;
    }

    private function stripProvince(string $part): string
    {
        $part = preg_replace('/\b(SURIGAO DEL NORTE|SD N|SDN)\b/', ' ', $part) ?? $part;

        return trim(preg_replace('/\s+/', ' ', $part) ?? $part);
    }

    private function outsidePlace(string $token): ?string
    {
        return match (trim($token)) {
            'ADS', 'AGUSAN DEL SUR' => 'AGUSAN DEL SUR',
            'ADN', 'AND', 'AGUSAN DEL NORTE' => 'AGUSAN DEL NORTE',
            'SDS', 'SURIGAO DEL SUR' => 'SURIGAO DEL SUR',
            'PDI', 'DINAGAT', 'DINAGAT ISLANDS', 'DINAGAT ISLAND' => 'OUTSIDE',
            default => null,
        };
    }

    private function outsideInside(string $text): ?string
    {
        $needles = [
            'AGUSAN DEL NORTE' => 'AGUSAN DEL NORTE',
            'AGUSAN DEL SUR' => 'AGUSAN DEL SUR',
            'SURIGAO DEL SUR' => 'SURIGAO DEL SUR',
            'DINAGAT ISLANDS' => 'OUTSIDE',
            'DINAGAT ISLAND' => 'OUTSIDE',
            'DINAGAT' => 'OUTSIDE',
            'ADN' => 'AGUSAN DEL NORTE',
            'AND' => 'AGUSAN DEL NORTE',
            'ADS' => 'AGUSAN DEL SUR',
            'SDS' => 'SURIGAO DEL SUR',
            'PDI' => 'OUTSIDE',
        ];
        $best = null;
        $bestPos = -1;
        foreach ($needles as $needle => $name) {
            if (preg_match('/\b'.preg_quote($needle, '/').'\b/', $text, $match, PREG_OFFSET_CAPTURE) !== 1) {
                continue;
            }
            $position = $match[0][1];
            if ($position >= $bestPos) {
                $bestPos = $position;
                $best = $name;
            }
        }

        return $best;
    }

    /**
     * @param  array<string, string>  $aliases
     * @return array{name: string, needle: string}|null
     */
    private function municipalityInside(string $part, array $aliases): ?array
    {
        $best = null;
        $bestPos = -1;
        $bestLength = 0;
        foreach ($aliases as $needle => $name) {
            if (preg_match('/\b'.preg_quote($needle, '/').'\b/', $part, $match, PREG_OFFSET_CAPTURE) !== 1) {
                continue;
            }
            $position = $match[0][1];
            $length = strlen($needle);
            if ($position > $bestPos || ($position === $bestPos && $length > $bestLength)) {
                $bestPos = $position;
                $bestLength = $length;
                $best = ['name' => $name, 'needle' => $needle];
            }
        }

        return $best;
    }

    private function stripNoise(string $part): string
    {
        $part = preg_replace('/\bP\s*-?\s*\d+[A-Z]?\b/', ' ', $part) ?? $part;
        $part = preg_replace('/\b(BRGYS|BRGY|BARANGAY|BARRIO|PUROK|SITIO|STREET|ST|BLK|BLOCK|ZONE|PHASE)\b/', ' ', $part) ?? $part;

        return trim(preg_replace('/\s+/', ' ', $part) ?? $part);
    }

    /**
     * @param  array{name: string, upper: string, base: string}  $item
     */
    private function barangayScore(string $cleaned, array $item): int
    {
        $base = $item['base'];
        if ($base === '' || strlen(str_replace(['-', ' '], '', $base)) < 3) {
            return 0;
        }
        $cleanedBase = trim(preg_replace('/\s+/', ' ', preg_replace('/\([^)]*\)/', ' ', $cleaned)) ?? $cleaned);
        $compact = static fn (string $value): string => str_replace(['-', ' '], '', $value);
        if ($cleaned === $item['upper']) {
            return 1000 + strlen($base);
        }
        if ($cleanedBase !== '' && ($cleanedBase === $base || $compact($cleanedBase) === $compact($base))) {
            return 800 + strlen($base);
        }
        if ($cleaned === $base || $compact($cleaned) === $compact($base)) {
            return 700 + strlen($base);
        }

        return 0;
    }

    /**
     * @return array<string, true>
     */
    private function existingSignatures(): array
    {
        $existing = [];
        DB::table('consumption_records')
            ->whereNotNull('patient_name')
            ->where('patient_name', '!=', '')
            ->orderBy('id')
            ->select(['patient_name', 'date_submitted', 'blood_component', 'number_of_bags', 'location'])
            ->chunk(1000, function ($rows) use (&$existing): void {
                foreach ($rows as $row) {
                    $existing[$this->signature(
                        (string) $row->patient_name,
                        substr((string) $row->date_submitted, 0, 10),
                        $row->blood_component !== null ? (string) $row->blood_component : null,
                        (int) $row->number_of_bags,
                        (string) $row->location,
                    )] = true;
                }
            });

        return $existing;
    }

    private function signature(string $name, string $date, ?string $component, int $bags, string $location): string
    {
        return $name.'|'.$date.'|'.($component ?? '').'|'.$bags.'|'.$location;
    }
}
