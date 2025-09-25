<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Invoice;
use Carbon\Carbon;

class CustomerAgen implements FromCollection, WithMapping, WithHeadings
{
    protected $type;
    protected $bulan;
    protected $startDate;
    protected $endDate;

    /**
     * @param string $type 'bulan' untuk filter bulan tertentu, 'range' untuk custom date, default ambil bulan ini
     * @param array|null $bulan ['month' => int, 'year' => int]
     * @param string|null $startDate format Y-m-d
     * @param string|null $endDate format Y-m-d
     */
    public function __construct($type = 'bulan', $bulan = null, $startDate = null, $endDate = null)
    {
        $this->type = $type;
        $this->bulan = $bulan;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Invoice::with(['customer', 'paket', 'status'])
            ->where('status_id', '!=', 8); // 6 = Sudah Bayar

        if ($this->type === 'range' && $this->startDate && $this->endDate) {
            // Custom date range
            $query->whereBetween('jatuh_tempo', [$this->startDate, $this->endDate]);
        } elseif ($this->type === 'bulan' && !empty($this->bulan)) {
            // Filter bulan & tahun tertentu
            $query->whereMonth('jatuh_tempo', $this->bulan['month'])
                ->whereYear('jatuh_tempo', $this->bulan['year']);
        } else {
            // Default: bulan ini
            $query->whereMonth('jatuh_tempo', now()->month)
                ->whereYear('jatuh_tempo', now()->year);
        }

        return $query->get();
    }

    public function map($invoice): array
    {
        return [
            $invoice->customer->nama_customer ?? '-',
            $invoice->customer->alamat ?? '-',
            $invoice->customer->no_hp ?? '-',
            $invoice->paket->nama_paket ?? '-',
            $invoice->tagihan ?? '0',
            $invoice->status->nama_status ?? '-',
            $invoice->jatuh_tempo ? Carbon::parse($invoice->jatuh_tempo)->locale('id')->isoFormat('MMMM') : '-',
            $invoice->customer->agen->name ?? '-'
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Pelanggan',
            'Alamat',
            'No HP',
            'Paket',
            'Tagihan',
            'Status Invoice',
            'Periode',
            'PIC'
        ];
    }
}