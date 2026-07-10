<?php

namespace Database\Seeders;

use App\Models\Slot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Slot::create(['capacity' => 5, 'remaining' => 2]);
        Slot::create(['capacity' => 7, 'remaining' => 4]);
    }
}
