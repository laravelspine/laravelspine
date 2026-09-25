<?php

declare(strict_types=1);

namespace Spine\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * GenericExport — export a data array to an Excel/CSV file.
 *
 * Columns are taken from the keys of the first array row or from the given headings.
 *
 * @implements FromArray
 */
class GenericExport implements FromArray, ShouldAutoSize, WithCustomCsvSettings, WithHeadings
{
    /**
     * @param  list<array<string, mixed>>  $data
     * @param  list<string>  $headings
     */
    public function __construct(
        private array $data,
        private array $headings = []
    ) {}

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        if (! empty($this->headings)) {
            return $this->headings;
        }

        return array_keys($this->data[0] ?? []);
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
        ];
    }
}
