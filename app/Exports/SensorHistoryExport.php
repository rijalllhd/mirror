<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SensorHistoryExport implements FromArray, WithHeadings
{
    public function __construct(private readonly array $rows) {}

    public function headings(): array
    {
        return [
            'Tanggal', 'Data',
            'Suhu Min', 'Suhu Rata-rata', 'Suhu Maks',
            'Kelembapan Min', 'Kelembapan Rata-rata', 'Kelembapan Maks',
            'Amonia Min', 'Amonia Rata-rata', 'Amonia Maks',
            'THI Min', 'THI Rata-rata', 'THI Maks',
        ];
    }

    public function array(): array
    {
        return array_map(fn (array $row) => [
            $row['date'], $row['dataPoints'],
            $row['suhu']['min'], $row['suhu']['avg'], $row['suhu']['max'],
            $row['kelembapan']['min'], $row['kelembapan']['avg'], $row['kelembapan']['max'],
            $row['amonia_ppm']['min'], $row['amonia_ppm']['avg'], $row['amonia_ppm']['max'],
            $row['thi']['min'], $row['thi']['avg'], $row['thi']['max'],
        ], $this->rows);
    }
}
