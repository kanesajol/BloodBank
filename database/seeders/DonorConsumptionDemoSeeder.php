<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DonorConsumptionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->warn('Demo donor and patient seeding is disabled. Add records through the dashboard or import seeders.');
    }
}
