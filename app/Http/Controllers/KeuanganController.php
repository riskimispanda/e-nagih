<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Paket;
use App\Models\Status;
use App\Models\Pembayaran;
use App\Models\Metode;
use App\Models\Pendapatan;
use Carbon\Carbon;
use App\Services\MikrotikServices;
use Paginate;
use Illuminate\Support\Facades\Storage;
use App\Models\Kas;
use App\Models\Pengeluaran;
use App\Models\Rab;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ChatServices;
use Illuminate\Support\Facades\Log;
use Exception;



class KeuanganController extends Controller
{

    public function dashboardKeuangan()
    {
        // Calculate financial metrics
        $subs = Pembayaran::sum('jumlah_bayar');
        $corp = Perusahaan::where('status_id', 3)->sum('harga');
        $nonSubs = Pendapatan::sum('jumlah_pendapatan');
        // dd($corp + $subs + $nonSubs);
        $totalFull = $subs + $corp + $nonSubs;
        $totalSubs = $subs + $corp;

        $totalRevenue = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->sum('tagihan');

        $monthlyRevenue = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->whereMonth('created_at', Carbon::now()->month)
          ->whereYear('created_at', Carbon::now()->year)
          ->sum('tagihan');

        $pendingRevenue = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Belum Bayar');
        })->sum('tagihan');

        $totalTransactions = Pembayaran::count();

        // Monthly statistics
        $monthlyPaid = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->whereMonth('created_at', Carbon::now()->month)
          ->whereYear('created_at', Carbon::now()->year)
          ->count();

        $monthlyUnpaid = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Belum Bayar');
        })->whereMonth('created_at', Carbon::now()->month)
          ->whereYear('created_at', Carbon::now()->year)
          ->count();

        // Calculate percentages
        $totalInvoices = Invoice::count();
        $paidCount = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->count();

        $pendingCount = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Belum Bayar');
        })->count();

        $paidPercentage = $totalInvoices > 0 ? round(($paidCount / $totalInvoices) * 100, 1) : 0;
        $pendingPercentage = $totalInvoices > 0 ? round(($pendingCount / $totalInvoices) * 100, 1) : 0;
        $overduePercentage = max(0, 100 - $paidPercentage - $pendingPercentage);

        // Get revenue trends data for the last 6 months
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->format('M');
            $year = $date->format('Y');
            
            // Get subscription revenue
            $subscriptionRevenue = Pembayaran::whereMonth('tanggal_bayar', $date->month)
                ->whereYear('tanggal_bayar', $date->year)
                ->sum('jumlah_bayar');

            // Get non-subscription revenue
            $nonSubscriptionRevenue = Pendapatan::whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->sum('jumlah_pendapatan');

            $monthlyData['labels'][] = $month . ' ' . $year;
            $monthlyData['subscription'][] = $subscriptionRevenue;
            $monthlyData['nonSubscription'][] = $nonSubscriptionRevenue;
        }

        return view('/keuangan/dashboard-keuangan/dashboard-keuangan', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'pendingRevenue' => $pendingRevenue,
            'totalTransactions' => $totalTransactions,
            'monthlyPaid' => $monthlyPaid,
            'monthlyUnpaid' => $monthlyUnpaid,
            'paidPercentage' => $paidPercentage,
            'pendingPercentage' => $pendingPercentage,
            'overduePercentage' => $overduePercentage,
            'totalFull' => $totalFull,
            'subs' => $totalSubs,
            'nonSubs' => $nonSubs,
            'monthlyData' => $monthlyData
        ]);
    }


    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $bulan = $request->get('bulan');

        $sumData = Invoice::whereIn('status_id', [7, 8])
            ->whereMonth('jatuh_tempo', now()->month)
            ->whereYear('jatuh_tempo', now()->year)
            ->selectRaw('
                SUM(tagihan) as total_tagihan,
                SUM(tunggakan) as total_tunggakan,
                SUM(tambahan) as total_tambahan,
                SUM(saldo) as total_saldo
            ')
            ->first();

        $tes = ($sumData->total_tagihan ?? 0) 
            + ($sumData->total_tunggakan ?? 0) 
            + ($sumData->total_tambahan ?? 0) 
            - ($sumData->total_saldo ?? 0);


        // Default to current month only if no bulan parameter is provided at all (first time load)
        if ($bulan === null && !$request->has('bulan')) {
            $bulan = Carbon::now()->month;
        }

        // Build query for invoices with relationships
        $query = Invoice::with(['customer', 'paket', 'status'])
            ->orderBy('created_at', 'desc')->whereIn('status_id', [1, 7]); // Exclude 'Dibatalkan' status

        // Apply search filter
        if ($search) {
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('nama_customer', 'like', '%' . $search . '%');
            })->orWhereHas('paket', function($q) use ($search) {
                $q->where('nama_paket', 'like', '%' . $search . '%');
            });
        }

        // Apply status filter
        if ($status) {
            $query->where('status_id', $status);
        }

        // Apply month filter - only filter by month if bulan is not empty (when "Semua Bulan" is selected, bulan will be empty)
        if ($bulan && $bulan !== '' && $bulan !== null) {
            $query->whereMonth('jatuh_tempo', $bulan);
        }

        // Apply date range filter
        if ($startDate && $endDate) {
            $query->whereBetween('jatuh_tempo', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }
        $perki = Invoice::whereIn('status_id', [7, 8]);
        
        // Clone query for statistics calculation
        $statisticsQuery = clone $query;
        $invoices = $query->paginate(10);

        if ($bulan) {
            $perki->whereMonth('jatuh_tempo', $bulan);
        }
        
        // Ambil semua data untuk kalkulasi
        $allInvoices = $perki->with('paket')->get();
        
        // Hitung estimasi
        $perkiraanPendapatan = $allInvoices->sum(fn($inv) => $inv->tagihan ?? 0);
        $tambahan           = $allInvoices->sum(fn($inv) => $inv->tambahan ?? 0);
        $tunggakan          = $allInvoices->sum(fn($inv) => $inv->tunggakan ?? 0);
        $saldo              = $allInvoices->sum(fn($inv) => $inv->saldo ?? 0);
        
        $estimasi = $perkiraanPendapatan + $tambahan + $tunggakan - $saldo;

        // Calculate revenue statistics based on filtered data
        if ($bulan && $bulan !== '' && $bulan !== null) {
            // When month filter is applied, calculate from filtered invoices
            $filteredInvoices = $statisticsQuery->get();

            $totalRevenue = 0;
            $pendingRevenue = 0;
            $totalInvoices = 0;

            foreach ($filteredInvoices as $invoice) {
                // Count total invoices with status_id = 7 (Belum Bayar)
                if ($invoice->status_id == 7) {
                    $totalInvoices++;
                }

                // Calculate based on status_id
                if ($invoice->status_id == 8) { // Sudah Bayar
                    $totalRevenue += ($invoice->tagihan + $invoice->tambahan - $invoice->tunggakan);
                } elseif ($invoice->status_id == 7) { // Belum Bayar
                    $pendingRevenue += ($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan);
                }
            }
        } else {
            // When no month filter (Semua Bulan), calculate from all invoices with status_id 8 (Sudah Bayar)
            $totalRevenue = Invoice::where('status_id', 8)
                ->sum('tagihan') + Invoice::where('status_id', 8)
                ->sum('tambahan') - Invoice::where('status_id', 8)
                ->sum('tunggakan');


            // Calculate pending revenue from status_id 7 (Belum Bayar)
            $pendingRevenue = Invoice::where('status_id', 7)
                ->sum('tagihan') + Invoice::where('status_id', 7)
                ->sum('tambahan') + Invoice::where('status_id', 7)
                ->sum('tunggakan');

            $totalInvoices = Invoice::where('status_id', 7)->count();
        }

        // Monthly revenue from payments (Pembayaran) - filtered by selected month or all if no month filter
        if ($bulan && $bulan !== '' && $bulan !== null) {
            $monthlyRevenue = Pembayaran::whereMonth('tanggal_bayar', $bulan)
                ->whereYear('tanggal_bayar', Carbon::now()->year)
                ->sum('jumlah_bayar');
        } else {
            $monthlyRevenue = Pembayaran::sum('jumlah_bayar');
        }

        // Get all status options for filter dropdown
        $statusOptions = Status::whereIn('id', [7, 8])->get();

        $metode = Metode::whereNot('id', 3)->get();
        $pendapatan = Pendapatan::paginate(5);
        $agen = User::where('roles_id', 6)->count();
        $totalPembayaran = Pembayaran::where('status_id', 1)->count();
        $pembayaran = Pembayaran::where('status_id', 8)->sum('jumlah_bayar');

        return view('keuangan.data-pendapatan',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'invoices' => $invoices,
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'pendingRevenue' => $pendingRevenue,
            'totalInvoices' => $totalInvoices,
            'statusOptions' => $statusOptions,
            'search' => $search,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'bulan' => $bulan,
            'metode' => $metode,
            'pendapatan' => $pendapatan,
            'agen' => $agen,
            'totalPembayaran' => $totalPembayaran,
            'pembayaran' => $pembayaran,
            'perkiraanPendapatan' => $estimasi,
            'tes' => $tes
        ]);
    }

    public function getRevenueData(Request $request)
    {
        // For AJAX requests to get updated data
        $search = $request->get('search');
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Invoice::with(['customer', 'paket', 'status'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->whereHas('customer', function($q) use ($search) {
            $q->where('nama_customer', 'like', '%' . $search . '%');
            })->orWhereHas('paket', function($q) use ($search) {
            $q->where('nama_paket', 'like', '%' . $search . '%');
            });
        }

        if ($status) {
            $query->where('status_id', $status);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $invoices = $query->paginate(10);

        if ($request->ajax()) {
            return response()->json([
            'invoices' => $invoices,
            'html' => view('keuangan.partials.revenue-table', compact('invoices'))->render(),
            'pagination' => $invoices->links()->toHtml()
            ]);
        }
    }

    public function pembayaran(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $metode = $request->get('metode');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $editPembayaran = Pembayaran::where('status_id', 1)->count();

        // Build query for payments with relationships
        $query = Pembayaran::with(['invoice.customer', 'invoice.paket', 'status', 'user'])
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($search) {
            $query->whereHas('invoice.customer', function($q) use ($search) {
                $q->where('nama_customer', 'like', '%' . $search . '%');
            })->orWhereHas('invoice.paket', function($q) use ($search) {
                $q->where('nama_paket', 'like', '%' . $search . '%');
            })->orWhere('metode_bayar', 'like', '%' . $search . '%');
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

        $payments = $query->paginate(10);

        // Calculate payment statistics

        $invoicePay = $query->paginate(10);

        $totalPayments = Pembayaran::sum('jumlah_bayar');

        $todayPayments = Pembayaran::whereDate('tanggal_bayar', Carbon::today())
            ->sum('jumlah_bayar');

        $monthlyPayments = Pembayaran::whereMonth('tanggal_bayar', Carbon::now()->month)
            ->whereYear('tanggal_bayar', Carbon::now()->year)
            ->sum('jumlah_bayar');

        $totalTransactions = Pembayaran::count();

        // Get payment methods for filter dropdown
        $paymentMethods = Pembayaran::select('metode_bayar')
            ->distinct()
            ->whereNotNull('metode_bayar')
            ->pluck('metode_bayar');

        // Calculate payment method statistics
        $cashPayments = Pembayaran::where(function($q) {
            $q->where('metode_bayar', 'like', '%cash%')
              ->orWhere('metode_bayar', 'like', '%tunai%');
        })->sum('jumlah_bayar');
        $CashCount = Pembayaran::where(function ($q) {
            $q->where('metode_bayar', 'like', '%cash%')
                ->orWhere('metode_bayar', 'like', '%tunai%');
        })->count();


        $transferPayments = Pembayaran::where(function($q) {
            $q->where('metode_bayar', 'like', '%transfer%')
              ->orWhere('metode_bayar', 'like', '%bank%')
              ->orWhere('metode_bayar', 'like', '%briva%')
              ->orWhere('metode_bayar', 'like', '%bniva%')
              ->orWhere('metode_bayar', 'like', '%bcava%')
              ->orWhere('metode_bayar', 'like', '%transfer bank%');
        })->sum('jumlah_bayar');
        $transferCount = Pembayaran::where(function ($q) {
            $q->where('metode_bayar', 'like', '%transfer%')
                ->orWhere('metode_bayar', 'like', '%bank%')
                ->orWhere('metode_bayar', 'like', '%briva%')
                ->orWhere('metode_bayar', 'like', '%bniva%')
                ->orWhere('metode_bayar', 'like', '%bcava%')
                ->orWhere('metode_bayar', 'like', '%transfer bank%');
        })->count();

        $tripay = Pembayaran::where(function($q) {
            $q->where('metode_bayar', 'like', '%tripay%')
              ->orWhere('metode_bayar', 'like', '%DANA%');
        })->count();

        // E-wallet payments count
        $ewalletPayments = Pembayaran::where(function($q) {
            $q->where('metode_bayar', 'like', '%ewallet%')
              ->orWhere('metode_bayar', 'like', '%e-wallet%')
              ->orWhere('metode_bayar', 'like', '%gopay%')
              ->orWhere('metode_bayar', 'like', '%ovo%')
              ->orWhere('metode_bayar', 'like', '%dana%')
              ->orWhere('metode_bayar', 'like', '%qris%')
              ->orWhere('metode_bayar', 'like', '%shopeepay%');
        })->sum('jumlah_bayar');
        $ewalletCount = Pembayaran::where(function ($q) {
            $q->where('metode_bayar', 'like', '%ewallet%')
                ->orWhere('metode_bayar', 'like', '%e-wallet%')
                ->orWhere('metode_bayar', 'like', '%gopay%')
                ->orWhere('metode_bayar', 'like', '%ovo%')
                ->orWhere('metode_bayar', 'like', '%dana%')
                ->orWhere('metode_bayar', 'like', '%qris%')
                ->orWhere('metode_bayar', 'like', '%shopeepay%');
        })->count();

        return view('/keuangan/data-pembayaran',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'payments' => $payments,
            'totalPayments' => $totalPayments,
            'todayPayments' => $todayPayments,
            'monthlyPayments' => $monthlyPayments,
            'totalTransactions' => $totalTransactions,
            'paymentMethods' => $paymentMethods,
            'cashPayments' => $cashPayments,
            'transferPayments' => $transferPayments,
            'ewalletPayments' => $ewalletPayments,
            'search' => $search,
            'metode' => $metode,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'tripay' => $tripay,
            'invoicePay' => $invoicePay,
            'editPembayaran' => $editPembayaran,
            'cashCount' => $CashCount,
            'transferCount' => $transferCount,
            'ewalletCount' => $ewalletCount,
        ]);
    }

    public function getDashboardData()
    {
        // Calculate financial metrics
        $totalRevenue = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->sum('tagihan');

        $monthlyRevenue = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->whereMonth('created_at', Carbon::now()->month)
          ->whereYear('created_at', Carbon::now()->year)
          ->sum('tagihan');

        $pendingRevenue = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Belum Bayar');
        })->sum('tagihan');

        $totalTransactions = Pembayaran::count();

        // Monthly statistics
        $monthlyPaid = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->whereMonth('created_at', Carbon::now()->month)
          ->whereYear('created_at', Carbon::now()->year)
          ->count();

        $monthlyUnpaid = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Belum Bayar');
        })->whereMonth('created_at', Carbon::now()->month)
          ->whereYear('created_at', Carbon::now()->year)
          ->count();

        // Calculate percentages
        $totalInvoices = Invoice::count();
        $paidCount = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->count();

        $pendingCount = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Belum Bayar');
        })->count();

        $paidPercentage = $totalInvoices > 0 ? round(($paidCount / $totalInvoices) * 100, 1) : 0;
        $pendingPercentage = $totalInvoices > 0 ? round(($pendingCount / $totalInvoices) * 100, 1) : 0;
        $overduePercentage = max(0, 100 - $paidPercentage - $pendingPercentage);

        // Get revenue data for the last 6 months
        $revenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = Invoice::whereHas('status', function($q) {
                $q->where('nama_status', 'Sudah Bayar');
            })->whereMonth('created_at', $date->month)
              ->whereYear('created_at', $date->year)
              ->sum('tagihan');
            $revenueData[] = (int) $revenue;
        }

        // Get payment method distribution
        $paymentMethods = Pembayaran::select('metode_bayar')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('metode_bayar')
            ->get();

        $methodData = [0, 0, 0, 0]; // Default for [Transfer Bank, E-Wallet, Cash, Lainnya]
        foreach ($paymentMethods as $method) {
            switch (strtolower($method->metode_bayar)) {
                case 'transfer':
                case 'bank':
                case 'transfer bank':
                    $methodData[0] += $method->count;
                    break;
                case 'ewallet':
                case 'e-wallet':
                case 'gopay':
                case 'ovo':
                case 'dana':
                    $methodData[1] += $method->count;
                    break;
                case 'cash':
                case 'tunai':
                    $methodData[2] += $method->count;
                    break;
                default:
                    $methodData[3] += $method->count;
                    break;
            }
        }

        return response()->json([
            'totalRevenue' => (int) $totalRevenue,
            'monthlyRevenue' => (int) $monthlyRevenue,
            'pendingPayments' => (int) $pendingRevenue,
            'totalTransactions' => $totalTransactions,
            'monthlyPaid' => $monthlyPaid,
            'monthlyUnpaid' => $monthlyUnpaid,
            'paidPercentage' => $paidPercentage,
            'pendingPercentage' => $pendingPercentage,
            'overduePercentage' => $overduePercentage,
            'revenueData' => $revenueData,
            'paymentMethods' => $methodData,
        ]);
    }

    public function approvePayment(Request $request, $customerId)
    {
        // dd($request->all());
        

        try {
            // Validate the request
            $request->validate([
                'paymentDate' => 'required|date',
                'paymentMethodSelect' => 'required|string',
                'paymentNotes' => 'nullable|string|max:500',
                'transferProof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
                'invoice_id' => 'nullable|integer'
            ]);

            // Find the customer
            $customer = Customer::findOrFail($customerId);
            // Get the invoice - either from request or find the latest unpaid invoice
            $invoiceId = $request->input('invoice_id');
            if ($invoiceId) {
                $invoice = Invoice::findOrFail($invoiceId);
            } else {
                // Find the latest unpaid invoice for this customer
                $invoice = Invoice::where('customer_id', $customerId)
                    ->whereHas('status', function($q) {
                        $q->where('nama_status', 'Belum Bayar');
                    })
                    ->latest()
                    ->first();

                if (!$invoice) {
                    return redirect()->back()->with('error', 'Tidak ada tagihan yang perlu dibayar untuk pelanggan ini.');
                }
            }

            // Handle file upload for transfer proof
            $buktiPath = null;
            if ($request->hasFile('transferProof')) {
                $file = $request->file('transferProof');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $buktiPath = $file->storeAs('bukti_pembayaran', $fileName, 'public');
            }

            // Get payment method name
            $metode = Metode::find($request->paymentMethodSelect);
            // dd($metode);
            $metodeNama = $metode ? $metode->nama_metode : 'Cash';
            $mikrotik = new MikrotikServices();
            $mikrotik->removeActiveConnections($customer->usersecret);
            $profile = $customer->paket ? $customer->paket->nama_paket: 'profile-test-aplikasi';

            $mikrotik->changeUserProfile($customer->usersecret, $profile);
            $jumlahBayar = $invoice->tagihan + $invoice->tambahan;
            // Create payment record
            $pembayaran = new Pembayaran();
            $pembayaran->invoice_id = $invoice->id;
            $pembayaran->jumlah_bayar = $jumlahBayar;
            $pembayaran->tanggal_bayar = $request->paymentDate;
            $pembayaran->metode_bayar = $metodeNama;
            $pembayaran->keterangan = $request->paymentNotes;
            $pembayaran->bukti_bayar = $buktiPath;
            $pembayaran->status_id = 6; // Sudah Bayar
            $pembayaran->user_id = auth()->id();
            $pembayaran->save();

            // Update invoice status to paid
            $invoice->status_id = 6; // Sudah Bayar
            $invoice->save();

            // Update customer status to active if needed
            if ($customer->status_id != 3) {
                $customer->status_id = 3; // Active
                $customer->save();
            }

            return redirect()->back()->with('success', 'Pembayaran berhasil dikonfirmasi untuk pelanggan ' . $customer->nama_customer);

        } catch (\Exception $e) {
            \Log::error('Error in approvePayment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage());
        }
    }

    public function dailyPembayaran()
    {
        // Get today's payments
        $today = Carbon::today();
        $payments = Pembayaran::with(['invoice.customer', 'invoice.paket', 'status'])
            ->whereDate('tanggal_bayar', $today)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $daily = Pembayaran::selectRaw('DATE(tanggal_bayar) as date, 
            SUM(jumlah_bayar) as total,
            SUM(CASE WHEN metode_bayar LIKE "%cash%" OR metode_bayar LIKE "%tunai%" THEN jumlah_bayar ELSE 0 END) as cash_total,
            SUM(CASE WHEN metode_bayar LIKE "%transfer%" OR metode_bayar LIKE "%bniva%" OR metode_bayar LIKE "%briva%" or metode_bayar LIKE "%bcava%" THEN jumlah_bayar ELSE 0 END) as transfer_total,
            SUM(CASE WHEN metode_bayar LIKE "%tripay%" OR metode_bayar LIKE "%DANA%" OR metode_bayar LIKE "%ewallet%" OR metode_bayar LIKE "%e-wallet%" OR metode_bayar LIKE "%gopay%" OR metode_bayar LIKE "%ovo%" OR metode_bayar LIKE "%qris%" OR metode_bayar LIKE "%shopeepay%" THEN jumlah_bayar ELSE 0 END) as ewallet_total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        // dd($daily);
        // Calculate total payment for today
        $totalToday = $payments->sum('jumlah_bayar');

        return view('/keuangan/pembayaran-daily', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'payments' => $payments,
            'totalToday' => $totalToday,
            'daily' => $daily,
        ]);
    }

    public function tambahPendapatan(Request $request)
    {
        // dd($jumlahTotal);
        $jumlahKas = Kas::latest()->value('jumlah_kas');
        // dd($jumlahKas);
        try {
            // Handle file upload for receipt
            $buktiPath = null;
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $buktiPath = $file->storeAs('bukti_pendapatan', $fileName, 'public');
            }


            // Create revenue record
            $pendapatan = new Pendapatan();
            $pendapatan->jumlah_pendapatan = $request->jumlah_pendapatan_raw;
            $pendapatan->jenis_pendapatan = $request->jenis_pendapatan;
            $pendapatan->deskripsi = $request->deskripsi;
            $pendapatan->tanggal = $request->tanggal;
            $pendapatan->bukti_pendapatan = $buktiPath;
            $pendapatan->metode_bayar = $request->metode_bayar;
            $pendapatan->user_id = auth()->id();
            $pendapatan->save();

            // Update Kas record
            $kas = new Kas();
            $kas->jumlah_kas = $pendapatan->jumlah_pendapatan + $jumlahKas;
            $kas->debit = $pendapatan->jumlah_pendapatan;
            $kas->kas_id = 1;
            $kas->keterangan = 'Pendapatan: ' . $request->jenis_pendapatan . ' - ' . $request->deskripsi;
            $kas->tanggal_kas = $request->tanggal;
            $kas->user_id = auth()->user()->id;
            $kas->status_id = 3;
            $kas->save();

            return redirect()->back()->with('success', 'Pendapatan berhasil ditambahkan');
        } catch (\Exception $e) {
            \Log::error('Error in tambahPendapatan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan pendapatan: ' . $e->getMessage());
        }
    }

    public function nonLangganan(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Build query for payments with relationships
        $query = Pendapatan::with(['user'])
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($search) {
            $query->where('jenis_pendapatan', 'like', '%' . $search . '%')
                ->orWhere('deskripsi', 'like', '%' . $search . '%');
        }

        // Apply date range filter
        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        $pendapatan = $query->paginate(10);

        $jumlah = Pendapatan::sum('jumlah_pendapatan');
        $todayRevenue = Pendapatan::whereDate('tanggal', Carbon::today())->sum('jumlah_pendapatan');
        $monthly = Pendapatan::whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('jumlah_pendapatan');

        // Get all payment methods for filter dropdown
        $cashCount = Pendapatan::where('metode_bayar', 'like', '%cash%')
            ->orWhere('metode_bayar', 'like', '%tunai%')
            ->count();
        $transferCount = Pendapatan::where('metode_bayar', 'like', '%transfer%')
            ->orWhere('metode_bayar', 'like', '%bank%')
            ->orWhere('metode_bayar', 'like', '%transfer bank%')
            ->count();
        $ewalletCount = Pendapatan::where('metode_bayar', 'like', '%ewallet%')
            ->orWhere('metode_bayar', 'like', '%e-wallet%')
            ->orWhere('metode_bayar', 'like', '%gopay%')
            ->orWhere('metode_bayar', 'like', '%ovo%')
            ->orWhere('metode_bayar', 'like', '%dana%')
            ->orWhere('metode_bayar', 'like', '%qris%')
            ->count();

        $metode = Metode::all();

        return view('/keuangan/non-langganan',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'pendapatan' => $pendapatan,
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'metode' => $metode,
            'jumlah' => $jumlah,
            'jumlahDaily' => $todayRevenue,
            'jumlahMonthly' => $monthly,
            'cashCount' => $cashCount,
            'transferCount' => $transferCount,
            'ewalletCount' => $ewalletCount,
        ]);
    }

    public function searchNonLangganan(Request $request)
    {
        try {
            $search = $request->get('search');
            $startDate = $request->get('start_date');

            $query = Pendapatan::with(['user'])
                ->orderBy('created_at', 'desc');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('jenis_pendapatan', 'like', '%' . $search . '%')
                      ->orWhere('deskripsi', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      });
                });
            }

            if ($startDate) {
                $query->whereDate('tanggal', '=', $startDate);
            }

            $pendapatan = $query->paginate(10);

            $html = view('keuangan.partials.pendapatan-table', compact('pendapatan'))->render();

            return response()->json([
                'html' => $html,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
    public function globalPendapatan()
    {
        // $co = Customer::all();
        // dd($co);

        // Ambil semua data pembayaran yang status invoice-nya "Sudah Bayar"
        $pembayaran = Pembayaran::with(['invoice.customer', 'invoice.status', 'invoice.paket'])
            ->whereHas('invoice.status', function ($q) {
                $q->where('nama_status', 'Sudah Bayar');
            })
        ->get();

        $allPembayaran = Pembayaran::with([
            'invoice.customer',
            'invoice.status',
            'invoice.paket'
        ])->whereHas('invoice.status', function ($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->get();

        // dd($pembayaran);
        // Susun data per customer dan per bulan
        $data = [];

        foreach ($pembayaran as $p) {
            $customer = $p->invoice->customer->nama_customer;
            $alamat = $p->invoice->customer->alamat;
            $paket = $p->invoice->paket ? $p->invoice->paket->nama_paket : 'Tidak Diketahui';
            // dd($total);
            $month = Carbon::parse($p->tanggal_bayar)->format('n'); // 1 - 12

            if (!isset($data[$customer])) {
                $data[$customer] = array_fill(1, 12, 0); // Inisialisasi 12 bulan
                $data[$customer]['total'] = 0; // Inisialisasi total
            }
            $data[$customer]['alamat'] = $alamat;
            $data[$customer]['paket'] = $paket;
            $data[$customer]['total'] += $p->jumlah_bayar; 
            $data[$customer][$month] += $p->jumlah_bayar;
        }
        // Ubah jadi array untuk dikirim ke view
        // dd($data);
        $formatted = [];
        foreach ($data as $customer => $months) {
            $formatted[] = [
            'nama' => $customer,
            'alamat' => $months['alamat'] ?? 'Tidak Diketahui', 
            'bulan' => array_filter($months, fn($v, $k) => is_numeric($k), ARRAY_FILTER_USE_BOTH),
            'paket' => $months['paket'] ?? 'Tidak Diketahui',
            'total' => $months['total'] ?? 0
            ];
        }

        // Convert array to collection and paginate
        $perPage = 10; // Number of items per page
        $currentPage = request()->get('page', 1);
        $collection = collect($formatted);
        
        $paginatedFormatted = new \Illuminate\Pagination\LengthAwarePaginator(
            $collection->forPage($currentPage, $perPage),
            $collection->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        // Replace original $formatted with paginated version
        $formatted = $paginatedFormatted;

        $bulanTotals = array_fill(1, 12, 0); // Inisialisasi
        
        foreach ($allPembayaran as $p) {
            $month = \Carbon\Carbon::parse($p->tanggal_bayar)->format('n');
            $bulanTotals[$month] += $p->jumlah_bayar;
        }

        $totalPendapatan = array_sum($bulanTotals);
        // dd($totalPendapatan);

        // dd($bulanTotals);
        // Tambahkan total per bulan ke array
        $nonLangganan = Pendapatan::with(['user'])
            ->orderBy('tanggal', 'desc')
            ->get();

        $nonFormatted = [];

        foreach ($nonLangganan as $pendapatan) {
            $user = $pendapatan->jenis_pendapatan ? $pendapatan->jenis_pendapatan : 'Tidak Diketahui';
            $admin = $pendapatan->user_id;
            $a = $pendapatan->user->roles->name ?? 'Tidak Diketahui';
            $month = Carbon::parse($pendapatan->tanggal)->format('n'); // 1 - 12

            if (!isset($nonFormatted[$user])) {
                $nonFormatted[$user] = array_fill(1, 12, 0); // Inisialisasi 12 bulan
            }
            $nonFormatted[$user][$month] += $pendapatan->jumlah_pendapatan;
        }
        // Ubah jadi array untuk dikirim ke view
        $nonFormattedData = [];
        foreach ($nonFormatted as $user => $months) {
            $nonFormattedData[] = [
                'nama' => $user,
                'a' => $a,
                'bulan' => $months // bulan 1-12 => jumlah pendapatan
            ];
        }
        // dd($nonFormattedData);
        $pakets = Paket::all();

        return view('/keuangan/pendapatan-global',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'pembayaran' => $pembayaran,
            'formatted' => $formatted,
            'nonFormattedData' => $nonFormattedData,
            'pakets' => $pakets,
            'bulanTotals' => $bulanTotals,
            'totalPendapatan' => $totalPendapatan,
        ]);
    }

    public function requestPembayaran(Request $request, $id)
    {
        $invoice = Invoice::with('customer', 'paket')->findOrFail($id);

        DB::beginTransaction();
        try {
            $pilihan = $request->input('bayar', []); // ["tagihan","tambahan","tunggakan"]
            $gunakanSaldo = $request->has('saldo');  // true kalau checkbox saldo dicentang

            // Nominal yang akan dibayar sesuai pilihan
            $bayarTagihan   = in_array('tagihan', $pilihan)   ? $invoice->tagihan   : 0;
            $bayarTambahan  = in_array('tambahan', $pilihan)  ? $invoice->tambahan  : 0;
            $bayarTunggakan = in_array('tunggakan', $pilihan) ? $invoice->tunggakan : 0;

            $totalDipilih = $bayarTagihan + $bayarTambahan + $bayarTunggakan;

            // Gunakan saldo kalau dicentang
            $saldoTerpakai = 0;
            $saldoBaru = $invoice->saldo;
            if ($gunakanSaldo && $invoice->saldo > 0) {
                if ($invoice->saldo >= $totalDipilih) {
                    $saldoTerpakai = $totalDipilih;
                    $totalDipilih  = 0;
                    $saldoBaru     = $invoice->saldo - $saldoTerpakai;
                } else {
                    $saldoTerpakai = $invoice->saldo;
                    $totalDipilih  -= $invoice->saldo;
                    $saldoBaru     = 0;
                }
            }

            // Tambahkan pembayaran manual dari form
            $jumlahBayarManual = (int) $request->input('jumlah_bayar', 0);

            if ($jumlahBayarManual >= $totalDipilih) {
                $jumlahBayarManual -= $totalDipilih;
                $totalDipilih = 0;
            } else {
                $totalDipilih -= $jumlahBayarManual;
                $jumlahBayarManual = 0;
            }

            // Total pembayaran hari ini
            $jumlahBayar = $saldoTerpakai + (int)$request->input('jumlah_bayar', 0);

            // Upload bukti pembayaran
            $buktiPath = null;
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $buktiPath = $file->storeAs('bukti_pendapatan', $fileName, 'public');
            }

            // ================================
            // Buat keterangan dinamis
            // ================================
            $keteranganArr = [];
            if ($bayarTagihan > 0)   $keteranganArr[] = "Tagihan Langganan";
            if ($bayarTambahan > 0)  $keteranganArr[] = "Biaya Tambahan";
            if ($bayarTunggakan > 0) $keteranganArr[] = "Tunggakan";
            if ($saldoTerpakai > 0)  $keteranganArr[] = "menggunakan saldo";
            if ($saldoBaru > 0)      $keteranganArr[] = "menyisakan saldo";

            $keteranganPembayaran = "Pembayaran " . implode(", ", $keteranganArr) .
                " dari " . auth()->user()->name .
                " untuk pelanggan " . $invoice->customer->nama_customer .
                " PIC : " . ($invoice->customer->agen->name ?? '-');


            // Simpan pembayaran
            $pembayaran = Pembayaran::create([
                'invoice_id'    => $invoice->id,
                'jumlah_bayar'  => $jumlahBayar,
                'tanggal_bayar' => now(),
                'metode_bayar'  => $request->metode_id,
                'keterangan'    => $keteranganPembayaran,
                'status_id'     => 8,
                'user_id'       => auth()->id(),
                'bukti_bayar'   => $buktiPath,
                'saldo'         => $saldoBaru,
            ]);

            // Notifikasi
            $chat = new ChatServices();
            $chat->pembayaranBerhasil($invoice->customer->no_hp, $pembayaran);

            // ================================
            // Update Invoice
            // ================================
            $newTagihan   = in_array('tagihan', $pilihan)   ? 0 : $invoice->tagihan;
            $newTambahan  = in_array('tambahan', $pilihan)  ? 0 : $invoice->tambahan;
            $newTunggakan = in_array('tunggakan', $pilihan) ? 0 : $invoice->tunggakan;

            $statusInvoice = ($newTagihan == 0 && $newTambahan == 0 && $newTunggakan == 0)
                ? 8 : 7;

            $invoice->update([
                'tagihan'   => $newTagihan,
                'tambahan'  => $newTambahan,
                'tunggakan' => $newTunggakan,
                'saldo'     => $saldoBaru,
                'status_id' => $statusInvoice,
            ]);

            // ================================
            // Buat Invoice Bulan Depan
            // ================================
            if (in_array('tagihan', $pilihan) && $newTagihan == 0) {
                $customer = $invoice->customer;
                $tanggalJatuhTempoLama = Carbon::parse($invoice->jatuh_tempo);
                $tanggalAwal = $tanggalJatuhTempoLama->copy()->addMonthsNoOverflow()->startOfMonth();
                $tanggalJatuhTempo = $tanggalAwal->copy()->endOfMonth();

                $sudahAda = Invoice::where('customer_id', $invoice->customer_id)
                    ->whereMonth('jatuh_tempo', $tanggalJatuhTempo->month)
                    ->whereYear('jatuh_tempo', $tanggalJatuhTempo->year)
                    ->exists();

                if (!$sudahAda) {
                    Invoice::create([
                        'customer_id'     => $invoice->customer_id,
                        'paket_id'        => $customer->paket_id,
                        'tagihan'         => $customer->paket->harga,
                        'tambahan'        => $newTambahan,
                        'tunggakan'       => $newTunggakan,
                        'saldo'           => $saldoBaru,
                        'status_id'       => 7,
                        'created_at'      => $tanggalAwal,
                        'updated_at'      => $tanggalAwal,
                        'jatuh_tempo'     => $tanggalJatuhTempo,
                        'tanggal_blokir'  => $invoice->tanggal_blokir,
                    ]);
                }
            }

            // ================================
            // Catat ke kas
            // ================================
            Kas::create([
                'debit'         => $pembayaran->jumlah_bayar,
                'tanggal_kas'   => $pembayaran->tanggal_bayar,
                'keterangan'    => 'Pembayaran Dari ' . auth()->user()->name . ' Untuk Pelanggan ' . $pembayaran->invoice->customer->nama_customer . ' PIC : ' . ($invoice->customer->agen->name ?? '-'),
                'kas_id'        => 1,
                'user_id'       => auth()->id(),
                'status_id'     => 3,
                'customer_id' => $invoice->customer_id,
                'pengeluaran_id'=> null,
            ]);

            // ================================
            // Update Status Customer jika perlu
            // ================================
            // Jika customer diblokir, buka blokir
            if ($customer->status_id == 9) {
                try {
                    $mikrotik = new MikrotikServices();
                    $client = MikrotikServices::connect($customer->router);
                    $mikrotik->unblokUser($client, $customer->usersecret, $customer->paket->paket_name);
                    $mikrotik->removeActiveConnections($client, $customer->usersecret);

                    $customer->update(['status_id' => 3]);

                    Log::info('Customer ' . $customer->nama_customer . 'berhasil di unblock', ['customer_id' => $customer->id]);
                } catch (Exception $e) {
                    Log::error('Failed to unblock customer', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Catat Log Aktivitas
            activity('keuangan')
                ->causedBy(auth()->user())
                ->performedOn($pembayaran)
                ->log('Pembayaran dari admin keuangan ' . auth()->user()->name . ' untuk pelanggan ' . $pembayaran->invoice->customer->nama_customer . ' dengan Jumlah Bayar ' . 'Rp ' . number_format($pembayaran->jumlah_bayar, 0, ',', '.'));

            DB::commit();

            return redirect()->back()->with('success', 'Pembayaran berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Gagal menyimpan pembayaran: ' . $e->getMessage() . ' pada line ' . $e->getLine());
            return redirect()->back()->with('error', 'Gagal menyimpan pembayaran: ' . $e->getMessage());
        }
    }


    public function agen(Request $request)
    {
        $query = User::whereIn('roles_id', [6, 7])->withCount('customer');
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('alamat', 'LIKE', "%{$search}%")
                  ->orWhere('no_hp', 'LIKE', "%{$search}%");
            });
        }

        $agen = $query->paginate(10);

        // If this is an AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $agen,
                'html' => view('keuangan.partials.agen-table-rows', compact('agen'))->render()
            ]);
        }

        return view('/keuangan/data-agen',[
            'users' => Auth::user(),
            'roles' => Auth::user()->roles,
            'agen' => $agen,
        ]);
    }

    public function searchAgen(Request $request)
    {
        $query = User::where('roles_id', 6)->withCount('customer');

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('alamat', 'LIKE', "%{$search}%")
                  ->orWhere('no_hp', 'LIKE', "%{$search}%");
            });
        }

        $agen = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $agen->items(),
            'pagination' => [
                'current_page' => $agen->currentPage(),
                'last_page' => $agen->lastPage(),
                'per_page' => $agen->perPage(),
                'total' => $agen->total(),
            ]
        ]);
    }

    public function pelangganAgen(Request $request, $id)
    {
        // Get agen info
        $agen = User::findOrFail($id);

        // Bulan saat ini
        $currentMonth = now()->format('m');
        $filterMonth = $request->get('month', $currentMonth);

        // Ambil hanya 1 invoice terakhir per customer (subquery)
        $latestInvoicesQuery = Invoice::select('invoice.*')
            ->join(DB::raw('(
                SELECT customer_id, MAX(jatuh_tempo) as latest_jatuh_tempo 
                FROM invoice 
                WHERE MONTH(jatuh_tempo) = ' . intval($filterMonth) . ' 
                AND YEAR(jatuh_tempo) = ' . date('Y') . '
                GROUP BY customer_id
            ) as latest'), function ($join) {
                $join->on('invoice.customer_id', '=', 'latest.customer_id')
                    ->on('invoice.jatuh_tempo', '=', 'latest.latest_jatuh_tempo');
            })
            ->with(['customer.paket', 'status', 'pembayaran.user'])
            ->whereHas('customer', function ($q) use ($id) {
                $q->where('agen_id', $id)->whereIn('status_id', [3, 9]);
            });


        // Filter bulan
        if ($filterMonth !== 'all') {
            $latestInvoicesQuery->whereRaw("MONTH(jatuh_tempo) = ?", [intval($filterMonth)]);
        }

        // Filter status
        $filterStatus = $request->get('status');
        if ($filterStatus) {
            if ($filterStatus == 'Sudah Bayar') {
                $latestInvoicesQuery->whereHas('status', fn($q) => $q->where('nama_status', 'Sudah Bayar'));
            } elseif ($filterStatus == 'Belum Bayar') {
                $latestInvoicesQuery->whereHas('status', fn($q) => $q->where('nama_status', 'Belum Bayar'));
            }
        }

        $invoices = (clone $latestInvoicesQuery)->withMax('pembayaran', 'tanggal_bayar')->orderByDesc('pembayaran_max_tanggal_bayar')->paginate(10);

        // Hitung total semua invoice sesuai filter
        $allInvoices = (clone $latestInvoicesQuery)->get();

        $totalPaid = $allInvoices->where('status.nama_status', 'Sudah Bayar')->sum('tagihan');
        $totalUnpaid = $allInvoices->where('status.nama_status', 'Belum Bayar')->sum('tagihan');
        $totalAmount = $allInvoices->sum('tagihan');

        // Data untuk view
        $monthNames = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $currentMonthNum = now()->format('m');
        $currentMonthName = $monthNames[$currentMonthNum];

        return view('keuangan.data-pelanggan-agen', [
            'users' => Auth::user(),
            'roles' => Auth::user()->roles,
            'invoices' => $invoices,
            'agen' => $agen,
            'totalPaid' => $totalPaid,
            'totalUnpaid' => $totalUnpaid,
            'totalAmount' => $totalAmount,
            'currentMonth' => $currentMonth,
            'filterMonth' => $filterMonth,
            'filterStatus' => $filterStatus,
            'monthNames' => $monthNames,
            'currentMonthNum' => $currentMonthNum,
            'currentMonthName' => $currentMonthName,
        ]);
    }


    public function laporan(Request $request)
    {
        return view('keuangan.laporan.laporan', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
        ]);
    }

    public function getLaporanData(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', 'all');

        // Get monthly data for the year
        $monthlyData = $this->getMonthlyFinancialData($year);

        // Get summary data
        $summary = $this->getFinancialSummary($year, $month);

        // Get chart data
        $chartData = $this->getChartData($year, $month);

        // Get table data
        $tableData = $this->getTableData($year, $month);

        return response()->json([
            'summary' => $summary,
            'charts' => $chartData,
            'tables' => $tableData
        ]);
    }

    private function getMonthlyFinancialData($year)
    {
        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            // Subscription revenue (Pembayaran)
            $subscriptionRevenue = Pembayaran::whereYear('tanggal_bayar', $year)
                ->whereMonth('tanggal_bayar', $month)
                ->sum('jumlah_bayar');

            // Non-subscription revenue (Pendapatan)
            $nonSubscriptionRevenue = Pendapatan::whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('jumlah_pendapatan');

            // Expenses (Pengeluaran)
            $expenses = Pengeluaran::whereYear('tanggal_pengeluaran', $year)
                ->whereMonth('tanggal_pengeluaran', $month)
                ->sum('jumlah_pengeluaran');

            $monthlyData[] = [
                'subscription' => (float) $subscriptionRevenue,
                'nonSubscription' => (float) $nonSubscriptionRevenue,
                'expenses' => (float) $expenses,
                'profitLoss' => (float) ($subscriptionRevenue + $nonSubscriptionRevenue - $expenses)
            ];
        }

        return $monthlyData;
    }

    private function getFinancialSummary($year, $month = 'all')
    {
        // Build queries with year filter
        $subscriptionQuery = Pembayaran::whereYear('tanggal_bayar', $year);
        $nonSubscriptionQuery = Pendapatan::whereYear('tanggal', $year);
        $expensesQuery = Pengeluaran::whereYear('tanggal_pengeluaran', $year);
        // Add month filter if specified
        if ($month !== 'all') {
            $subscriptionQuery->whereMonth('tanggal_bayar', $month);
            $nonSubscriptionQuery->whereMonth('tanggal', $month);
            $expensesQuery->whereMonth('tanggal_pengeluaran', $month);
        }

        // Current period totals
        $totalSubscription = $subscriptionQuery->sum('jumlah_bayar');
        $totalNonSubscription = $nonSubscriptionQuery->sum('jumlah_pendapatan');
        $totalExpenses = $expensesQuery->sum('jumlah_pengeluaran');

        // Previous year totals for growth calculation
        $prevYear = $year - 1;
        $prevTotalSubscription = Pembayaran::whereYear('tanggal_bayar', $prevYear)->sum('jumlah_bayar');
        $prevTotalNonSubscription = Pendapatan::whereYear('tanggal', $prevYear)->sum('jumlah_pendapatan');
        $prevTotalExpenses = Pengeluaran::whereYear('tanggal_pengeluaran', $prevYear)->sum('jumlah_pengeluaran');

        // Calculate growth percentages
        $subscriptionGrowth = $prevTotalSubscription > 0 ?
            round((($totalSubscription - $prevTotalSubscription) / $prevTotalSubscription) * 100, 1) : 0;
        $nonSubscriptionGrowth = $prevTotalNonSubscription > 0 ?
            round((($totalNonSubscription - $prevTotalNonSubscription) / $prevTotalNonSubscription) * 100, 1) : 0;
        $expensesGrowth = $prevTotalExpenses > 0 ?
            round((($totalExpenses - $prevTotalExpenses) / $prevTotalExpenses) * 100, 1) : 0;

        // Calculate monthly revenue differences for current year
        $currentMonth = date('n'); // Current month number
        $currentMonthRevenue = Pembayaran::whereYear('tanggal_bayar', $year)
            ->whereMonth('tanggal_bayar', $currentMonth)
            ->sum('jumlah_bayar') +
            Pendapatan::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $currentMonth)
            ->sum('jumlah_pendapatan');

        $prevMonth = $currentMonth > 1 ? $currentMonth - 1 : 12;
        $prevMonthYear = $currentMonth > 1 ? $year : $year - 1;
        $prevMonthRevenue = Pembayaran::whereYear('tanggal_bayar', $prevMonthYear)
            ->whereMonth('tanggal_bayar', $prevMonth)
            ->sum('jumlah_bayar') +
            Pendapatan::whereYear('tanggal', $prevMonthYear)
            ->whereMonth('tanggal', $prevMonth)
            ->sum('jumlah_pendapatan');

        // Calculate yearly revenue differences
        $currentYearRevenue = $totalSubscription + $totalNonSubscription;
        $prevYearRevenue = $prevTotalSubscription + $prevTotalNonSubscription;

        // Calculate profit/loss
        $currentProfit = $currentYearRevenue - $totalExpenses;
        $prevYearProfit = $prevYearRevenue - $prevTotalExpenses;

        // Calculate cash balance
        $totalKasDebit = Kas::whereYear('tanggal_kas', $year)->sum('debit');
        $totalKasKredit = Kas::whereYear('tanggal_kas', $year)->sum('kredit');
        $totalKasSaldo = $totalKasDebit - $totalKasKredit;

        // Calculate customer statistics
        $totalCustomers = Customer::where('status_id', 3)->count(); // Active customers

        // Determine month for customer statistics
        $customerMonth = $month !== 'all' ? $month : date('m');
        $customerYear = $year;

        // Build base query for invoices with month/year filter
        $invoiceBaseQuery = Invoice::whereYear('jatuh_tempo', $customerYear);
        if ($month !== 'all') {
            $invoiceBaseQuery->whereMonth('jatuh_tempo', $customerMonth);
        }

        // Total pendapatan dari invoice: sudah bayar + belum bayar + tunggakan
        $totalPendapatan = (clone $invoiceBaseQuery)
            ->whereIn('status_id', [7, 8])
            ->sum(DB::raw('tagihan + tambahan + COALESCE(tunggakan, 0)'));

        // Revenue from paid customers (status_id = 8)
        $pelangganLunas = (clone $invoiceBaseQuery)
            ->where('status_id', 8)
            ->sum(DB::raw('tagihan + tambahan + COALESCE(tunggakan, 0)'));

        // Revenue from unpaid customers (status_id = 7)
        $pelangganBelumLunas = (clone $invoiceBaseQuery)
            ->where('status_id', 7)
            ->sum(DB::raw('tagihan + tambahan + COALESCE(tunggakan, 0)'));

        // Count of paid customers for specified period
        $paidCustomers = (clone $invoiceBaseQuery)
            ->where('status_id', 8)
            ->distinct('customer_id')
            ->count();

        // Count of unpaid customers for specified period
        $unpaidCustomers = (clone $invoiceBaseQuery)
            ->where('status_id', 7)
            ->distinct('customer_id')
            ->count();

        return [
            'totalSubscription' => (float) $totalSubscription,
            'totalNonSubscription' => (float) $totalNonSubscription,
            'totalExpenses' => (float) $totalExpenses,
            'totalRevenue' => (float) $currentYearRevenue,
            'profitLoss' => (float) $currentProfit,
            'totalKasSaldo' => (float) $totalKasSaldo,
            'totalCustomers' => (int) $totalCustomers,
            'paidCustomers' => (int) $paidCustomers,
            'totalPendapatan' => (float) $totalPendapatan,
            'pelangganLunas' => (float) $pelangganLunas,
            'unpaidCustomers' => (int) $unpaidCustomers,
            'pelangganBelumLunas' => (float) $pelangganBelumLunas,
            'monthlyRevenueDifference' => (float) ($currentMonthRevenue - $prevMonthRevenue),
            'yearlyRevenueDifference' => (float) ($currentYearRevenue - $prevYearRevenue),
            'currentMonthRevenue' => (float) $currentMonthRevenue,
            'prevMonthRevenue' => (float) $prevMonthRevenue,
            'growth' => [
                'subscription' => $subscriptionGrowth,
                'nonSubscription' => $nonSubscriptionGrowth,
                'expenses' => $expensesGrowth,
                'profit' => $prevYearProfit > 0 ? round((($currentProfit - $prevYearProfit) / $prevYearProfit) * 100, 1) : 0
            ]
        ];
    }

    private function getChartData($year, $month = 'all')
    {
        $monthlyData = $this->getMonthlyFinancialData($year);

        // Extract data for charts
        $subscription = array_column($monthlyData, 'subscription');
        $nonSubscription = array_column($monthlyData, 'nonSubscription');
        $expenses = array_column($monthlyData, 'expenses');
        $profitLoss = array_column($monthlyData, 'profitLoss');

        // Cash flow data
        $kasQuery = Kas::whereYear('tanggal_kas', $year);
        $kreditQuery = Kas::whereYear('tanggal_kas', $year);

        if ($month !== 'all') {
            $kasQuery->whereMonth('tanggal_kas', $month);
            $kreditQuery->whereMonth('tanggal_kas', $month);
        }

        $totalDebit = $kasQuery->sum('debit');
        $totalKredit = $kreditQuery->sum('kredit');

        // RAB data
        $rabQuery = Rab::whereYear('created_at', $year);
        if ($month !== 'all') {
            $rabQuery->whereMonth('created_at', $month);
        }

        $rabData = $rabQuery->get();
        $rabCategories = [];
        $rabBudget = [];
        $rabRealization = [];

        foreach ($rabData as $rab) {
            $realizationQuery = Pengeluaran::where('rab_id', $rab->id);
            if ($month !== 'all') {
                $realizationQuery->whereMonth('tanggal_pengeluaran', $month);
            }
            $realization = $realizationQuery->sum('jumlah_pengeluaran');

            $rabCategories[] = substr($rab->keterangan, 0, 20) . '...';
            $rabBudget[] = (float) $rab->jumlah_anggaran;
            $rabRealization[] = (float) $realization;
        }

        return [
            'monthly' => [
                'subscription' => $subscription,
                'nonSubscription' => $nonSubscription,
                'expenses' => $expenses,
                'profitLoss' => $profitLoss
            ],
            'cashFlow' => [
                'debit' => (float) $totalDebit,
                'kredit' => (float) $totalKredit
            ],
            'rab' => [
                'categories' => $rabCategories,
                'budget' => $rabBudget,
                'realization' => $rabRealization
            ]
        ];
    }

    private function getTableData($year, $month = 'all')
    {
        // Monthly report data
        $monthlyData = $this->getMonthlyFinancialData($year);

        // Subscription revenue details (Pembayaran)
        $subscriptionQuery = Pembayaran::with(['invoice.customer', 'invoice.paket', 'user'])
            ->whereYear('tanggal_bayar', $year);

        if ($month !== 'all') {
            $subscriptionQuery->whereMonth('tanggal_bayar', $month);
        }

        $subscriptionData = $subscriptionQuery->orderBy('tanggal_bayar', 'desc')
            ->limit(50)
            ->get();

        // Non-subscription revenue details
        $nonSubscriptionQuery = Pendapatan::with('user')
            ->whereYear('tanggal', $year);

        if ($month !== 'all') {
            $nonSubscriptionQuery->whereMonth('tanggal', $month);
        }

        $nonSubscriptionData = $nonSubscriptionQuery->orderBy('tanggal', 'desc')
            ->limit(50)
            ->get();

        // Expenses details
        $expensesQuery = Pengeluaran::with(['user', 'status'])
            ->whereYear('tanggal_pengeluaran', $year);

        if ($month !== 'all') {
            $expensesQuery->whereMonth('tanggal_pengeluaran', $month);
        }

        $expensesData = $expensesQuery->orderBy('tanggal_pengeluaran', 'desc')
            ->limit(50)
            ->get();

        // Cash flow details
        $kasQuery = Kas::with(['user', 'kas'])
            ->whereYear('tanggal_kas', $year);

        if ($month !== 'all') {
            $kasQuery->whereMonth('tanggal_kas', $month);
        }

        $kasData = $kasQuery->orderBy('tanggal_kas', 'desc')
            ->limit(100)
            ->get();

        // RAB details with realization
        $rabData = Rab::whereYear('created_at', $year)->get()->map(function ($rab) {
            $realization = Pengeluaran::where('rab_id', $rab->id)->sum('jumlah_pengeluaran');
            $rab->realization = $realization;
            return $rab;
        });

        return [
            'monthly' => $monthlyData,
            'subscription' => $subscriptionData,
            'nonSubscription' => $nonSubscriptionData,
            'expenses' => $expensesData,
            'kas' => $kasData,
            'rab' => $rabData
        ];
    }

    private function buildMonthlyReport($pembayaran, $pendapatan, $pengeluaran)
    {
        $bulanNama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $laporan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $pendapatanLangganan = $pembayaran->get($bulan, 0);
            $pendapatanLain = $pendapatan->get($bulan, 0);
            $pengeluaranBulan = $pengeluaran->get($bulan, 0);
            $pendapatanTotal = $pendapatanLangganan + $pendapatanLain;
            $labaRugi = $pendapatanTotal - $pengeluaranBulan;

            $laporan[] = [
                'bulan' => $bulanNama[$bulan],
                'pendapatan_langganan' => $pendapatanLangganan,
                'pendapatan_nonlangganan' => $pendapatanLain,
                'pendapatan' => $pendapatanTotal,
                'pendapatan_total' => $pendapatanTotal,
                'pengeluaran' => $pengeluaranBulan,
                'laba_rugi' => $labaRugi,
                'status' => $labaRugi >= 0 ? 'Laba' : 'Rugi',
            ];
        }

        return $laporan;
    }

    private function getRabData($year)
    {
        return Rab::whereYear('created_at', $year)->get()->map(function ($rab) {
            $expenses = Pengeluaran::where('rab_id', $rab->id)->sum('jumlah_pengeluaran');
            return [
                'nama' => $rab->keterangan,
                'anggaran' => $rab->jumlah_anggaran,
                'realisasi' => $expenses,
                'sisa' => $rab->jumlah_anggaran - $expenses,
            ];
        })->toArray();
    }



}
