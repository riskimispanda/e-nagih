<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use App\Services\MikrotikServices;
use App\Models\Koneksi;
use App\Models\Perangkat;
use App\Models\Server as ServerModels;
use App\Models\Lokasi;
use App\Models\ODC;
use App\Models\ODP;
use App\Models\MediaKoneksi;
use App\Models\Invoice;
use App\Models\KategoriLogistik;
use App\Models\Perusahaan;
use App\Services\ChatServices;
use App\Models\Router;
use Intervention\Image\ImageManager as Image;
use Intervention\Image\Drivers\Gd\Driver;
// use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use App\Models\ModemDetail;
use Spatie\Activitylog\Models\Activity;


class TeknisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    //  Rumus Prorate
     private function calculateProrate($hargaPaket, $tanggalInstalasi)
     {
         // Mendapatkan jumlah hari dalam bulan
         $jumlahHariDalamBulan = $tanggalInstalasi->daysInMonth;
         // Mendapatkan hari terakhir bulan
         $akhirBulan = $tanggalInstalasi->copy()->endOfMonth();
         // Menghitung jumlah hari tersisa dalam bulan (termasuk hari instalasi)
         $jumlahHariTersisa = $tanggalInstalasi->diffInDays($akhirBulan) + 1;
         // Menghitung tagihan prorata
         $tagihanProrata = ($hargaPaket / $jumlahHariDalamBulan) * $jumlahHariTersisa;
         // Membulatkan ke bilangan bulat (opsional, sesuaikan dengan kebutuhan)
         return round($tagihanProrata);
     }

    public function index()
    {
        $teknisi = auth()->user()->id;
        $customers = Customer::all();
        $customersWithTeknisi = Customer::whereNotNull('teknisi_id')->exists();
        $user = auth()->user();

        $corp = Perusahaan::where('status_id', 1)
            ->when($user->roles_id != 4, function ($query) use ($user) {
                $query->where('admin_id', $user->id);
            })
            ->get();

        return view('/teknisi/data-antrian-teknisi', [
            'data' => $customers,
            'antrian' => Customer::where('status_id', 2)->get(),
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customersWithTeknisi' => $customersWithTeknisi,
            'corp' => $corp
        ]);
    }

    public function selesai($id)
    {
        $customer = Customer::with('router')->findOrFail($id);
        $customer->status_id = 2;
        $customer->teknisi_id = auth()->user()->id;
        $customer->save();

        // Connect ke router
        $router = $customer->router; // pastikan relasi 'router' sudah didefinisikan di model Customer
        $client = MikrotikServices::connect($router);

        // Dapatkan profil router
        $profile = MikrotikServices::getProfile($client);
        \Log::info('Router Info Saat Selesai:', $profile);

        return redirect()->back()->with('toast_success', 'Antrian dipilih');
    }

    public function print($id)
    {
        $customer = Customer::with('router')->findOrFail($id);
        $customer->status_id = 2;
        $customer->save();

        $modem = Perangkat::with('kategori')
            ->whereHas('kategori', function ($q) {
                $q->whereIn('nama_logistik', ['modem', 'tenda']);
            })
            ->where('jumlah_stok', '>', 0)
            ->get();

        $client = MikrotikServices::connect($customer->router);

        return view('/teknisi/detail-antrian', [
            'customer' => $customer,
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'mikrotik' => MikrotikServices::getProfile($client), // ✅ benar
            'paket' => MikrotikServices::getProfiles($client),   // ✅ benar
            'koneksi' => Koneksi::all(),
            'modem' => $modem,
            'server' => ServerModels::all(),
            'olt' => Lokasi::all(),
            'odc' => ODC::all(),
            'odp' => ODP::all(),
            'media' => MediaKoneksi::all(),
        ]);
    }


    public function getByServer($serverId)
    {
        $olt = Lokasi::where('id_server', $serverId)->get();
        return response()->json($olt);
    }

    public function getByOdc($odcId)
    {
        $odc = ODC::where('lokasi_id', $odcId)->get();
        // dd($odc);
        return response()->json($odc);
    }

    public function getByOdp($odpId)
    {
        $odp = ODP::where('odc_id', $odpId)->get();
        return response()->json($odp);
    }

    public function konfirmasi(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        DB::beginTransaction(); // Mulai transaksi

        try {
            $image = new Image(new Driver());

            // Upload & Kompres Foto Rumah
            if ($request->hasFile('foto_rumah')) {
                $fotoRumahFile = $request->file('foto_rumah');
                $fotoRumahName = time() . '_' . str_replace(' ', '_', $fotoRumahFile->getClientOriginalName());
                $fotoRumahPath = 'uploads/foto_rumah/' . $fotoRumahName;

                $img = $image->read($fotoRumahFile)
                    ->resize(1024, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->toJpeg(75);

                $img->save(public_path($fotoRumahPath));
            } else {
                $fotoRumahPath = null;
            }

            // Upload & Kompres Foto Perangkat
            if ($request->hasFile('foto_perangkat')) {
                $fotoPerangkatFile = $request->file('foto_perangkat');
                $fotoPerangkatName = time() . '_' . str_replace(' ', '_', $fotoPerangkatFile->getClientOriginalName());
                $fotoPerangkatPath = 'uploads/foto_perangkat/' . $fotoPerangkatName;

                $img = $image->read($fotoPerangkatFile)
                    ->resize(1024, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->toJpeg(75);

                $img->save(public_path($fotoPerangkatPath));
            } else {
                $fotoPerangkatPath = null;
            }

            $tanggalSelesai = now();

            $customer->update([
                'panjang_kabel'   => $request->panjang_kabel,
                'redaman'         => $request->redaman,
                'transiver'       => $request->transiver,
                'receiver'        => $request->receiver,
                'access_point'    => $request->access_point,
                'station'         => $request->station,
                'media_id'        => $request->media_id,
                'tanggal_selesai' => $tanggalSelesai,
                'lokasi_id'       => $request->odp,
                'status_id'       => 3,
                'foto_rumah'      => $fotoRumahPath,
                'foto_perangkat'  => $fotoPerangkatPath,
                'mac_address'     => $request->mac_address,
                'perangkat_id'    => $request->modem,
                'seri_perangkat'  => $request->serial_number,
                'gps'             => $request->gps,
            ]);

            LOG::info('Customer updated', [
                'customer_id' => $customer->id,
                'tanggal_selesai' => $tanggalSelesai,
            ]);

            $jatuhTempo = $tanggalSelesai->copy()->endOfMonth();

            $panjangKabel = (int) $request->panjang_kabel;
            $tagihanTambahan = $panjangKabel > 200 ? ($panjangKabel - 200) * 1000 : 0;

            $hargaPaket = $customer->paket->harga;
            $tagihanProrate = ($tanggalSelesai->day == 1)
                ? $hargaPaket
                : $this->calculateProrate($hargaPaket, $tanggalSelesai);

            // 🔍 Cek apakah sudah ada invoice bulan ini
            $existingInvoice = Invoice::where('customer_id', $customer->id)
                ->whereMonth('jatuh_tempo', $jatuhTempo->month)
                ->whereYear('jatuh_tempo', $jatuhTempo->year)
                ->first();

            if (!$existingInvoice) {
                // Buat invoice baru
                $invoice = Invoice::create([
                    'customer_id'  => $customer->id,
                    'status_id'    => 7,
                    'paket_id'     => $customer->paket_id,
                    'tagihan'      => $tagihanProrate,
                    'jatuh_tempo'  => $jatuhTempo,
                    'tambahan'     => $tagihanTambahan,
                ]);

                LOG::info('Invoice created', [
                    'invoice_id' => $invoice->id,
                    'customer_id' => $customer->id,
                    'tagihan' => $invoice->tagihan,
                    'jatuh_tempo' => $invoice->jatuh_tempo,
                ]);

                // Kirim WA hanya kalau invoice baru dibuat
                $chat = new ChatServices();
                $chat->invoiceProrate($customer->no_hp, $invoice);

                LOG::info('WA sent', [
                    'customer_id' => $customer->id,
                    'no_hp' => $customer->no_hp,
                ]);
            } else {
                LOG::info('Invoice sudah ada, tidak dibuat ulang', [
                    'customer_id' => $customer->id,
                    'invoice_id'  => $existingInvoice->id,
                ]);
            }

            // Simpan modem detail
            ModemDetail::create([
                'serial_number' => $request->serial_number,
                'mac_address' => $request->mac_address,
                'logistik_id' => $request->modem,
                'status_id' => 13,
                'customer_id' => $customer->id,
            ]);

            DB::commit(); // Jika semua sukses, simpan ke DB

            // ✍️ Tambahkan log activity
            activity('teknisi')
                ->causedBy(auth()->user())
                ->performedOn($customer)
                ->withProperties([
                    'nama' => $customer->nama_customer,
                    'no_hp' => $customer->no_hp,
                    'email' => $customer->email,
                    'paket_id' => $customer->paket_id
                ])
                ->log('Konfirmasi instalasi pelanggan');

            return redirect()->route('teknisi')->with('toast_success', 'Instalasi Selesai');
        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua perubahan DB
            Log::info('Gagal konfirmasi instalasi', [
                'customer' => $customer->nama_customer,
                'error' => $e->getMessage(),
            ]);
            return back()->with('toast_error', 'Gagal konfirmasi instalasi. Coba lagi.');
        }
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
}
