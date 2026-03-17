<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;

class PersonnelSeeder extends Seeder
{
    public function run(): void
    {
        $depts = [
            'Operations' => ['Kristianstad', 'Umeå', 'Helsingborg'],
            'Engineering' => ['Stockholm', 'Göteborg', 'Lund'],
            'Human Resources' => ['Stockholm', 'Kristianstad'],
            'Sales' => ['Malmö', 'Göteborg', 'Örebro'],
        ];

        $firstNames = ['Erik', 'Lars', 'Karl', 'Anders', 'Johan', 'Per', 'Nils', 'Jan', 'Lennart', 'Hans', 'Maria', 'Anna', 'Margareta', 'Elisabeth', 'Eva', 'Birgitta', 'Kristina', 'Karin', 'Elisabet', 'Marie'];
        $lastNames = ['Andersson', 'Johansson', 'Karlsson', 'Nilsson', 'Eriksson', 'Larsson', 'Olsson', 'Persson', 'Svensson', 'Gustafsson', 'Pettersson', 'Jonsson', 'Jansson', 'Hansson', 'Bengtsson', 'Jönsson', 'Lindberg', 'Jakobsson', 'Magnusson', 'Olofsson'];

        foreach ($depts as $deptName => $locations) {
            $dept = Department::create(['name' => $deptName]);
            
            for ($i = 0; $i < 5; $i++) {
                $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
                $user = $dept->users()->create([
                    'name' => $name,
                    'location' => $locations[array_rand($locations)],
                ]);


                // Random absences
                if ($i % 2 === 0) {
                    $start = Carbon::create(2026, 6, 15 + $i*3);
                    for ($j = 0; $j < 5 + $i; $j++) {
                        $user->absences()->create([
                            'date' => $start->copy()->addDays($j)->format('Y-m-d'),
                            'type' => 'S',
                        ]);
                    }
                }
            }
        }

        // Add the specific users from the reference for continuity
        $ops = Department::where('name', 'Operations')->first();
        $olle = $ops->users()->create([
            'name' => 'Olle Klanger Nyrén',
            'location' => 'Stockholm',
        ]);

        $dates = ['2026-07-06', '2026-07-07', '2026-07-08', '2026-07-09', '2026-07-10'];
        foreach ($dates as $date) {
            $olle->absences()->create([
                'date' => $date,
                'type' => 'S',
            ]);
        }
    }
}
