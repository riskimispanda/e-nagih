<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Customer as CustomerModel;
use App\Models\User;
use App\Models\JPengaduan;
use App\Models\Pengaduan;
use App\Models\Invoice;
use App\Models\Metode;
use App\Events\UpdateBaru;

class Customer extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customer = CustomerModel::where('nama_customer', Auth::user()->name)->first();
        $invoice = Invoice::where('customer_id', $customer->id)->first();
        $metode = Metode::where('nama_metode', 'Transfer')->first();
        event(new UpdateBaru($customer->toArray(), 'success', 'Data pelanggan telah diperbarui'));

        return view('/pelanggan/dashboard-pelanggan', [
            'users' => Auth::user(),
            'roles' => auth()->user()->roles,
            'customer' => $customer,
            'invoice' => $invoice,
            'metode' => $metode,
        ]);
    }

    public function pengaduan()
    {
        return view('/pelanggan/pengaduan', [
            'users' => Auth::user(),
            'roles' => auth()->user()->roles,
            'customer' => CustomerModel::where('nama_customer', Auth::user()->name)->first(),
            'admin' => User::where('roles_id', 7)->get(),
            'jenis' => JPengaduan::all(),
        ]);
    }

    public function history()
    {
        return view('/pelanggan/history',[
            'users' => Auth::user(),
            'roles' => auth()->user()->roles,
            'customer' => CustomerModel::where('nama_customer', Auth::user()->name)->first(),
            'pengaduan' => Pengaduan::where('customer_id', CustomerModel::where('nama_customer', Auth::user()->name)->first()->id)->get(),
        ]);
    }

    public function req()
    {
        return view('/pelanggan/request',[
            'users' => Auth::user(),
            'roles' => auth()->user()->roles,
            'customer' => CustomerModel::where('nama_customer', Auth::user()->name)->first(),
        ]);
    }

    public function addPengaduan(Request $request)
    {
        // dd($request->all());
        $pengaduan = $request->id_pengaduan;
        $customer = $request->customer_id;
        $judul = $request->judul;
        $deskripsi = $request->deskripsi;
        $lampiran = $request->file('lampiran');
        $status = 1;
        $teknisi = null;

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('uploads/lampiran'), $filename);
            $lampiran = 'uploads/lampiran/' . $filename;
        } else {
            $lampiran = null;
        }

        $data = Pengaduan::create([
            'customer_id' => $customer,
            'pengaduan_id' => $pengaduan,
            'status_id' => $status,
            'teknisi_id' => $teknisi,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'lampiran' => $lampiran,
        ]);

        if ($data) {
            return redirect()->back()->with('success', 'Pengaduan created successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to create pengaduan');
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
        $name = $request->input('nama_customer');
        $email = $request->input('email');
        $phone = $request->input('no_hp');
        $address = $request->input('alamat');
        $reg = $request->input('created_at');
        if ($request->hasFile('identitas')) {
            $file = $request->file('identitas');
            $extension = $file->getClientOriginalExtension();

            if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            return back()->with('error', 'File must be jpg or png');
            }

            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/identitas'), $fileName);
            $identitas = 'uploads/identitas/' . $fileName;
        } else {
            $identitas = null;
        }
        $no_identitas = $request->input('no_identitas');
        $gps = $request->input('gps');
        // dd($identitas);
        $data = CustomerModel::create([
            'nama_customer' => $name,
            'email' => $email,
            'no_hp' => $phone,
            'alamat' => $address,
            'identitas' => $identitas,
            'no_identitas' => $no_identitas,
            'gps' => $gps,
            'logistik_id' => Auth::user()->id,
            'status_id' => 1,
            'created_at' => $reg,
        ]);

        $newUser = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('12345678'),
            'roles_id' => 8,
        ]);
        // dd($newUser);
        // dd($data);
        if ($data) {
            return redirect()->back()->with('success', 'Customer created successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to create customer');
        }
    }

    public function dataInvoice(Request $request, $name)
    {
        $customer = CustomerModel::where('nama_customer', $name)->first();

        // Get search query if exists
        $search = $request->get('search');

        // Base query
        $query = Invoice::where('customer_id', $customer->id);

        // Apply search filter if search parameter exists
        if ($search) {
            $query->where(function($q) use ($search) {
                // Search in invoice fields
                $q->where('tagihan', 'like', '%' . $search . '%')
                  ->orWhere('jatuh_tempo', 'like', '%' . $search . '%')
                  // Search in related status
                  ->orWhereHas('status', function($statusQuery) use ($search) {
                      $statusQuery->where('nama_status', 'like', '%' . $search . '%');
                  });
            });
        }

        // Get results ordered by latest
        $invoice = $query->latest()->get();

        return view('/pelanggan/data-invoice', [
            'users' => Auth::user(),
            'roles' => auth()->user()->roles,
            'customer' => $customer,
            'invoice' => $invoice,
        ]);
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
