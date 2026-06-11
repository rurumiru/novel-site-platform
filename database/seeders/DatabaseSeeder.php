<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(GenreSeeder::class);
        if (\Illuminate\Support\Facades\Schema::hasTable('commenter_badges')) {
            $this->call(CommenterBadgeBackgroundSeeder::class);
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('reviews')) {
            $this->call(ReviewSeeder::class);
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('plus_plans')) {
            $this->call(PlusPlanSeeder::class);
        }
    }
}
