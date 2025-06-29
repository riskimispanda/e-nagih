<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Invoice;
use App\Models\Status;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use App\Models\Paket;
use App\Models\Kas;
use App\Services\ChatServices;

class SuperAdmin extends Controller
{
    public function approvalPembayaran(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $status = $request->get('status');
        $metode = $request->get('metode');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Build query for payment confirmation requests
        // Get payments that need confirmation (not yet approved/paid)
        $query = Pembayaran::with(['invoice.customer', 'invoice.paket', 'status', 'user'])
        ->whereHas('status', function($q) {
            // Exclude already confirmed payments (status_id 8 = Sudah Bayar)
            $q->where('id', '!=', 8);
        })
        ->orderBy('created_at', 'desc');
        
        // Apply search filter
        if ($search) {
            $query->whereHas('invoice.customer', function($q) use ($search) {
                $q->where('nama_customer', 'like', '%' . $search . '%');
            })->orWhereHas('invoice.paket', function($q) use ($search) {
                $q->where('nama_paket', 'like', '%' . $search . '%');
            })->orWhere('metode_bayar', 'like', '%' . $search . '%');
        }
        
        // Apply status filter
        if ($status) {
            $query->where('status_id', $status);
        }
        
        // Apply payment method filter
        if ($metode) {
            $query->where('metode_bayar', $metode);
        }
        
        // Apply date range filter
        if ($startDate) {
            $query->whereDate('tanggal_bayar', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal_bayar', '<=', $endDate);
        }
        
        $paymentRequests = $query->paginate(15);
        
        // Get statistics
        $totalRequests = Pembayaran::whereHas('status', function($q) {
            $q->where('id', '!=', 8);
        })->count();
        
        $todayRequests = Pembayaran::whereHas('status', function($q) {
            $q->where('id', '!=', 8);
        })->whereDate('created_at', Carbon::today())->count();
        
        $pendingAmount = Pembayaran::whereHas('status', function($q) {
            $q->where('id', '!=', 8);
        })->sum('jumlah_bayar');
        
        // Get available payment methods for filter
        $paymentMethods = Pembayaran::distinct()->pluck('metode_bayar')->filter()->values();
        
        // Get available statuses for filter
        $statuses = Status::whereIn('id', [1, 2, 7, 8, 10])->get(); // Common payment statuses
        
        return view('/SuperAdmin/payment/approve-pembayaran', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'paymentRequests' => $paymentRequests,
            'totalRequests' => $totalRequests,
            'todayRequests' => $todayRequests,
            'pendingAmount' => $pendingAmount,
            'paymentMethods' => $paymentMethods,
            'statuses' => $statuses,
            'search' => $search,
            'status' => $status,
            'metode' => $metode,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
    
    public function acc($id)
    {
        $pembayaran = Pembayaran::find($id);
        $pembayaran->status_id = 8;
        $pembayaran->save();
        
        $invoice = Invoice::find($pembayaran->invoice_id);
        $invoice->status_id = 8;
        $invoice->save();

        $chat = new ChatServices();
        $chat->pembayaranBerhasil($invoice->customer->no_hp, $pembayaran);
        
        // Tanggal awal bulan depan
        $tanggalAwal = Carbon::parse($invoice->jatuh_tempo)->addMonth()->startOfMonth(); // 1 bulan depan
        $tanggalJatuhTempo = $tanggalAwal->copy()->endOfMonth(); // Akhir bulan depan
        $tanggalBlokir = $invoice->tanggal_blokir; // Blokir H+3
        
        // Cek apakah sudah ada invoice untuk bulan berikutnya
        $sudahAda = Invoice::where('customer_id', $invoice->customer_id)
            ->whereMonth('jatuh_tempo', $tanggalJatuhTempo->month)
            ->whereYear('jatuh_tempo', $tanggalJatuhTempo->year)
            ->exists();
        
        // Buat Invoice Baru
        if (!$sudahAda) {
            Invoice::create([
                'customer_id'     => $invoice->customer_id,
                'paket_id'        => $invoice->paket_id,
                'tagihan'         => $invoice->paket->harga,
                'tambahan'        => 0,
                'saldo'           => $pembayaran->saldo,
                'status_id'       => 7, // Belum bayar
                'created_at'      => $tanggalAwal,
                'updated_at'      => $tanggalAwal,
                'jatuh_tempo'     => $tanggalJatuhTempo,
                'tanggal_blokir'  => $tanggalBlokir,
            ]);
        }
        
        // Kas baru
        Kas::create([
            'debit' => $pembayaran->jumlah_bayar,
            'kas_id' => 1,
            'keterangan' => 'Pembayaran diterima dari ' . $pembayaran->invoice->customer->nama_customer,
            'tanggal_kas' => $pembayaran->tanggal_bayar,
            'user_id' => $pembayaran->user_id
        ]);
        
        return redirect()->back()->with('success', 'Pembayaran berhasil disetujui');
    }
    
    public function logAktivitas(Request $request)
    {
        $logs = Activity::with('causer')->latest()->paginate(10);
        return view('log.aktivitas', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'logs' => $logs
        ]);
    }
    
    public function logDetail($id)
    {
        $log = Activity::find($id);
        $profil = $log->causer->profile;
        $prop = $log->properties;
        // dd($prop);
        $paket = Paket::find($prop['paket_id'] ?? null);
        // dd($paket);
        return view('/log/logs-detail',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'log' => $log,
            'profil' => $profil,
            'prop' => $prop,
            'paket' => $paket
        ]);
    }
    
}
