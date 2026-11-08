<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use App\Models\ConsumptionRecord;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BloodConsumptionReportAddressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        URL::forceRootUrl('http://localhost');
    }

    public function test_consumption_report_drills_down_by_province_municipality_and_patient(): void
    {
        foreach ([
            [
                'location' => 'P-3, PAYAG, BACUAG, SDN',
                'patient_name' => 'Patient One',
                'blood_type' => 'O+',
                'blood_component' => 'Whole Blood',
                'number_of_bags' => 2,
                'date_submitted' => '2026-10-07',
            ],
            [
                'location' => 'BACUAG, SDN',
                'patient_name' => 'Patient Two',
                'blood_type' => 'A+',
                'blood_component' => 'Platelets',
                'number_of_bags' => 3,
                'date_submitted' => '2026-10-06',
            ],
            [
                'location' => 'Purok 4, Claver, SDN',
                'patient_name' => 'Patient Three',
                'blood_type' => 'B+',
                'blood_component' => 'Plasma',
                'number_of_bags' => 4,
                'date_submitted' => '2026-10-05',
            ],
            [
                'location' => 'Sitio Uno, Hinatuan, SDS',
                'patient_name' => 'Patient Four',
                'blood_type' => 'AB+',
                'blood_component' => 'Platelets',
                'number_of_bags' => 1,
                'date_submitted' => '2026-10-04',
            ],
        ] as $recordData) {
            $record = new ConsumptionRecord;
            $record->forceFill([
                ...$recordData,
                'age' => 35,
                'sex' => 'F',
                'month' => 10,
                'year' => 2026,
                'reporting_period' => 'October 2026',
            ])->save();
        }

        $report = app(ReportService::class)->consumption();
        $surigaoDelNorte = $report['provinces']->firstWhere('name', 'Surigao del Norte');
        $surigaoDelSur = $report['provinces']->firstWhere('name', 'Surigao del Sur');

        $this->assertCount(2, $report['provinces']);
        $this->assertSame(9, $surigaoDelNorte['total_units']);
        $this->assertSame(2, $surigaoDelNorte['municipality_count']);
        $this->assertSame(1, $surigaoDelSur['total_units']);
        $this->assertSame(['Bacuag', 'Claver'], collect($surigaoDelNorte['municipalities'])->pluck('name')->all());
        $this->assertSame(['Patient Two', 'Patient One'], collect($surigaoDelNorte['municipalities'][0]['records'])->pluck('patient_name')->all());
        $this->assertSame(5, $surigaoDelNorte['municipalities'][0]['total_units']);

        ConsumptionRecord::query()->create([
            'location' => 'Tagbilaran City, Bohol',
            'province' => 'Bohol',
            'municipality' => 'Tagbilaran City',
            'patient_name' => 'Older Patient',
            'age' => 42,
            'sex' => 'M',
            'blood_type' => 'O+',
            'blood_component' => 'Whole Blood',
            'number_of_bags' => 1,
            'month' => 3,
            'year' => 2025,
            'reporting_period' => 'March 2025',
            'date_submitted' => '2025-03-12',
        ]);

        $this->actingAs(User::factory()->create());
        $view = app(ReportController::class)->index(Request::create('/dashboard/reports', 'GET', [
            'view' => 'consumption',
            'consumption_year' => '2026',
            'consumption_month' => '10',
        ]));
        $html = $view->render();

        $this->assertStringContainsString('Surigao del Norte', $html);
        $this->assertStringContainsString('Surigao del Sur', $html);
        $this->assertStringContainsString('Bacuag', $html);
        $this->assertStringContainsString('Claver', $html);
        $this->assertStringNotContainsString('Patient One', $html);
        $this->assertStringNotContainsString('Patient Two', $html);
        $this->assertStringNotContainsString('Patient Three', $html);
        $this->assertStringNotContainsString('Patient Four', $html);
        $this->assertStringNotContainsString('Older Patient', $html);
        $this->assertStringContainsString('Total Units', $html);
        $this->assertStringContainsString('All Records', $html);
        $this->assertStringNotContainsString('id="consumptionBloodType"', $html);
        $this->assertStringNotContainsString('<h4 class="box-title">Blood Consumption</h4>', $html);
        $this->assertStringNotContainsString('id="consumptionMonth"', $html);
        $this->assertStringNotContainsString('id="consumptionYear"', $html);

        $this->getJson('/dashboard/reports/consumption-details?'.http_build_query([
            'province' => 'Surigao del Norte',
            'municipality' => 'Bacuag',
        ]))
            ->assertOk()
            ->assertJsonPath('province', 'Surigao del Norte')
            ->assertJsonPath('municipality', 'Bacuag')
            ->assertJsonPath('records.0.patient_name', 'Patient Two')
            ->assertJsonPath('records.1.patient_name', 'Patient One');
    }

    public function test_consumption_report_includes_records_with_explicit_location_fields(): void
    {
        ConsumptionRecord::query()->create([
            'location' => '',
            'patient_name' => 'Patient Outside Caraga',
            'age' => 35,
            'sex' => 'F',
            'blood_component' => 'Whole Blood',
            'province' => 'Bohol',
            'municipality' => 'Tagbilaran City',
            'barangay' => 'Poblacion',
            'number_of_bags' => 2,
            'month' => 10,
            'year' => 2026,
            'reporting_period' => 'October 2026',
            'date_submitted' => '2026-10-07',
        ]);

        $report = app(ReportService::class)->consumption('Bohol', '', '', '2026');
        $province = $report['provinces']->firstWhere('name', 'Bohol');

        $this->assertIsArray($province);
        $this->assertSame(2, $province['total_units']);
        $this->assertSame('Tagbilaran City', $province['municipalities'][0]['name']);
        $this->assertSame('Patient Outside Caraga', $province['municipalities'][0]['records'][0]['patient_name']);

        ConsumptionRecord::query()->create([
            'location' => 'Surigao City, Surigao del Norte',
            'patient_name' => 'Casing Patient',
            'age' => 30,
            'sex' => 'F',
            'blood_component' => 'Packed RBC',
            'province' => 'Surigao Del Norte',
            'municipality' => 'Surigao City',
            'number_of_bags' => 2,
            'month' => 10,
            'year' => 2026,
            'reporting_period' => 'October 2026',
            'date_submitted' => '2026-10-08',
        ]);

        $merged = app(ReportService::class)->consumption('', '', '', '2026');
        $this->assertNull($merged['provinces']->firstWhere('name', 'Surigao Del Norte'));
        $official = $merged['provinces']->firstWhere('name', 'Surigao del Norte');
        $this->assertIsArray($official);
        $this->assertSame(2, $official['total_units']);

        $this->actingAs(User::factory()->create());
        $view = app(ReportController::class)->index(Request::create('/dashboard/reports', 'GET', [
            'view' => 'consumption',
            'consumption_year' => '2026',
            'location' => 'Bohol',
        ]));
        $html = $view->render();

        $this->assertStringContainsString('Bohol', $html);
        $this->assertStringContainsString('Tagbilaran City', $html);
        $this->assertStringNotContainsString('Patient Outside Caraga', $html);

        $this->getJson('/dashboard/reports/consumption-details?'.http_build_query([
            'province' => 'Bohol',
            'municipality' => 'Tagbilaran City',
            'location' => 'Bohol',
        ]))
            ->assertOk()
            ->assertJsonPath('records.0.patient_name', 'Patient Outside Caraga');
    }
}
