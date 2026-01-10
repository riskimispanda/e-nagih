<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengaduan;
use App\Models\Paket;
use App\Models\Customer;
use App\Models\User;
use App\Models\Status;
use App\Services\ChatServices;
use App\Models\Perusahaan;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;
use App\Models\ODP;
use Carbon\Carbon;


// Log
use Spatie\Activitylog\Models\Activity;

class HelpdeskController extends Controller
{
    public function dataPengaduan()
    {
        return view('Helpdesk.data-pengaduan', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'pengaduan' => Pengaduan::all(),
        ]);
    }

    /**
     * Get the latest pengaduan data for AJAX polling
     */
    public function getPengaduanData(Request $request)
    {
        // Get the timestamp of the last check (if provided)
        $lastCheck = $request->input('last_check');

        // Get all pengaduan data with relationships
        $pengaduan = Pengaduan::with(['customer', 'pengaduan', 'status', 'teknisi'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Check for new entries since last check
        $newEntries = [];
        if ($lastCheck) {
            $lastCheckTime = \Carbon\Carbon::parse($lastCheck);
            $newEntries = $pengaduan->filter(function($item) use ($lastCheckTime) {
                return $item->created_at > $lastCheckTime ||
                       $item->updated_at > $lastCheckTime;
            })->values();
        }

        return response()->json([
            'success' => true,
            'data' => $pengaduan,
            'count' => $pengaduan->count(),
            'new_entries' => $newEntries,
            'has_new' => count($newEntries) > 0,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }

    public function antrian()
    {
        $corp = Paket::where('nama_paket', 'Dedicated')->get();
        $perusahaan = Perusahaan::all();
        $agen = User::where('roles_id', 6)->get();
        $odp = ODP::all();
        $user = auth()->user();
        // $coba = $user->roles_id == 6;
        // dd($coba);
        $query = Customer::query();
        if($user->roles_id == 6){
            $query->where('agen_id', $user->id);
        }
        $customer = $query->whereIn('status_id', [1, 2, 5])->paginate(10);
        $teknisi = User::where('roles_id', 5)->get();
        return view('Helpdesk.data-antrian-helpdesk', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'paket' => Paket::whereNotIn('nama_paket', ['ISOLIREBILLING', 'Dedicated'])->get(),
            'customer' => $customer,
            'corp' => $corp,
            'perusahaan' => $perusahaan,
            'agen' => $agen,
            'odp' => $odp,
            'teknisi' => $teknisi
        ]);
    }

    // Store
    public function addAntrian(Request $request, ChatServices $chat)
    {
        // dd($request->file('identitas_file'));
        try {
            $jenis = $request->input('jenis_pelanggan');
            $rules = ['jenis_pelanggan' => 'required'];

            if ($jenis == 'Personal') {
                $rules = array_merge($rules, [
                    'nama_customer' => 'required',
                    'no_hp' => 'required',
                    'email' => 'required|email',
                    'no_identitas' => 'required',
                    'alamat' => 'required',
                    'gps' => 'required',
                    'paket_id' => 'required',
                    'tanggal_reg' => 'required|date'
                ]);
            } elseif ($jenis == 'Perusahaan') {
                $rules = array_merge($rules, [
                    'nama_perusahaan' => 'required',
                    'nama_pic' => 'required',
                    'no_hp' => 'required',
                    'alamat' => 'required',
                    'gps' => 'required',
                    'harga' => 'required',
                    'tanggal' => 'required|date',
                    'paket' => 'required|string',
                    'teknisi' => 'required'
                ]);
            }

            $request->validate($rules);
            $nomor = preg_replace('/^0/', '62', $request->no_hp);

            // ==== PERUSAHAAN ====
            if ($jenis == 'Perusahaan') {
                $img = null;
                if ($request->hasFile('foto')) {
                    $foto = $request->file('foto');
                    $fileName = uniqid() . '_' . str_replace(' ', '_', $foto->getClientOriginalName());
                    $savePath = public_path('uploads/identitas/' . $fileName);

                    $imageManager = new ImageManager(Driver::class);
                    $image = $imageManager->read($foto->getRealPath());
                    $image = $image->scale(width: 1024); // Resize
                    $image->toJpeg(75)->save($savePath);

                    $img = 'uploads/identitas/' . $fileName;
                }


                $perusahaan = Perusahaan::create([
                    'nama_perusahaan' => $request->nama_perusahaan,
                    'nama_pic' => $request->nama_pic,
                    'no_hp' => $nomor,
                    'alamat' => $request->alamat,
                    'gps' => $request->gps,
                    'paket' => $request->paket,
                    'user_id' => auth()->id(),
                    'status_id' => 5,
                    'harga' => $request->harga,
                    'speed' => $request->speed,
                    'tanggal' => $request->tanggal,
                    'admin_id' => $request->teknisi,
                    'foto' => $img,
                ]);

                activity('perusahaan')
                    ->causedBy(auth()->user())
                    ->log(auth()->user()->name . ' Menambahkan data perusahaan baru nama perusahaan ' . $perusahaan->nama_perusahaan);

                return redirect()->back()->with('success', 'Perusahaan berhasil didaftarkan');
            }

            // ==== PERSONAL ====
            $identitas_file = null;

            if ($request->hasFile('identitas_file')) {
                $ktp = $request->file('identitas_file');
                $fileName = uniqid() . '_' . str_replace(' ', '_', $ktp->getClientOriginalName());
                $savePath = public_path('uploads/identitas/' . $fileName);

                $imageManager = new ImageManager(Driver::class);
                $image = $imageManager->read($ktp->getRealPath());
                $image = $image->scale(width: 1024); // Resize
                $image->toJpeg(75)->save($savePath);

                $identitas_file = 'uploads/identitas/' . $fileName;
            }
            // dd($identitas_file);
            $data = Customer::create([
                'nama_customer' => $request->nama_customer,
                'no_hp' => $nomor,
                'alamat' => $request->alamat,
                'gps' => $request->gps,
                'paket_id' => $request->paket_id,
                'status_id' => 1,
                'agen_id' => $request->agen ?? auth()->id() ?? 1,
                'no_identitas' => $request->no_identitas,
                'email' => $request->email,
                'teknisi_id' => null,
                'identitas' => $identitas_file,
                'created_at' => $request->tanggal_reg,
            ]);

            $noc = User::where('roles_id', 4)->get();
            activity('customer')
                ->causedBy(auth()->user())
                ->performedOn($data)
                ->withProperties([
                    'nama' => $data->nama_customer,
                    'no_hp' => $data->no_hp,
                    'email' => $data->email,
                    'paket_id' => $data->paket_id,
                    'agen_id' => $data->agen_id,
                ])
                ->log(auth()->user()->name . ' Menambahkan data pelanggan baru ' . $data->nama_customer . ' pada ' . Carbon::parse($data->created_at)->locale('id')->isoFormat('dddd, D MMMM Y H:mm:ss') . ' PIC: ' . ($data->agen?->name ?? '-'));

            // try {
            //     foreach ($noc as $n) {
            //         $nomor = preg_replace('/[^0-9]/', '', $n->no_hp);
            //         if (str_starts_with($nomor, '0')) {
            //             $nomor = '62' . substr($nomor, 1);
            //         }
            //         $chat->kirimNotifikasiNoc($nomor, $n, $data);
            //     }
            // } catch (\Throwable $e) {
            //     Log::error('Gagal kirim WhatsApp: ' . $e->getMessage());
            // }

            return redirect()->back()->with('success', 'Antrian berhasil ditambahkan');
        } catch (\Throwable $e) {
            Log::error('Gagal tambah antrian: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Gagal menambahkan antrian. Cek log server.');
        }
    }


    /**
     * Display the details of a specific queue item
     */
    public function detailAntrian($id)
    {
        $customer = Customer::with(['paket', 'status', 'agen', 'teknisi', 'getServer'])->findOrFail($id);
        $agen = User::where('roles_id', 6)->get();
        return view('Helpdesk.detail-antrian-helpdesk', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customer' => $customer,
            'status' => Status::all(),
            'paket' => Paket::all(),
            'agen' => $agen,
        ]);
    }

    /**
     * Update the specified customer in storage.
     */
    public function updateAntrian(Request $request, $id)
    {
        $request->validate([
            'nama_customer' => 'required',
            'no_hp' => 'required',
            'alamat' => 'required',
        ]);

        $customer = Customer::findOrFail($id);

        $customer->update([
            'nama_customer' => $request->nama_customer,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'gps' => $request->gps ?? null,
            'no_identitas' => $request->no_identitas,
            'paket_id' => $request->paket_id,
            'agen_id' => $request->agen_id,
        ]);

        // Update the associated user if email has changed
        if ($request->email && $customer->email != $request->email) {
            $user = User::where('email', $customer->email)->first();
            if ($user) {
                $user->update([
                    'name' => $request->nama_customer,
                    'email' => $request->email,
                ]);
            }
        }

        return redirect('/helpdesk/detail-antrian/'.$id)->with('success', 'Data pelanggan berhasil diperbarui');
    }

    public function corpDetail($id)
    {
        $perusahaan = Perusahaan::where('id', $id)->first();
        return view('/helpdesk/corp/detail-antrian-corp',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'corp' => $perusahaan,
        ]);
    }

    public function hapusAntrian($id)
    {
        $customer = Customer::find($id);
        $customer->forceDelete();
        activity('delete')
          ->causedBy(auth()->user())
          ->log(auth()->user()->name . " Menghapus data antrian " . $customer->nama_customer);
        return redirect()->back()->with('success', 'Antrian berhasil dihapus');
    }

}
