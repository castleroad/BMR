<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $users = \App\Models\User::factory(50)->create();
        $events = \App\Models\Event::factory(250)->create();

        foreach ($events as $event) {
            $owner = $users->random();
            
            $event->users()->attach(
                $owner,
                [
                    'permission' => 0,
                    'status' => 0,
                ],    
            );
            
            $event->users()->attach(
                $users->random(random_int(3,7))->where('id', '<>', $owner->id),
                [
                    'permission' => random_int(1,2),
                    'status' => random_int(1,3),
                ],    
            );
        }
    }
}
