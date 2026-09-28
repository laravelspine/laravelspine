<?php

declare(strict_types=1);

namespace Modules\Sample\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Sample\app\Models\SampleItem;

class SampleItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Sample Item 1',
                'description' => 'Deskripsi sample item pertama',
                'quantity' => 10,
                'price' => 150000.00,
                'status' => 'draft',
                'weight' => 1.5,
                'dimensions' => '10x20x5 cm',
                'category' => 'Alat Tulis',
                'is_active' => true,
            ],
            [
                'name' => 'Sample Item 2',
                'description' => 'Deskripsi sample item kedua',
                'quantity' => 5,
                'price' => 250000.50,
                'status' => 'in_progress',
                'weight' => 0.8,
                'dimensions' => '5x10x3 cm',
                'category' => 'Elektronik',
                'is_active' => true,
            ],
            [
                'name' => 'Sample Item 3',
                'description' => 'Deskripsi sample item ketiga',
                'quantity' => 20,
                'price' => 75000.00,
                'status' => 'done',
                'weight' => 2.3,
                'dimensions' => '15x25x10 cm',
                'category' => 'Peralatan',
                'is_active' => false,
            ],
            [
                'name' => 'Produk Digital',
                'description' => 'Item digital tanpa kuantitas fisik',
                'quantity' => 0,
                'price' => 500000.00,
                'status' => 'draft',
                'weight' => 0.1,
                'dimensions' => 'N/A',
                'category' => 'Digital',
                'is_active' => true,
            ],
            [
                'name' => 'Jasa Konsultasi',
                'description' => 'Layanan konsultasi per jam',
                'quantity' => 1,
                'price' => 300000.00,
                'status' => 'in_progress',
                'weight' => null,
                'dimensions' => 'N/A',
                'category' => 'Jasa',
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            SampleItem::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }

        $this->command->info('Sample items seeded successfully!');
    }
}
