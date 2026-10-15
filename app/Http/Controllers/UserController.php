<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Roles;

class UserController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $roleFilter = $request->query('role');

        $userQuery = User::query()
            // Kolom seperlunya + eager load role (mencegah N+1 query per baris tabel)
            ->select(['id', 'name', 'email', 'no_hp', 'alamat', 'roles_id', 'profile'])
            ->with('roles:id,name')
            // roles_id bertipe varchar → bandingkan sebagai string agar index terpakai
            // (perbandingan dengan integer memicu implicit CAST dan mematikan index).
            ->where('roles_id', '!=', '8');

        if ($search !== '') {
            $userQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== null && $roleFilter !== '' && ctype_digit((string) $roleFilter)) {
            // Cast ke string (bukan int) agar index roles_id tetap dipakai.
            $userQuery->where('roles_id', (string) $roleFilter);
        }

        $user = $userQuery
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $role = Roles::query()
            ->select(['id', 'name'])
            ->whereNot('name', 'Customer')
            ->orderBy('name')
            ->get();

        // Rekap jumlah user per role dalam 1 query agregat (untuk kartu ringkasan).
        // Bandingkan roles_id sebagai string agar index (roles_id, name) dipakai.
        $roleSummary = User::query()
            ->select('roles_id', DB::raw('count(*) as total'))
            ->where('roles_id', '!=', '8')
            ->groupBy('roles_id')
            ->pluck('total', 'roles_id');

        return view('user_management.management_user', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'user' => $user,
            'role' => $role,
            'roleSummary' => $roleSummary,
            'search' => $search,
            'roleFilter' => $roleFilter,
        ]);
    }

    public function profileUser($id)
    {
        $user = User::find($id);
        return view('/user_management/profile-user', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'user' => $user,
        ]);
    }

    public function updatePhoto(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $profile = $request->file('profile_photo')->getClientOriginalName();
        $profile = time() . '.' . $profile;
        $request->file('profile_photo')->move(public_path('uploads/identitas'), $profile);
        $user->profile = 'uploads/identitas/' . $profile;
        $user->save();

        return redirect()->back()->with('toast_success', 'Foto Berhasil di Update');
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
        $nama = $request->input('name');
        $email = $request->input('email');
        $roles_id = $request->input('roles_id');
        $password = $request->input('password');

        $user = new User();
        $user->name = $nama;
        $user->email = $email;
        $user->roles_id = $roles_id;
        $user->password = $password;
        $user->no_hp = $request->input('no_hp');
        $user->alamat = $request->input('alamat');
        $user->save();
        return redirect()->back()->with('success', 'User created successfully');
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
    public function editRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Update username (kolom `name`, dipakai untuk login) jika diisi & berubah.
        if ($request->filled('name')) {
            $username = trim((string) $request->input('name'));

            if (mb_strlen($username) < 3) {
                return redirect('/user/management')->with('error', 'Username minimal 3 karakter');
            }

            if ($username !== $user->name
                && User::where('name', $username)->where('id', '!=', $user->id)->exists()) {
                return redirect('/user/management')->with('error', 'Username "' . $username . '" sudah dipakai user lain');
            }

            $user->name = $username;
        }

        // update role
        $user->roles_id = $request->input('roles_id');

        // cek apakah admin mengisi field password
        if ($request->filled('password')) {
            if (mb_strlen((string) $request->input('password')) < 8) {
                return redirect('/user/management')->with('error', 'Password minimal 8 karakter');
            }

            $user->password = bcrypt($request->input('password'));
        }

        $user->save();

        return redirect('/user/management')->with('success', 'Data user berhasil diubah');
    }

    public function updateUser(Request $request, $id)
    {
        // dd($request->all());
        $user = User::find($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->alamat = $request->input('address');
        $user->no_hp = $request->input('phone');
        $user->bio = $request->input('bio');
        $user->save();
        return redirect()->back()->with('success', 'Profile berhasil diubah');
    }

    public function updatePassword(Request $request, $id)
    {
        $user = User::find($id);
        $user->password = bcrypt($request->input('password'));
        $user->save();
        return redirect()->back()->with('success', 'Password berhasil diubah');
    }

}
