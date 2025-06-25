<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Roles;
use App\Models\Paket;
use App\Models\Lokasi;
use App\Models\Router;
use App\Models\Koneksi;
use App\Models\Perangkat;
use App\Models\Customer;
use App\Models\Status;
use App\Models\Invoice;
use App\Models\Pembayaran;
use App\Models\Metode;
use App\Services\MikrotikServices;
use App\Events\UpdateBaru;

class Analytics extends Controller
{
  public function index()
  {
    return view("dashboard.dashboard", [
      "users" => auth()->user(),
      "roles" => Roles::find(auth()->user()->roles_id),
    ]);
  }
  
  public function pelanggan()
  {
    // Ambil semua data invoice lengkap dengan relasinya
    $customers = Customer::with([
      "status",
      "paket",
      "lokasi",
      "invoice.status",
      "invoice",
      "getServer",
      ])
      ->whereIn("status_id", [3, 9])
      ->get();
      $metode = Metode::all();
      $pembayaran = Pembayaran::where("status_id", 6)->get();
      
      // Format data sesuai kebutuhan frontend (struktur customer & paket harus ada)
      $customerData = $customers->map(function ($customer) {
        // Get the latest invoice for this customer
        $latestInvoice = $customer->invoice
        ->sortByDesc("created_at")
        ->first();
        
        return [
          "id" => $customer->id,
          "nama_customer" => $customer->nama_customer ?? "Unknown",
          "alamat" => $customer->alamat ?? "",
          "no_hp" => $customer->no_hp ?? "",
          "status_id" => $customer->status_id,
          "status" => [
            "id" => $customer->status->id ?? null,
            "nama_status" =>
            $customer->status->nama_status ?? "Unknown",
          ],
          "paket" => [
            "id" => $customer->paket->id ?? null,
            "nama_paket" => $customer->paket->nama_paket ?? "Unknown",
          ],
          "getServer" => [
            "lokasi_server" =>
            $customer->getServer->lokasi_server ?? "Unknown",
          ],
          "invoice" => $customer->invoice
          ->map(function ($invoice) {
            return [
              "id" => $invoice->id,
              "status" => [
                "id" => $invoice->status->id ?? null,
                "nama_status" =>
                $invoice->status->nama_status ?? "Unknown",
              ],
              "tagihan" => $invoice->tagihan,
              "jatuh_tempo" => $invoice->jatuh_tempo,
            ];
          })
          ->values()
          ->toArray(),
          "updated_at" => $customer->updated_at
          ? $customer->updated_at->format("Y-m-d H:i:s")
          : null,
        ];
      });
      
      // Cek apakah ada perubahan data dengan cache
      $currentUpdate = md5($customerData->toJson());
      $lastUpdate = cache("last_customer_update");
      
      if (!$lastUpdate || $lastUpdate !== $currentUpdate) {
        // Buat notifikasi sederhana
        $message = "Data pelanggan berhasil diperbarui";
        
        // Kirim event realtime
        event(
          new UpdateBaru($customerData->toArray(), "success", $message)
        );
        
        // Simpan hash baru ke cache
        cache(
          ["last_customer_update" => $currentUpdate],
          now()->addMinutes(5)
        );
      }
      
      // Tampilkan halaman dengan data pelanggan
      return view("data.data-pelanggan", [
        "users" => auth()->user(),
        "roles" => auth()->user()->roles,
        "data" => $customers,
        "metode" => $metode,
        "pembayaran" => $pembayaran,
      ]);
    }
    
    public function logistik()
    {
      return view("data.data-logistik", [
        "users" => auth()->user(),
        "roles" => auth()->user()->roles,
        "perangkat" => Perangkat::all(),
      ]);
    }
    
    public function antrian()
    {
      return view("data.data-antrian", [
        "users" => auth()->user(),
        "roles" => auth()->user()->roles,
        "paket" => Paket::all(),
        "lokasi" => Lokasi::all(),
        "router" => Router::all(),
        "koneksi" => Koneksi::all(),
        "perangkat" => Perangkat::all(),
        "customer" => Customer::all(),
      ]);
    }
    
    public function user()
    {
      return view("user_management.management_user", [
        "users" => auth()->user(),
        "roles" => auth()->user()->roles,
        "status" => Status::all(),
        "user" => User::all(),
        "role" => Roles::all(),
      ]);
    }
    
    /**
    * Block a customer by changing their status and Mikrotik profile to ISOLIREBILLING only
    * This function does NOT enable the disabled flag
    *
    * @param Request $request
    * @param int $id Customer ID
    * @return \Illuminate\Http\RedirectResponse
    */
    public function blokir(Request $request, $id)
    {
      try {
        $customer = Customer::find($id);
        $invoice = Invoice::where("customer_id", $id)->first();
        
        if (!$customer) {
          return redirect()
          ->back()
          ->with("toast_error", "Pelanggan tidak ditemukan");
        }
        
        $customer->status_id = 9;
        $customer->save();
        
        $invoice->paket_id = 8;
        $invoice->save();
        
        // Initialize Mikrotik service
        $mikrotik = new MikrotikServices();
        $profileResult = $mikrotik->changeUserProfile(
          $customer->usersecret,
          "ISOLIREBILLING"
        );
        
        // Remove Active Connections
        $disconnectResult = $mikrotik->removeActiveConnections(
          $customer->usersecret
        );
        
        // Log the results for debugging
        \Log::info("Customer blocked by profile change only", [
          "customer_id" => $id,
          "usersecret" => $customer->usersecret,
          "new_profile" => "ISOLIREBILLING",
          "profile_result" => $profileResult,
        ]);
        
        return redirect()->back();
      } catch (\Exception $e) {
        \Log::error("Error blocking customer: " . $e->getMessage(), [
          "customer_id" => $id,
          "trace" => $e->getTraceAsString(),
        ]);
        
        return redirect()
        ->back()
        ->with(
          "toast_error",
          "Gagal memblokir pelanggan: " . $e->getMessage()
        );
      }
    }
    
    /**
    * Unblock a customer by changing their status and restoring their original Mikrotik profile
    * This function does NOT change the disabled flag
    *
    * @param Request $request
    * @param int $id Customer ID
    * @return \Illuminate\Http\RedirectResponse
    */
    public function unblokir(Request $request, $id)
    {
      try {
        $customer = Customer::find($id);
        $invoice = Invoice::where("customer_id", $id)->first();
        
        if (!$customer) {
          return redirect()
          ->back()
          ->with("toast_error", "Pelanggan tidak ditemukan");
        }
        
        $customer->status_id = 3;
        $customer->save();
        $invoice->paket_id = $customer->paket_id;
        $invoice->save();
        
        // Initialize Mikrotik service
        $mikrotik = new MikrotikServices();
        $originalProfile = $customer->paket
        ? $customer->paket->nama_paket
        : "default";
        $profileResult = $mikrotik->changeUserProfile(
          $customer->usersecret,
          $originalProfile
        );
        
        // Log the results for debugging
        \Log::info("Customer unblocked by profile change only", [
          "customer_id" => $id,
          "usersecret" => $customer->usersecret,
          "restored_profile" => $originalProfile,
          "profile_result" => $profileResult,
        ]);
        
        return redirect()->back();
      } catch (\Exception $e) {
        \Log::error("Error unblocking customer: " . $e->getMessage(), [
          "customer_id" => $id,
          "trace" => $e->getTraceAsString(),
        ]);
        
        return redirect()
        ->back()
        ->with(
          "toast_error",
          "Gagal mengaktifkan pelanggan: " . $e->getMessage()
        );
      }
    }
    
    public function detailPelanggan($id)
    {
      $customer = Customer::find($id);
      $invoice = Invoice::where("customer_id", $id)->first();
      $profile = User::where("name", $customer->nama_customer)->first();
      // dd($profile);
      return view("/data/detail-pelanggan", [
        "users" => auth()->user(),
        "roles" => auth()->user()->roles,
        "customer" => $customer,
        "invoice" => $invoice,
        "profile" => $profile,
      ]);
    }
    
    /**
    * Get customer data for real-time updates
    *
    * @param int|null $id Optional customer ID to filter results
    * @return \Illuminate\Http\JsonResponse
    */
    public function getCustomerData(Request $request, $id = null)
    {
      try {
        if ($id) {
          // Get specific customer data with all necessary relationships
          $customer = Customer::with([
            "status",
            "paket",
            "invoice.status",
            "getServer",
            ])->findOrFail($id);
            
            // Get the latest invoice for this customer
            $invoice = $customer->invoice
            ->sortByDesc("created_at")
            ->first();
            
            $data = [
              "id" => $customer->id,
              "nama_customer" => $customer->nama_customer,
              "alamat" => $customer->alamat ?? "",
              "no_hp" => $customer->no_hp ?? "",
              "status_id" => $customer->status_id,
              "status" => [
                "id" => $customer->status->id ?? null,
                "nama_status" =>
                $customer->status->nama_status ?? "Unknown",
              ],
              "paket" => [
                "id" => $customer->paket->id ?? null,
                "nama_paket" =>
                $customer->paket->nama_paket ?? "Unknown",
              ],
              "getServer" => [
                "lokasi_server" =>
                $customer->getServer->lokasi_server ?? "Unknown",
              ],
              "invoice" => $customer->invoice
              ->map(function ($inv) {
                return [
                  "id" => $inv->id,
                  "status" => [
                    "id" => $inv->status->id ?? null,
                    "nama_status" =>
                    $inv->status->nama_status ?? "Unknown",
                  ],
                  "tagihan" => $inv->tagihan,
                  "jatuh_tempo" => $inv->jatuh_tempo,
                ];
              })
              ->values()
              ->toArray(),
              "updated_at" => $customer->updated_at
              ? $customer->updated_at->format("Y-m-d H:i:s")
              : null,
            ];
            
            // Create more informative notification message based on customer status
            $message = "";
            if ($customer->status_id === 9) {
              $message =
              "Pelanggan " .
              $customer->nama_customer .
              " telah diblokir";
              // Add reason if available
              if ($customer->status && $customer->status->nama_status) {
                $message .=
                " dengan status: " . $customer->status->nama_status;
              }
              $message .= ".";
            } elseif ($customer->status_id === 3) {
              $message =
              "Pelanggan " .
              $customer->nama_customer .
              " telah diaktifkan kembali";
              // Add package info if available
              if ($customer->paket && $customer->paket->nama_paket) {
                $message .=
                " dengan paket: " . $customer->paket->nama_paket;
              }
              $message .= ".";
            } else {
              $message =
              "Status pelanggan " .
              $customer->nama_customer .
              " telah diperbarui.";
            }
            // Broadcast the event for real-time notification
            event(new UpdateBaru($data, "success", $message));
          } else {
            // Get all customers data with necessary relationships
            $customers = Customer::with([
              "status",
              "paket",
              "invoice.status",
              "getServer",
              ])->get();
              
              $data = $customers
              ->map(function ($customer) {
                return [
                  "id" => $customer->id,
                  "nama_customer" =>
                  $customer->nama_customer ?? "Unknown",
                  "alamat" => $customer->alamat ?? "",
                  "no_hp" => $customer->no_hp ?? "",
                  "status_id" => $customer->status_id,
                  "status" => [
                    "id" => $customer->status->id ?? null,
                    "nama_status" =>
                    $customer->status->nama_status ?? "Unknown",
                  ],
                  "paket" => [
                    "id" => $customer->paket->id ?? null,
                    "nama_paket" =>
                    $customer->paket->nama_paket ?? "Unknown",
                  ],
                  "getServer" => [
                    "lokasi_server" =>
                    $customer->getServer->lokasi_server ??
                    "Unknown",
                  ],
                  "invoice" => $customer->invoice
                  ->map(function ($invoice) {
                    return [
                      "id" => $invoice->id,
                      "status" => [
                        "id" =>
                        $invoice->status->id ?? null,
                        "nama_status" =>
                        $invoice->status->nama_status ??
                        "Unknown",
                      ],
                      "tagihan" => $invoice->tagihan,
                      "jatuh_tempo" => $invoice->jatuh_tempo,
                    ];
                  })
                  ->values()
                  ->toArray(),
                  "updated_at" => $customer->updated_at
                  ? $customer->updated_at->format("Y-m-d H:i:s")
                  : null,
                ];
              })
              ->values()
              ->toArray();
              
              // Broadcast the event for real-time notification
              event(
                new UpdateBaru(
                  $data,
                  "success",
                  "Data pelanggan telah diperbarui"
                  )
                );
              }
              return response()->json([
                "success" => true,
                "data" => $data,
              ]);
            } catch (\Exception $e) {
              \Log::error("Error fetching customer data: " . $e->getMessage(), [
                "trace" => $e->getTraceAsString(),
              ]);
              
              return response()->json(
                [
                  "success" => false,
                  "message" =>
                  "Error fetching customer data: " . $e->getMessage(),
                ],
                500
              );
            }
          }
        }