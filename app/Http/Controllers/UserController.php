<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Roles;

class UserController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index()
    {
        $user = User::whereNot('roles_id', 8)->paginate(10);
        // dd($user);
        $role = Roles::whereNot('name', 'Customer')->get();
        // dd($role);
        return view('user_management.management_user',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'user' => $user,
            'role' => $role
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

        // update role
        $user->roles_id = $request->input('roles_id');

        // cek apakah admin mengisi field password
        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->save();

        return redirect('/user/management')->with('success', 'Role user berhasil diubah');
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
