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

        $user = auth()->user();
        // $coba = $user->roles_id == 6;
        // dd($coba);
        $query = Customer::query();
        if($user->roles_id == 6){
            $query->where('agen_id', $user->id);
        }
        $customer = $query->whereIn('status_id', [1, 2, 5])->paginate(10);

        return view('Helpdesk.data-antrian-helpdesk', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'paket' => Paket::whereNotIn('nama_paket', ['ISOLIREBILLING', 'Dedicated'])->get(),
            'customer' => $customer,
            'corp' => $corp,
            'perusahaan' => $perusahaan,
            'agen' => $agen
        ]);
    }

    // Store
    public function addAntrian(Request $request, ChatServices $chat)
    {
        // dd($request->all());
        $jenis = $request->input('jenis_pelanggan');
        // dd($jenis);
        $rules = [
            'jenis_pelanggan' => 'required',
        ];
    
        if ($jenis == 'Personal') {
            $rules = array_merge($rules, [
                'nama_customer' => 'required',
                'no_hp' => 'required',
                'email' => 'required|email',
                'no_identitas' => 'required',
                'alamat' => 'required',
                'gps' => 'required',
                'paket_id' => 'required',
                'tanggal_reg' => 'required|date',
            ]);
        } elseif ($jenis == 'Perusahaan') {
            $rules = array_merge($rules, [
                'nama_perusahaan' => 'required',
                'nama_pic' => 'required',
                'no_hp' => 'required',
                'alamat' => 'required',
                'gps' => 'required',
                'paket' => 'required',
                'harga' => 'required',
                'tanggal' => 'required|date',
            ]);
        }

        $nomor = preg_replace('/^0/', '62', $request->no_hp);

        // Perusahaan
        if ($jenis == 'Perusahaan') {
            // Perusahaan
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $fileName = time() . '_' . str_replace(' ', '_', $foto->getClientOriginalName());
                $foto->move(public_path('uploads/identitas'), $fileName);
                $img = 'uploads/identitas/' . $fileName;
            } else {
                $img = null;
            }
            // dd($request->all());
            $perusahaan = Perusahaan::create([
                'nama_perusahaan' => $request->nama_perusahaan,
                'nama_pic' => $request->nama_pic,
                'no_hp' => $nomor,
                'alamat' => $request->alamat,
                'gps' => $request->gps,
                'paket_id' => $request->paket,
                'user_id' => auth()->user()->id,
                'status_id' => 5,
                'harga' => $request->harga,
                'speed' => $request->speed,
                'tanggal' => $request->tanggal,
                'foto' => $img,
            ]);
    
            activity('perusahaan')
                ->causedBy(auth()->user())
                ->performedOn($perusahaan)
                ->withProperties([
                    'nama_perusahaan' => $perusahaan->nama_perusahaan,
                    'nama_pic' => $perusahaan->nama_pic,
                    'paket_id' => $perusahaan->paket_id,
                    'harga' => $perusahaan->harga,
                ])
                ->log('Menambahkan data perusahaan baru');

            return redirect()->back()->with('success', 'Perusahaan berhasil didaftarkan');
        }
        

        // Personal
        if ($request->hasFile('identitas_file')) {
            $ktp = $request->file('identitas_file');
            $fileName = time() . '_' . str_replace(' ', '_', $ktp->getClientOriginalName());
            $ktp->move(public_path('uploads/identitas'), $fileName);
            $identitas_file = 'uploads/identitas/' . $fileName;
        } else {
            $identitas_file = null;
        }
        $nomor = preg_replace('/^0/', '62', $request->no_hp);
        $data = Customer::create([
            'nama_customer' => $request->nama_customer,
            'no_hp' => $nomor,
            'alamat' => $request->alamat,
            'gps' => $request->gps,
            'paket_id' => $request->paket_id,
            'status_id' => 1,
            'agen_id' => $request->agen ?? auth()->user()->id,
            'no_identitas' => $request->no_identitas,
            'email' => $request->email,
            'teknisi_id' => null,
            'identitas' => $identitas_file,
            'created_at' => $request->tanggal_reg,
        ]);

        // User::create([
        //     'name' => $request->nama_customer,
        //     'email' => $request->email,
        //     'password' => bcrypt('123456'),
        //     'roles_id' => 8,
        // ]);

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
            ->log('Menambahkan data pelanggan baru');

        if ($data) {
            $chat->CustomerBaru($nomor, $data);
            return redirect()->back()->with('success', 'Antrian created successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to create antrian');
        }
    }

    /**
     * Display the details of a specific queue item
     */
    public function detailAntrian($id)
    {
        $customer = Customer::with(['paket', 'status', 'agen', 'teknisi', 'lokasi'])->findOrFail($id);
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
            'gps' => 'required',
        ]);

        $customer = Customer::findOrFail($id);

        $customer->update([
            'nama_customer' => $request->nama_customer,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'gps' => $request->gps,
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
        $customer->delete();
        return redirect()->back()->with('success', 'Antrian berhasil dihapus');
    }

}
