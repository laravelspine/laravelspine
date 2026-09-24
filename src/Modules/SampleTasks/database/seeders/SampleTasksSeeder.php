<?php

declare(strict_types=1);

namespace Modules\SampleTasks\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Sample\Models\SampleItem;
use Modules\SampleTasks\Models\SampleTask;

class SampleTasksSeeder extends Seeder
{
    public function run(): void
    {
        // Get sample items to assign tasks to
        $sampleItems = SampleItem::all();
        
        if ($sampleItems->isEmpty()) {
            $this->command->warn('No sample items found. Please run SampleItemsSeeder first.');
            return;
        }

        $tasks = [
            [
                'sample_item_id' => $sampleItems[0]->id,
                'title' => 'Setup lingkungan development',
                'status' => 'done',
            ],
            [
                'sample_item_id' => $sampleItems[0]->id,
                'title' => 'Implementasi fitur login',
                'status' => 'in_progress',
            ],
            [
                'sample_item_id' => $sampleItems[0]->id,
                'title' => 'Testing dan debugging',
                'status' => 'pending',
            ],
            [
                'sample_item_id' => $sampleItems[1]->id,
                'title' => 'Desain database',
                'status' => 'done',
            ],
            [
                'sample_item_id' => $sampleItems[1]->id,
                'title' => 'Membuat migration',
                'status' => 'done',
            ],
            [
                'sample_item_id' => $sampleItems[2]->id,
                'title' => 'Review kode',
                'status' => 'pending',
            ],
            [
                'sample_item_id' => $sampleItems[2]->id,
                'title' => 'Deploy ke staging',
                'status' => 'pending',
            ],
            [
                'sample_item_id' => $sampleItems[3]->id,
                'title' => 'Persiapan dokumentasi API',
                'status' => 'in_progress',
            ],
            [
                'sample_item_id' => $sampleItems[4]->id,
                'title' => 'Meeting dengan client',
                'status' => 'done',
            ],
            [
                'sample_item_id' => $sampleItems[4]->id,
                'title' => 'Finalisasi proposal',
                'status' => 'pending',
            ],
        ];

        foreach ($tasks as $task) {
            SampleTask::updateOrCreate(
                [
                    'sample_item_id' => $task['sample_item_id'],
                    'title' => $task['title'],
                ],
                $task
            );
        }

        $this->command->info('Sample tasks seeded successfully!');
    }
}
