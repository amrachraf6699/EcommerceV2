<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductBulkImportSheet implements ToCollection, WithHeadingRow
{
    public function __construct()
    {
        HeadingRowFormatter::default('none');
    }

    public function collection(Collection $collection): void
    {
        //
    }
}
