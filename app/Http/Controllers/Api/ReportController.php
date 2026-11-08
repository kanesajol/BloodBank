<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Support\BloodBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(Request $request): JsonResponse
    {
        $period = (string) $request->query('period', 'monthly');
        if (! in_array($period, ['monthly', 'quarterly', 'yearly'], true)) {
            $period = 'monthly';
        }

        $view = (string) $request->query('view', 'periodic');
        if (! in_array($view, ['periodic', 'demographics', 'consumption'], true)) {
            $view = 'periodic';
        }

        $month = $this->normalizeMonth($request->query('month'));
        $year = (string) $this->normalizeYear(
            $request->query('year', (string) now()->year),
            (int) now()->year
        );
        $consumptionMonth = $this->normalizeMonth($request->query('consumption_month'));
        $consumptionYear = (string) $this->normalizeYear(
            $request->query('consumption_year', (string) now()->year),
            (int) now()->year
        );
        $periodicYears = BloodBank::YEARS;
        $periodicYear = (int) $year;
        if (! in_array($periodicYear, $periodicYears, true)) {
            $periodicYear = in_array((int) now()->year, $periodicYears, true)
                ? (int) now()->year
                : max($periodicYears);
        }
        $reportLocation = $this->displayLocation(trim((string) $request->query('location', '')));
        $consumptionBlood = (string) $request->query('blood_type', '');
        $ageYear = $year;
        $printAll = $request->boolean('print_all');

        if ($printAll) {
            $consumptionBlood = '';
            $consumptionMonth = 'all';
            $consumptionYear = (string) $periodicYear;
        }

        $ageReport = $this->presentAge($this->reports->ageReport($ageYear, $reportLocation, $month));
        $gender = $this->presentGender($this->reports->gender((int) $year, $month, $reportLocation));
        $consumptionDemographics = $this->reports->consumptionDemographics(
            $consumptionMonth,
            $consumptionYear,
            $reportLocation,
        );
        $consumption = $this->reports->consumption(
            $reportLocation,
            $consumptionBlood,
            $consumptionMonth === 'all' ? '' : $consumptionMonth,
            $consumptionYear,
        );

        $periodic = ['period' => $period, 'rows' => []];
        $printPeriods = null;
        if ($view === 'periodic' || $printAll) {
            $periodic = $this->periodicReport($period, $periodicYear, $reportLocation);
        }
        if ($printAll) {
            $printPeriods = $this->printPeriodSummaries($periodicYear, $reportLocation);
        }

        return response()->json([
            'view' => $view,
            'printAll' => $printAll,
            'period' => $period,
            'months' => BloodBank::MONTHS,
            'years' => BloodBank::YEARS,
            'bloodTypes' => BloodBank::BLOOD_TYPES,
            'locations' => $this->reports->locations(),
            'month' => $month,
            'year' => $year,
            'periodicYear' => $periodicYear,
            'periodicYears' => $periodicYears,
            'reportLocation' => $reportLocation,
            'periodic' => $periodic,
            'printPeriods' => $printPeriods,
            'ageReport' => $ageReport,
            'gender' => $gender,
            'consumptionDemographics' => $consumptionDemographics,
            'consumption' => $consumption,
            'consumptionBlood' => $consumptionBlood,
            'consumptionMonth' => $consumptionMonth,
            'consumptionYear' => $consumptionYear,
        ]);
    }

    private function normalizeMonth(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '' || strcasecmp($value, 'all') === 0) {
            return 'all';
        }

        $month = (int) $value;
        if ($month < 1 || $month > 12) {
            return 'all';
        }

        return (string) $month;
    }

    private function normalizeYear(mixed $value, int|string $whenAll): int|string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '' || strcasecmp($value, 'all') === 0 || ! ctype_digit($value)) {
            return $whenAll;
        }

        return is_int($whenAll) ? (int) $value : $value;
    }

    /**
     * @return array{monthly: array{period: string, rows: list<array<string, mixed>>}, quarterly: array{period: string, rows: list<array<string, mixed>>}, yearly: array{period: string, rows: list<array<string, mixed>>}}
     */
    private function printPeriodSummaries(int $year, string $location): array
    {
        $withoutRecords = function (array $report): array {
            $report['rows'] = array_map(static function (array $row): array {
                unset($row['records']);

                return $row;
            }, $report['rows']);

            return $report;
        };

        return [
            'monthly' => $withoutRecords($this->periodicReport('monthly', $year, $location)),
            'quarterly' => $withoutRecords($this->periodicReport('quarterly', $year, $location)),
            'yearly' => $withoutRecords([
                'period' => 'yearly',
                'rows' => [
                    $this->periodRow(
                        'year-'.$year,
                        (string) $year,
                        (string) $year,
                        sprintf('%d-01-01', $year),
                        sprintf('%d-12-31', $year),
                        $location,
                    ),
                ],
            ]),
        ];
    }

    /**
     * @return array{period: string, rows: list<array<string, mixed>>}
     */
    private function periodicReport(string $period, int $year, string $location): array
    {
        if ($period === 'yearly') {
            $rows = [];
            foreach (BloodBank::YEARS as $historicalYear) {
                $rows[] = $this->periodRow(
                    'year-'.$historicalYear,
                    (string) $historicalYear,
                    (string) $historicalYear,
                    sprintf('%d-01-01', $historicalYear),
                    sprintf('%d-12-31', $historicalYear),
                    $location,
                );
            }

            return ['period' => 'yearly', 'rows' => $rows];
        }

        if ($period === 'quarterly') {
            $ranges = [
                ['q1-'.$year, 'Q1', 'January to March', $year.'-01-01', $year.'-03-31'],
                ['q2-'.$year, 'Q2', 'April to June', $year.'-04-01', $year.'-06-30'],
                ['q3-'.$year, 'Q3', 'July to September', $year.'-07-01', $year.'-09-30'],
                ['q4-'.$year, 'Q4', 'October to December', $year.'-10-01', $year.'-12-31'],
            ];
            $rows = [];
            foreach ($ranges as [$id, $label, $detail, $start, $end]) {
                $rows[] = $this->periodRow($id, $label, $detail, $start, $end, $location);
            }

            return ['period' => 'quarterly', 'rows' => $rows];
        }

        $rows = [];
        foreach (BloodBank::MONTHS as $index => $name) {
            $month = $index + 1;
            $start = Carbon::create($year, $month, 1);
            $rows[] = $this->periodRow(
                'month-'.$year.'-'.$month,
                $name,
                $name.' '.$year,
                $start->toDateString(),
                $start->copy()->endOfMonth()->toDateString(),
                $location,
            );
        }

        return ['period' => 'monthly', 'rows' => $rows];
    }

    /**
     * @return array<string, mixed>
     */
    private function periodRow(string $id, string $label, string $detail, string $start, string $end, string $location): array
    {
        $records = $this->periodDonors($start, $end, $location);

        return [
            'id' => $id,
            'label' => $label,
            'detail' => $detail,
            'donors' => count($records),
            'bags' => (int) array_sum(array_column($records, 'bags')),
            'records' => $records,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function periodDonors(string $start, string $end, string $location): array
    {
        $query = DB::table('donations')
            ->join('donors', 'donors.id', '=', 'donations.donor_id')
            ->whereDate('donations.donated_on', '>=', $start)
            ->whereDate('donations.donated_on', '<=', $end);

        $location = trim($location);
        if ($location !== '') {
            $like = '%'.addcslashes(mb_strtolower($location), '%_\\').'%';
            $query->where(function ($inner) use ($like) {
                $inner->whereRaw('LOWER(donors.barangay) like ?', [$like])
                    ->orWhereRaw('LOWER(donors.municipality) like ?', [$like])
                    ->orWhereRaw('LOWER(donors.province) like ?', [$like]);
            });
        }

        return $query
            ->groupBy(
                'donors.id',
                'donors.full_name',
                'donors.blood_type',
                'donors.age',
                'donors.contact',
                'donors.barangay',
                'donors.municipality',
                'donors.province',
            )
            ->select([
                'donors.id',
                'donors.full_name',
                'donors.blood_type',
                'donors.age',
                'donors.contact',
                'donors.barangay',
                'donors.municipality',
                'donors.province',
            ])
            ->selectRaw('SUM(CASE WHEN donations.bags IS NULL OR donations.bags = 0 THEN 1 ELSE donations.bags END) as total_period_bags')
            ->selectRaw('MAX(donations.donated_on) as latest_collection_date')
            ->orderBy('donors.full_name')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->full_name,
                'blood_type' => $row->blood_type,
                'age' => (int) $row->age,
                'contact' => $row->contact,
                'address' => BloodBank::formatAssignedAddress($row->barangay, $row->municipality, $row->province),
                'date' => $row->latest_collection_date,
                'bags' => (int) $row->total_period_bags,
            ])
            ->all();
    }

    private function displayLocation(string $selected): string
    {
        if ($selected === '' || strcasecmp($selected, 'all') === 0) {
            return '';
        }

        if (str_starts_with(mb_strtolower($selected), 'all locations')) {
            return '';
        }

        if (str_starts_with(mb_strtolower($selected), 'all ')) {
            return trim(substr($selected, 4));
        }

        return $selected;
    }

    /**
     * @param  array<string, mixed>  $ageReport
     * @return array<string, mixed>
     */
    private function presentAge(array $ageReport): array
    {
        $groups = [];

        foreach ($ageReport['groups'] as $index => $group) {
            $donors = array_values($group['donors']);
            if ($donors === []) {
                continue;
            }

            $groups[] = [
                'group' => $group['group'],
                'count' => count($donors),
                'bags' => (int) $group['totalDonations'],
                'donors' => $donors,
                'modalId' => 'age-donors-'.$index,
            ];
        }

        $total = (int) collect($groups)->sum('count');
        foreach ($groups as $index => $group) {
            $groups[$index]['percentage'] = $total > 0 ? (int) round(($group['count'] / $total) * 100) : 0;
        }

        return [
            'totalDonors' => $total,
            'groups' => $groups,
        ];
    }

    /**
     * @param  array<string, mixed>  $gender
     * @return array<string, mixed>
     */
    private function presentGender(array $gender): array
    {
        $donors = collect($gender['donors']);
        $total = $donors->count();
        $rows = [];

        foreach (['Female', 'Male'] as $genderName) {
            $group = $donors
                ->filter(fn (array $donor) => strcasecmp($donor['gender'], $genderName) === 0)
                ->values();
            $typeBags = [];
            foreach ($gender['typeBags'] as $name => $bagsByType) {
                if (strcasecmp((string) $name, $genderName) === 0) {
                    $typeBags = $bagsByType;
                    break;
                }
            }
            arsort($typeBags);
            $count = $group->count();
            $rows[] = [
                'gender' => $genderName,
                'count' => $count,
                'percentage' => $total > 0 ? (int) round(($count / $total) * 100) : 0,
                'bags' => (int) $group->sum('bags'),
                'topBloodType' => $typeBags === [] ? '—' : (string) array_key_first($typeBags),
                'donors' => $group,
            ];
        }

        return [
            'total' => $total,
            'rows' => $rows,
            'donorsList' => $donors,
        ];
    }
}
