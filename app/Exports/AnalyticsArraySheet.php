<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class AnalyticsArraySheet implements FromArray, ShouldAutoSize, WithTitle
{
    /**
     * @param array<int, array<int, mixed>> $rows
     */
    public function __construct(private readonly string $title, private readonly array $rows)
    {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return mb_substr($this->title, 0, 31);
    }
}
