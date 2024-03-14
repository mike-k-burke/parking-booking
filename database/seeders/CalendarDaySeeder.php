<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CalendarDaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate  = now()->startOfYear();
        $endDate    = $startDate->clone()->addYears(3);

        $current    = $startDate->clone();

        while ($current->isBefore($endDate)) {

            /** For seasonal price calulation, determine if the date is between the end of April and the start of November */
            $onSeason = $current->isAfter($current->clone()->setMonth(4)->endOfMonth()) &&
                        $current->isBefore($current->clone()->setMonth(11)->startOfMonth());

            DB::table('calendar_days')->insert([
                'date'              => $current->format('Y-m-d'),
                'year'              => (int) $current->format('Y'),
                'month'             => (int) $current->format('n'),
                'day'               => (int) $current->format('j'),
                'day_of_week'       => (int) $current->format('N'),
                'is_weekend'        => (int) $current->isWeekend(),
                'available_spaces'  => 10,
                'price'             => 5000 + ($onSeason ? 1000 : 0) + ($current->isWeekend() ? 500 : 0),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $current = $current->addDay();
        }
    }
}
