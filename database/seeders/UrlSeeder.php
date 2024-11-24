<?php

namespace Database\Seeders;

use App\Models\Url;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         
         User::factory(10)->create()->each(function ($user) {
            Url::factory(20)->create([
                'user_id' => $user->id,
                'expires_at' => null, 
            ]);
        });

        
        Url::factory(20)->anonymous()->create();
    }
}
