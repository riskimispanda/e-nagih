<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Dismantle;
use App\Models\KategoriLogistik;
use App\Models\ModemDetail;
use App\Models\Paket;
use App\Models\Perangkat;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PelangganDismantleRestoreTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $helpdesk;
    private User $agen;
    private Paket $paket;
    private Perangkat $perangkat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $roleSuperAdmin = Roles::firstOrCreate(['name' => 'Super Admin'])->id;
        $roleHelpdesk = Roles::firstOrCreate(['name' => 'Helpdesk'])->id;
        $roleAgen = Roles::firstOrCreate(['name' => 'Agen'])->id;

        foreach ([
            3 => 'Aktif',
            4 => 'Maintenance',
            9 => 'Isolir',
            13 => 'Terpakai',
            14 => 'Tersedia',
            15 => 'Rusak',
        ] as $id => $nama) {
            DB::table('status')->insertOrIgnore([
                'id' => $id,
                'nama_status' => $nama,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->superAdmin = User::factory()->create(['roles_id' => $roleSuperAdmin]);
        $this->helpdesk = User::factory()->create(['roles_id' => $roleHelpdesk]);
        $this->agen = User::factory()->create(['roles_id' => $roleAgen]);

        $this->paket = Paket::create([
            'nama_paket' => 'Paket Restore Test',
            'harga' => '150000',
        ]);

        $kategori = KategoriLogistik::firstOrCreate(['nama_logistik' => 'modem']);
        $this->perangkat = Perangkat::create([
            'nama_perangkat' => 'Modem Restore Test',
            'jumlah_stok' => 5,
            'harga' => 100000,
            'kategori_id' => $kategori->id,
        ]);
    }

    private function makeDismantledCustomer(array $overrides = []): Customer
    {
        $customer = Customer::create(array_merge([
            'nama_customer' => 'Pelanggan Dismantle Test',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Restore No. 1',
            'paket_id' => $this->paket->id,
            'perangkat_id' => $this->perangkat->id,
            'status_id' => 4,
            'seri_perangkat' => 'SN-RESTORE-01',
            'mac_address' => 'AA:BB:CC:DD:EE:01',
        ], $overrides));

        $customer->delete();

        return $customer;
    }

    public function test_hanya_role_staf_internal_yang_boleh_restore_pelanggan(): void
    {
        $customer = $this->makeDismantledCustomer();

        $this->actingAs($this->agen)
            ->postJson(route('pelanggan.restore', $customer->id))
            ->assertStatus(403);

        $this->assertSoftDeleted('customer', ['id' => $customer->id]);
    }

    public function test_restore_mengaktifkan_kembali_pelanggan_dan_mengaitkan_modem_yang_tersedia(): void
    {
        $customer = $this->makeDismantledCustomer([
            'seri_perangkat' => 'SN-REATTACH',
            'mac_address' => 'AA:BB:CC:11:22:33',
            'status_id' => 4,
        ]);

        $modem = ModemDetail::create([
            'logistik_id' => $this->perangkat->id,
            'serial_number' => 'SN-REATTACH',
            'mac_address' => 'AA:BB:CC:11:22:33',
            'status_id' => 4,
            'customer_id' => null,
        ]);

        $this->actingAs($this->helpdesk)
            ->postJson(route('pelanggan.restore', $customer->id))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'modem_restored' => true,
            ]);

        $restored = Customer::findOrFail($customer->id);
        $this->assertNull($restored->deleted_at);
        $this->assertSame(3, (int) $restored->status_id);

        $modem->refresh();
        $this->assertSame(13, (int) $modem->status_id);
        $this->assertSame($customer->id, (int) $modem->customer_id);
    }

    public function test_restore_menggunakan_fallback_riwayat_dismantle_dan_tidak_mencuri_modem_rusak_atau_terpakai(): void
    {
        // Kasus 1: fallback via tabel dismantles jika customer tidak punya seri/mac
        $customerFallback = $this->makeDismantledCustomer([
            'seri_perangkat' => null,
            'mac_address' => null,
            'status_id' => 9,
        ]);

        $modemFallback = ModemDetail::create([
            'logistik_id' => $this->perangkat->id,
            'serial_number' => 'SN-FALLBACK',
            'status_id' => 14,
            'customer_id' => null,
        ]);

        Dismantle::create([
            'modem_detail_id' => $modemFallback->id,
            'perangkat_id' => $this->perangkat->id,
            'customer_id_lama' => $customerFallback->id,
            'customer_nama' => $customerFallback->nama_customer,
            'perangkat_nama' => $this->perangkat->nama_perangkat,
            'status_barang' => 14,
            'tanggal_dismantle' => now(),
        ]);

        $this->actingAs($this->superAdmin)
            ->postJson(route('pelanggan.restore', $customerFallback->id))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'modem_restored' => true,
            ]);

        // Status 9 (Isolir) dipertahankan, deleted_at terhapus, modem fallback terkoneksi
        $this->assertSame(9, (int) $customerFallback->fresh()->status_id);
        $this->assertSame(13, (int) $modemFallback->fresh()->status_id);
        $this->assertSame($customerFallback->id, (int) $modemFallback->fresh()->customer_id);

        // Kasus 2: modem sudah dipakai customer lain -> tidak dicuri
        $activeOther = Customer::create([
            'nama_customer' => 'Pelanggan Lain',
            'no_hp' => '08999999999',
            'alamat' => 'Jl. Lain',
            'paket_id' => $this->paket->id,
            'status_id' => 3,
        ]);
        $modemTaken = ModemDetail::create([
            'logistik_id' => $this->perangkat->id,
            'serial_number' => 'SN-TAKEN',
            'status_id' => 13,
            'customer_id' => $activeOther->id,
        ]);

        $customerTaken = $this->makeDismantledCustomer([
            'seri_perangkat' => 'SN-TAKEN',
            'mac_address' => null,
        ]);

        $this->actingAs($this->superAdmin)
            ->postJson(route('pelanggan.restore', $customerTaken->id))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'modem_restored' => false,
            ]);

        $this->assertSame($activeOther->id, (int) $modemTaken->fresh()->customer_id);
    }

    public function test_restore_pelanggan_sudah_aktif_atau_tidak_ada_mengembalikan_status_error_yang_tepat(): void
    {
        $active = Customer::create([
            'nama_customer' => 'Sudah Aktif',
            'no_hp' => '08111111111',
            'alamat' => 'Jl. Aktif',
            'paket_id' => $this->paket->id,
            'status_id' => 3,
        ]);

        $this->actingAs($this->superAdmin)
            ->postJson(route('pelanggan.restore', $active->id))
            ->assertStatus(409)
            ->assertJson(['success' => false]);

        $this->actingAs($this->superAdmin)
            ->postJson(route('pelanggan.restore', 99999999))
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    public function test_halaman_pelanggan_dismantle_merender_tombol_restore(): void
    {
        $customer = $this->makeDismantledCustomer(['nama_customer' => 'Dismantle UI Test']);

        $this->actingAs($this->superAdmin)
            ->get('/pelanggan-dismantle')
            ->assertOk()
            ->assertSee('restoreCustomer(' . $customer->id, false)
            ->assertSee('/pelanggan-dismantle/restore/');
    }
}
