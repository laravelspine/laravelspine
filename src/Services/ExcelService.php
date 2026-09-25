<?php

declare(strict_types=1);

namespace Spine\Services;

use Spine\Exports\GenericExport;
use Spine\Imports\GenericImport;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * ExcelService — Excel/CSV data import/export.
 *
 * Heavy import/export work is dispatched as queued jobs.
 */
class ExcelService
{
    public function disk(): Filesystem
    {
        return Storage::disk((string) config('excel.disk', 'local'));
    }

    /**
     * Export an array of data to an Excel/CSV file in storage.
     *
     * @param  list<array<string, mixed>>  $rows
     * @param  string  $filename  without extension
     * @param  string  $extension  xlsx | csv
     * @param  list<string>  $headings
     * @return array{path: string, count: int}
     */
    public function export(array $rows, string $filename, string $extension = 'xlsx', array $headings = []): array
    {
        $name = $this->dir().'/'.$filename.'.'.$extension;

        Excel::store(
            new GenericExport($rows, $headings),
            $name,
            (string) config('excel.disk', 'local'),
            $extension === 'csv'
                ? \Maatwebsite\Excel\Excel::CSV
                : \Maatwebsite\Excel\Excel::XLSX
        );

        return [
            'path' => $name,
            'count' => count($rows),
        ];
    }

    /**
     * Import an Excel/CSV file into an array of rows (associative, key = heading).
     *
     * @param  string  $path  relative path on the excel storage disk
     * @return array{rows: list<array<string, mixed>>, count: int}
     */
    public function import(string $path): array
    {
        $import = new GenericImport;
        $fullPath = $this->disk()->path($path);

        if (! file_exists($fullPath)) {
            return ['rows' => [], 'count' => 0];
        }

        Excel::import($import, $fullPath);

        return [
            'rows' => $import->rows,
            'count' => count($import->rows),
        ];
    }

    /**
     * Upload a file and import it immediately (convenience).
     *
     * @return array{rows: list<array<string, mixed>>, count: int}
     */
    public function importUpload(UploadedFile $file): array
    {
        $import = new GenericImport;
        Excel::import($import, $file);

        return [
            'rows' => $import->rows,
            'count' => count($import->rows),
        ];
    }

    private function dir(): string
    {
        return (string) config('excel.dir', 'excel');
    }
}
