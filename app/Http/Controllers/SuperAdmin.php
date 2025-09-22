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
use App\Models\KategoriTiket;
use App\Services\ChatServices;
use App\Services\MikrotikServices;
use App\Models\User;
use App\Models\Roles;
use App\Models\BeritaAcara;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class SuperAdmin extends Controller
{
    public function index()
    {
        $data = BeritaAcara::with('invoice', 'customer', 'tiket')->orderBy('updated_at', 'desc')->get();
        $countCustomer = Customer::whereIn('status_id', [3, 9])->count();
        $countBeritaAcara = BeritaAcara::with('customer', 'invoice')->count();
        return view('SuperAdmin.payment.berita-acara', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $data,
            'countCustomer' => $countCustomer,
            'countBerita' => $countBeritaAcara,
        ]);
    }

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

        $role = Roles::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->get();

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
        $agen = User::find($prop['agen_id'] ?? null);
        // dd($paket);
        return view('/log/logs-detail',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'log' => $log,
            'profil' => $profil,
            'prop' => $prop,
            'paket' => $paket,
            'agen' => $agen
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


    public function requestEdit()
    {
        $pembayaran = Pembayaran::where('status_id', 1)->paginate(10);
        return view('/keuangan/editPembayaran',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'pembayaran' => $pembayaran
        ]);
    }

    public function konfirmasiEditPembayaran($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->update([
            'status_id' => 8,
            'jumlah_bayar' => $pembayaran->jumlah_bayar_baru
        ]);

        $kas = Kas::where('customer_id', $pembayaran->invoice->customer_id);
        $kas->update([
            'debit' => $pembayaran->jumlah_bayar_baru,
            'status_id' => 3
        ]);

        // Catat Log Aktivitas
        activity('Super Admin')
            ->causedBy(auth()->user())
            ->performedOn($pembayaran)
            ->log(auth()->user()->name . ' Mengkonfirmasi request edit pembayaran dari ' . $pembayaran->admin->name);

        return redirect('/data/pembayaran')->with('toast_success', 'Berhasil Konfirmasi Edit Pembayaran');

    }

    public function rejectEditPembayaran($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->update([
            'status_id' => 8,
            'jumlah_bayar' => $pembayaran->jumlah_bayar
        ]);
        // Catat Log Aktivitas
        activity('Super Admin')
            ->causedBy(auth()->user())
            ->performedOn($pembayaran)
            ->log(auth()->user()->name . ' Mengkonfirmasi request edit pembayaran dari ' . $pembayaran->admin->name);
        return redirect('/data/pembayaran')->with('toast_success', 'Berhasil Di Reject');
    }

    public function FormBeritaAcara(Request $request, $id)
    {
        $data = Customer::findOrFail($id);
        $kategori = KategoriTiket::all();
        return view('SuperAdmin.payment.form-berita-acara', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $data,
            'kategori' => $kategori,
        ]);
    }

    public function StoreBeritaAcara(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $customer = Customer::findOrFail($id);

            // Ambil invoice terakhir dengan status tertentu (opsional)
            $invoice = $customer->invoice()->latest()->first();

            if (!$invoice) {
                return redirect()->back()->with('error', 'Invoice tidak ditemukan untuk customer ini.');
            }

            // Simpan berita acara
            BeritaAcara::create([
                'customer_id'       => $customer->id,
                'invoice_id'        => $invoice->id,
                'tanggal_ba'        => Carbon::parse($request->tanggal_mulai),
                'tanggal_selesai_ba' => Carbon::parse($request->tanggal_selesai),
                'keterangan'        => $request->keterangan,
                'kategori_tiket'    => $request->kategori,
                'admin_id'          => auth()->user()->id,
            ]);

            DB::commit();
            LOG::info('Berhasil Membuat Berita Acara Untuk Customer: ' . $customer->nama_customer);
            return redirect('/berita-acara')->with('success', 'Berhasil Membuat Berita Acara');
        } catch (\Exception $e) {
            DB::rollBack();
            LOG::error('Gagal Membuat Berita Acara Untuk Customer: ' . $customer->nama_customer . ' Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat Berita Acara: ' . $e->getMessage());
        }
    }

    public function PreviewBeritaAcara($id)
    {
        $data = BeritaAcara::where('customer_id', $id)->latest()->first();
        $invoice = $data->customer->invoice()->latest()->first();
        return view('SuperAdmin.payment.preview-berita-acara', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $data,
            'invoice' => $invoice,
        ]);
    }

    public function viewBeritaAcara()
    {
        $data = Customer::with('latestInvoice')->whereIn('status_id', [3, 9])->get();
        return view('SuperAdmin.payment.view-berita-acara', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $data
        ]);
    }

    public function ApproveBeritaAcara($id)
    {
        $data = Customer::findOrFail($id);
        $berita = BeritaAcara::where('customer_id', $id)->latest()->first();
        $blokir = Carbon::parse($berita->tanggal_selesai_ba)->format('d-m-Y H:i:s');

        // Update Berita acara
        $berita->update([
            'noc_id' => auth()->user()->id,
            'tanggal_selesai_ba' => $blokir
        ]);

        // Update Mikrotik Profile sesuai Usersecret
        if ($data->status_id == 9) {
            $mikrotik = new MikrotikServices();
            $client = MikrotikServices::connect($data->router);
            $mikrotik->unblokUser($client, $data->usersecret, $data->paket->paket_name);
            $mikrotik->removeActiveConnections($client, $data->usersecret);

            // Update Status Id ke 3
            $data->update([
                'status_id' => 3
            ]);
        }

        // Catat Log Aktivitas
        activity('NOC')
            ->causedBy(auth()->user())
            ->log(auth()->user()->name . ' Menyetujui pengajuan Aktivasi sementara dari ' . $berita->admin->name . ' Untuk pelanggan ' . $berita->customer->nama_customer);

        return redirect('/berita-acara')->with('toast_success', 'Berhasil Menyetujui');
    }

    public function hapusBeritaAcara($id)
    {
        $data = BeritaAcara::findOrFail($id);
        $data->delete();

        // Catat Log Aktivitas
        activity('Hapus BA')
            ->causedBy(auth()->user())
            ->log(auth()->user()->name . ' Menghapus Berita Acara');

        return redirect('/berita-acara')->with('toast_success', 'Berhasil Menghapus Berita Acara');
    }
}
