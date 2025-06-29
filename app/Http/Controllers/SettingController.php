<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Invoice;

class SettingController extends Controller
{
    public function blokirSetting()
    {
        return view('setting.blokir-setting',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles
        ]);
    }

    public function settBlokir(Request $request)
    {
        Invoice::query()->update([
            'tanggal_blokir' => $request->tanggal_blokir
        ]);
        return redirect()->back()->with('toast_success', 'Berhasil mengubah tanggal blokir');
    }

}
