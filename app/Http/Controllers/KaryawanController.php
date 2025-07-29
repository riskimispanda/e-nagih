<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Roles;

class KaryawanController extends Controller
{
    public function index()
    {
        // $roles = Roles::orderBy('name', 'asc')->get();
        $karyawan = User::whereIn('roles_id', [1, 2, 3, 4, 5, 7])->orderBy('roles_id')->get();
        // dd($karyawan);
        return view('data.data-karyawan',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'karyawan' => $karyawan,
        ]);
    }
}
