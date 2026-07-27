<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Reusable Excel export for any report: pass the already-formatted rows
 * (array of arrays) and column headings, shared by every report in
 * Admin\ReportController rather than writing one export class per report.
 */
class GenericExport implements FromArray, WithHeadings
{
    public function __construct(private array $rows, private array $headings)
    {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
