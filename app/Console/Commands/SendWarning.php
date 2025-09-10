<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Services\ChatServices;
use Carbon\Carbon;

class SendWarning extends Command
{
    protected $signature = 'app:send-warning';
    protected $description = 'Kirim pesan warning jika pelanggan belum bayar setelah jatuh tempo (1 - 10 tiap bulan)';

    public function handle()
    {
        $today = Carbon::today();

        // hanya jalan tanggal 1 - 10
        if (!in_array($today->day, range(1, 10))) {
            $this->info("Hari ini {$today->format('d F Y')} bukan jadwal kirim warning.");
            return Command::SUCCESS;
        }

        $chat = new ChatServices();

        // Ambil invoice terakhir (jatuh tempo terbaru) yang belum bayar per customer
        $invoices = Invoice::with('customer')
            ->where('status_id', 7) // Belum bayar
            ->whereDate('jatuh_tempo', '<', $today) // Sudah lewat jatuh tempo
            ->latestOfMany('jatuh_tempo') // hanya ambil 1 per customer
            ->get();

        foreach ($invoices as $invoice) {
            if (!$invoice->customer) {
                continue;
            }

            $hasil = $chat->kirimWarningBayar($invoice->customer, $invoice);

            if (isset($hasil['success']) && $hasil['success'] === false) {
                $this->warn("⚠️ {$hasil['message']}");
            } elseif (isset($hasil['error']) && $hasil['error'] === true) {
                $this->error("❌ Gagal kirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}: {$hasil['pesan']}");
            } else {
                $this->info("✅ Warning terkirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}");
            }
        }

        return Command::SUCCESS;
    }
}