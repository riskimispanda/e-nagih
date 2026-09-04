<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class DamagedItemsExport implements FromCollection
{
    private $damagedItems;

    public function __construct($damagedItems)
    {
        $this->damagedItems = $damagedItems;
    }

    public function collection()
    {
        return $this->damagedItems->map(function ($item) {
            return [
                'No' => $item->id,
                'Serial Number' => $item->serial_number ?? 'N/A',
                'MAC Address' => $item->mac_address ?? 'N/A',
                'Category' => $item->perangkat->kategori->nama_logistik ?? 'N/A',
                'Device Name' => $item->perangkat->nama_perangkat ?? 'N/A',
                'Status' => 'Rusak',
                'Date Damaged' => $item->created_at->format('Y-m-d H:i:s'),
                'Stock Level' => 'damaged',
                'Condition' => 'buruk',
            ];
        });
    }
}