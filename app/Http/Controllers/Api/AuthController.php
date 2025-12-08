<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Pembayaran;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('name', $request->name)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'name' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Delete semua token yang sudah ada
        $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('auth_token')->plainTextToken;

        // Inisialisasi customer_id
        $customerId = null;

        // Cek jika user adalah customer (roles_id == 8)
        if($user->roles_id == 8){
          $customer = Customer::where('user_id', $user->id)->first();
          if($customer) {
              $customerId = $customer->id;
          }
        }

        activity('NbillingApps')
            ->causedBy(auth()->user())
            ->log($user->name . ' Login ke aplikasi');

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'customer_id' => $customerId,
            'roles' => $user->roles->name,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function invoiceCustomer(Request $request)
    {
        // Mengambil langsung nilai id customer
        $customerId = Customer::where('user_id', $request->user()->id)
            ->value('id'); // Mengembalikan langsung nilai id atau null

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer data not found'
            ], 404);
        }

        $invoice = Invoice::where('customer_id', $customerId)->latest()->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tagihan' => $invoice->tagihan ?? 0,
                'tambahan' => $invoice->tambahan ?? 0,
                'tunggakan' => $invoice->tunggakan ?? 0,
                'saldo' => $invoice->saldo ?? 0,
                'status' => $invoice->status->nama_status
            ]
        ]);
    }

    public function allCustomer()
    {
        // Optimasi query dengan eager loading dan filter relasi
        $customers = Customer::with([
                'paket:id,nama_paket',
                'agen:id,name'
            ])
            ->select('id', 'nama_customer', 'paket_id', 'agen_id')
            ->whereIn('status_id', [3, 4, 9])
            ->whereNull('deleted_at')
            ->get();

        // Transform data sesuai kebutuhan
        $formattedCustomers = $customers->map(function($customer) {
            return [
                'id' => $customer->id,
                'nama' => $customer->nama_customer,
                'paket' => $customer->paket->nama_paket
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedCustomers,
            'count' => $customers->count()
        ]);
    }

}
