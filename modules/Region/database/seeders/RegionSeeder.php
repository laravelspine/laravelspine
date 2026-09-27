<?php

declare(strict_types=1);

namespace Modules\Region\database\seeders;

use Illuminate\Database\Seeder as BaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Region\app\Models\Province;
use Modules\Region\app\Models\Regency;

class RegionSeeder extends BaseSeeder
{
    private const PROVINCE_URL = 'https://raw.githubusercontent.com/alifbint/indonesia-38-provinsi/main/provinsi.csv';
    private const REGENCY_URL  = 'https://raw.githubusercontent.com/alifbint/indonesia-38-provinsi/main/kabupaten_kota.csv';

    public function run(): void
    {
        $this->command?->info('Fetching province data from GitHub...');
        $provinces = $this->fetchCsv(self::PROVINCE_URL);

        $this->command?->info('Fetching regency data from GitHub...');
        $regencies = $this->fetchCsv(self::REGENCY_URL);

        DB::transaction(function () use ($provinces, $regencies) {
            Province::query()->delete();
            Regency::query()->delete();

            $provinceMap = [];
            foreach ($provinces as $row) {
                $province = Province::create([
                    'name'     => $row['name'],
                    'iso_code' => $row['id'],
                ]);
                $provinceMap[$row['id']] = $province->id;
            }

            $this->command?->info("Seeded " . count($provinceMap) . " provinces.");

            $batch = [];
            $count = 0;
            foreach ($regencies as $row) {
                $provinceId = $provinceMap[substr((string) $row['id'], 0, 2)] ?? null;
                if (! $provinceId) {
                    continue;
                }

                $batch[] = [
                    'province_id' => $provinceId,
                    'name'        => $row['name'],
                    'type'        => $this->determineType($row['id']),
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
                $count++;

                if (count($batch) >= 500) {
                    DB::table('regencies')->insert($batch);
                    $batch = [];
                }
            }

            if (! empty($batch)) {
                DB::table('regencies')->insert($batch);
            }

            $this->command?->info("Seeded {$count} regencies.");
        });
    }

    private function fetchCsv(string $url): array
    {
        $response = Http::timeout(30)->get($url);

        if (! $response->successful()) {
            $this->command?->error("Failed to fetch CSV from {$url}");
            return [];
        }

        $rows = [];
        $lines = preg_split('/\r\n|\r|\n/', trim($response->body()));

        foreach ($lines as $index => $line) {
            if ($index === 0) {
                continue; // skip header
            }

            $data = str_getcsv($line);
            if (count($data) < 2) {
                continue;
            }

            $rows[] = [
                'id'   => trim($data[0]),
                'name' => trim($data[1]),
            ];
        }

        return $rows;
    }

    private function determineType(string $regencyId): string
    {
        // Kota IDs typically end with 71-74 or 71-80 in this dataset
        $lastTwo = (int) substr($regencyId, -2);
        return ($lastTwo >= 71 && $lastTwo <= 80) ? 'kota' : 'kabupaten';
    }
}