<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Services\ChatServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendWarning extends Command
{
    protected $signature = 'app:send-warning';
    protected $description = 'Kirim pesan warning jika pelanggan belum bayar setelah jatuh tempo (1 - 10 tiap bulan)';

    public function handle()
    {
        $today = Carbon::today();

        // hanya jalan tanggal 1 - 10
        if (!in_array($today->day, range(1, 5, 10))) {
            $this->info("Hari ini {$today->format('d F Y')} bukan jadwal kirim warning.");
            return Command::SUCCESS;
        }

        $chat = new ChatServices();

        // Get unpaid invoices grouped by customer with latest due date
        $invoices = Invoice::with('customer')
            ->where('status_id', 7)
            ->whereDate('jatuh_tempo', '<', $today)
            ->get()
            ->groupBy('customer_id')
            ->map(function ($customerInvoices) {
                return $customerInvoices->sortByDesc('jatuh_tempo')->first();
            });

        foreach ($invoices as $invoice) {
            if (!$invoice->customer) {
                continue;
            }

            $hasil = $chat->kirimWarningBayar($invoice->customer, $invoice);

            if (isset($hasil['success']) && $hasil['success'] === false) {
                $this->warn("⚠️ {$hasil['message']}");
            } elseif (isset($hasil['error']) && $hasil['error'] === true) {
                $this->error("❌ Gagal kirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}: {$hasil['pesan']}");
                Log::error("❌ Gagal kirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}: {$hasil['pesan']}");
            } else {
                $this->info("✅ Berhasil terkirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}");
                Log::info("✅ Berhasil terkirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}");
            }
        }

        return Command::SUCCESS;
    }
}