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
}