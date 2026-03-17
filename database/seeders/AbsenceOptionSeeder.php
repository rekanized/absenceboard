<?php

namespace Database\Seeders;

use App\Models\AbsenceOption;
use Illuminate\Database\Seeder;

class AbsenceOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            ['code' => 'S', 'label' => 'Vacation', 'color' => '#4ade80', 'sort_order' => 1],
            ['code' => 'FL', 'label' => 'Parental', 'color' => '#38bdf8', 'sort_order' => 2],
        ];

        foreach ($options as $option) {
            AbsenceOption::query()->updateOrCreate(
                ['code' => $option['code']],
                $option,
            );
        }
    }
}
