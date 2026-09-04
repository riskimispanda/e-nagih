<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ModemDetail;
use App\Models\Perangkat;
use App\Models\KategoriLogistik;
use App\Models\Customer;
use Exception;
use Maatwebsite\Excel\Facades\Excel;

class DamagedItemsController extends Controller
{
    public function index(Request $request)
    {
        $query = ModemDetail::with(['perangkat.kategori', 'perangkat.kategori'])
            ->where('status_id', 15); // Rusak
            
        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('perangkat.kategori', function ($q) use ($request) {
                $q->where('nama_logistik', $request->category);
            });
        }
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('perangkat', function ($p) use ($search) {
                    $p->where('nama_perangkat', 'like', "%$search%");
                })
                ->orWhere('mac_address', 'like', "%$search%")
                ->orWhere('serial_number', 'like', "%$search%");
            });
        }
        
        $damagedItems = $query->paginate(20);
        
        $categories = KategoriLogistik::all();
        $totalDamaged = ModemDetail::where('status_id', 15)->count();
        
        // Category statistics
        $categoryStats = $categories->map(function ($category) use ($totalDamaged) {
            $category->damagedCount = ModemDetail::where('status_id', 15)
                ->whereHas('perangkat', function($query) use ($category) {
                    $query->where('kategori_id', $category->id);
                })->count();
            
            $category->percentage = ($totalDamaged > 0) ? 
                round(($category->damagedCount / $totalDamaged) * 100) : 0;
            
            return $category;
        });
        
        $recentlyDamaged = ModemDetail::with(['perangkat.kategori'])
            ->where('status_id', 15)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('damaged-items.index', [
            'damagedItems' => $damagedItems,
            'categories' => $categoryStats,
            'recentlyDamaged' => $recentlyDamaged,
            'totalDamaged' => $totalDamaged
        ]);
    }
    
    public function repair($id)
    {
        DB::beginTransaction();
        
        try {
            $device = ModemDetail::findOrFail($id);
            
            // Update device status to Available (14)
            $device->update([
                'status_id' => 14, // Tersedia
                'customer_id' => null,
                'updated_at' => now()
            ]);
            
            // Restore stock in perangkat table
            if ($device->logistik_id) {
                $perangkat = Perangkat::find($device->logistik_id);
                if ($perangkat) {
                    $perangkat->increment('jumlah_stok');
                }
            }
            
            DB::commit();
            
            return redirect()->back()->with('toast_success', 
                'Perangkat berhasil diperbaiki dan dikembalikan ke stok Tersedia.');
                
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbaiki perangkat rusak: ' . $e->getMessage());
            return redirect()->back()->with('toast_error', 'Gagal memperbaiki perangkat: ' . $e->getMessage());
        }
    }
    
    public function bulkRepair(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:ModemDetail,id'
        ]);
        
        $damagedIds = ModemDetail::whereIn('id', $request->ids)
            ->where('status_id', 15)
            ->pluck('id')
            ->toArray();
        
        if (empty($damagedIds)) {
            return redirect()->back()->with('toast_error', 'Tidak ada perangkat yang dapat diperbaiki');
        }
        
        DB::beginTransaction();
        
        try {
            foreach ($damagedIds as $id) {
                $device = ModemDetail::find($id);
                
                $device->update([
                    'status_id' => 14,
                    'customer_id' => null,
                    'updated_at' => now()
                ]);
                
                if ($device->logistik_id) {
                    $perangkat = Perangkat::find($device->logistik_id);
                    if ($perangkat) {
                        $perangkat->increment('jumlah_stok');
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->back()->with('toast_success', 
                count($damagedIds) . ' perangkat rusak berhasil diperbaiki dan dikembalikan ke stok.');
                
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('toast_error', 'Gagal memperbaiki perangkat: ' . $e->getMessage());
        }
    }
    
    public function getAnalytics()
    {
        $damagedByCategory = ModemDetail::with('perangkat.kategori')
            ->where('status_id', 15)
            ->get()
            ->groupBy(function ($item) {
                return $item->perangkat->kategori->nama_logistik ?? 'Unknown';
            })
            ->map(function ($items) {
                return $items->count();
            });
        
        $damageTrends = ModemDetail::where('status_id', 15)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    'count' => $item->count
                ];
            });
        
        return response()->json([
            'damagedByCategory' => $damagedByCategory,
            'damageTrends' => $damageTrends,
            'total' => ModemDetail::where('status_id', 15)->count()
        ]);
    }
    
    public function export(Request $request)
    {
        // Create a simple Excel export for damaged items
        $damagedItems = ModemDetail::with(['perangkat.kategori'])
            ->where('status_id', 15)
            ->get();
        
        $filename = 'damaged_items_' . now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new DamagedItemsExport($damagedItems), $filename);
    }
}