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
        $currentMonth = Carbon::now()->format('m'); // Format: 01, 02, 03, etc.
        $filterMonth = $request->get('month', $currentMonth);

        // Get customers with invoices, filtered by month
        $query = Customer::with(['invoice' => function($q) use ($filterMonth) {
                $q->with(['status', 'pembayaran.user']);

                // Filter invoices by month if not showing all
                if ($filterMonth !== 'all') {
                    $q->whereRaw("MONTH(STR_TO_DATE(jatuh_tempo, '%Y-%m-%d')) = ?", [intval($filterMonth)]);
                }

                $q->orderBy('jatuh_tempo', 'desc');
            }, 'paket'])
            ->where('agen_id', $agen)
            ->whereIn('status_id', [3, 9])
            ->whereHas('invoice', function($q) use ($filterMonth) {
                // Only include customers who have invoices in the selected month
                if ($filterMonth !== 'all') {
                    $q->whereRaw("MONTH(STR_TO_DATE(jatuh_tempo, '%Y-%m-%d')) = ?", [intval($filterMonth)]);
                }
            });
            // dd($query);
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_customer', 'LIKE', "%{$search}%")
                  ->orWhere('alamat', 'LIKE', "%{$search}%")
                  ->orWhere('no_hp', 'LIKE', "%{$search}%");
            });
        }

        $customers = $query->paginate(10);

        // Calculate statistics for the filtered period
        $searchTerm = $request->get('search', '');
        $statistics = $this->calculateStatistics($agen, $filterMonth, $searchTerm);

        // Get available months for filter dropdown
        $availableMonths = $this->getAvailableMonths($agen);

        // Get current month name in Indonesian
        $currentMonthName = $this->getIndonesianMonthName($currentMonth);

        // Get selected month name for display
        $selectedMonthName = $filterMonth === 'all' ? 'Semua Periode' : $this->getIndonesianMonthName($filterMonth);

        // If this is an AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $customers,
                'html' => view('agen.partials.customer-table-rows', compact('customers'))->render()
            ]);
        }

        return view('agen.data-pelanggan-agen',[
            'users' => Auth::user(),
            'roles' => Auth::user()->roles,
            'customers' => $customers,
            'availableMonths' => $availableMonths,
            'currentMonth' => $currentMonth,
            'currentMonthName' => $currentMonthName,
            'selectedMonth' => $filterMonth,
            'selectedMonthName' => $selectedMonthName,
            'statistics' => $statistics,
        ]);
    }

    public function search(Request $request)
    {
        $agen = Auth::user()->id;

        // Get month filter (default to current month)
        $currentMonth = Carbon::now()->format('m');
        $filterMonth = $request->get('month', $currentMonth);

        // Get customers with invoices, filtered by month
        $query = Customer::with(['invoice' => function($q) use ($filterMonth) {
                $q->with('status');

                // Filter invoices by month if not showing all
                if ($filterMonth !== 'all') {
                    $q->whereRaw("MONTH(STR_TO_DATE(jatuh_tempo, '%Y-%m-%d')) = ?", [intval($filterMonth)]);
                }

                $q->orderBy('jatuh_tempo', 'desc');
            }, 'paket'])
            ->where('agen_id', $agen)
            ->whereIn('status_id', [3, 9])
            ->whereHas('invoice', function($q) use ($filterMonth) {
                // Only include customers who have invoices in the selected month
                if ($filterMonth !== 'all') {
                    $q->whereRaw("MONTH(STR_TO_DATE(jatuh_tempo, '%Y-%m-%d')) = ?", [intval($filterMonth)]);
                }
            });

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_customer', 'LIKE', "%{$search}%")
                  ->orWhere('alamat', 'LIKE', "%{$search}%")
                  ->orWhere('no_hp', 'LIKE', "%{$search}%");
            });
        }

        $customers = $query->paginate(10);

        // Calculate statistics for the filtered period
        $searchTerm = $request->get('search', '');
        $statistics = $this->calculateStatistics($agen, $filterMonth, $searchTerm);

        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'statistics' => $statistics,
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ]
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
        $invoices = Invoice::whereHas('customer', function($q) use ($agenId) {
                $q->where('agen_id', $agenId)
                  ->whereIn('status_id', [3, 9]);
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
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        return $months[$monthNumber] ?? 'Unknown';
    }

    /**
     * Calculate statistics for invoices based on filter
     */
    private function calculateStatistics($agenId, $filterMonth, $searchTerm = '')
    {
        // Base query for invoices from this agent's customers
        $invoicesQuery = Invoice::whereHas('customer', function($q) use ($agenId, $searchTerm) {
                $q->where('agen_id', $agenId)
                  ->whereIn('status_id', [3, 9]);

                // Apply search filter if provided
                if (!empty($searchTerm)) {
                    $q->where(function($subQ) use ($searchTerm) {
                        $subQ->where('nama_customer', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('alamat', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('no_hp', 'LIKE', "%{$searchTerm}%");
                    });
                }
            });

        // Apply month filter if not showing all
        if ($filterMonth !== 'all') {
            $invoicesQuery->whereRaw("MONTH(STR_TO_DATE(jatuh_tempo, '%Y-%m-%d')) = ?", [intval($filterMonth)]);
        }

        // Get all invoices for calculation
        $invoices = $invoicesQuery->with('status')->get();

        $totalPaid = 0;
        $totalUnpaid = 0;
        $totalAmount = 0;
        $countPaid = 0;
        $countUnpaid = 0;
        $countTotal = 0;

        foreach ($invoices as $invoice) {
            $tagihan = floatval($invoice->tagihan ?? 0);
            $tambahan = floatval($invoice->tambahan ?? 0);
            $tunggakan = floatval($invoice->tunggakan ?? 0);
            $saldo = floatval($invoice->saldo ?? 0);
            $invoiceTotal = $tagihan + $tambahan + $tunggakan - $saldo;

            $totalAmount += $invoiceTotal;
            $countTotal++;

            if ($invoice->status && $invoice->status->nama_status == 'Sudah Bayar') {
                $totalPaid += $invoiceTotal;
                $countPaid++;
            } else {
                $totalUnpaid += $invoiceTotal;
                $countUnpaid++;
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
     * Calculate statistics with status filter included
     */
    private function calculateStatisticsWithStatus($agenId, $filterMonth, $searchTerm = '', $statusFilter = '')
    {
        // Base query for invoices from this agent's customers
        $invoicesQuery = Invoice::whereHas('customer', function($q) use ($agenId, $searchTerm) {
                $q->where('agen_id', $agenId)
                  ->whereIn('status_id', [3, 9]);

                // Apply search filter if provided
                if (!empty($searchTerm)) {
                    $q->where(function($subQ) use ($searchTerm) {
                        $subQ->where('nama_customer', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('alamat', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('no_hp', 'LIKE', "%{$searchTerm}%");
                    });
                }
            });

        // Apply month filter if not showing all
        if ($filterMonth !== 'all') {
            $invoicesQuery->whereRaw("MONTH(STR_TO_DATE(jatuh_tempo, '%Y-%m-%d')) = ?", [intval($filterMonth)]);
        }

        // Apply status filter if provided
        if (!empty($statusFilter)) {
            if ($statusFilter === 'Sudah Bayar') {
                $invoicesQuery->whereHas('status', function($q) {
                    $q->where('nama_status', 'Sudah Bayar');
                });
            } elseif ($statusFilter === 'Belum Bayar') {
                $invoicesQuery->whereHas('status', function($q) {
                    $q->where('nama_status', '!=', 'Sudah Bayar');
                });
            }
        }

        // Get all invoices for calculation
        $invoices = $invoicesQuery->with('status')->get();

        $totalPaid = 0;
        $totalUnpaid = 0;
        $totalAmount = 0;
        $countPaid = 0;
        $countUnpaid = 0;
        $countTotal = 0;

        foreach ($invoices as $invoice) {
            $tagihan = floatval($invoice->tagihan ?? 0);
            $tambahan = floatval($invoice->tambahan ?? 0);
            $invoiceTotal = $tagihan + $tambahan;

            $totalAmount += $invoiceTotal;
            $countTotal++;

            if ($invoice->status && $invoice->status->nama_status == 'Sudah Bayar') {
                $totalPaid += $invoiceTotal;
                $countPaid++;
            } else {
                $totalUnpaid += $invoiceTotal;
                $countUnpaid++;
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
                'tagihan'   => $invoice->tagihan,
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

                if (!$sudahAda) {
                    Invoice::create([
                        'customer_id'    => $invoice->customer_id,
                        'paket_id'       => $customer->paket_id,
                        'tagihan'        => $customer->paket->harga,
                        'tambahan'       => $newTambahan,
                        'tunggakan'      => $newTunggakan,
                        'saldo'          => $saldoBaru,
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


    public function pelanggan()
    {
        $pelanggan = Customer::where('agen_id', auth()->user()->id)->paginate(10);
        // dd($pelanggan->nama_customer);
        return view('agen.pelanggan-agen',[
            'users' => Auth::user(),
            'roles' => Auth::user()->roles,
            'pelanggan' => $pelanggan
        ]);
    }


}
