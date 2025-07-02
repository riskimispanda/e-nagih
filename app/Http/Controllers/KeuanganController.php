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
use App\Models\Perusahaan;

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
        // dd($bulan);

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

        // Apply month filter
        if ($bulan) {
            $query->whereMonth('jatuh_tempo', $bulan);
        }

        // Apply date range filter
        if ($startDate && $endDate) {
            $query->whereBetween('jatuh_tempo', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }
        

        $invoices = $query->paginate(10);

        // Calculate revenue statistics
        $totalRevenue = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->sum('tagihan') + Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->sum('tambahan');

        $monthlyRevenue = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->whereMonth('created_at', Carbon::now()->month)
          ->whereYear('created_at', Carbon::now()->year)
          ->sum('tagihan') + Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Sudah Bayar');
        })->whereMonth('created_at', Carbon::now()->month)
          ->whereYear('created_at', Carbon::now()->year)
          ->sum('tambahan');

        $pendingRevenue = Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Belum Bayar');
        })->sum('tagihan') + Invoice::whereHas('status', function($q) {
            $q->where('nama_status', 'Belum Bayar');
        })->sum('tambahan');

        $totalInvoices = Invoice::where('status_id', 7)->count();

        // Get all status options for filter dropdown
        $statusOptions = Status::whereIn('id', [7, 8])->get();

        $metode = Metode::whereNot('id', 3)->get();
        $pendapatan = Pendapatan::paginate(5);

        return view('/keuangan/data-pendapatan',[
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
            'metode' => $metode,
            'pendapatan' => $pendapatan,
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

        $payments = $query->paginate(15);

        // Calculate payment statistics

        $invoicePay = Pembayaran::latest()->get();

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
        })->count(); // Kosongkan dulu sesuai permintaan
        $transferPayments = Pembayaran::where(function($q) {
            $q->where('metode_bayar', 'like', '%transfer%')
              ->orWhere('metode_bayar', 'like', '%bank%')
              ->orWhere('metode_bayar', 'like', '%briva%')
              ->orWhere('metode_bayar', 'like', '%bniva%')
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
              ->orWhere('metode_bayar', 'like', '%qris%');
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
            SUM(CASE WHEN metode_bayar LIKE "%transfer%" OR metode_bayar LIKE "%bniva%" OR metode_bayar LIKE "%briva%" THEN jumlah_bayar ELSE 0 END) as transfer_total,
            SUM(CASE WHEN metode_bayar LIKE "%tripay%" OR metode_bayar LIKE "%DANA%" OR metode_bayar LIKE "%ewallet%" OR metode_bayar LIKE "%e-wallet%" OR metode_bayar LIKE "%gopay%" OR metode_bayar LIKE "%ovo%" OR metode_bayar LIKE "%qris%" THEN jumlah_bayar ELSE 0 END) as ewallet_total')
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
        $invoice = Invoice::with('customer')->findOrFail($id);

        // Hitung sisa saldo (jika lebih bayar)
        $sisa = ($request->jumlah_bayar ?? 0) - ($invoice->tagihan + $invoice->tambahan);

        // Upload bukti pembayaran jika ada
        $buktiPath = null;
        if ($request->hasFile('bukti_pembayaran')) {
            $file = $request->file('bukti_pembayaran');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $buktiPath = $file->storeAs('bukti_pendapatan', $fileName, 'public');
        }

        // Buat catatan pembayaran (dengan status 1 = menunggu konfirmasi)
        Pembayaran::create([
            'invoice_id'    => $invoice->id,
            'jumlah_bayar'  => $request->jumlah_bayar,
            'tanggal_bayar' => now(),
            'metode_bayar'  => $request->metode_id,
            'keterangan'    => 'Permintaan Pembayaran langganan ' . $invoice->customer->nama_customer . ' dari ' . auth()->user()->name,
            'status_id'     => 1, // status menunggu konfirmasi
            'user_id'       => auth()->id(),
            'bukti_bayar'   => $buktiPath,
            'saldo'         => $sisa > 0 ? $sisa : 0, // hanya simpan jika lebih bayar
        ]);

        // Update status invoice menjadi "menunggu konfirmasi"
        $invoice->update(['status_id' => 1]);

        return redirect()->back()->with('success', 'Permintaan pembayaran berhasil dikirim untuk ' . $invoice->customer->nama_customer);
    }


}
