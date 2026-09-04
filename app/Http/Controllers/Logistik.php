<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perangkat;
use App\Models\KategoriLogistik;
use App\Helpers\LogistikStatus;
use App\Models\ModemDetail;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\TiketOpen;
use App\Models\ODC;
use App\Models\ODP;
use Illuminate\Support\Facades\DB;

class Logistik extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perangkat = Perangkat::with('kategori')
            ->withCount([
                'modem as stok_terpakai' => fn($q) => $q->where('status_id', LogistikStatus::TERPAKAI),
                'modem as stok_maintenance' => fn($q) => $q->where('status_id', LogistikStatus::MAINTENANCE),
                'modem as stok_rusak' => fn($q) => $q->where('status_id', LogistikStatus::RUSAK),
                'customer as customer_count',
            ])
            ->get()
            ->each(function ($item) {
                if (LogistikStatus::isSerialized($item->kategori->nama_logistik ?? '')) {
                    $item->stok_tersedia = max(0, $item->jumlah_stok - $item->stok_terpakai - $item->stok_maintenance - $item->stok_rusak);
                } else {
                    $item->stok_maintenance = 0;
                    $item->stok_rusak = $item->jumlah_rusak ?? 0;
                    $item->stok_terpakai = $item->customer_count ?? 0;
                    $item->stok_tersedia = max(0, $item->jumlah_stok - $item->stok_terpakai - $item->stok_rusak);
                }
            });
        $terpakai = Customer::with('perangkat')->count();
        $tersedia = Perangkat::sum('jumlah_stok');
        
        // Enhanced statistics for Super Admin dashboard
        $damagedDevices = ModemDetail::with('perangkat.kategori')
            ->where('status_id', 15)
            ->count();
        
        $maintenanceDevices = ModemDetail::with('perangkat.kategori')
            ->where('status_id', 4)
            ->count();

        $dismantleItems = \App\Models\Dismantle::with(['teknisi', 'modemDetail'])
            ->orderByDesc('tanggal_dismantle')
            ->get();
        $dismantleCount = $dismantleItems->count();

        $availableForRepair = ModemDetail::with('perangkat.kategori')
            ->where('status_id', 4)
            ->whereNull('customer_id')
            ->count();
        
        // Calculate damaged rate
        $damagedRate = ($damagedDevices > 0) ? floor(($damagedDevices / max($perangkat->count(), 1)) * 100) : 0;
        
        // Inventory value calculation
        $totalInventoryValue = $perangkat->sum(function ($item) {
            return $item->harga * $item->jumlah_stok;
        });
        
        // Stock turnover rate
        $stockTurnoverRate = ($tersedia > 0) ? floor((($damagedDevices + $maintenanceDevices) / $tersedia) * 100) : 0;
        
        // Category distribution (total barang per kategori)
        $categoryDistribution = KategoriLogistik::withSum('logistik', 'jumlah_stok')
            ->get()
            ->map(function ($kategori) {
                return [
                    'kategori' => $kategori->nama_logistik,
                    'count' => (int) ($kategori->logistik_sum_jumlah_stok ?? 0)
                ];
            });
        
        // Data untuk form modal pengadaan (RAB)
        $rab = \App\Models\Rab::all();
        
        return view('dashboard.dashboard-logistik-enhanced', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'perangkat' => $perangkat,
            'terpakai' => $terpakai,
            'tersedia' => $tersedia,
            'damagedDevices' => $damagedDevices,
            'maintenanceDevices' => $maintenanceDevices,
            'dismantleItems' => $dismantleItems,
            'dismantleCount' => $dismantleCount,
            'availableForRepair' => $availableForRepair,
            'damagedRate' => $damagedRate,
            'totalInventoryValue' => $totalInventoryValue,
            'stockTurnoverRate' => $stockTurnoverRate,
            'damagedByCategory' => $categoryDistribution,
            'rab' => $rab
        ]);
    }

    public function statusView(Request $request, $status)
    {
        $config = [
            'tersedia' => [
                'view' => 'logistik.logistik-tersedia',
                'title' => 'Logistik Tersedia',
                'label' => 'Tersedia',
                'field' => 'stok_tersedia',
                'icon' => 'bx-check-circle',
                'accentText' => 'text-green-600',
                'accentBadge' => 'bg-green-100 text-green-700',
            ],
            'terpakai' => [
                'view' => 'logistik.logistik-terpakai',
                'title' => 'Logistik Terpakai',
                'label' => 'Terpakai',
                'field' => 'stok_terpakai',
                'icon' => 'bx-task',
                'accentText' => 'text-orange-600',
                'accentBadge' => 'bg-orange-100 text-orange-700',
            ],
            'barang-rusak' => [
                'view' => 'logistik.logistik-barang-rusak',
                'title' => 'Logistik Barang Rusak',
                'label' => 'Rusak',
                'field' => 'stok_rusak',
                'icon' => 'bx-error-circle',
                'accentText' => 'text-red-600',
                'accentBadge' => 'bg-red-100 text-red-700',
            ],
            'maintenance' => [
                'view' => 'logistik.logistik-maintenance',
                'title' => 'Logistik Dalam Perbaikan',
                'label' => 'Maintenance',
                'field' => 'stok_maintenance',
                'icon' => 'bx-wrench',
                'accentText' => 'text-yellow-600',
                'accentBadge' => 'bg-yellow-100 text-yellow-700',
            ],
        ];

        if (!isset($config[$status])) {
            abort(404);
        }

        $cfg = $config[$status];

        $selectedBulan = $request->input('bulan');
        if ($selectedBulan && !preg_match('/^\d{4}-\d{2}$/', $selectedBulan)) {
            $selectedBulan = null;
        }
        $filterBulan = ($status === 'terpakai') ? $selectedBulan : null;

        if ($status === 'terpakai') {
            $query = ModemDetail::with(['perangkat.kategori', 'customer.teknisi'])
                ->where('status_id', LogistikStatus::TERPAKAI);
            if ($filterBulan) {
                [$year, $month] = explode('-', $filterBulan);
                $query->whereYear('tanggal_terpakai', $year)->whereMonth('tanggal_terpakai', $month);
            }
            $filtered = $query->orderByDesc('tanggal_terpakai')->get();
        } else {
            $perangkatQuery = Perangkat::with('kategori')
                ->withCount([
                    'modem as stok_terpakai' => fn($q) => $q->where('status_id', LogistikStatus::TERPAKAI),
                    'modem as stok_maintenance' => fn($q) => $q->where('status_id', LogistikStatus::MAINTENANCE),
                    'modem as stok_rusak' => fn($q) => $q->where('status_id', LogistikStatus::RUSAK),
                    'customer as customer_count',
                ]);

            if ($filterBulan) {
                [$year, $month] = explode('-', $filterBulan);
                $perangkatQuery->withCount([
                    'modem as stok_terpakai_modem' => fn($q) => $q
                        ->where('status_id', LogistikStatus::TERPAKAI)
                        ->whereYear('tanggal_terpakai', $year)
                        ->whereMonth('tanggal_terpakai', $month),
                    'customer as stok_terpakai_customer' => fn($q) => $q
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month),
                ]);
            }

            $perangkat = $perangkatQuery->get()
                ->each(function ($item) use ($filterBulan) {
                    if (LogistikStatus::isSerialized($item->kategori->nama_logistik ?? '')) {
                        if ($filterBulan) {
                            $item->stok_terpakai = $item->stok_terpakai_modem ?? 0;
                        }
                        $item->stok_tersedia = max(0, $item->jumlah_stok - $item->stok_terpakai - $item->stok_maintenance - $item->stok_rusak);
                    } else {
                        $item->stok_maintenance = 0;
                        $item->stok_rusak = $item->jumlah_rusak ?? 0;
                        if ($filterBulan) {
                            $item->stok_terpakai = $item->stok_terpakai_customer ?? 0;
                        } else {
                            $item->stok_terpakai = $item->customer_count ?? 0;
                        }
                        $item->stok_tersedia = max(0, $item->jumlah_stok - $item->stok_terpakai - $item->stok_rusak);
                    }
                });

            $filtered = $perangkat->filter(fn($item) => ($item->{$cfg['field']} ?? 0) > 0)->values();
        }

        // Khusus barang rusak: tampilkan per tiket tertutup (status 3)
        // agar kolom Customer, Teknisi, Tanggal Closing & Keterangan tersedia.
        if ($status === 'barang-rusak') {
            $query = TiketOpen::with([
                    'customer' => fn($q) => $q->withTrashed()->with('teknisi'),
                    'kategori',
                ])
                ->where('status_id', 3);
            if ($selectedBulan) {
                [$year, $month] = explode('-', $selectedBulan);
                $query->whereYear('tanggal_selesai', $year)
                      ->whereMonth('tanggal_selesai', $month);
            }
            $filtered = $query->orderByDesc('tanggal_selesai')->paginate(10)->withQueryString();
        }

        $bulanOptions = [];
        $now = now();
        for ($i = 11; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i);
            $bulanOptions[] = [
                'value' => $d->format('Y-m'),
                'label' => $d->translatedFormat('F Y'),
            ];
        }

        return view($cfg['view'], [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'perangkat' => $filtered,
            'title' => $cfg['title'],
            'label' => $cfg['label'],
            'field' => $cfg['field'],
            'icon' => $cfg['icon'],
            'accentText' => $cfg['accentText'],
            'accentBadge' => $cfg['accentBadge'],
            'selectedBulan' => $selectedBulan,
            'bulanOptions' => $bulanOptions,
        ]);
    }

    public function dismantleView(Request $request)
    {
        $selectedBulan = $request->input('bulan');
        if ($selectedBulan && !preg_match('/^\d{4}-\d{2}$/', $selectedBulan)) {
            $selectedBulan = null;
        }

        $query = \App\Models\Dismantle::with(['teknisi', 'modemDetail'])
            ->orderByDesc('tanggal_dismantle');

        if ($selectedBulan) {
            [$year, $month] = explode('-', $selectedBulan);
            $query->whereYear('tanggal_dismantle', $year)
                  ->whereMonth('tanggal_dismantle', $month);
        }

        $dismantles = $query->get();

        $bulanOptions = [];
        $now = now();
        for ($i = 11; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i);
            $bulanOptions[] = [
                'value' => $d->format('Y-m'),
                'label' => $d->translatedFormat('F Y'),
            ];
        }

        return view('logistik.logistik-dismantle', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'dismantles' => $dismantles,
            'title' => 'Logistik Barang Dismantle',
            'label' => 'Dismantle',
            'icon' => 'bx-package',
            'accentText' => 'text-purple-600',
            'selectedBulan' => $selectedBulan,
            'bulanOptions' => $bulanOptions,
        ]);
    }

    public function dismantleDelete($id)
    {
        $dismantle = \App\Models\Dismantle::findOrFail($id);
        $dismantle->delete();

        return redirect('/logistik/dismantle')
            ->with('toast_success', 'Data dismantle berhasil dihapus');
    }

    public function dismantleBulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect('/logistik/dismantle')
                ->with('toast_error', 'Tidak ada data yang dipilih');
        }

        \App\Models\Dismantle::whereIn('id', $ids)->delete();

        return redirect('/logistik/dismantle')
            ->with('toast_success', count($ids) . ' data dismantle berhasil dihapus');
    }

    public function tambahKategori(Request $request)
    {
        $kategori = new KategoriLogistik();
        $kategori->nama_logistik = $request->nama_logistik;
        $kategori->save();
        return redirect('/data/logistik')->with('toast_success','Berhasil Menambah Kategori Untuk Logistik');
    }

    public function editLogistik($id)
    {
        $log = Perangkat::with('kategori')->findOrFail($id);
        $data = KategoriLogistik::all();
        $modemDetails = ModemDetail::where('logistik_id', $id)->get();
        return view('/data/edit-logistik',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'log' => $log,
            'data' => $data,
            'modemDetails' => $modemDetails,
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */

    public function TiketBarang()
    {
        $kategori = KategoriLogistik::whereNotIn('nama_logistik', ['Modem', 'Tenda', 'HTB', 'Kabel'])->get();
        $perangkat = Perangkat::with('kategori')
            ->whereHas('kategori', function ($q) {
                $q->whereNotIn('nama_logistik', ['Tenda', 'Modem', 'Kabel', 'HTB']);
            })->get();
        return view('logistik.tiket-barang-keluar', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'kategori' => $kategori,
            'perangkat' => $perangkat
        ]);
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $nama_perangkat = $request->input('nama_perangkat');
        $jumlah = (int)$request->input('jumlah_stok');
        $harga = (int) str_replace(['Rp', '.', ' '], '', $request->input('harga'));

        $data = [
            'nama_perangkat' => $nama_perangkat,
            'jumlah_stok' => $jumlah,
            'harga' => $harga,
            'kategori_id' => $request->input('kategori_id')
        ];

        $jumlahRusak = (int) $request->input('jumlah_rusak', 0);
        if ($jumlahRusak < 0) {
            $jumlahRusak = 0;
        }

        $perangkat = Perangkat::create($data);

        $kategoriNama = strtolower($perangkat->kategori->nama_logistik ?? '');

        // Splitter: input rasio + SN per unit, buat 1 ModemDetail per unit
        if ($kategoriNama === 'splitter') {
            $rasio = $request->input('rasio');
            $serials = $request->input('serial_number', []);
            $isRusakArr = $request->input('is_rusak', []);
            $hasPerUnitRusak = is_array($isRusakArr) && count($isRusakArr) > 0;
            $terpakai = max(0, $jumlah - $jumlahRusak);
            for ($i = 0; $i < $jumlah; $i++) {
                $sn = trim($serials[$i] ?? '');
                $rusakPerUnit = $hasPerUnitRusak ? !empty($isRusakArr[$i] ?? null) : ($i >= $terpakai);
                ModemDetail::create([
                    'logistik_id'   => $perangkat->id,
                    'serial_number' => $sn ?: null,
                    'mac_address'   => null,
                    'rasio'         => $rasio,
                    'status_id'     => $rusakPerUnit ? LogistikStatus::RUSAK : LogistikStatus::TERSEIDA,
                ]);
            }
        }
        // Jika ada input SN/MAC, buat ModemDetail untuk setiap unit
        elseif ($request->has('serial_number') && is_array($request->serial_number)) {
            $serials = $request->serial_number;
            $macs = $request->mac_address ?? [];
            $isRusakArr = $request->input('is_rusak', []);

            $units = [];
            foreach ($serials as $i => $sn) {
                $sn = trim($sn);
                $mac = trim($macs[$i] ?? '');
                if (empty($sn)) continue;
                $units[] = [
                    'sn'    => $sn,
                    'mac'   => $mac,
                    'rusak' => !empty($isRusakArr[$i] ?? null),
                ];
            }

            foreach ($units as $unit) {
                ModemDetail::create([
                    'logistik_id' => $perangkat->id,
                    'serial_number' => $unit['sn'],
                    'mac_address' => $unit['mac'],
                    'status_id' => $unit['rusak'] ? LogistikStatus::RUSAK : LogistikStatus::TERSEIDA,
                ]);
            }
        } else {
            // Kategori non-serial (kabel/dropcore, dsb): simpan jumlah rusak di kolom perangkat
            $perangkat->jumlah_rusak = min($jumlahRusak, $jumlah);
            $perangkat->save();
        }

        return redirect('/data/logistik')->with('toast_success', 'Perangkat Berhasil ditambahkan');
    }

    public function updateLogistik(Request $request, $id)
    {
        $perangkat = Perangkat::findOrFail($id);
        $perangkat->update([
            'nama_perangkat' => $request->nama_perangkat,
            'jumlah_stok' => $request->stok,
            'harga' => $request->harga,
            'kategori_id' => $request->kategori,
        ]);

        // Update ModemDetail
        $kategoriNama = strtolower($perangkat->kategori->nama_logistik ?? '');

        if (!LogistikStatus::isSerialized($kategoriNama)) {
            $perangkat->jumlah_rusak = min((int) $request->jumlah_rusak, (int) $request->stok);
            $perangkat->save();
        }

        if ($kategoriNama === 'splitter') {
            // Splitter: perbarui rasio pada semua unit
            $rasio = $request->input('rasio');
            ModemDetail::where('logistik_id', $perangkat->id)->update(['rasio' => $rasio]);

            // Perbarui / tambah SN per unit
            $submittedIds = [];
            $editIsRusak = $request->input('edit_is_rusak', []);
            if ($request->has('edit_serial_number') && is_array($request->edit_serial_number)) {
                foreach ($request->edit_serial_number as $i => $sn) {
                    $sn = trim($sn);
                    $detailId = $request->edit_detail_id[$i] ?? null;
                    if (empty($sn)) continue;

                    if ($detailId) {
                        $md = ModemDetail::find($detailId);
                        if ($md) {
                            if (!empty($editIsRusak[$i] ?? null)) {
                                $statusId = LogistikStatus::RUSAK;
                            } elseif ((int) $md->status_id === LogistikStatus::TERPAKAI) {
                                $statusId = LogistikStatus::TERPAKAI;
                            } else {
                                $statusId = LogistikStatus::TERSEIDA;
                            }
                            $md->update([
                                'serial_number' => $sn,
                                'status_id' => $statusId,
                            ]);
                            $submittedIds[] = $md->id;
                        }
                    } else {
                        $md = ModemDetail::create([
                            'logistik_id'   => $perangkat->id,
                            'serial_number' => $sn,
                            'mac_address'   => null,
                            'rasio'         => $rasio,
                            'status_id'     => LogistikStatus::TERSEIDA,
                        ]);
                        $submittedIds[] = $md->id;
                    }
                }
            }

            // Selaraskan jumlah unit tersedia (tidak menghapus unit terpakai)
            $existing = ModemDetail::where('logistik_id', $perangkat->id)->count();
            $target = (int) $request->stok;
            for ($i = $existing; $i < $target; $i++) {
                ModemDetail::create([
                    'logistik_id'   => $perangkat->id,
                    'serial_number' => null,
                    'mac_address'   => null,
                    'rasio'         => $rasio,
                    'status_id'     => LogistikStatus::TERSEIDA,
                ]);
            }
        } elseif ($request->has('edit_serial_number') && is_array($request->edit_serial_number)) {
            $submittedIds = [];
            $editIsRusak = $request->input('edit_is_rusak', []);
            foreach ($request->edit_serial_number as $i => $sn) {
                $sn = trim($sn);
                $mac = trim($request->edit_mac_address[$i] ?? '');
                $detailId = $request->edit_detail_id[$i] ?? null;
                if (empty($sn)) continue;

                if ($detailId) {
                    $md = ModemDetail::find($detailId);
                    if ($md) {
                        if (!empty($editIsRusak[$i] ?? null)) {
                            $statusId = LogistikStatus::RUSAK;
                        } elseif ((int) $md->status_id === LogistikStatus::TERPAKAI) {
                            $statusId = LogistikStatus::TERPAKAI;
                        } else {
                            $statusId = LogistikStatus::TERSEIDA;
                        }
                        $md->update([
                            'serial_number' => $sn,
                            'mac_address' => $mac,
                            'status_id' => $statusId,
                        ]);
                        $submittedIds[] = $md->id;
                    }
                } else {
                    $md = ModemDetail::create([
                        'logistik_id' => $perangkat->id,
                        'serial_number' => $sn,
                        'mac_address' => $mac,
                        'status_id' => LogistikStatus::TERSEIDA,
                    ]);
                    $submittedIds[] = $md->id;
                }
            }

            // Hapus ModemDetail yang statusnya Tersedia tapi tidak dikirim ulang
            ModemDetail::where('logistik_id', $perangkat->id)
                ->where('status_id', LogistikStatus::TERSEIDA)
                ->whereNotIn('id', $submittedIds)
                ->delete();
        }

        return redirect('/data/logistik')->with('toast_success', 'Data Berhasil diperbarui');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function deleteLogistik($id)
    {
        try {
            $logistik = Perangkat::findOrFail($id); // pakai findOrFail biar langsung throw error kalau tidak ada
            $logistik->delete();

            return redirect('/data/logistik')->with('toast_success', 'Berhasil hapus perangkat');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect('/data/logistik')->with('toast_error', 'Perangkat tidak ditemukan');
        } catch (\Exception $e) {
            // log error untuk debugging
            Log::error('Gagal hapus perangkat: '.$e->getMessage());

            return redirect('/data/logistik')->with('toast_error', 'Terjadi kesalahan saat menghapus perangkat');
        }
    }

    public function tracking(Request $request)
    {
        $search = $request->input('search');

        $data = ModemDetail::with('perangkat.kategori', 'status', 'customer')
            ->whereHas('perangkat.kategori', function ($q) {
                $q->whereIn(DB::raw('LOWER(nama_logistik)'), ['modem', 'tenda', 'sfp', 'olt', 'odp', 'odc', 'htb']);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('customer', function ($qc) use ($search) {
                        $qc->where('nama_customer', 'like', "%{$search}%");
                    })
                        ->orWhereHas('perangkat', function ($qp) use ($search) {
                            $qp->where('nama_perangkat', 'like', "%{$search}%");
                        })
                        ->orWhere('mac_address', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('logistik.tracking-tools', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $data
        ]);
    }

    public function perbaikiBarang($id)
    {
        try {
            $device = ModemDetail::findOrFail($id);
            $device->update([
                'status_id' => 14, // Tersedia
                'customer_id' => null
            ]);
            return redirect()->back()->with('toast_success', 'Barang berhasil diperbaiki dan dikembalikan ke stok Tersedia.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbaiki barang: ' . $e->getMessage());
            return redirect()->back()->with('toast_error', 'Gagal memperbaiki barang: ' . $e->getMessage());
        }
    }

    public function setMaintenanceBarang($id)
    {
        try {
            $device = ModemDetail::findOrFail($id);
            $device->update([
                'status_id' => 4, // Maintenance
                'customer_id' => null
            ]);
            return redirect()->back()->with('toast_success', 'Barang berhasil dipindahkan ke status Maintenance.');
        } catch (\Exception $e) {
            Log::error('Gagal memindahkan ke maintenance: ' . $e->getMessage());
            return redirect()->back()->with('toast_error', 'Gagal memindahkan ke maintenance: ' . $e->getMessage());
        }
    }

    public function afkirBarang($id)
    {
        try {
            $device = ModemDetail::findOrFail($id);
            $device->update([
                'status_id' => 15, // Rusak
                'customer_id' => null
            ]);
            return redirect()->back()->with('toast_success', 'Barang berhasil di-afkir ke status Rusak.');
        } catch (\Exception $e) {
            Log::error('Gagal meng-afkir barang: ' . $e->getMessage());
            return redirect()->back()->with('toast_error', 'Gagal meng-afkir barang: ' . $e->getMessage());
        }
    }

    public function buangBarang($id)
    {
        try {
            $device = ModemDetail::findOrFail($id);
            
            DB::beginTransaction();
            
            $perangkat = Perangkat::find($device->logistik_id);
            if ($perangkat && $perangkat->jumlah_stok > 0) {
                $perangkat->decrement('jumlah_stok');
            }
            
            $device->delete();
            
            DB::commit();
            return redirect()->back()->with('toast_success', 'Barang rusak berhasil dihapus permanen dari sistem.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus barang rusak: ' . $e->getMessage());
            return redirect()->back()->with('toast_error', 'Gagal menghapus barang rusak: ' . $e->getMessage());
        }
    }


}
