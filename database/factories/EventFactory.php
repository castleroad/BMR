<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $starts_at = time()+rand(1, 84000);
        $ends_at = time()+rand(184000, 250000);
        
        $timeTo = null;
        $timeFrom = null;

        $allDay = rand(0,1);
        if (!$allDay){
            $timeTo = date("H:i:s", $ends_at-rand(1000, 10000));
            $timeFrom = date("H:i:s", $starts_at+rand(1000, 10000));
        }

        return [
            'title' => fake()->sentence(3),
            'all_day' => $allDay,
            'starts_at' => date("Y-m-d", $starts_at),
            'ends_at' => date("Y-m-d", $ends_at),
            'time_to' => $timeTo,
            'time_from' => $timeFrom,
        ];
    }
}
