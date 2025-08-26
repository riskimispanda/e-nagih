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
use App\Models\Customer;
use App\Services\ChatServices;
use App\Services\MikrotikServices;
use App\Models\User;
use App\Models\Roles;


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
        
        $tagihanTotal = $invoice->tagihan + $invoice->tambahan - $invoice->saldo;
        $tunggakan = max($tagihanTotal - $pembayaran->jumlah_bayar, 0);

        $invoice->status_id = 8;
        $invoice->save();

        $chat = new ChatServices();
        $chat->pembayaranBerhasil($invoice->customer->no_hp, $pembayaran);
        
        $customer = Customer::find($invoice->customer_id);
        $customer->status_id = 3;
        $customer->save();
        
        $mikrotik = new MikrotikServices();
        $client = MikrotikServices::connect($customer->router);
        $mikrotik->removeActiveConnections($client, $customer->usersecret); // Hapus koneksi aktif
        $mikrotik->unblokUser($client, $customer->usersecret, $customer->paket->paket_name); // Unblok user & kembalikan profil

        // Tanggal awal bulan depan
        $tanggalAwal = Carbon::parse($invoice->jatuh_tempo)->addMonthsNoOverflow()->startOfMonth(); // 1 bulan depan
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
                'paket_id'        => $customer->paket_id,
                'tagihan'         => $customer->paket->harga,
                'tambahan'        => 0,
                'saldo'           => $pembayaran->saldo,
                'status_id'       => 7, // Belum bayar
                'created_at'      => $tanggalAwal,
                'updated_at'      => $tanggalAwal,
                'jatuh_tempo'     => $tanggalJatuhTempo,
                'tanggal_blokir'  => $tanggalBlokir,
                'tunggakan' => $tunggakan,
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
        $perPage    = min((int) $request->get('per_page', 10), 500);
        $filterRole = $request->get('roles');
        $filterDate = $request->get('date');
        $search     = $request->get('search');

        $logs = Activity::with('causer')
            ->when($filterRole, function ($query) use ($filterRole) {
                $query->whereHas('causer', function ($q) use ($filterRole) {
                    $q->where('roles_id', $filterRole);
                });
            })
            ->when($filterDate, function ($query) use ($filterDate) {
                // ganti ke updated_at jika memang mau filter berdasarkan updated_at
                $query->whereDate('created_at', $filterDate);
            })
            ->when($search, function ($query) use ($search) {
                // Kelompokkan agar orWhere tidak “membatalkan” filter lain
                $query->where(function ($qq) use ($search) {
                    $qq->where('description', 'like', "%{$search}%")
                    ->orWhereHas('causer', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->latest('created_at')
            ->paginate($perPage)
            ->appends($request->query()); // bawa semua query (?roles, ?date, ?search, ?per_page)

        $role = Roles::whereIn('id', [1,2,3,4,5,7])->get();

        return view('log.aktivitas', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'logs'  => $logs,
            'role'  => $role,
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

    public function globalInvoice(Request $request)
    {
        $invoices = Invoice::with('customer', 'paket')
                    ->where('status_id', 7)
                    ->whereMonth('jatuh_tempo', now()->month)
                    ->whereYear('jatuh_tempo', now()->year)
                    ->get()
                    ->groupBy('customer_id');

        // dd($invoices);
        $chat = new ChatServices();

        foreach ($invoices as $customerId => $invoiceGroup) {
            $customer = $invoiceGroup->first()->customer;

            if (!$customer || !$customer->no_hp) {
                continue; // Skip jika tidak ada customer atau no_hp
            }

            $chat->kirimInvoiceMassal($customer, $invoiceGroup);
        }
        return redirect()->back()->with('success', 'Invoice massal berhasil dikirim.');
    }

    public function kirimInvoice($id)
    {
        $invoice = Invoice::find($id);
        $customer = $invoice->customer->nama_customer;
        $chat = new ChatServices();
        $chat->kirimInvoice($invoice->customer->no_hp, $invoice);
        return redirect()->back()->with('success', "Invoice berhasil dikirim ke {$customer}.");
    }
    
    public function hapusUser($id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect()->back()->with('success', 'User berhasil dihapus');
    }

}
