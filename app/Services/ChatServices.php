<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Services\WhatspieServices;
use App\Model\WhatsAppLog as ModelLog;
use App\Models\WhatsAppLog;

class ChatServices
{
  protected $baseURL;
  protected $apiKey;
  protected $whatspie;

  public function __construct()
  {
    $this->baseURL = env('WHATSPIE_BASE_URL');
    $this->apiKey = env('WHATSPIE_API_KEY');
    $this->whatspie = new WhatspieServices(); // Inisialisasi WhatsPie service
  }

  public function CustomerBaru($to, $customer)
  {
    $pesan = "Halo {$customer->nama_customer}, pendaftaran Anda berhasil. Kami akan menghubungi Anda segera untuk proses pemasangan.";

    return $this->sendWhatsPieMessage($to, $pesan);
  }

  public function pembayaranBerhasil($to, $pembayaran)
  {
    $namaCustomer = optional($pembayaran->invoice->customer)->nama_customer ?? '-';
    $adminKeuangan = optional($pembayaran->user)->name ?? 'Tripay';
    $tagihan = $pembayaran->invoice->tagihan ?? 0;
    $tambahan = $pembayaran->invoice->tambahan ?? 0;
    $saldo = $pembayaran->invoice->saldo ?? 0;
    $tunggakanLama = $pembayaran->invoice->tunggakan ?? 0;
    $jumlahBayar = $pembayaran->jumlah_bayar ?? 0;

    $totalTagihan = $tagihan + $tambahan + $tunggakanLama;
    $sisaTagihan = $totalTagihan - $jumlahBayar - $saldo;

    $tunggakan = max($sisaTagihan, 0);
    $tanggalBayar = Carbon::parse($pembayaran->tanggal_bayar)->locale('id')->isoFormat('dddd, D MMMM Y');
    $url = url('/print-kwitansi/' . $pembayaran->invoice->id);

    $pesan = "Pembayaran langganan internet Anda telah *berhasil* ✅\n\n" .
      "📅 Tanggal Pembayaran: " . $tanggalBayar . "\n" .
      "💰 Jumlah Dibayar: Rp " . number_format($pembayaran->jumlah_bayar, 0, ',', '.') . "\n" .
      "💵 Tunggakan: Rp " . number_format($tunggakan ?? 0, 0, ',', '.') . "\n" .
      "💳 Tipe Pembayaran: " . $pembayaran->tipe_pembayaran . "\n" .
      "👤 Nama Pelanggan: " . $namaCustomer . "\n" .
      "👩‍💻 Admin Keuangan: " . $adminKeuangan . "\n\n" .
      "🖨️ Link Cetak Kwitansi: " . $url . "\n\n" .
      "Terima kasih telah menggunakan layanan kami 🙏\n" .
      "Pesan ini dikirim otomatis oleh sistem *NBilling* ⚙️";

    return $this->sendWhatsPieMessage($to, $pesan);
  }

  public function kirimInvoice($to, $invoice)
  {
    $invoiceId = encrypt($invoice->customer_id);
    $url = url('/payment/invoice/' . $invoiceId);

    // Ambil hari blokir dari kolom (misalnya: "10")
    $hariBlokir = (int) $invoice->tanggal_blokir;

    // Buat tanggal lengkap: tanggal 10 bulan depan
    if ($invoice->tagihan !== $invoice->paket->harga) {
      $tanggalLengkap = now()
        ->addMonthNoOverflow()
        ->setDay($hariBlokir)
        ->format('d-m-Y');
    } else {
      $tanggalLengkap = now()
        ->setDay($hariBlokir)
        ->format('d-m-Y');
    }

    // Hitung total tagihan
    $totalTagihan = $invoice->tagihan + $invoice->tambahan - $invoice->saldo;

    // Buat kode invoice unik berdasarkan tanggal
    $time = now()->format('dmY');

    $pesan = "Halo {$invoice->customer->nama_customer}, berikut adalah tagihan Anda:\n\n" .
      "📅 Tanggal Tagihan: " . now()->format('d-m-Y') . "\n" .
      "💰 Jumlah Tagihan: Rp " . number_format($totalTagihan, 0, ',', '.') . "\n" .
      "💵 Tunggakan: Rp " . number_format($invoice->tunggakan ?? 0, 0, ',', '.') . "\n" .
      "📄 Nomor Invoice: INV-NBilling-{$invoice->customer->nama_customer}-{$time}\n\n" .
      "🔗 Link Pembayaran:\n{$url}\n\n" .
      "Silakan lakukan pembayaran untuk menghindari pemutusan layanan.\n\n" .
      "Pesan ini dikirim otomatis oleh sistem *NBilling* ⚙️";

    return $this->sendWhatsPieMessage($to, $pesan);
  }

  public function kirimInvoiceMassal($customer, $invoices)
  {
    $url = url('/payment/invoice/' . $invoices[0]->id);
    $jatuhTempo = Carbon::parse($invoices[0]->jatuh_tempo);
    $pesan = "Halo {$customer->nama_customer}, berikut adalah daftar tagihan Anda:\n\n";

    foreach ($invoices as $invoice) {
      $pesan .= "📄 *Invoice:* INV-NBilling-{$customer->nama_customer}-{$invoice->id}\n";
      $pesan .= "📅 Tanggal: " . now()->format('d-m-Y') . "\n";
      $pesan .= "💰 Tagihan Bulan: " . $jatuhTempo->translatedFormat('F Y') . "\n";
      $pesan .= "💰 Jumlah: Rp " . number_format($invoice->tagihan, 0, ',', '.') . "\n";
      $pesan .= "💵 Tunggakan: Rp " . number_format($invoice->tunggakan ?? 0, 0, ',', '.') . "\n";
      $pesan .= "📊 Status: {$invoice->status->nama_status}\n\n";
      $pesan .= "🔗 Link Pembayaran: \n{$url}\n";
      $pesan .= "--------------------------\n";
    }

    $pesan .= "\nSilakan lakukan pembayaran untuk menghindari pemutusan layanan.\n\n";
    $pesan .= "Pesan ini dikirim otomatis oleh sistem *NBilling* ⚙️";

    return $this->sendWhatsPieMessage($customer->no_hp, $pesan);
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

    $pesan = "Halo {$invoice->customer->nama_customer}, Selamat proses instalasi Anda telah selesai. Berikut adalah tagihan Anda:\n\n" .
      "📅 Tanggal Tagihan: " . now()->format('d-m-Y') . "\n" .
      "💰 Jumlah Tagihan: Rp " . number_format($totalTagihan, 0, ',', '.') . "\n" .
      "📄 Nomor Invoice: INV-NBilling-{$invoice->customer->nama_customer}-{$time}\n\n" .
      "🔗 Link Pembayaran:\n{$url}\n\n" .
      "Silakan lakukan pembayaran untuk menghindari pemutusan layanan.\n\n" .
      "Pesan ini dikirim otomatis oleh sistem *NBilling* ⚙️";

    return $this->sendWhatsPieMessage($to, $pesan);
  }

  public function kirimNotifikasiBlokir($to, $inv)
  {
    if (!$inv->customer) {
      Log::error('❌ Customer tidak ditemukan pada invoice ID: ' . $inv->id);
      return [
        'error' => true,
        'pesan' => 'Customer tidak ditemukan',
      ];
    }

    $url = url('/payment/invoice/' . $inv->id);

    $pesan = "⚠️ Halo {$inv->customer->nama_customer}, layanan internet Anda telah *diblokir* karena tagihan belum dibayar.\n\n" .
      "📅 Tanggal Blokir: " . now()->format('d-m-Y') . "\n" .
      "Silakan segera lakukan pembayaran untuk menghindari pemutusan permanen.\n" .
      "🔗 Link Pembayaran:\n{$url}\n\n" .
      "Pesan ini dikirim otomatis oleh sistem *NBilling* ⚙️";

    $response = $this->sendWhatsPieMessage($to, $pesan);

    Log::info("📩 Kirim Notifikasi Blokir ke {$to}", [
      'status' => $response['status'] ?? 'unknown',
      'success' => $response['success'] ?? false,
    ]);

    return $response;
  }

  public function kirimNotifikasiTeknisi($to, $tek)
  {
    $url = url('/teknisi/antrian');
    $pesan = "Halo {$tek->name}, Antrian Instalasi Pelanggan baru tersedia. Silakan login ke aplikasi untuk melihat detail.\n\n" .
      "🔗 Link Aplikasi:\n{$url}\n\n" .
      "Pesan ini dikirim otomatis oleh sistem *NBilling* ⚙️";

    return $this->sendWhatsPieMessage($to, $pesan);
  }

  public function kirimNotifikasiNoc($to, $noc, $customer)
  {
    $url = url('/data/antrian-noc');
    $pesan = "Halo {$noc->name}, Antrian Pelanggan baru tersedia untuk di proses. Silakan login ke aplikasi untuk melihat detail.\n\n" .
      "Nama Pelanggan: {$customer->nama_customer}\n" .
      "Nama Agen: " . ($customer->agen->nama_agen ?? '-') . "\n" .
      "🔗 Link Aplikasi:\n{$url}\n\n" .
      "Pesan ini dikirim otomatis oleh sistem *NBilling* ⚙️";

    return $this->sendWhatsPieMessage($to, $pesan);
  }

  public function kirimNotifikasiTiketOpen($to, $user, $tiket)
  {
    $url = url('/tiket-closed');
    $pesan = "Halo {$user->name}, Tiket Open baru telah ditambahkan. Silakan login ke aplikasi untuk melihat detail.\n\n" .
      "Nama Pelanggan: {$tiket->customer->nama_customer}\n" .
      "Alamat Pelanggan: {$tiket->customer->alamat}\n" .
      "No HP Pelanggan: {$tiket->customer->no_hp}\n" .
      "Kategori: {$tiket->kategori->nama_kategori}\n" .
      "Keterangan: {$tiket->keterangan}\n" .
      "By Admin: {$tiket->user->name}\n" .
      "🔗 Link Aplikasi:\n{$url}\n\n" .
      "Pesan ini dikirim otomatis oleh sistem *NBilling* ⚙️";

    return $this->sendWhatsPieMessage($to, $pesan);
  }

  public function kirimWarningBayar($customer)
  {
    // ambil semua invoice yang belum dibayar untuk customer ini
    $invoices = Invoice::where('customer_id', $customer->id)
      ->where('status_id', 7)
      ->get();

    if ($invoices->isEmpty()) {
      return [
        'success' => false,
        'message' => "Tidak ada invoice tertunggak untuk {$customer->nama_customer}"
      ];
    }

    // buat pesan gabungan
    $pesan = "⚠️ *Peringatan Tagihan Internet* ⚠️\n\n";
    $pesan .= "Halo *{$customer->nama_customer}*,\n\n";
    $pesan .= "Berikut daftar tagihan Anda yang belum dibayarkan:\n\n";

    foreach ($invoices as $invoice) {
      $jatuhTempo = \Carbon\Carbon::parse($invoice->jatuh_tempo);

      $pesan .= "📄 *Invoice:* INV-NBilling-{$customer->nama_customer}-{$invoice->id}\n";
      $pesan .= "💰 Jumlah Tagihan: Rp " . number_format(
        $invoice->tagihan + ($invoice->tambahan ?? 0) + ($invoice->tunggakan ?? 0) - ($invoice->saldo ?? 0),
        0,
        ',',
        '.'
      ) . "\n";
      $pesan .= "📊 Status: {$invoice->status->nama_status}\n";
      $pesan .= "🔗 Link: " . url('/payment/invoice/' . $invoice->id) . "\n\n";
    }

    $pesan .= "Mohon segera lakukan pembayaran agar layanan tetap aktif.\n";
    $pesan .= "Jika sudah melakukan pembayaran, abaikan pesan ini 🙏\n\n";
    $pesan .= "Pesan ini dikirim otomatis oleh sistem *NBilling* ⚙️";

    return $this->sendWhatsPieMessage($customer->no_hp, $pesan);
  }

  /**
   * Helper method untuk mengirim pesan menggunakan WhatsPie
   */
  private function sendWhatsPieMessage($to, $pesan)
  {
    try {
      // Format nomor telepon (hapus karakter non-digit dan pastikan format internasional)
      $phone = $this->formatPhoneNumber($to);

      // Dapatkan device ID default
      $deviceId = $this->getDefaultDevice();

      if (!$deviceId) {
        throw new \Exception('Tidak ada device WhatsApp yang tersedia');
      }

      // Kirim pesan menggunakan WhatsPie
      $response = $this->whatspie->sendMessage(
        $phone,        // receiver
        $pesan,        // message
        $deviceId,     // device ID
        false          // simulate typing
      );

      // Log hasil pengiriman
      Log::info("WhatsPie Message Sent", [
        'to' => $phone,
        'device_id' => $deviceId,
        'success' => $response['success'] ?? false,
        'status' => $response['status'] ?? 'unknown'
      ]);

      return $response;
    } catch (\Exception $e) {
      Log::error('WhatsPie Send Message Error: ' . $e->getMessage(), [
        'to' => $to,
        'message_length' => strlen($pesan)
      ]);

      return [
        'success' => false,
        'status' => 500,
        'error' => 'Failed to send message via WhatsPie: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Helper method untuk memformat nomor telepon
   */
  private function formatPhoneNumber($phone)
  {
    // Hapus semua karakter non-digit
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

    // Jika nomor diawali dengan 0, ganti dengan 62
    if (substr($cleanPhone, 0, 1) === '0') {
      $cleanPhone = '62' . substr($cleanPhone, 1);
    }

    // Jika nomor sudah diawali dengan 62, biarkan saja
    // Jika nomor diawali dengan 8, tambahkan 62
    if (substr($cleanPhone, 0, 1) === '8') {
      $cleanPhone = '62' . $cleanPhone;
    }

    return $cleanPhone;
  }

  /**
   * Helper method untuk mendapatkan device ID default
   */
  public function getDefaultDevice()
  {
    try {
      // Coba ambil devices dari WhatsPie
      $devicesResponse = $this->whatspie->getDevicesPhone();

      if ($devicesResponse['success'] && !empty($devicesResponse['data'])) {
        // Cari device yang connected/active
        foreach ($devicesResponse['data'] as $device) {
          $deviceData = $device['raw'] ?? $device;
          $status = $deviceData['paired_status'] ?? 'UNPAIRED';
          $isActive = ($deviceData['status'] ?? '') === 'ACTIVE';

          // Ambil nomor telepon device (bukan ID)
          $devicePhone = $deviceData['phone'] ?? $device['phone'] ?? $device['value'] ?? null;

          if ($status === 'PAIRED' && $isActive && $devicePhone) {
            Log::info("Using connected device: {$devicePhone}");
            return $devicePhone;
          }
        }

        // Jika tidak ada yang connected, ambil device pertama yang ada nomornya
        foreach ($devicesResponse['data'] as $device) {
          $deviceData = $device['raw'] ?? $device;
          $devicePhone = $deviceData['phone'] ?? $device['phone'] ?? $device['value'] ?? null;

          if ($devicePhone) {
            Log::info("Using first available device: {$devicePhone}");
            return $devicePhone;
          }
        }
      }

      Log::warning('No available WhatsApp devices found');
      return null;
    } catch (\Exception $e) {
      Log::error('Error getting default device: ' . $e->getMessage());
      return null;
    }
  }
}
