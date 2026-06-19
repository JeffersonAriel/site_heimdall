<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\FeatureFlag::updateOrCreate(
            ['key' => 'ai.enabled'],
            ['enabled' => true, 'config' => ['provider' => 'stub']]
        );
        \App\Models\FeatureFlag::updateOrCreate(
            ['key' => 'crm.enabled'],
            ['enabled' => true]
        );
        \App\Models\FeatureFlag::updateOrCreate(
            ['key' => 'bi.enabled'],
            ['enabled' => true]
        );
    }
}
