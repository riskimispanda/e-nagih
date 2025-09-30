<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Services\ChatServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;

class SendWarning extends Command
{
    protected $signature = 'app:send-warning';
    protected $description = 'Kirim pesan warning jika pelanggan belum bayar setelah jatuh tempo (1 - 10 tiap bulan)';

    public function handle()
    {
        $today = Carbon::today();

        if (in_array($today->day, [2, 11])) {
            $count = Customer::whereNotNull('warning_sent')->update(['warning_sent' => null]);
            Log::info("Berhasil reset field warning_sent. Total customer direset: " . $count);
        }

        // hanya jalan tanggal 1 - 10
        if (!in_array($today->day, range(1, 10))) {
            return Command::SUCCESS;
        }

        $chat = new ChatServices();

        // Get unpaid invoices grouped by customer with latest due date
        $invoices = Invoice::with('customer')
            ->where('status_id', 7)
            ->whereDate('jatuh_tempo', '<', $today)
            ->where('paket_id', '!=', 11)
            ->get()
            ->groupBy('customer_id')
            ->map(function ($customerInvoices) {
                return $customerInvoices->sortByDesc('jatuh_tempo')->first();
            });

        foreach ($invoices as $invoice) {
            if (!$invoice->customer) {
                continue;
            }
            if ($invoice->customer->warning_sent == 1) {
                Log::info($invoice->customer->nama_customer . ' sudah di kirim notifikasi');
                continue;
            }
            $hasil = $chat->kirimWarningBayar($invoice->customer, $invoice);

            if (isset($hasil['success']) && $hasil['success'] === false) {
                $this->warn("⚠️ {$hasil['message']}");
            } elseif (isset($hasil['error']) && $hasil['error'] === true) {
                $this->error("❌ Gagal kirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}: {$hasil['pesan']}");
                Log::error("❌ Gagal kirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}: {$hasil['pesan']}");
            } else {
                $invoice->customer->update(['warning_sent' => 1]);
                $this->info("✅ Berhasil terkirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}");
                Log::info("✅ Berhasil terkirim ke {$invoice->customer->nama_customer} untuk invoice {$invoice->id}");
            }
        }
        return Command::SUCCESS;
    }
}