<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Pembayaran;
use App\Services\ChatServices;
use App\Services\MikrotikServices;
use App\Models\Kas;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
class AgenController extends Controller
{
    public function index(Request $request)
    {
        $agen = Auth::user()->id;

        // Get current month as default filter
        $currentYear = Carbon::now()->format('Y');
        $currentMonth = Carbon::now()->format('m');
        $filterMonth = $request->get('month', $currentMonth);
        $perPage = $request->get('per_page', 10); // Default 10
        $statusFilter = $request->get('status');

        // Query dari Invoice sebagai basis
        $query = Invoice::with([
            'status',
            'pembayaran.user',
            'customer' => function ($q) {
                $q->withTrashed()->with('paket');
            }
        ])
            ->whereHas('customer', function ($q) use ($agen) {
                $q->withTrashed()->where('agen_id', $agen);
            })
            ->where(function ($q) {
                // Kondisi 1: Tampilkan semua invoice dari customer yang aktif
                $q->whereHas('customer', function ($subQ) {
                    $subQ->whereNull('deleted_at');
                })
                    // Kondisi 2: Atau, tampilkan HANYA invoice yang sudah lunas dari customer yang sudah dihapus
                    ->orWhere(function ($subQ) {
                        $subQ->whereHas('customer', function ($customerQuery) {
                            $customerQuery->onlyTrashed();
                    })->whereHas('status', function ($statusQuery) {
                    $statusQuery->where('nama_status', 'Sudah Bayar');
                    });
                });
            })
            ->orderBy('jatuh_tempo', 'desc');

        // Apply month filter
        if ($filterMonth !== 'all') {
            $query->whereYear('jatuh_tempo', $currentYear)
                ->whereMonth('jatuh_tempo', intval($filterMonth));
        }

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('nama_customer', 'like', '%' . $search . '%')
                        ->orWhere('alamat', 'like', '%' . $search . '%')
                        ->orWhere('no_hp', 'like', '%' . $search . '%')
                        ->orWhereHas('paket', function ($paketQuery) use ($search) {
                            $paketQuery->where('nama_paket', 'like', '%' . $search . '%');
                        });
                });
            });
        }

        // Apply status filter
        if ($statusFilter) {
            if ($statusFilter === 'Sudah Bayar') {
                $query->whereHas('status', function ($q) {
                    $q->where('nama_status', 'Sudah Bayar');
                });
            } elseif ($statusFilter === 'Belum Bayar') {
                $query->where(function ($q) {
                    $q->whereHas('status', fn($sq) => $sq->where('nama_status', '!=', 'Sudah Bayar'))->orWhereNull('status_id');
                })->whereHas('customer', fn($cq) => $cq->whereNull('deleted_at')); // Hanya tampilkan yg belum bayar dari customer aktif
            }
        }

        // Handle pagination
        if ($perPage === 'all') {
            $invoices = $query->get();
            // Buat instance Paginator manual jika diperlukan, atau handle di view
            $invoices = new \Illuminate\Pagination\LengthAwarePaginator($invoices, $invoices->count(), -1);
        } else {
            $invoices = $query->paginate($perPage)->withQueryString();
        }

        // Calculate statistics
        $searchTerm = $request->get('search', '');
        $statistics = $this->calculateStatistics($agen, $filterMonth, $searchTerm);

        // Get available months for filter dropdown
        $availableMonths = $this->getAvailableMonths($agen);

        // Get month names
        $currentMonthName = $this->getIndonesianMonthName($currentMonth);
        $selectedMonthName = $filterMonth === 'all' ? 'Semua Periode' : $this->getIndonesianMonthName($filterMonth);

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('agen.partials.customer-table-rows', ['invoices' => $invoices, 'is_ajax' => true])->render(),
                'modals_html' => view('agen.partials.payment-modal', ['invoices' => $invoices])->render(),
                'pagination_html' => $invoices->links()->toHtml(),
                'statistics' => $statistics,
                'visible_count' => $invoices->count(),
                'total_count' => $invoices->total(),
            ]);
        }

        return view('agen.data-pelanggan-agen', [
            'users' => Auth::user(),
            'roles' => Auth::user()->roles,
            'invoices' => $invoices,
            'availableMonths' => $availableMonths,
            'currentMonth' => $currentMonth,
            'currentMonthName' => $currentMonthName,
            'selectedMonth' => $filterMonth,
            'selectedMonthName' => $selectedMonthName,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Get statistics only (for AJAX updates)
     */
    public function getStatistics(Request $request)
    {
        $agen = Auth::user()->id;

        // Get current month as default filter
        $currentMonth = Carbon::now()->format('m');
        $filterMonth = $request->get('month', $currentMonth);
        $searchTerm = $request->get('search', '');
        $statusFilter = $request->get('status', '');

        // Calculate statistics with all filters
        $statistics = $this->calculateStatisticsWithStatus($agen, $filterMonth, $searchTerm, $statusFilter);

        return response()->json([
            'success' => true,
            'statistics' => $statistics
        ]);
    }

    /**
     * Get available months from invoices for filter dropdown
     */
    private function getAvailableMonths($agenId)
    {
        $invoices = Invoice::where(function ($query) use ($agenId) {
            // Customer aktif
            $query->whereHas('customer', function ($subQ) use ($agenId) {
                $subQ->where('agen_id', $agenId)
                    ->whereIn('status_id', [3, 9]);
            });
        })
            ->orWhere(function ($query) use ($agenId) {
                // Customer yang dihapus
                $query->whereHas('customer', function ($subQ) use ($agenId) {
                    $subQ->onlyTrashed()
                        ->where('agen_id', $agenId)
                        ->whereIn('status_id', [3, 9]);
                });
            })
            ->whereNotNull('jatuh_tempo')
            ->get();

        $months = [];
        foreach ($invoices as $invoice) {
            try {
                $date = Carbon::parse($invoice->jatuh_tempo);
                $monthNum = $date->format('m');
                $monthName = $this->getIndonesianMonthName($monthNum);
                $months[$monthNum] = $monthName;
            } catch (\Exception $e) {
                // Skip invalid dates
                continue;
            }
        }

        // Sort by month number
        ksort($months);

        return $months;
    }

    /**
     * Get Indonesian month name from month number
     */
    private function getIndonesianMonthName($monthNumber)
    {
        $months = [
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

        return $months[$monthNumber] ?? 'Unknown';
    }

    /**
     * Calculate statistics for invoices including soft deleted customers
     */
    /**
     * Calculate statistics for invoices including soft deleted customers - FIXED VERSION
     */
    /**
     * Calculate statistics for invoices including soft deleted customers - FIXED VERSION
     */
    /**
     * Calculate statistics for invoices including soft deleted customers - CONSISTENT WITH INDEX
     */
    private function calculateStatistics($agenId, $filterMonth, $searchTerm = '')
    {
        // GUNAKAN QUERY YANG SAMA PERSIS DENGAN INDEX
        $invoicesQuery = Invoice::with([
            'status',
            'pembayaran.user', // Konsisten dengan index
            'customer' => function ($q) {
                $q->withTrashed()->with('paket'); // Konsisten dengan index
            }
        ])
            ->whereHas('customer', function ($q) use ($agenId, $searchTerm) {
                $q->withTrashed()
                    ->where('agen_id', $agenId)
                    ->whereIn('status_id', [3, 4, 9]) // Konsisten: [3, 4, 9] bukan [3, 9]
                    ->when(!empty($searchTerm), function ($subQ) use ($searchTerm) {
                        $subQ->where(function ($s) use ($searchTerm) {
                            $s->where('nama_customer', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('alamat', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('no_hp', 'LIKE', "%{$searchTerm}%");
                        });
            });
            })
            ->orderBy('jatuh_tempo', 'desc'); // Konsisten dengan index

        // Apply month filter - SAMA DENGAN INDEX
        if ($filterMonth !== 'all') {
            $invoicesQuery->whereMonth('jatuh_tempo', intval($filterMonth));
        }

        $invoices = $invoicesQuery->get();

        $totalPaid = 0;
        $totalUnpaid = 0;
        $totalAmount = 0;
        $countPaid = 0;
        $countUnpaid = 0;
        $countTotal = 0;

        \Log::info("=== CALCULATE STATISTICS - CONSISTENT WITH INDEX ===");
        \Log::info("Query conditions - Agen: {$agenId}, Month: {$filterMonth}, Search: '{$searchTerm}'");
        \Log::info("Total invoices from query: " . $invoices->count());

        foreach ($invoices as $invoice) {
            if (!$invoice->customer) {
                \Log::warning("Invoice {$invoice->id}: No customer found, skipping");
                continue;
            }

            $tagihan = floatval($invoice->tagihan ?? 0);
            $tambahan = floatval($invoice->tambahan ?? 0);
            $tunggakan = floatval($invoice->tunggakan ?? 0);
            $saldo = floatval($invoice->saldo ?? 0);
            $invoiceTotal = $tagihan + $tambahan + $tunggakan - $saldo;

            // CEK STATUS - sama seperti di view
            $isPaid = $invoice->status && $invoice->status->nama_status == 'Sudah Bayar';
            $isDeleted = $invoice->customer->trashed(); // Gunakan method trashed()

            // CEK PEMBAYARAN - dari relasi yang sudah di-load
            $hasPayments = $invoice->pembayaran && $invoice->pembayaran->isNotEmpty();

            \Log::info("Processing Invoice {$invoice->id}: " .
                "Customer: {$invoice->customer->nama_customer}, " .
                "Deleted: " . ($isDeleted ? 'YES' : 'NO') . ", " .
                "Paid: " . ($isPaid ? 'YES' : 'NO') . ", " .
                "Payments: " . ($hasPayments ? $invoice->pembayaran->count() : '0') . ", " .
                "Total: Rp " . number_format($invoiceTotal, 0, ',', '.'));

            // LOGIC YANG SEDERHANA DAN KONSISTEN:
            // 1. SEMUA invoice yang status "Sudah Bayar" DIHITUNG (baik customer aktif maupun deleted)
            // 2. Hanya invoice yang "Belum Bayar" dari customer AKTIF yang dihitung sebagai unpaid
            // 3. Invoice "Belum Bayar" dari customer deleted TIDAK dihitung

            if ($isPaid) {
                // ✅ INI YANG HARUSNYA TERHITUNG: Semua yang sudah bayar
                $totalAmount += $invoiceTotal;
                $totalPaid += $invoiceTotal;
                $countPaid++;
                $countTotal++;

                \Log::info("→ COUNTED as PAID: Invoice {$invoice->id}");
            } else {
                if (!$isDeleted) {
                    // ✅ Customer aktif yang belum bayar
                    $totalAmount += $invoiceTotal;
                $totalUnpaid += $invoiceTotal;
                $countUnpaid++;
                    $countTotal++;

                    \Log::info("→ COUNTED as UNPAID: Invoice {$invoice->id} (active customer)");
                } else {
                    // ❌ Customer deleted yang belum bayar - TIDAK DIHITUNG
                    \Log::info("→ SKIPPED: Invoice {$invoice->id} (deleted customer, not paid)");
                }
            }
        }

        return [
            'total_paid' => $totalPaid,
            'total_unpaid' => $totalUnpaid,
            'total_amount' => $totalAmount,
            'count_paid' => $countPaid,
            'count_unpaid' => $countUnpaid,
            'count_total' => $countTotal,
            'percentage_paid' => $countTotal > 0 ? round(($countPaid / $countTotal) * 100, 1) : 0,
            'percentage_unpaid' => $countTotal > 0 ? round(($countUnpaid / $countTotal) * 100, 1) : 0,
        ];
    }

    /**
     * Calculate statistics with status filter included - INCLUDE SOFT DELETED CUSTOMERS
     */
    /**
     * Calculate statistics with status filter included - FIXED VERSION
     */
    /**
     * Calculate statistics with status filter included - CONSISTENT VERSION
     */
    private function calculateStatisticsWithStatus($agenId, $filterMonth, $searchTerm = '', $statusFilter = '')
    {
        // GUNAKAN QUERY YANG SAMA PERSIS
        $invoicesQuery = Invoice::with([
            'status',
            'pembayaran.user',
            'customer' => function ($q) {
                $q->withTrashed()->with('paket');
            }
        ])
            ->whereHas('customer', function ($q) use ($agenId, $searchTerm) {
                $q->withTrashed()
                    ->where('agen_id', $agenId)
                    ->whereIn('status_id', [3, 4, 9]) // Konsisten: [3, 4, 9]
                    ->when(!empty($searchTerm), function ($subQ) use ($searchTerm) {
                        $subQ->where(function ($s) use ($searchTerm) {
                            $s->where('nama_customer', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('alamat', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('no_hp', 'LIKE', "%{$searchTerm}%");
                        });
            });
            })
            ->orderBy('jatuh_tempo', 'desc');

        // Apply month filter - SAMA
        if ($filterMonth !== 'all') {
            $invoicesQuery->whereMonth('jatuh_tempo', intval($filterMonth));
        }

        // Apply status filter if provided
        if (!empty($statusFilter)) {
            if ($statusFilter === 'Sudah Bayar') {
                $invoicesQuery->whereHas('status', function ($q) {
                    $q->where('nama_status', 'Sudah Bayar');
                });
            } elseif ($statusFilter === 'Belum Bayar') {
                $invoicesQuery->whereHas('status', function ($q) {
                    $q->where('nama_status', '!=', 'Sudah Bayar');
                });
            }
        }

        $invoices = $invoicesQuery->get();

        $totalPaid = 0;
        $totalUnpaid = 0;
        $totalAmount = 0;
        $countPaid = 0;
        $countUnpaid = 0;
        $countTotal = 0;

        foreach ($invoices as $invoice) {
            if (!$invoice->customer) {
                continue;
            }

            $tagihan = floatval($invoice->tagihan ?? 0);
            $tambahan = floatval($invoice->tambahan ?? 0);
            $tunggakan = floatval($invoice->tunggakan ?? 0);
            $saldo = floatval($invoice->saldo ?? 0);
            $invoiceTotal = $tagihan + $tambahan + $tunggakan - $saldo;

            $isPaid = $invoice->status && $invoice->status->nama_status == 'Sudah Bayar';
            $isDeleted = $invoice->customer->trashed();

            // LOGIC YANG SAMA PERSIS
            if (!empty($statusFilter)) {
                if ($statusFilter === 'Sudah Bayar') {
                    // Filter "Sudah Bayar": hitung SEMUA yang sudah bayar
                    if ($isPaid) {
                        $totalAmount += $invoiceTotal;
                        $totalPaid += $invoiceTotal;
                        $countPaid++;
                        $countTotal++;
                    }
                } elseif ($statusFilter === 'Belum Bayar') {
                    // Filter "Belum Bayar": hanya yang belum bayar dan customer AKTIF
                    if (!$isPaid && !$isDeleted) {
                        $totalAmount += $invoiceTotal;
                        $totalUnpaid += $invoiceTotal;
                        $countUnpaid++;
                        $countTotal++;
                    }
                }
            } else {
                // Tanpa filter: gunakan logic yang sama
                if ($isPaid) {
                    $totalAmount += $invoiceTotal;
                $totalPaid += $invoiceTotal;
                $countPaid++;
                    $countTotal++;
                } else if (!$isDeleted) {
                    $totalAmount += $invoiceTotal;
                $totalUnpaid += $invoiceTotal;
                $countUnpaid++;
                    $countTotal++;
                }
            }
        }

        return [
            'total_paid' => $totalPaid,
            'total_unpaid' => $totalUnpaid,
            'total_amount' => $totalAmount,
            'count_paid' => $countPaid,
            'count_unpaid' => $countUnpaid,
            'count_total' => $countTotal,
            'percentage_paid' => $countTotal > 0 ? round(($countPaid / $countTotal) * 100, 1) : 0,
            'percentage_unpaid' => $countTotal > 0 ? round(($countUnpaid / $countTotal) * 100, 1) : 0,
        ];
    }

    public function requestPembayaran(Request $request, $id)
    {
        $invoice = Invoice::with('customer', 'paket')->findOrFail($id);

        $request->validate([
            'metode_id'        => 'required|string',
            'jumlah_bayar'     => 'nullable|numeric|min:0',
            'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $pilihan = $request->input('bayar', []);
            $gunakanSaldo = $request->has('saldo');

            // ====== Hitung Nominal ======
            $bayarTagihan   = in_array('tagihan', $pilihan)   ? $invoice->tagihan   : 0;
            $bayarTambahan  = in_array('tambahan', $pilihan)  ? $invoice->tambahan  : 0;
            $bayarTunggakan = in_array('tunggakan', $pilihan) ? $invoice->tunggakan : 0;

            $totalDipilih = $bayarTagihan + $bayarTambahan + $bayarTunggakan;

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

            $jumlahBayar = $saldoTerpakai + (int)$request->input('jumlah_bayar', 0);

            // Upload bukti pembayaran
            $buktiPath = null;
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $buktiPath = $file->storeAs('bukti_pembayaran', $fileName, 'public');
            }

            // ====== Keterangan ======
            $keteranganArr = [];
            if ($bayarTagihan > 0)   $keteranganArr[] = "Tagihan";
            if ($bayarTambahan > 0)  $keteranganArr[] = "Tambahan";
            if ($bayarTunggakan > 0) $keteranganArr[] = "Tunggakan";
            if ($saldoTerpakai > 0)  $keteranganArr[] = "pakai saldo";

            $keteranganPembayaran = "Pembayaran " . implode(", ", $keteranganArr) .
                " dari " . auth()->user()->name .
                " untuk " . $invoice->customer->nama_customer;

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
                'tipe_pembayaran' => 'reguler'
            ]);

            // Notifikasi
            (new ChatServices())->pembayaranBerhasil($invoice->customer->no_hp, $pembayaran);

            // Update invoice
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

            // Buat invoice bulan depan kalau lunas
            if (in_array('tagihan', $pilihan) && $newTagihan == 0) {
                $customer = $invoice->customer;
                $tanggalAwal = Carbon::parse($invoice->jatuh_tempo)->addMonthNoOverflow()->startOfMonth();
                $tanggalJatuhTempo = $tanggalAwal->copy()->endOfMonth();


                $sudahAda = Invoice::where('customer_id', $invoice->customer_id)
                    ->whereMonth('jatuh_tempo', $tanggalJatuhTempo->month)
                    ->whereYear('jatuh_tempo', $tanggalJatuhTempo->year)
                    ->exists();

                // Generate Merchant Reference
                $merchant = 'INV-' . $customer->id . '-' . time();

                if (!$sudahAda) {
                    Invoice::create([
                        'customer_id'    => $invoice->customer_id,
                        'paket_id'       => $customer->paket_id,
                        'tagihan'        => $customer->paket->harga,
                        'tambahan'       => $newTambahan,
                        'tunggakan'      => $newTunggakan,
                        'saldo'          => $saldoBaru,
                        'merchant_ref' => $merchant,
                        'status_id'      => 7,
                        'created_at'     => $tanggalAwal,
                        'updated_at'     => $tanggalAwal,
                        'jatuh_tempo'    => $tanggalJatuhTempo,
                        'tanggal_blokir' => $invoice->tanggal_blokir,
                    ]);
                }
            }

            // Catat ke kas
            Kas::create([
                'debit'       => $pembayaran->jumlah_bayar,
                'tanggal_kas' => $pembayaran->tanggal_bayar,
                'keterangan'  => 'Pembayaran dari ' . auth()->user()->name . ' untuk ' . $invoice->customer->nama_customer,
                'kas_id'      => 1,
                'user_id'     => auth()->id(),
                'status_id'   => 3,
                'customer_id' => $invoice->customer_id,
                'pengeluaran_id' => null,
            ]);
            // Update status jika di blokir
            if ($customer->status_id == 9) {
                try {
                    $mikrotik = new MikrotikServices();
                    $client = $mikrotik->connect($customer->router);
                    $mikrotik->unblokUser($client, $customer->usersecret, $customer->paket->paket_name);
                    $mikrotik->removeActiveConnections($client, $customer->usersecret);
                    $customer->update(['status_id' => 3]);
                    Log::info("Berhasil Unblokir User {$customer->nama_customer} setelah pembayaran.");
                } catch (\Exception $e) {
                    Log::error("❌ Gagal mengubah status customer {$customer->nama_customer} (ID: {$customer->id}): " . $e->getMessage());
                }
            }

            // Catat Log Aktivitas
            activity('agen')
                ->causedBy(auth()->user())
                ->performedOn($pembayaran)
                ->log('Pembayaran dari agen ' . auth()->user()->name . ' untuk pelanggan ' . $invoice->customer->nama_customer . ' dengan Jumlah Bayar ' . 'Rp ' . number_format($pembayaran->jumlah_bayar, 0, ',', '.'));

            DB::commit();
            return redirect()->back()->with('success', 'Pembayaran berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }


    public function pelanggan(Request $request)
    {
        $agenId = auth()->user()->id;
        $search = $request->get('search');

        $query = Customer::where('agen_id', $agenId)
            ->with(['paket', 'status'])->whereNull('deleted_at')
            ->withTrashed(); // Ambil juga yang soft-deleted untuk menampilkan statusnya

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_customer', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $pelanggan = $query->orderBy('nama_customer', 'asc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('agen.partials.pelanggan-agen-rows', compact('pelanggan'))->render(),
                'pagination_html' => $pelanggan->links('pagination::bootstrap-5')->toHtml(),
                'visible_count' => $pelanggan->count(),
                'total_count' => $pelanggan->total(),
            ]);
        }

        return view('agen.pelanggan-agen',[
            'users' => Auth::user(),
            'roles' => Auth::user()->roles,
            'pelanggan' => $pelanggan
        ]);
    }


}
