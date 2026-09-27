<?php

declare(strict_types=1);

namespace Modules\{{Studly}}\database\seeders;

use Illuminate\Database\Seeders\Seed;
use Modules\{{Studly}}\app\Models\{{Entity}};

class {{Studly}}Seeder extends Seed
{
    public function run(): void
    {
        $this->command?->info('Seeding {{studly}} data...');

        // TODO: Tambahkan seed data di sini.
        // Contoh:
        // {{Entity}}::create(['name' => 'Sample', 'status' => 'draft']);
        //
        // Untuk data dari external source (CSV/JSON), gunakan HTTP client:
        // $response = Http::get('https://example.com/data.csv');
        // Parse CSV, then insert.

        $this->command?->info('{{studly}} data seeded.');
    }
}