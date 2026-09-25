<?php

declare(strict_types=1);

namespace Spine\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * GenericImport — imports an Excel/CSV file into a collection of rows.
 *
 * The first row (heading) is used as the associative key for each row.
 *
 * @implements ToCollection
 */
class GenericImport implements ToCollection, WithHeadingRow
{
    /**
     * @var list<array<string, mixed>>
     */
    public array $rows = [];

    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            if (! is_array($row->toArray())) {
                continue;
            }

            $this->rows[] = $row->toArray();
        }
    }
}
