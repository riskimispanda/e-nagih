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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerAgen;


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

        // Build query for invoices with relationships - INCLUDE SOFT DELETED CUSTOMERS
        $query = Invoice::with(['customer' => function ($query) {
            $query->withTrashed(); // Include soft deleted customers
        }, 'paket', 'status'])
            ->orderBy('created_at', 'desc')
            ->whereIn('status_id', [1, 7]);

        // Apply search filter - INCLUDE SOFT DELETED CUSTOMERS IN SEARCH
        if ($search) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->withTrashed() // Include soft deleted customers in search
                    ->where('nama_customer', 'like', '%' . $search . '%');
            })->orWhereHas('paket', function ($q) use ($search) {
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

        // Ambil semua data untuk kalkulasi - INCLUDE SOFT DELETED CUSTOMERS
        $allInvoices = $perki->with(['customer' => function ($query) {
            $query->withTrashed();
        }, 'paket'])->get();

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
                // Count total invoices with status_id = 7 (Belum Bayar) - HANYA CUSTOMER AKTIF
                if ($invoice->status_id == 7 && $invoice->customer && !$invoice->customer->trashed()) {
                    $totalInvoices++;
                }

                // Calculate based on status_id
                if ($invoice->status_id == 8) { // Sudah Bayar
                    $totalRevenue += ($invoice->tagihan + $invoice->tambahan - $invoice->tunggakan);
                } elseif ($invoice->status_id == 7 && $invoice->customer && !$invoice->customer->trashed()) {
                    // ✅ Belum Bayar - HANYA untuk customer AKTIF (tidak soft delete)
                    $pendingRevenue += ($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan);
                }
            }
        } else {
            // When no month filter (Semua Bulan), calculate from all invoices

            // ✅ Total Revenue - termasuk semua customer (aktif + soft delete)
            $totalRevenue = Invoice::where('status_id', 8)
                ->whereHas('customer', function ($q) {
                    $q->withTrashed(); // Include semua customer untuk revenue
                })
                ->sum('tagihan') +
                Invoice::where('status_id', 8)
                ->whereHas('customer', function ($q) {
                    $q->withTrashed();
                })
                ->sum('tambahan') -
                Invoice::where('status_id', 8)
                ->whereHas('customer', function ($q) {
                    $q->withTrashed();
                })
                ->sum('tunggakan');

            // ✅ Pending Revenue - HANYA untuk customer AKTIF (tidak soft delete)
            $pendingRevenue = Invoice::where('status_id', 7)
                ->whereHas('customer', function ($q) {
                $q->whereNull('deleted_at');
            })
                ->selectRaw('SUM(tagihan + tambahan + tunggakan - saldo) as total')
                ->value('total');


            // ✅ Total Invoices - HANYA untuk customer AKTIF yang belum bayar
            $totalInvoices = Invoice::where('status_id', 7)
                ->whereHas('customer', function ($q) {
                    $q->whereNull('deleted_at'); // ❌ Hanya customer aktif
                })
                ->count();
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
        $pembayaran = Pembayaran::where('status_id', 8)
            ->whereHas('invoice.customer', function ($query) {
                $query->withTrashed(); // Include soft deleted customers
            })
            ->sum('jumlah_bayar');

        $customerCountQuery = clone $query;

        // Hitung jumlah unique customer dari invoice yang difilter
        $jumlahCustomer = $customerCountQuery
            ->distinct('customer_id')
            ->count('customer_id');

        return view('keuangan.data-pendapatan', [
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
            'tes' => $tes,
            'jumlah_customer' => $jumlahCustomer
        ]);
    }

    public function getAjaxData(Request $request)
    {
        try {
            // Get filter parameters dengan default values
            $search = $request->get('search', '');
            $status = $request->get('status', '');
            $startDate = $request->get('start_date', '');
            $endDate = $request->get('end_date', '');
            $bulan = $request->get('bulan', '');
            $perPage = $request->get('per_page', 25);
            $page = $request->get('page', 1);

            Log::info('AJAX Request Parameters:', [
                'search' => $search,
                'status' => $status,
                'bulan' => $bulan,
                'perPage' => $perPage,
                'page' => $page
            ]);

            // PERBAIKAN: Gunakan withTrashed() untuk customer relationship
            $query = Invoice::with([
                'customer' => function ($query) {
                    $query->withTrashed(); // ✅ INI YANG PERLU DITAMBAHKAN
                },
                'paket',
                'status'
            ])
                ->orderBy('created_at', 'desc')
                ->whereIn('status_id', [1, 7]);

            // Apply search filter - PERBAIKI: Include withTrashed() di search juga
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->withTrashed() // ✅ INI JUGA
                            ->where('nama_customer', 'like', '%' . $search . '%')
                            ->orWhere('no_hp', 'like', '%' . $search . '%')
                            ->orWhere('alamat', 'like', '%' . $search . '%');
                    })->orWhereHas('paket', function ($paketQuery) use ($search) {
                        $paketQuery->where('nama_paket', 'like', '%' . $search . '%');
                    });
                });
            }

            // Apply status filter
            if (!empty($status)) {
                $query->where('status_id', $status);
            }

            // Apply month filter
            if (!empty($bulan) && $bulan !== '') {
                $query->whereMonth('jatuh_tempo', $bulan);
            }

            // Apply date range filter
            if (!empty($startDate) && !empty($endDate)) {
                $query->whereBetween('jatuh_tempo', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }

            // Get paginated results
            $invoices = [];
            $paginationData = [];

            if ($perPage === 'all') {
                $invoices = $query->get();
                $paginationData = [
                    'total' => $invoices->count(),
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $invoices->count(),
                    'from' => 1,
                    'to' => $invoices->count(),
                ];
            } else {
                \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($page) {
                    return $page;
                });

                $invoicesPaginated = $query->paginate((int)$perPage);
                $invoices = $invoicesPaginated->items();
                $paginationData = [
                    'current_page' => $invoicesPaginated->currentPage(),
                    'last_page' => $invoicesPaginated->lastPage(),
                    'per_page' => $invoicesPaginated->perPage(),
                    'total' => $invoicesPaginated->total(),
                    'from' => $invoicesPaginated->firstItem(),
                    'to' => $invoicesPaginated->lastItem(),
                ];
            }

            // ===== STATISTICS CALCULATION =====

            // PERBAIKAN: Untuk statistics, gunakan withTrashed() secara konsisten
            $totalRevenue = Pembayaran::sum('jumlah_bayar');

            // Monthly Revenue - Based on selected month or current month
            $currentMonthForRevenue = !empty($bulan) ? $bulan : date('n');
            $monthlyRevenue = Pembayaran::whereMonth('tanggal_bayar', $currentMonthForRevenue)
                ->whereYear('tanggal_bayar', Carbon::now()->year)
                ->sum('jumlah_bayar');

            // PERBAIKAN: Pending Revenue - Include soft deleted customers
            $pendingRevenueQuery = Invoice::where('status_id', 7)
                ->whereHas('customer', function ($q) {
                    $q->withTrashed(); // ✅ Include soft deleted
                });

            if (!empty($bulan)) {
                $pendingRevenueQuery->whereMonth('jatuh_tempo', $bulan)
                    ->whereYear('jatuh_tempo', Carbon::now()->year);
            }

            $pendingRevenue = $pendingRevenueQuery->get()
                ->sum(function ($invoice) {
                    return ($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan);
                });

            // PERBAIKAN: Total Invoices - Include soft deleted customers
            $totalInvoicesQuery = Invoice::where('status_id', 7)
                ->whereHas('customer', function ($q) {
                    $q->withTrashed(); // ✅ Include soft deleted
                });

            if (!empty($bulan)) {
                $totalInvoicesQuery->whereMonth('jatuh_tempo', $bulan)
                    ->whereYear('jatuh_tempo', Carbon::now()->year);
            }

            $totalInvoices = $totalInvoicesQuery->count();

            Log::info('Statistics Calculated:', [
                'totalRevenue' => $totalRevenue,
                'monthlyRevenue' => $monthlyRevenue,
                'pendingRevenue' => $pendingRevenue,
                'totalInvoices' => $totalInvoices
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'invoices' => $invoices,
                    'pagination' => $paginationData,
                    'statistics' => [
                        'totalRevenue' => $totalRevenue,
                        'monthlyRevenue' => $monthlyRevenue,
                        'pendingRevenue' => $pendingRevenue,
                        'totalInvoices' => $totalInvoices,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getAjaxData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
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
        $month = $request->get('month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Default to current month if no month filter is set (first time load)
        if ($month === null && !$request->has('month')) {
            $month = Carbon::now()->month;
        }

        $editPembayaran = Pembayaran::where('status_id', 1)->count();
        // Build query for payments with relationships
        $query = Pembayaran::with(['invoice.customer' => function ($query) {
            $query->withTrashed(); // Hanya tambahkan ini
        }, 'invoice.paket', 'status', 'user'])
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($search) {
            $query->whereHas('invoice.customer', function ($q) use ($search) {
                $q->withTrashed() // Hanya tambahkan ini
                    ->where('nama_customer', 'like', '%' . $search . '%');
            })->orWhereHas('invoice.paket', function ($q) use ($search) {
                $q->where('nama_paket', 'like', '%' . $search . '%');
            })->orWhere('metode_bayar', 'like', '%' . $search . '%');
        }

        // Apply payment method filter
        if ($metode) {
            $query->where('metode_bayar', $metode);
        }

        // Apply month filter - only if month is not empty
        if ($month && $month !== '' && $month !== null) {
            $query->whereMonth('tanggal_bayar', $month)
                  ->whereYear('tanggal_bayar', Carbon::now()->year);
        }

        // Apply date range filter (only if month filter is not set)
        if (!$month || $month === '' || $month === null) {
            if ($startDate) {
                $query->whereDate('tanggal_bayar', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('tanggal_bayar', '<=', $endDate);
            }
        }

        $payments = $query->paginate(10)->appends($request->all());

        // Calculate payment statistics based on current filter
        $invoicePay = $query->paginate(10);

        // Calculate totals based on filtered data
        $totalPayments = Pembayaran::sum('jumlah_bayar');

        $todayPayments = Pembayaran::whereDate('tanggal_bayar', Carbon::today())
            ->sum('jumlah_bayar');

        // Monthly payments based on current filter
        if ($month && $month !== '' && $month !== null) {
            $monthlyPayments = Pembayaran::whereMonth('tanggal_bayar', $month)
                ->whereYear('tanggal_bayar', Carbon::now()->year)
                ->sum('jumlah_bayar');
        } else {
            $monthlyPayments = Pembayaran::whereMonth('tanggal_bayar', Carbon::now()->month)
                ->whereYear('tanggal_bayar', Carbon::now()->year)
                ->sum('jumlah_bayar');
        }

        $totalTransactions = Pembayaran::whereMonth('created_at', $month)->whereYear('created_at', Carbon::now()->year)->count();

        // Get payment methods for filter dropdown
        $paymentMethods = Pembayaran::select('metode_bayar')
            ->distinct()
            ->whereNotNull('metode_bayar')
            ->pluck('metode_bayar');

        $cashPayments = Pembayaran::where(function ($q) {
            $q->where('metode_bayar', 'like', '%cash%')
                ->orWhere('metode_bayar', 'like', '%tunai%')
                ->orWhere('metode_bayar', 'like', '%Cash%'); // tambahan
        });

        if ($month && $month !== '' && $month !== null) {
            $cashPayments->whereMonth('tanggal_bayar', $month)
                ->whereYear('tanggal_bayar', Carbon::now()->year);
        }

        $cashPayments = $cashPayments->sum('jumlah_bayar');

        // Cash count with month filter - DIPERBAIKI
        $CashCount = Pembayaran::where(function ($q) {
            $q->where('metode_bayar', 'like', '%cash%')
                ->orWhere('metode_bayar', 'like', '%tunai%')
                ->orWhere('metode_bayar', 'like', '%Cash%'); // tambahan
        });

        if ($month && $month !== '' && $month !== null) {
            $CashCount->whereMonth('tanggal_bayar', $month)
                ->whereYear('tanggal_bayar', Carbon::now()->year);
        }

        $CashCount = $CashCount->count();

        // Transfer payments with month filter - DIPERBAIKI (GABUNGKAN SEMUA)
        $transferPayments = Pembayaran::where(function ($q) {
            $q->where('metode_bayar', 'like', '%transfer%')
                ->orWhere('metode_bayar', 'like', '%transfer bank%')
                ->orWhere('metode_bayar', 'like', '%Transfer%')
                ->orWhere('metode_bayar', 'like', '%bri virtual account%')
                ->orWhere('metode_bayar', 'like', '%bca virtual account%')
                ->orWhere('metode_bayar', 'like', '%briva%')
                ->orWhere('metode_bayar', 'like', '%bcava%')
                ->orWhere('metode_bayar', 'like', '%bniva%')
                ->orWhere('metode_bayar', 'like', '%BRI Virtual Account%')
                ->orWhere('metode_bayar', 'like', '%BCA Virtual Account%')
                ->orWhere('metode_bayar', 'like', '%alfamart%')
                ->orWhere('metode_bayar', 'like', '%indomaret%')
                ->orWhere('metode_bayar', 'like', '%ALFAMART%')
                ->orWhere('metode_bayar', 'like', '%INDOMARET%');
        });

        if ($month && $month !== '' && $month !== null) {
            $transferPayments->whereMonth('tanggal_bayar', $month)
                ->whereYear('tanggal_bayar', Carbon::now()->year);
        }

        $transferPayments = $transferPayments->sum('jumlah_bayar');

        // Transfer count with month filter - DIPERBAIKI (GABUNGKAN SEMUA)
        $transferCount = Pembayaran::where(function ($q) {
            $q->where('metode_bayar', 'like', '%transfer%')
                ->orWhere('metode_bayar', 'like', '%transfer bank%')
                ->orWhere('metode_bayar', 'like', '%Transfer%')
                ->orWhere('metode_bayar', 'like', '%bri virtual account%')
                ->orWhere('metode_bayar', 'like', '%bca virtual account%')
                ->orWhere('metode_bayar', 'like', '%briva%')
                ->orWhere('metode_bayar', 'like', '%bcava%')
                ->orWhere('metode_bayar', 'like', '%bniva%')
                ->orWhere('metode_bayar', 'like', '%BRI Virtual Account%')
                ->orWhere('metode_bayar', 'like', '%BCA Virtual Account%')
                ->orWhere('metode_bayar', 'like', '%alfamart%')
                ->orWhere('metode_bayar', 'like', '%indomaret%')
                ->orWhere('metode_bayar', 'like', '%ALFAMART%')
                ->orWhere('metode_bayar', 'like', '%INDOMARET%');
        });

        if ($month && $month !== '' && $month !== null) {
            $transferCount->whereMonth('tanggal_bayar', $month)
                ->whereYear('tanggal_bayar', Carbon::now()->year);
        }

        $transferCount = $transferCount->count();

        // Tripay count with month filter - DIPERBAIKI (HAPUS DANA dari sini)
        $tripay = Pembayaran::where(function ($q) {
            $q->where('metode_bayar', 'like', '%tripay%');
            // HAPUS: ->orWhere('metode_bayar', 'like', '%DANA%');
        });

        if ($month && $month !== '' && $month !== null) {
            $tripay->whereMonth('tanggal_bayar', $month)
                ->whereYear('tanggal_bayar', Carbon::now()->year);
        }

        $tripay = $tripay->count();

        // E-wallet payments with month filter - DIPERBAIKI (TAMBAH METODE)
        $ewalletPayments = Pembayaran::where(function ($q) {
            $q->where('metode_bayar', 'like', '%ewallet%')
                ->orWhere('metode_bayar', 'like', '%e-wallet%')
                ->orWhere('metode_bayar', 'like', '%gopay%')
                ->orWhere('metode_bayar', 'like', '%ovo%')
                ->orWhere('metode_bayar', 'like', '%dana%')
                ->orWhere('metode_bayar', 'like', '%qris%')
                ->orWhere('metode_bayar', 'like', '%qris2%')
                ->orWhere('metode_bayar', 'like', '%shopeepay%')
                ->orWhere('metode_bayar', 'like', '%shopee%')
                ->orWhere('metode_bayar', 'like', '%QRIS%')
                ->orWhere('metode_bayar', 'like', '%DANA%') // DANA pindah ke sini
                ->orWhere('metode_bayar', 'like', '%QRIS by ShopeePay%');
        });

        if ($month && $month !== '' && $month !== null) {
            $ewalletPayments->whereMonth('tanggal_bayar', $month)
                ->whereYear('tanggal_bayar', Carbon::now()->year);
        }

        $ewalletPayments = $ewalletPayments->sum('jumlah_bayar');

        // E-wallet count with month filter - DIPERBAIKI
        $ewalletCount = Pembayaran::where(function ($q) {
            $q->where('metode_bayar', 'like', '%ewallet%')
                ->orWhere('metode_bayar', 'like', '%e-wallet%')
                ->orWhere('metode_bayar', 'like', '%gopay%')
                ->orWhere('metode_bayar', 'like', '%ovo%')
                ->orWhere('metode_bayar', 'like', '%dana%')
                ->orWhere('metode_bayar', 'like', '%qris%')
                ->orWhere('metode_bayar', 'like', '%qris2%')
                ->orWhere('metode_bayar', 'like', '%shopeepay%')
                ->orWhere('metode_bayar', 'like', '%shopee%')
                ->orWhere('metode_bayar', 'like', '%QRIS%')
                ->orWhere('metode_bayar', 'like', '%DANA%') // DANA pindah ke sini
                ->orWhere('metode_bayar', 'like', '%QRIS by ShopeePay%');
        });

        if ($month && $month !== '' && $month !== null) {
            $ewalletCount->whereMonth('tanggal_bayar', $month)
                ->whereYear('tanggal_bayar', Carbon::now()->year);
        }

        $ewalletCount = $ewalletCount->count();
        $totalCustomer = Invoice::distinct('customer_id')
            ->where('status_id', 8)
            ->whereHas('pembayaran', function ($q) use ($month) {
                $q->whereMonth('tanggal_bayar', $month);
                $q->whereYear('tanggal_bayar', Carbon::now()->year);
            })
            ->count('customer_id');

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
            'month' => $month,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'tripay' => $tripay,
            'invoicePay' => $invoicePay,
            'editPembayaran' => $editPembayaran,
            'cashCount' => $CashCount,
            'transferCount' => $transferCount,
            'ewalletCount' => $ewalletCount,
            'totalCustomer' => $totalCustomer
        ]);
    }

    /**
     * Get payment data for AJAX requests with filtering, pagination, and statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPembayaranAjaxData(Request $request)
    {
        try {
            // Get filter parameters
            $filters = $this->extractFilters($request);

            // Build base query with relationships
            $query = $this->buildBaseQuery();

            // Apply all filters
            $this->applyFilters($query, $filters);

            // Get paginated results with consistent pagination
            $paginationResult = $this->getPaginatedResults($query, $filters['per_page'], $filters['page']);

            // Calculate statistics based on filtered data including payment methods with month filter
            $statistics = $this->calculateStatistics(clone $query, $filters['month']);

            return response()->json([
                'success' => true,
                'data' => [
                    'payments' => $paginationResult['payments'],
                    'pagination' => $paginationResult['pagination'],
                    'statistics' => $statistics
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract and validate filter parameters from request
     */
    private function extractFilters(Request $request): array
    {
        return [
            'search' => $request->get('search'),
            'metode' => $request->get('metode'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'month' => $request->get('month'),
            'year' => $request->get('year', date('Y')),
            'per_page' => $request->get('per_page', 10),
            'page' => max(1, (int)$request->get('page', 1))
        ];
    }

    /**
     * Build base query with eager loading - INCLUDE SOFT DELETED CUSTOMERS
     */
    private function buildBaseQuery()
    {
        return Pembayaran::with([
            'invoice' => function ($query) {
                $query->with(['customer' => function ($q) {
                    $q->withTrashed(); // Include soft deleted customers
                }, 'paket']);
            },
            'status',
            'user'
        ])
            ->orderBy('created_at', 'desc');
    }

    /**
     * Apply all filters to the query - UPDATED FOR SOFT DELETED CUSTOMERS
     */
    private function applyFilters($query, array $filters): void
    {
        // Apply search filter - UPDATED untuk handle soft deleted customers
        if ($filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('invoice.customer', function ($subQ) use ($search) {
                    $subQ->withTrashed() // Include soft deleted customers dalam search
                        ->where('nama_customer', 'like', "%{$search}%");
                })->orWhereHas('invoice.paket', function ($subQ) use ($search) {
                    $subQ->where('nama_paket', 'like', "%{$search}%");
                })->orWhere('metode_bayar', 'like', "%{$search}%");
            });
        }

        // Apply payment method filter
        if ($filters['metode']) {
            $query->where('metode_bayar', $filters['metode']);
        }

        // Apply month filter
        if ($filters['month']) {
            $query->whereMonth('tanggal_bayar', $filters['month'])
                ->whereYear('tanggal_bayar', $filters['year']);
        }

        // Apply date range filter (only if month filter is not set)
        if (!$filters['month']) {
            if ($filters['start_date']) {
                $query->whereDate('tanggal_bayar', '>=', $filters['start_date']);
            }
            if ($filters['end_date']) {
                $query->whereDate('tanggal_bayar', '<=', $filters['end_date']);
            }
        }
    }

    /**
     * Get paginated results with consistent pagination handling
     */
    private function getPaginatedResults($query, $perPage, $page): array
    {
        if ($perPage === 'all') {
            $payments = $query->get();
            return [
                'payments' => $payments,
                'pagination' => [
                    'total' => $payments->count(),
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $payments->count(),
                    'from' => $payments->count() > 0 ? 1 : 0,
                    'to' => $payments->count(),
                ]
            ];
        }

        // Use consistent pagination with proper page resolver
        $perPageInt = max(1, (int)$perPage);
        $currentPage = max(1, $page);

        // Set current page resolver for consistent pagination
        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        // Get total count before pagination to ensure consistency
        $totalCount = $query->count();
        $lastPage = max(1, ceil($totalCount / $perPageInt));

        // Adjust current page if it exceeds last page
        if ($currentPage > $lastPage) {
            $currentPage = $lastPage;
            \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });
        }

        $paymentsPaginated = $query->paginate($perPageInt, ['*'], 'page', $currentPage);

        return [
            'payments' => $paymentsPaginated->items(),
            'pagination' => [
                'current_page' => $paymentsPaginated->currentPage(),
                'last_page' => $paymentsPaginated->lastPage(),
                'per_page' => $paymentsPaginated->perPage(),
                'total' => $paymentsPaginated->total(),
                'from' => $paymentsPaginated->firstItem() ?: 0,
                'to' => $paymentsPaginated->lastItem() ?: 0,
            ]
        ];
    }

    /**
     * Calculate payment statistics from filtered data - UPDATED untuk handle soft deleted customers
     */
    private function calculateStatistics($query, $month = null): array
    {
        $allPayments = $query->get();
        $today = Carbon::today();
        $currentMonth = Carbon::now();

        // Filter payments untuk bulan ini
        $monthlyPayments = $allPayments->filter(function ($payment) use ($currentMonth) {
            $paymentDate = Carbon::parse($payment->tanggal_bayar);
            return $paymentDate->month === $currentMonth->month &&
                $paymentDate->year === $currentMonth->year;
        });

        // Jika ada filter bulan spesifik, gunakan data dari monthlyPayments yang sudah difilter
        if ($month && $month !== '' && $month !== null) {
            $filteredMonthlyPayments = $monthlyPayments->filter(function ($payment) use ($month) {
                $paymentDate = Carbon::parse($payment->tanggal_bayar);
                return $paymentDate->month == $month;
            });

            return [
            'totalPayments' => $allPayments->sum('jumlah_bayar'),
            'todayPayments' => $allPayments->where('tanggal_bayar', $today->format('Y-m-d'))->sum('jumlah_bayar'),
                'monthlyPayments' => $filteredMonthlyPayments->sum('jumlah_bayar'),
            'totalTransactions' => $allPayments->count(),
            'month' => $month,

                // Payment method statistics - menggunakan filteredMonthlyPayments yang konsisten
                'cashPayments' => $this->calculatePaymentsByMethod($filteredMonthlyPayments, ['cash', 'tunai']),
                'cashCount' => $this->calculatePaymentsCountByMethod($filteredMonthlyPayments, ['cash', 'tunai']),

                'transferPayments' => $this->calculatePaymentsByMethod($filteredMonthlyPayments, [
                    'transfer',
                    'bank',
                    'briva',
                    'bniva',
                    'bcava',
                    'transfer bank',
                    'INDOMARET',
                    'ALFAMART',
                    'ALFAMIDI'
                ]),
                'transferCount' => $this->calculatePaymentsCountByMethod($filteredMonthlyPayments, [
                    'transfer',
                    'bank',
                    'briva',
                    'bniva',
                    'bcava',
                    'transfer bank',
                    'INDOMARET',
                    'ALFAMART',
                    'ALFAMIDI'
                ]),

                'ewalletPayments' => $this->calculatePaymentsByMethod($filteredMonthlyPayments, [
                    'ewallet',
                    'e-wallet',
                    'gopay',
                    'ovo',
                    'dana',
                    'qris',
                    'qris2',
                    'shopeepay'
                ]),
                'ewalletCount' => $this->calculatePaymentsCountByMethod($filteredMonthlyPayments, [
                    'ewallet',
                    'e-wallet',
                    'gopay',
                    'ovo',
                    'dana',
                    'qris',
                    'shopeepay'
                ]),

                'tripayCount' => $this->calculatePaymentsCountByMethod($filteredMonthlyPayments, ['tripay', 'DANA']),
            ];
        }

        // Untuk tanpa filter bulan (default current month)
        return [
            'totalPayments' => $allPayments->sum('jumlah_bayar'),
            'todayPayments' => $allPayments->where('tanggal_bayar', $today->format('Y-m-d'))->sum('jumlah_bayar'),
            'monthlyPayments' => $monthlyPayments->sum('jumlah_bayar'),
            'totalTransactions' => $allPayments->count(),
            'month' => $month,

            // Payment method statistics - menggunakan monthlyPayments yang konsisten
            'cashPayments' => $this->calculatePaymentsByMethod($monthlyPayments, ['cash', 'tunai']),
            'cashCount' => $this->calculatePaymentsCountByMethod($monthlyPayments, ['cash', 'tunai']),

            'transferPayments' => $this->calculatePaymentsByMethod($monthlyPayments, [
                'transfer',
                'bank',
                'briva',
                'bniva',
                'bcava',
                'transfer bank',
                'INDOMARET',
                'ALFAMART',
                'ALFAMIDI'
            ]),
            'transferCount' => $this->calculatePaymentsCountByMethod($monthlyPayments, [
                'transfer',
                'bank',
                'briva',
                'bniva',
                'bcava',
                'transfer bank',
                'INDOMARET',
                'ALFAMART',
                'ALFAMIDI'
            ]),

            'ewalletPayments' => $this->calculatePaymentsByMethod($monthlyPayments, [
                'ewallet',
                'e-wallet',
                'gopay',
                'ovo',
                'dana',
                'qris',
                'qris2',
                'shopeepay'
            ]),
            'ewalletCount' => $this->calculatePaymentsCountByMethod($monthlyPayments, [
                'ewallet',
                'e-wallet',
                'gopay',
                'ovo',
                'dana',
                'qris',
                'shopeepay'
            ]),

            'tripayCount' => $this->calculatePaymentsCountByMethod($monthlyPayments, ['tripay', 'DANA']),
        ];
    }

    /**
     * Calculate payments sum by payment method keywords
     */
    private function calculatePaymentsByMethod($payments, $methods): float
    {
        return $payments->filter(function ($payment) use ($methods) {
            foreach ($methods as $method) {
                if (stripos($payment->metode_bayar, $method) !== false) {
                    return true;
                }
            }
            return false;
        })->sum('jumlah_bayar');
    }

    /**
     * Calculate payments count by payment method keywords
     */
    private function calculatePaymentsCountByMethod($payments, $methods): int
    {
        return $payments->filter(function ($payment) use ($methods) {
            foreach ($methods as $method) {
                if (stripos($payment->metode_bayar, $method) !== false) {
                    return true;
                }
            }
            return false;
        })->count();
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

            activity('Tambah Pendapatan')
                ->causedBy(auth()->user()->id)
                ->log(auth()->user()->name . ' Menambah Pendapatan Non Langganan');

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
        $month = $request->get('month');
        $year = $request->get('year', Carbon::now()->year); // Default to current year

        // Default to current month if no month filter is set (first time load)
        if ($month === null || $month === '') {
            $month = Carbon::now()->month;
        }

        // Build query for Pendapatan with relationships
        $baseQuery = Pendapatan::with(['user'])
            ->orderBy('created_at', 'desc');

        // Clone the base query for filtered statistics
        $filteredQuery = clone $baseQuery;

        // Apply month and year filter to the filtered query
        $filteredQuery->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year);

        // Apply search filter
        if ($search) {
            $filteredQuery->where(function ($q) use ($search) {
                $q->where('jenis_pendapatan', 'like', '%' . $search . '%')
                    ->orWhere('deskripsi', 'like', '%' . $search . '%');
            });
        }

        // Paginate the results from the filtered query
        $pendapatan = $filteredQuery->paginate(10);

        // Calculate statistics
        $jumlah = Pendapatan::sum('jumlah_pendapatan'); // Total pendapatan (tidak difilter, sesuai permintaan)

        // Query untuk pendapatan harian, terpengaruh oleh filter pencarian
        $todayRevenueQuery = Pendapatan::whereDate('tanggal', Carbon::today());
        if ($search) {
            $todayRevenueQuery->where(function ($q) use ($search) {
                $q->where('jenis_pendapatan', 'like', '%' . $search . '%')
                    ->orWhere('deskripsi', 'like', '%' . $search . '%');
            });
        }
        $todayRevenue = $todayRevenueQuery->sum('jumlah_pendapatan');

        // Clone the filtered query again to calculate filtered stats without pagination limits
        $statsQuery = clone $filteredQuery;
        $monthly = $statsQuery->sum('jumlah_pendapatan'); // Total pendapatan bulanan (difilter)

        // Payment method counts based on the filtered query
        $cashCount = (clone $statsQuery)->where(function ($q) {
            $q->where('metode_bayar', 'like', '%cash%')
            ->orWhere('metode_bayar', 'like', '%tunai%')
                ->orWhere('metode_bayar', 'like', '%Cash%');
        })->count();
        $transferCount = (clone $statsQuery)->where(function ($q) {
            $q->where('metode_bayar', 'like', '%transfer%')
            ->orWhere('metode_bayar', 'like', '%bank%')
                ->orWhere('metode_bayar', 'like', '%transfer bank%');
        })->count();
        $ewalletCount = (clone $statsQuery)->where(function ($q) {
            $q->where('metode_bayar', 'like', '%ewallet%')
            ->orWhere('metode_bayar', 'like', '%e-wallet%')
            ->orWhere('metode_bayar', 'like', '%gopay%')
            ->orWhere('metode_bayar', 'like', '%ovo%')
            ->orWhere('metode_bayar', 'like', '%dana%')
                ->orWhere('metode_bayar', 'like', '%qris%');
        })->count();

        $metode = Metode::all();

        return view('/keuangan/non-langganan',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'pendapatan' => $pendapatan,
            'search' => $search,
            'metode' => $metode,
            'jumlah' => $jumlah,
            'jumlahDaily' => $todayRevenue,
            'jumlahMonthly' => $monthly,
            'cashCount' => $cashCount,
            'transferCount' => $transferCount,
            'ewalletCount' => $ewalletCount,
            'month' => $month
        ]);
    }

    public function searchNonLangganan(Request $request)
    {
        try {
            $search = $request->get('search');
            $month = $request->get('month');
            $year = $request->get('year', Carbon::now()->year); // Default to current year

            $query = Pendapatan::with(['user'])
                ->orderBy('created_at', 'desc');

            // Apply month and year filter if 'month' is provided and not 'all'
            if ($month && $month !== 'all') {
                $query->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('jenis_pendapatan', 'like', '%' . $search . '%')
                      ->orWhere('deskripsi', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      });
                });
            }

            $pendapatan = $query->get();

            return response()->json([
                'success' => true,
                'data' => $pendapatan // Return data for DataTables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
    public function globalPendapatan(Request $request)
    {
        // Get filter parameters
        $year = $request->get('year', date('Y'));

        // Initialize monthly data arrays
        $monthlySubscription = array_fill(1, 12, 0);
        $monthlyNonSubscription = array_fill(1, 12, 0);
        $monthlyOperationalCost = array_fill(1, 12, 0);

        // Get subscription revenue (Pembayaran) by month
        $pembayaranData = Pembayaran::with(['invoice.customer', 'invoice.status', 'invoice.paket'])
            ->whereYear('tanggal_bayar', $year)
            ->selectRaw('MONTH(tanggal_bayar) as month, SUM(jumlah_bayar) as total')
            ->groupBy('month')
        ->get();

        foreach ($pembayaranData as $data) {
            $monthlySubscription[$data->month] = $data->total;
        }

        // Get non-subscription revenue (Pendapatan) by month
        $pendapatanData = Pendapatan::whereYear('tanggal', $year)
            ->selectRaw('MONTH(tanggal) as month, SUM(jumlah_pendapatan) as total')
            ->groupBy('month')
            ->get();

        foreach ($pendapatanData as $data) {
            $monthlyNonSubscription[$data->month] = $data->total;
        }

        // Get operational costs (Pengeluaran) by month
        $pengeluaranData = Pengeluaran::whereYear('tanggal_pengeluaran', $year)
            ->selectRaw('MONTH(tanggal_pengeluaran) as month, SUM(jumlah_pengeluaran) as total')
            ->groupBy('month')
            ->get();

        foreach ($pengeluaranData as $data) {
            $monthlyOperationalCost[$data->month] = $data->total;
        }

        // Calculate monthly totals and profit/loss
        $monthlyTotalRevenue = [];
        $monthlyProfitLoss = [];

        for ($month = 1; $month <= 12; $month++) {
            $totalRevenue = $monthlySubscription[$month] + $monthlyNonSubscription[$month];
            $monthlyTotalRevenue[$month] = $totalRevenue;
            $monthlyProfitLoss[$month] = $totalRevenue - $monthlyOperationalCost[$month];
        }

        // Calculate yearly totals for summary cards
        $totalSubscription = array_sum($monthlySubscription);
        $totalNonSubscription = array_sum($monthlyNonSubscription);
        $totalOperationalCost = array_sum($monthlyOperationalCost);
        $totalRevenue = $totalSubscription + $totalNonSubscription;
        $totalProfitLoss = $totalRevenue - $totalOperationalCost;

        // Prepare data for view
        $financialData = [
            'subscription' => array_values($monthlySubscription),
            'nonSubscription' => array_values($monthlyNonSubscription),
            'totalRevenue' => array_values($monthlyTotalRevenue),
            'operationalCost' => array_values($monthlyOperationalCost),
            'profitLoss' => array_values($monthlyProfitLoss)
        ];

        // Summary data
        $summaryData = [
            'totalSubscription' => $totalSubscription,
            'totalNonSubscription' => $totalNonSubscription,
            'totalRevenue' => $totalRevenue,
            'totalOperationalCost' => $totalOperationalCost,
            'totalProfitLoss' => $totalProfitLoss
        ];

        // Get available years for filter dropdown
        $availableYears = collect();

        // Get years from Pembayaran
        $pembayaranYears = Pembayaran::selectRaw('YEAR(tanggal_bayar) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Get years from Pendapatan
        $pendapatanYears = Pendapatan::selectRaw('YEAR(tanggal) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Get years from Pengeluaran
        $pengeluaranYears = Pengeluaran::selectRaw('YEAR(tanggal_pengeluaran) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $availableYears = $pembayaranYears->merge($pendapatanYears)
            ->merge($pengeluaranYears)
            ->unique()
            ->sort()
            ->values();

        // If AJAX request, return JSON data
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'financialData' => $financialData,
                    'summaryData' => $summaryData,
                    'selectedYear' => $year
                ]
            ]);
        }

        $pengeluaran = Pengeluaran::sum('jumlah_pengeluaran');

        return view('/keuangan/pendapatan-global', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'financialData' => $financialData,
            'summaryData' => $summaryData,
            'selectedYear' => $year,
            'availableYears' => $availableYears,
            'pengeluaran' => $pengeluaran
        ]);
    }

    // Add new method for AJAX data updates
    public function getGlobalPendapatanData(Request $request)
    {
        $year = $request->get('year', date('Y'));

        // Initialize monthly data arrays
        $monthlySubscription = array_fill(1, 12, 0);
        $monthlyNonSubscription = array_fill(1, 12, 0);
        $monthlyOperationalCost = array_fill(1, 12, 0);

        // Get subscription revenue by month
        $pembayaranData = Pembayaran::whereYear('tanggal_bayar', Carbon::now()->year)
            ->selectRaw('MONTH(tanggal_bayar) as month, SUM(jumlah_bayar) as total')
            ->groupBy('month')
            ->get();

        foreach ($pembayaranData as $data) {
            $monthlySubscription[$data->month] = $data->total;
        }

        // Get non-subscription revenue by month
        $pendapatanData = Pendapatan::whereYear('tanggal', $year)
            ->selectRaw('MONTH(tanggal) as month, SUM(jumlah_pendapatan) as total')
            ->groupBy('month')
            ->get();

        foreach ($pendapatanData as $data) {
            $monthlyNonSubscription[$data->month] = $data->total;
        }

        // Get operational costs by month
        $pengeluaranData = Pengeluaran::whereYear('tanggal_pengeluaran', $year)
            ->selectRaw('MONTH(tanggal_pengeluaran) as month, SUM(jumlah_pengeluaran) as total')
            ->groupBy('month')
            ->get();

        foreach ($pengeluaranData as $data) {
            $monthlyOperationalCost[$data->month] = $data->total;
        }

        // Calculate totals and profit/loss
        $monthlyTotalRevenue = [];
        $monthlyProfitLoss = [];

        for ($month = 1; $month <= 12; $month++) {
            $totalRevenue = $monthlySubscription[$month] + $monthlyNonSubscription[$month];
            $monthlyTotalRevenue[$month] = $totalRevenue;
            $monthlyProfitLoss[$month] = $totalRevenue - $monthlyOperationalCost[$month];
        }

        // Calculate yearly totals
        $totalSubscription = array_sum($monthlySubscription);
        $totalNonSubscription = array_sum($monthlyNonSubscription);
        $totalOperationalCost = array_sum($monthlyOperationalCost);
        $totalRevenue = $totalSubscription + $totalNonSubscription;
        $totalProfitLoss = $totalRevenue - $totalOperationalCost;

        return response()->json([
            'success' => true,
            'data' => [
                'financialData' => [
                    'subscription' => array_values($monthlySubscription),
                    'nonSubscription' => array_values($monthlyNonSubscription),
                    'totalRevenue' => array_values($monthlyTotalRevenue),
                    'operationalCost' => array_values($monthlyOperationalCost),
                    'profitLoss' => array_values($monthlyProfitLoss)
                ],
                'summaryData' => [
                    'totalSubscription' => $totalSubscription,
                    'totalNonSubscription' => $totalNonSubscription,
                    'totalRevenue' => $totalRevenue,
                    'totalOperationalCost' => $totalOperationalCost,
                    'totalProfitLoss' => $totalProfitLoss
                ],
                'selectedYear' => $year
            ]
        ]);
    }

    public function requestPembayaran(Request $request, $id)
    {
        $invoice = Invoice::with('customer', 'paket')->findOrFail($id);

        DB::beginTransaction();
        try {
            $pilihan = $request->input('bayar', []); // ["tagihan","tambahan","tunggakan"]
            $gunakanSaldo = $request->has('saldo');  // true kalau checkbox saldo dicentang

            // Validasi tipe_pembayaran
            $request->validate([
                'tipe_pembayaran' => 'required|in:reguler,diskon',
            ]);

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
            // Buat keterangan dinamis dengan informasi tipe pembayaran
            // ================================
            $keteranganArr = [];
            if ($bayarTagihan > 0)   $keteranganArr[] = "Tagihan Langganan";
            if ($bayarTambahan > 0)  $keteranganArr[] = "Biaya Tambahan";
            if ($bayarTunggakan > 0) $keteranganArr[] = "Tunggakan";
            if ($saldoTerpakai > 0)  $keteranganArr[] = "menggunakan saldo";
            if ($saldoBaru > 0)      $keteranganArr[] = "menyisakan saldo";

            // Tambahkan informasi tipe pembayaran ke keterangan
            $tipePembayaranLabel = $request->tipe_pembayaran == 'reguler' ? 'Reguler' : 'Diskon';

            $keteranganPembayaran = "Pembayaran " . $tipePembayaranLabel . " - " . implode(", ", $keteranganArr) .
                " dari " . auth()->user()->name .
                " untuk pelanggan " . $invoice->customer->nama_customer .
                " PIC : " . ($invoice->customer->agen->name ?? '-');


            // Simpan pembayaran DENGAN TIPE PEMBAYARAN
            $pembayaran = Pembayaran::create([
                'invoice_id'      => $invoice->id,
                'jumlah_bayar'    => $jumlahBayar,
                'tanggal_bayar'   => now(),
                'metode_bayar'    => $request->metode_id,
                'tipe_pembayaran' => $request->tipe_pembayaran, // TAMBAHKAN INI
                'keterangan'      => $keteranganPembayaran,
                'status_id'       => 8,
                'user_id'         => auth()->id(),
                'bukti_bayar'     => $buktiPath,
                'saldo'           => $saldoBaru,
            ]);
            $pembayaran->refresh();
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

                // Generate Merchant Reference
                $merchant = 'INV-' . $customer->id . '-' . time();

                if (!$sudahAda) {
                    Invoice::create([
                        'customer_id'     => $invoice->customer_id,
                        'paket_id'        => $customer->paket_id,
                        'tagihan'         => $customer->paket->harga,
                        'tambahan'        => $newTambahan,
                        'tunggakan'       => $newTunggakan,
                        'saldo'           => $saldoBaru,
                        'status_id'       => 7,
                        'merchant_ref'    => $merchant,
                        'created_at'      => $tanggalAwal,
                        'updated_at'      => $tanggalAwal,
                        'jatuh_tempo'     => $tanggalJatuhTempo,
                        'tanggal_blokir'  => $invoice->tanggal_blokir,
                    ]);
                }
            }

            // ================================
            // Catat ke kas dengan informasi tipe pembayaran
            // ================================
            Kas::create([
                'debit'         => $pembayaran->jumlah_bayar,
                'tanggal_kas'   => $pembayaran->tanggal_bayar,
                'keterangan'    => 'Pembayaran ' . $tipePembayaranLabel . ' dari ' . auth()->user()->name .
                    ' untuk pelanggan ' . $pembayaran->invoice->customer->nama_customer .
                    ' PIC : ' . ($invoice->customer->agen->name ?? '-'),
                'kas_id'        => 1,
                'user_id'       => auth()->id(),
                'status_id'     => 3,
                'customer_id'   => $invoice->customer_id,
                'pengeluaran_id' => null,
            ]);

            // ================================
            // Update Status Customer jika perlu
            // ================================
            // Jika customer diblokir, buka blokir
            if ($invoice->customer->status_id == 9) {
                try {
                    $mikrotik = new MikrotikServices();
                    $client = MikrotikServices::connect($invoice->customer->router);
                    $mikrotik->unblokUser($client, $invoice->customer->usersecret, $invoice->customer->paket->paket_name);
                    $mikrotik->removeActiveConnections($client, $invoice->customer->usersecret);

                    $invoice->customer->update(['status_id' => 3]);

                    Log::info('Customer ' . $invoice->customer->nama_customer . ' berhasil di unblock', ['customer_id' => $invoice->customer->id]);
                } catch (Exception $e) {
                    Log::error('Failed to unblock customer', [
                        'customer_id' => $invoice->customer->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Catat Log Aktivitas dengan informasi tipe pembayaran
            activity('keuangan')
                ->causedBy(auth()->user())
                ->performedOn($pembayaran)
                ->log('Pembayaran ' . $tipePembayaranLabel . ' dari admin keuangan ' . auth()->user()->name .
                    ' untuk pelanggan ' . $pembayaran->invoice->customer->nama_customer .
                    ' dengan Jumlah Bayar ' . 'Rp ' . number_format($pembayaran->jumlah_bayar, 0, ',', '.'));

            DB::commit();

            return redirect()->back()->with('success', 'Pembayaran ' . $tipePembayaranLabel . ' berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Gagal menyimpan pembayaran: ' . $e->getMessage() . ' pada line ' . $e->getLine());
            return redirect()->back()->with('error', 'Gagal menyimpan pembayaran: ' . $e->getMessage());
        }
    }


    public function agen(Request $request)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // $agenQuery = User::where('roles_id', 6)
        //     ->withCount(['customer as total_customer' => function ($query) use ($currentMonth, $currentYear) {
        //         $query->whereIn('status_id', [1, 2, 3, 4, 5, 9])
        //         ->whereHas('invoice', function ($invoiceQuery) use ($currentMonth, $currentYear) {
        //             $invoiceQuery->whereMonth('jatuh_tempo', $currentMonth)
        //             ->whereYear('jatuh_tempo', $currentYear);
        //             });
        //     }]);
        $agenQuery = User::where('roles_id', 6)
            ->withCount(['customer as total_customer' => function ($query) {
                $query->whereIn('status_id', [3, 4, 9])
                ->whereNull('deleted_at');
            }]);

        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $agenQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('alamat', 'LIKE', "%{$search}%")
                    ->orWhere('no_hp', 'LIKE', "%{$search}%");
            });
        }

        $agen = $agenQuery->paginate(10);

        // Debug: Cek hasil count
        if ($request->has('debug')) {
            foreach ($agen as $a) {
                Log::info("Agen {$a->name} - Total Customer: {$a->total_customer}");
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $agen,
                'html' => view('keuangan.partials.agen-table-rows', compact('agen'))->render()
            ]);
        }

        return view('keuangan.data-agen', [
            'users' => Auth::user(),
            'roles' => Auth::user()->roles,
            'agen' => $agen
        ]);
    }

    public function searchAgen(Request $request)
    {
        $currentMonth = now()->format('m');

        $query = User::where('roles_id', 6)->withCount(['customer' => function ($query) use ($currentMonth) {
            // Count customers with status 1, 2, 3, 4, 5, 9 that have invoices in current month
            $query->whereIn('status_id', [1, 2, 3, 4, 5, 9])
                ->whereHas('invoice', function ($invoiceQuery) use ($currentMonth) {
                    $invoiceQuery->whereMonth('jatuh_tempo', $currentMonth)
                        ->whereYear('jatuh_tempo', date('Y'));
                });
        }]);

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

        // Get per_page parameter, default to 10
        $perPage = $request->get('per_page', 10);
        if ($perPage === 'all') {
            $perPage = 999999;
        } else {
            $perPage = (int) $perPage;
        }

        // Base query untuk customer dengan agen_id (include soft deleted)
        $baseCustomerQuery = function ($q) use ($id) {
            $q->where('agen_id', $id)
                ->whereIn('status_id', [3, 4, 9])
                ->whereNull('deleted_at');
        };

        // SOLUSI: Gunakan subquery yang lebih stabil dengan fromSub
        if ($filterMonth !== 'all') {
            $latestInvoiceSubquery = Invoice::select('customer_id', DB::raw('MAX(id) as latest_invoice_id'))
                ->whereMonth('jatuh_tempo', intval($filterMonth))
                ->whereYear('jatuh_tempo', date('Y'))
                ->whereHas('customer', function ($q) use ($id) {
                    $q->where('agen_id', $id);
                })
                ->groupBy('customer_id');

            $latestInvoicesQuery = Invoice::with([
                'customer' => function ($q) use ($id) {
                    $q->withTrashed()->where('agen_id', $id)->with('paket');
                },
                'status',
                'pembayaran' => function ($q) {
                    $q->orderBy('id', 'desc')->take(1);
                },
                'pembayaran.user'
            ])
                ->whereIn('id', function ($query) use ($latestInvoiceSubquery) {
                    $query->select('latest_invoice_id')
                        ->fromSub($latestInvoiceSubquery, 'latest_invoices');
                })
                ->whereHas('customer', function ($query) use ($id) {
                    $query->where('agen_id', $id);
                })
                ->where(function ($query) {
                    $query->whereHas('customer', function ($q) {
                        $q->whereNull('deleted_at');
                    })->orWhere(function ($q) {
                        $q->whereHas('customer', function ($q) {
                            $q->whereNotNull('deleted_at');
                        })->whereHas('status', function ($q) {
                            $q->where('nama_status', 'Sudah Bayar');
                        });
                    });
                });
        } else {
            $latestInvoiceSubquery = Invoice::select('customer_id', DB::raw('MAX(id) as latest_invoice_id'))
                ->whereYear('jatuh_tempo', date('Y'))
                ->whereHas('customer', function ($q) use ($id) {
                    $q->where('agen_id', $id);
                })
                ->groupBy('customer_id');

            $latestInvoicesQuery = Invoice::with([
                'customer' => function ($q) use ($id) {
                    $q->withTrashed()->where('agen_id', $id)->with('paket');
                },
                'status',
                'pembayaran' => function ($q) {
                    $q->orderBy('id', 'desc')->take(1);
                },
                'pembayaran.user'
            ])
                ->whereIn('id', function ($query) use ($latestInvoiceSubquery) {
                    $query->select('latest_invoice_id')
                        ->fromSub($latestInvoiceSubquery, 'latest_invoices');
                })
                ->whereHas('customer', function ($query) use ($id) {
                    $query->where('agen_id', $id);
                })
                ->where(function ($query) {
                    $query->whereHas('customer', function ($q) {
                        $q->whereNull('deleted_at');
                    })->orWhere(function ($q) {
                        $q->whereHas('customer', function ($q) {
                            $q->whereNotNull('deleted_at');
                        })->whereHas('status', function ($q) {
                            $q->where('nama_status', 'Sudah Bayar');
                        });
                    });
                });
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

        // SOLUSI: Tambahkan order by yang stabil
        $invoices = $latestInvoicesQuery
            ->withMax('pembayaran', 'tanggal_bayar')
            ->orderByDesc('pembayaran_max_tanggal_bayar')
            ->orderBy('id', 'desc') // TAMBAHKAN ORDER BY STABIL
            ->paginate($perPage)
            ->appends($request->all());

        // DEBUG: Cek duplikasi
        $duplicateCheck = $invoices->groupBy('customer_id')
            ->filter(function ($group) {
                return $group->count() > 1;
            });

        if ($duplicateCheck->isNotEmpty()) {
            Log::warning('DUPLICATE CUSTOMERS FOUND IN PAGINATION', [
                'page' => $request->get('page', 1),
                'total_duplicates' => $duplicateCheck->count(),
                'duplicates' => $duplicateCheck->map(function ($invoices, $customerId) {
                    return [
                        'customer_id' => $customerId,
                        'customer_name' => $invoices->first()->customer->nama_customer ?? 'Unknown',
                        'invoice_count' => $invoices->count(),
                        'invoice_ids' => $invoices->pluck('id')->toArray()
                    ];
                })->values()->toArray()
            ]);

            // FALLBACK: Jika masih ada duplikat, gunakan manual grouping
            $uniqueInvoices = $invoices->groupBy('customer_id')->map->first();
            $invoices = new \Illuminate\Pagination\LengthAwarePaginator(
                $uniqueInvoices->values(),
                $invoices->total(),
                $perPage,
                $request->get('page', 1),
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        // HITUNG TOTAL UNTUK STATISTIK - menggunakan approach yang sama
        $totalQuery = Invoice::whereIn('id', function ($query) use ($filterMonth, $baseCustomerQuery) {
            $subquery = Invoice::select('customer_id', DB::raw('MAX(id) as latest_invoice_id'))
                ->whereYear('jatuh_tempo', date('Y'))
                ->whereHas('customer', $baseCustomerQuery);

            if ($filterMonth !== 'all') {
                $subquery->whereMonth('jatuh_tempo', intval($filterMonth));
            }

            $query->select('latest_invoice_id')
                ->fromSub($subquery->groupBy('customer_id'), 'latest_invoices');
        })
            ->with(['customer' => function ($q) {
                $q->withTrashed()->with('paket');
            }, 'status'])
            ->where(function ($query) {
                $query->whereHas('customer', function ($q) {
                    $q->whereNull('deleted_at');
                })->orWhere(function ($q) {
                    $q->whereHas('customer', function ($q) {
                        $q->whereNotNull('deleted_at');
                    })->whereHas('status', function ($q) {
                        $q->where('nama_status', 'Sudah Bayar');
                    });
                });
            });

        // Apply status filter untuk total calculation
        if ($filterStatus) {
            if ($filterStatus == 'Sudah Bayar') {
                $totalQuery->whereHas('status', fn($q) => $q->where('nama_status', 'Sudah Bayar'));
            } elseif ($filterStatus == 'Belum Bayar') {
                $totalQuery->whereHas('status', fn($q) => $q->where('nama_status', 'Belum Bayar'));
            }
        }

        $allInvoices = $totalQuery->get();

        // HITUNG TOTAL PAID: termasuk customer aktif dan customer deleted yang sudah bayar
        $totalPaid = $allInvoices
            ->where('status.nama_status', 'Sudah Bayar')
            ->sum(function ($invoice) {
                return floatval($invoice->tagihan ?? 0) + floatval($invoice->tambahan ?? 0);
            });

        // HITUNG TOTAL UNPAID: HANYA customer aktif yang belum bayar (exclude customer deleted)
        $totalUnpaid = $allInvoices
            ->where('status.nama_status', 'Belum Bayar')
            ->filter(function ($invoice) {
                // Hanya hitung jika customer masih aktif (tidak dihapus)
                return $invoice->customer && !$invoice->customer->trashed();
            })
            ->sum(function ($invoice) {
                return floatval($invoice->tagihan ?? 0) + floatval($invoice->tambahan ?? 0);
            });

        // HITUNG TOTAL AMOUNT: semua yang seharusnya ditampilkan
        $totalAmount = $allInvoices
            ->sum(function ($invoice) {
                return floatval($invoice->tagihan ?? 0) + floatval($invoice->tambahan ?? 0);
            });

        // Hitung khusus untuk customer yang sudah dihapus dan sudah bayar
        $deletedCustomersPaid = $allInvoices
            ->filter(function ($invoice) {
                return $invoice->customer &&
                    $invoice->customer->trashed() &&
                    $invoice->status->nama_status == 'Sudah Bayar';
            });

        $totalDeletedPaid = $deletedCustomersPaid->sum(function ($invoice) {
            return floatval($invoice->tagihan ?? 0) + floatval($invoice->tambahan ?? 0);
        });

        $countDeletedPaid = $deletedCustomersPaid->count();

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
        $totalPelanggan = Invoice::distinct('customer_id')->whereHas('customer', function ($q) use ($id) {
            $q->where('agen_id', $id)->whereIn('status_id', [3, 4, 9])->withTrashed();
        })->count();
        return view('keuangan.data-pelanggan-agen', [
            'users' => Auth::user(),
            'roles' => Auth::user()->roles,
            'invoices' => $invoices,
            'agen' => $agen,
            'totalPaid' => $totalPaid,
            'totalUnpaid' => $totalUnpaid,
            'totalAmount' => $totalAmount,
            'totalDeletedPaid' => $totalDeletedPaid,
            'countDeletedPaid' => $countDeletedPaid,
            'currentMonth' => $currentMonth,
            'filterMonth' => $filterMonth,
            'filterStatus' => $filterStatus,
            'monthNames' => $monthNames,
            'currentMonthNum' => $currentMonthNum,
            'currentMonthName' => $currentMonthName,
            'totalPelanggan' => $totalPelanggan
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
                ->whereHas('invoice.customer')
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
            // $subscriptionQuery->whereMonth('tanggal_bayar', $month);
            // $nonSubscriptionQuery->whereMonth('tanggal', $month);
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

        // Calculate customer statistics - EXCLUDE SOFT DELETED CUSTOMERS
        $totalCustomers = Customer::whereIn('status_id', [3, 4, 9])
            ->whereNull('deleted_at')
            ->count();

        // Determine month for customer statistics
        $customerMonth = $month !== 'all' ? $month : date('m');
        $customerYear = $year;

        // **PERUBAHAN: Query untuk pelanggan lunas - INCLUDE SOFT DELETED yang sudah bayar**
        $pelangganLunasQuery = Invoice::whereYear('jatuh_tempo', $customerYear)
            ->where('status_id', 8); // Hanya yang status LUNAS

        if ($month !== 'all') {
            $pelangganLunasQuery->whereMonth('jatuh_tempo', $customerMonth);
        }
        $lunas = Invoice::whereYear('jatuh_tempo', $customerYear)
            ->where('status_id', 7)
            ->whereHas('customer', function ($q) {
                $q->whereNull('deleted_at');
            })->sum(DB::raw('tagihan + tambahan + COALESCE(tunggakan, 0)'));

        // **Hitung pelanggan lunas - Termasuk yang sudah di-soft delete**
        $pelangganLunas = Pembayaran::whereMonth('tanggal_bayar', Carbon::now()->month)
            ->whereYear('tanggal_bayar', Carbon::now()->year)
            ->sum('jumlah_bayar');

        // **PERUBAHAN: Query untuk pelanggan belum lunas - EXCLUDE SOFT DELETED**
        $pelangganBelumLunasQuery = Invoice::whereYear('jatuh_tempo', $customerYear)
            ->where('status_id', 7) // Status BELUM LUNAS
            ->whereHas('customer', function ($q) {
                $q->whereNull('deleted_at'); // Exclude soft deleted untuk yang belum lunas
            });

        if ($month !== 'all') {
            $pelangganBelumLunasQuery->whereMonth('jatuh_tempo', $customerMonth);
        }

        // **Hitung pelanggan belum lunas - Hanya yang tidak di-soft delete**
        $pelangganBelumLunas = $pelangganBelumLunasQuery->sum(DB::raw('tagihan + tambahan + COALESCE(tunggakan, 0)'));

        // **PERUBAHAN: Total pendapatan - Gabungan lunas (include deleted) + belum lunas (exclude deleted)**
        $totalPendapatan = $pelangganLunas + $pelangganBelumLunas;

        // **PERUBAHAN: Count paid customers - Termasuk yang sudah di-soft delete**
        $paidCustomers = Invoice::distinct('customer_id')
            ->where('status_id', 8)
            ->whereHas('customer', function ($q) {
                $q->withTrashed();
            })
            ->whereHas('pembayaran', function ($q) use ($customerMonth, $month) {
                $q->whereMonth('tanggal_bayar', Carbon::now()->month)->whereYear('tanggal_bayar', Carbon::now()->year)
                    ->when($month !== 'all', function ($r) use ($customerMonth) {
                        $r->whereMonth('tanggal_bayar', $customerMonth);
                    });
            })->count();

        // **PERUBAHAN: Count unpaid customers - Hanya yang tidak di-soft delete**
        $unpaidCustomers = Invoice::distinct('customer_id')
            ->where('status_id', 7) // Status BELUM LUNAS
            ->whereHas('customer', function ($q) {
            $q->whereNull('deleted_at');
            })
            ->when($month !== 'all', function ($q) use ($customerMonth) {
                $q->whereMonth('jatuh_tempo', $customerMonth);
            })->count();

        return [
            'totalSubscription' => (float) $totalSubscription,
            'totalNonSubscription' => (float) $totalNonSubscription,
            'totalExpenses' => (float) $totalExpenses,
            'totalRevenue' => (float) $currentYearRevenue,
            'profitLoss' => (float) $currentProfit,
            'totalKasSaldo' => (float) $totalKasSaldo,
            'totalCustomers' => (int) $totalCustomers,
            'paidCustomers' => (int) $paidCustomers,
            'totalPendapatan' => (float) $lunas,
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

        // Subscription revenue details (Pembayaran) - EXCLUDE SOFT DELETED CUSTOMERS
        $subscriptionQuery = Pembayaran::with([
            'invoice.customer' => function ($q) {
                $q->withTrashed(); // TAMBAHAN: Exclude soft deleted
            },
            'invoice.paket',
            'user'
        ])
            ->whereYear('tanggal_bayar', $year)
            ->whereHas('invoice.customer', function ($q) {
                $q->withTrashed(); // TAMBAHAN: Exclude soft deleted
            });

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

    public function exportPelangganAgen(Request $request, $id)
    {
        try {
            // Get agen info
            $agen = User::findOrFail($id);

            // Get export parameters
            $exportType = $request->get('export_type');
            $format = $request->get('format', 'xlsx');

            // Prepare parameters untuk class CustomerAgen
            // Tentukan include_deleted
            $includeDeleted = $request->get('include_deleted', true);
            $exportParams = [
                'agen_id' => $id,
                'export_type' => $exportType,
                'format' => $format,
                'include_deleted' => $includeDeleted
            ];

            // Apply filters based on export type
            switch ($exportType) {
                case 'today':
                    $date = $request->get('date', now()->format('Y-m-d'));
                    $exportParams['type'] = 'range';
                    $exportParams['startDate'] = $date;
                    $exportParams['endDate'] = $date;
                    $filename = "Data_Pelanggan_Agen_{$agen->name}_Hari_Ini_" . now()->format('Y-m-d');
                    break;

                case 'month':
                    $month = $request->get('month', now()->month);
                    $year = $request->get('year', now()->year);
                    $exportParams['type'] = 'bulan';
                    $exportParams['bulan'] = ['month' => $month, 'year' => $year];
                    $monthName = [
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember'
                    ][$month];
                    $filename = "Data_Pelanggan_Agen_{$agen->name}_{$monthName}_{$year}";
                    break;

                case 'current_filter':
                    $filterMonth = $request->get('month');
                    $filterStatus = $request->get('status');

                    $exportParams['type'] = 'bulan';

                    // PERBAIKAN: Handle 'all' value untuk bulan
                    if ($filterMonth && $filterMonth !== 'all') {
                        $exportParams['bulan'] = ['month' => $filterMonth, 'year' => now()->year];
                    } else {
                        $exportParams['bulan'] = 'all'; // Kirim 'all' bukan array
                    }

                    $exportParams['filterStatus'] = $filterStatus;
                    $filename = "Data_Pelanggan_Agen_{$agen->name}_Filter_" . now()->format('Y-m-d');
                    break;

                case 'custom_range':
                    $startDate = $request->get('start_date');
                    $endDate = $request->get('end_date');

                    $exportParams['type'] = 'range';
                    $exportParams['startDate'] = $startDate;
                    $exportParams['endDate'] = $endDate;

                    $filename = "Data_Pelanggan_Agen_{$agen->name}_" .
                        Carbon::parse($startDate)->format('Y-m-d') . "_sampai_" .
                        Carbon::parse($endDate)->format('Y-m-d');
                    break;

                default:
                    // Default to current month
                    $exportParams['type'] = 'bulan';
                    $exportParams['bulan'] = ['month' => now()->month, 'year' => now()->year];
                    $filename = "Data_Pelanggan_Agen_{$agen->name}_" . now()->format('Y-m');
                    break;
            }

            // Create export instance dengan semua parameter
            $exportInstance = new CustomerAgen(
                $exportParams['type'] ?? 'bulan',
                $exportParams['bulan'] ?? null,
                $exportParams['startDate'] ?? null,
                $exportParams['endDate'] ?? null,
                $exportParams['agen_id'] ?? null,
                $exportParams['filterStatus'] ?? null,
                $exportParams['include_deleted'] ?? true // Default true
            );

            // Return export based on format
            switch ($format) {
                case 'xls':
                    return Excel::download($exportInstance, $filename . '.xls', \Maatwebsite\Excel\Excel::XLS);
                case 'csv':
                    return Excel::download($exportInstance, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
                case 'xlsx':
                default:
                    return Excel::download($exportInstance, $filename . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
            }
        } catch (\Exception $e) {
            Log::error('Error in exportPelangganAgen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat export: ' . $e->getMessage()
            ], 500);
        }
    }

    private function exportToXLSX($data, $filename, $agen)
    {
        try {
            return Excel::download(
                new CustomerAgen($data, $agen->name),
                $filename . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX,
                [
                    'Cache-Control' => 'max-age=0',
                ]
            );
        } catch (\Exception $e) {
            // Fallback to CSV jika XLSX gagal
            \Log::warning('XLSX export failed, falling back to CSV: ' . $e->getMessage());
            return $this->exportToCSV($data, $filename);
        }
    }

    private function exportToXLS($data, $filename, $agen)
    {
        try {
            return Excel::download(
                new CustomerAgen($data, $agen->name),
                $filename . '.xls',
                \Maatwebsite\Excel\Excel::XLS,
                [
                    'Cache-Control' => 'max-age=0',
                ]
            );
        } catch (\Exception $e) {
            // Fallback to CSV jika XLS gagal
            \Log::warning('XLS export failed, falling back to CSV: ' . $e->getMessage());
            return $this->exportToCSV($data, $filename);
        }
    }

    private function exportToCSV($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8 compatibility
            fwrite($file, "\xEF\xBB\xBF");

            // Add headers
            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));

                // Add data rows
                foreach ($data as $row) {
                    // Format numeric values properly
                    $formattedRow = $row;
                    $numericColumns = ['Total Tagihan', 'Tambahan', 'Tunggakan', 'Saldo', 'Total Keseluruhan'];

                    foreach ($numericColumns as $col) {
                        if (isset($formattedRow[$col])) {
                            $formattedRow[$col] = is_numeric($formattedRow[$col]) ?
                                number_format($formattedRow[$col], 0, ',', '.') : $formattedRow[$col];
                        }
                    }

                    fputcsv($file, $formattedRow);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get single pendapatan data for editing
     */
    public function showPendapatan($id)
    {
        try {
            $pendapatan = Pendapatan::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $pendapatan->id,
                    'jumlah_pendapatan' => $pendapatan->jumlah_pendapatan,
                    'jenis_pendapatan' => $pendapatan->jenis_pendapatan,
                    'deskripsi' => $pendapatan->deskripsi,
                    'tanggal' => $pendapatan->tanggal,
                    'metode_bayar' => $pendapatan->metode_bayar,
                    'bukti_pendapatan' => $pendapatan->bukti_pendapatan
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in showPendapatan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Data pendapatan tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update pendapatan data
     */
    public function updatePendapatan(Request $request, $id)
    {
        try {
            $pendapatan = Pendapatan::findOrFail($id);
            $jumlahKas = Kas::latest()->value('jumlah_kas') ?? 0;

            // Get jumlah_pendapatan - gunakan nilai baru jika ada, jika tidak gunakan nilai lama
            $jumlahBaru = $request->input('jumlah_pendapatan_raw') ?? $request->input('jumlah_pendapatan');

            // Jika masih string format rupiah, ekstrak angkanya
            if (is_string($jumlahBaru) && strpos($jumlahBaru, 'Rp') !== false) {
                $jumlahBaru = (int) preg_replace('/[^0-9]/', '', $jumlahBaru);
            } else {
                $jumlahBaru = (int) $jumlahBaru;
            }

            // Jika jumlah baru tidak valid atau 0, gunakan nilai lama
            if (empty($jumlahBaru) || $jumlahBaru === 0) {
                $jumlahBaru = $pendapatan->jumlah_pendapatan;
            }

            // Calculate difference in amount for Kas adjustment
            $selisihJumlah = $jumlahBaru - $pendapatan->jumlah_pendapatan;

            // Handle file upload for receipt
            $buktiPath = $pendapatan->bukti_pendapatan;
            if ($request->hasFile('bukti_pembayaran')) {
                // Delete old file if exists
                if ($pendapatan->bukti_pendapatan) {
                    Storage::disk('public')->delete($pendapatan->bukti_pendapatan);
                }

                $file = $request->file('bukti_pembayaran');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $buktiPath = $file->storeAs('bukti_pendapatan', $fileName, 'public');
            }

            // Update pendapatan record
            $pendapatan->update([
                'jumlah_pendapatan' => $jumlahBaru,
                'jenis_pendapatan' => $request->input('jenis_pendapatan') ?? $pendapatan->jenis_pendapatan,
                'deskripsi' => $request->input('deskripsi') ?? $pendapatan->deskripsi,
                'tanggal' => $request->input('tanggal') ?? $pendapatan->tanggal,
                'bukti_pendapatan' => $buktiPath,
                'metode_bayar' => $request->input('metode_bayar') ?? $pendapatan->metode_bayar,
                'user_id' => auth()->id()
            ]);

            // Update Kas record if amount changed
            if ($selisihJumlah != 0) {
                $kas = new Kas();
                $kas->jumlah_kas = $jumlahKas + $selisihJumlah;
                $kas->debit = $selisihJumlah > 0 ? $selisihJumlah : 0;
                $kas->kredit = $selisihJumlah < 0 ? abs($selisihJumlah) : 0;
                $kas->kas_id = 1;
                $kas->keterangan = 'Pembaruan Pendapatan: ' . $request->input('jenis_pendapatan') . ' - ' . $request->input('deskripsi');
                $kas->tanggal_kas = $request->input('tanggal');
                $kas->user_id = auth()->user()->id;
                $kas->status_id = 3;
                $kas->save();
            }

            activity('Edit Pendapatan')
                ->causedBy(auth()->user()->id)
                ->log(auth()->user()->name . ' Mengubah data Pendapatan Non Langganan');

            return response()->json([
                'success' => true,
                'message' => 'Pendapatan berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in updatePendapatan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui pendapatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete pendapatan data
     */
    public function destroyPendapatan($id)
    {
        try {
            $pendapatan = Pendapatan::findOrFail($id);
            $jumlahKas = Kas::latest()->value('jumlah_kas') ?? 0;

            // Delete bukti file if exists
            if ($pendapatan->bukti_pendapatan) {
                Storage::disk('public')->delete($pendapatan->bukti_pendapatan);
            }

            // Buat Kas entry untuk penghapusan (kredit/pengurangan)
            $kas = new Kas();
            $kas->jumlah_kas = $jumlahKas - $pendapatan->jumlah_pendapatan;
            $kas->kredit = $pendapatan->jumlah_pendapatan;
            $kas->kas_id = 1;
            $kas->keterangan = 'Penghapusan Pendapatan: ' . $pendapatan->jenis_pendapatan . ' - ' . $pendapatan->deskripsi;
            $kas->tanggal_kas = now();
            $kas->user_id = auth()->user()->id;
            $kas->status_id = 3;
            $kas->save();

            // Delete pendapatan record
            $pendapatan->delete();

            activity('Hapus Pendapatan')
                ->causedBy(auth()->user()->id)
                ->log(auth()->user()->name . ' Menghapus Pendapatan Non Langganan');

            return response()->json([
                'success' => true,
                'message' => 'Pendapatan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in destroyPendapatan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus pendapatan: ' . $e->getMessage()
            ], 500);
        }
    }
}
