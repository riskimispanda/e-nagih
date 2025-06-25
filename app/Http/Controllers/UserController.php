<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

        $user = new User();
        $user->name = $nama;
        $user->email = $email;
        $user->roles_id = $roles_id;
        $user->password = bcrypt('123'); // Set a default 123
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
}
