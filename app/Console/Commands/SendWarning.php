<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
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
        if (!in_array($today->day, [1, 10])) {
            $this->info("Hari ini {$today->format('d F Y')} bukan jadwal kirim warning.");
            return Command::SUCCESS;
        }

        $chat = new ChatServices();

        // ambil semua customer
        $customers = Customer::all();

        foreach ($customers as $customer) {
            // cari invoice yang BELUM BAYAR dan SUDAH LEWAT JATUH TEMPO
            $invoice = Invoice::where('customer_id', $customer->id)
                ->where('status_id', 7) // 7 = BELUM BAYAR
                ->whereDate('jatuh_tempo', '<', $today) // sudah lewat jatuh tempo
                ->latest('jatuh_tempo') // ambil invoice jatuh tempo paling baru
                ->first();

            if (!$invoice) {
                continue; // skip kalau tidak ada invoice lewat jatuh tempo
            }

            $hasil = $chat->kirimWarningBayar($customer, $invoice);

            if (isset($hasil['success']) && $hasil['success'] === false) {
                $this->warn("⚠️ {$hasil['message']}");
            } elseif (isset($hasil['error']) && $hasil['error'] === true) {
                $this->error("❌ Gagal kirim ke {$customer->nama_customer} untuk invoice {$invoice->id}: {$hasil['pesan']}");
            } else {
                $this->info("✅ Warning terkirim ke {$customer->nama_customer} untuk invoice {$invoice->id}");
            }
        }

        return Command::SUCCESS;
    }
}