<?php

declare(strict_types=1);

namespace Modules\Sample\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Sample\Models\SampleItem;

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
            ],
            [
                'name' => 'Sample Item 2',
                'description' => 'Deskripsi sample item kedua',
                'quantity' => 5,
                'price' => 250000.50,
                'status' => 'in_progress',
            ],
            [
                'name' => 'Sample Item 3',
                'description' => 'Deskripsi sample item ketiga',
                'quantity' => 20,
                'price' => 75000.00,
                'status' => 'done',
            ],
            [
                'name' => 'Produk Digital',
                'description' => 'Item digital tanpa kuantitas fisik',
                'quantity' => 0,
                'price' => 500000.00,
                'status' => 'draft',
            ],
            [
                'name' => 'Jasa Konsultasi',
                'description' => 'Layanan konsultasi per jam',
                'quantity' => 1,
                'price' => 300000.00,
                'status' => 'in_progress',
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
