<?php

namespace Database\Seeders;

use App\Models\HealthMetric;
use Illuminate\Database\Seeder;

class HealthMetricSeeder extends Seeder
{
    public function run(): void
    {
        HealthMetric::seedDefaults();
    }
}
