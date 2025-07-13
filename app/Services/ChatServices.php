<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;


class ChatServices
{
    protected $baseURL;
    
    public function __construct(){
        $this->baseURL = env('WHATSAPP_BOT_CHAT', 'http://203.175.11.34:3000');
    }
    
    public function CustomerBaru($to, $customer)
    {
        $response = Http::post("{$this->baseURL}/send-pesan",[
            'to' => $to . '@c.us',
            'pesan' => "Halo {$customer->nama_customer}, pendaftaran Anda berhasil. Kami akan menghubungi Anda segera untuk proses pemasangan."
        ]);
        
        if ($response->successful()) {
            return $response->json();
        }
        
        return [
            'error' => true,
            'status' => $response->status(),
            'pesan' => $response->body(),
        ];
    }
    
    public function pembayaranBerhasil($to, $pembayaran)
    {
        $response = Http::post("{$this->baseURL}/send-pesan",[
            'to' => $to . '@c.us',
            'pesan' => "Pembayaran langganan internet Anda telah *berhasil* ✅\n\n" .
                        "📅 Tanggal Pembayaran: " . now()->format('d-m-Y') . "\n" .
                        "💰 Jumlah Dibayar: Rp " . number_format($pembayaran->jumlah_bayar, 0, ',', '.') . "\n" .
                        "👤 Nama Pelanggan: " . $pembayaran->invoice->customer->nama_customer . "\n" .
                        "👩‍💻 Admin Keuangan: " . $pembayaran->user->name . "\n\n" .
                        "Terima kasih telah menggunakan layanan kami 🙏\n" .
                        "Pesan ini dikirim otomatis oleh sistem *E-Nagih* ⚙️"
        ]);
        
        if ($response->successful()) {
            return $response->json();
        }
        
        return [
            'error' => true,
            'status' => $response->status(),
            'pesan' => $response->body(),
        ];
    }

    public function kirimInvoice($to, $invoice)
    {
        // dd($to, $invoice);
        $url = url('/payment/invoice/' . $invoice->id);
        $tanggalLengkap = \Carbon\Carbon::createFromFormat('Y-m-d', now()->format('Y-m') . '-' . $invoice->tanggal_blokir)
            ->format('d-m-Y');

        $totalTagihan = $invoice->tagihan + $invoice->tambahan - $invoice->saldo;
        $time = now()->format('dmY');
        $response = Http::post("{$this->baseURL}/send-pesan",[
            'to' => $to . '@c.us',
            'pesan' => "Halo {$invoice->customer->nama_customer}, berikut adalah tagihan Anda:\n\n" .
                        "📅 Tanggal Tagihan: " . now()->format('d-m-Y') . "\n" .
                        "💰 Jumlah Tagihan: Rp " . number_format($totalTagihan, 0, ',', '.') . "\n" .
                        "📄 Nomor Invoice: INV-E-NAGIH-{$invoice->customer->nama_customer}-{$time}\n\n" .
                        "🔗 Link Pembayaran:\n{$url}\n\n" .
                        "Silakan lakukan pembayaran sebelum tanggal {$tanggalLengkap} untuk menghindari pemutusan layanan.\n\n" .
                        "Pesan ini dikirim otomatis oleh sistem *E-Nagih* ⚙️"
        ]);
        
        if ($response->successful()) {
            return $response->json();
        }
        
        return [
            'error' => true,
            'status' => $response->status(),
            'pesan' => $response->body(),
        ];
    }

    public function kirimInvoiceMassal($customer, $invoices)
    {
        // dd($customer, $invoices);
        $pesan = "Halo {$customer->nama_customer}, berikut adalah daftar tagihan Anda:\n\n";

        foreach ($invoices as $invoice) {
            $pesan .= "📄 *Invoice:* INV-E-NAGIH-{$customer->nama_customer}-{$invoice->id}\n";
            $pesan .= "📅 Tanggal: " . now()->format('d-m-Y') . "\n";
            $pesan .= "💰 Jumlah: Rp " . number_format($invoice->tagihan, 0, ',', '.') . "\n";
            $pesan .= "🔔 Jatuh Tempo: {$invoice->tanggal_blokir}\n";
            $pesan .= "--------------------------\n";
        }

        $pesan .= "\nSilakan lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari pemutusan layanan.\n\n";
        $pesan .= "Pesan ini dikirim otomatis oleh sistem *E-Nagih* ⚙️";

        $response = Http::post("{$this->baseURL}/send-pesan", [
            'to' => $customer->no_hp . '@c.us',
            'pesan' => $pesan,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [
            'error' => true,
            'status' => $response->status(),
            'pesan' => $response->body(),
        ];
    }

    public function invoiceProrate($to, $invoice)
    {
        $url = url('/payment/invoice/' . $invoice->id);

        // Tangani tanggal blokir dengan aman
        $tanggalBlokir = $invoice->tanggal_blokir;
        $tanggalLengkap = '-';

        if ($tanggalBlokir && is_numeric($tanggalBlokir) && (int) $tanggalBlokir >= 1 && (int) $tanggalBlokir <= 31) {
            $tanggalString = now()->format('Y-m') . '-' . str_pad($tanggalBlokir, 2, '0', STR_PAD_LEFT);

            try {
                $tanggalLengkap = \Carbon\Carbon::createFromFormat('Y-m-d', $tanggalString)->format('d-m-Y');
            } catch (\Exception $e) {
                // Jika format gagal, fallback ke akhir bulan ini
                $tanggalLengkap = now()->endOfMonth()->format('d-m-Y');
            }
        } else {
            // Jika tanggal_blokir tidak valid, gunakan akhir bulan
            $tanggalLengkap = now()->endOfMonth()->format('d-m-Y');
        }

        // Hitung total tagihan
        $totalTagihan = $invoice->tagihan + $invoice->tambahan - $invoice->saldo;
        $time = now()->format('dmY');

        // Kirim pesan ke WhatsApp melalui endpoint
        $response = Http::post("{$this->baseURL}/send-pesan", [
            'to' => $to . '@c.us',
            'pesan' => "Halo {$invoice->customer->nama_customer}, Selamat proses instalasi Anda telah selesai. Berikut adalah tagihan Anda:\n\n" .
                    "📅 Tanggal Tagihan: " . now()->format('d-m-Y') . "\n" .
                    "💰 Jumlah Tagihan: Rp " . number_format($totalTagihan, 0, ',', '.') . "\n" .
                    "📄 Nomor Invoice: INV-E-NAGIH-{$invoice->customer->nama_customer}-{$time}\n\n" .
                    "🔗 Link Pembayaran:\n{$url}\n\n" .
                    "Silakan lakukan pembayaran sebelum tanggal {$tanggalLengkap} untuk menghindari pemutusan layanan.\n\n" .
                    "Pesan ini dikirim otomatis oleh sistem *E-Nagih* ⚙️"
        ]);

        // Respons
        if ($response->successful()) {
            return $response->json();
        }

        return [
            'error' => true,
            'status' => $response->status(),
            'pesan' => $response->body(),
        ];
    }


}