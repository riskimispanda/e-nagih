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
        Log::info('requestPembayaran called', [
            'invoice_id' => $id,
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        try {
            // Validasi input
            $request->validate([
                'jumlah_bayar' => 'required|numeric|min:1',
                'metode_id' => 'required|string',
                'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'jumlah_bayar.required' => 'Jumlah pembayaran harus diisi',
                'jumlah_bayar.numeric' => 'Jumlah pembayaran harus berupa angka',
                'jumlah_bayar.min' => 'Jumlah pembayaran minimal 1',
                'metode_id.required' => 'Metode pembayaran harus dipilih',
                'bukti_pembayaran.image' => 'File bukti pembayaran harus berupa gambar',
                'bukti_pembayaran.mimes' => 'Format file yang diizinkan: jpeg, png, jpg, gif',
                'bukti_pembayaran.max' => 'Ukuran file maksimal 2MB'
            ]);

            DB::transaction(function () use ($request, $id) {
                $invoice = Invoice::findOrFail($id);
                $customer = $invoice->customer;

                // Upload bukti pembayaran
                $buktiPath = null;
                if ($request->hasFile('bukti_pembayaran')) {
                    $file = $request->file('bukti_pembayaran');
                    $fileName = 'bukti_' . time() . '_' . $invoice->id . '.' . $file->getClientOriginalExtension();
                    $buktiPath = $file->storeAs('bukti_pembayaran', $fileName, 'public');
                }

                // Buat pembayaran baru
                $pembayaran = Pembayaran::create([
                    'invoice_id'   => $invoice->id,
                    'jumlah_bayar' => $request->jumlah_bayar,
                    'tanggal_bayar'=> now(),
                    'metode_bayar' => $request->metode_id,
                    'keterangan'   => 'Pembayaran dari agen ' . Auth::user()->name . ' untuk pelanggan ' . $customer->nama_customer,
                    'bukti_bayar'  => $buktiPath,
                    'status_id'    => 8, // Menunggu konfirmasi
                    'user_id'      => Auth::id(),
                ]);

                $tagihanTotal = $invoice->tagihan + $invoice->tambahan - $invoice->saldo;
                $tunggakan = max($tagihanTotal - $pembayaran->jumlah_bayar, 0);

                // Kirim notifikasi pelanggan
                $chat = new ChatServices();
                $chat->pembayaranBerhasil($customer->no_hp, $pembayaran);

                // Update status customer
                $customer->update(['status_id' => 3]);

                // Unblok jaringan jika status sebelumnya 9
                if ($customer->status_id == 9) {
                    $mikrotik = new MikrotikServices();
                    $client = MikrotikServices::connect($customer->router);
                    $mikrotik->removeActiveConnections($client, $customer->usersecret);
                    $mikrotik->unblokUser($client, $customer->usersecret, $customer->paket->paket_name);
                }

                // Update status invoice
                $invoice->update(['status_id' => 8]);

                // Tentukan tanggal invoice baru
                $tanggalAwal = Carbon::parse($invoice->jatuh_tempo)->addMonthsNoOverflow()->startOfMonth();
                $tanggalJatuhTempo = $tanggalAwal->copy()->endOfMonth();
                $tanggalBlokir = $invoice->tanggal_blokir;

                // Cek apakah invoice bulan berikutnya sudah ada
                $sudahAda = Invoice::where('customer_id', $invoice->customer_id)
                    ->whereMonth('jatuh_tempo', $tanggalJatuhTempo->month)
                    ->whereYear('jatuh_tempo', $tanggalJatuhTempo->year)
                    ->exists();

                if (!$sudahAda) {
                    Invoice::create([
                        'customer_id'    => $invoice->customer_id,
                        'paket_id'       => $customer->paket_id,
                        'tagihan'        => $customer->paket->harga,
                        'tambahan'       => 0,
                        'saldo'          => $pembayaran->saldo,
                        'status_id'      => 7, // Belum bayar
                        'created_at'     => $tanggalAwal,
                        'updated_at'     => $tanggalAwal,
                        'jatuh_tempo'    => $tanggalJatuhTempo,
                        'tanggal_blokir' => $tanggalBlokir,
                        'tunggakan'      => $tunggakan,
                    ]);
                }

                // Buat catatan kas
                $kas = Kas::create([
                    'debit'       => $pembayaran->jumlah_bayar,
                    'kas_id'      => 1,
                    'keterangan'  => 'Pembayaran diterima dari ' . $pembayaran->invoice->customer->nama_customer,
                    'tanggal_kas' => $pembayaran->tanggal_bayar,
                    'user_id'     => $pembayaran->user_id,
                    'status_id'   => 3
                ]);

                Log::info('Sukses pembayaran', [
                    'debit'      => $kas->debit,
                    'kas_id'     => $kas->kas_id,
                    'keterangan' => $kas->keterangan
                ]);
            });

            return redirect()->back()->with('success', 'Request pembayaran berhasil dikirim dan menunggu konfirmasi admin.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Terjadi kesalahan validasi. Silakan periksa input Anda.');
        } catch (\Exception $e) {
            Log::error('Error in requestPembayaran: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.')
                ->withInput();
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
