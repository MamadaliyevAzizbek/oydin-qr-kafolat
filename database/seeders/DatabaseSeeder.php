<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Production default: demo yo‘q. Localda: php artisan db:seed --class=DemoDataSeeder
        $this->call(ProductionSeeder::class);

        if ($this->command?->option('class') === null && app()->environment('local')) {
            // localda ixtiyoriy: faqat aniq so‘ralsa
            // $this->call(DemoDataSeeder::class);
        }
    }
}
