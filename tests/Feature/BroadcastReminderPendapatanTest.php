<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Paket;
use App\Models\Roles;
use App\Models\User;
use App\Services\QontakServices;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class BroadcastReminderPendapatanTest extends TestCase
{
    use DatabaseTransactions;

    private User $adminKeuangan;
    private User $superAdmin;
    private User $helpdesk;
    private User $agenA;
    private User $agenB;
    private Paket $paketRegular;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $roleKeuanganId = Roles::firstOrCreate(['name' => 'Admin Keuangan'])->id;
        $roleSuperAdminId = Roles::firstOrCreate(['name' => 'Super Admin'])->id;
        $roleHelpdeskId = Roles::firstOrCreate(['name' => 'Helpdesk'])->id;
        $roleAgenId = Roles::firstOrCreate(['name' => 'Agen'])->id;

        foreach ([
            1 => 'Menunggu',
            3 => 'Aktif',
            4 => 'Maintenance',
            7 => 'Belum Bayar',
            8 => 'Sudah Bayar',
            9 => 'Isolir',
        ] as $id => $nama) {
            DB::table('status')->insertOrIgnore([
                'id' => $id,
                'nama_status' => $nama,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('paket')->insertOrIgnore([
            'id' => 11,
            'nama_paket' => 'Khusus Non Tagihan',
            'harga' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->adminKeuangan = User::factory()->create(['roles_id' => $roleKeuanganId]);
        $this->superAdmin = User::factory()->create(['roles_id' => $roleSuperAdminId]);
        $this->helpdesk = User::factory()->create(['roles_id' => $roleHelpdeskId]);
        $this->agenA = User::factory()->create(['roles_id' => $roleAgenId]);
        $this->agenB = User::factory()->create(['roles_id' => $roleAgenId]);

        $this->paketRegular = Paket::create([
            'nama_paket' => 'Paket Reminder Test 20M',
            'harga' => '185000',
        ]);
    }

    private function makeCustomer(array $overrides = []): Customer
    {
        return Customer::create(array_merge([
            'nama_customer' => 'Pelanggan Reminder ' . uniqid(),
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Merdeka No. 1',
            'paket_id' => $this->paketRegular->id,
            'status_id' => 3,
            'agen_id' => $this->agenA->id,
        ], $overrides));
    }

    private function makeInvoice(Customer $customer, array $overrides = []): Invoice
    {
        return Invoice::create(array_merge([
            'customer_id' => $customer->id,
            'status_id' => 7,
            'paket_id' => $this->paketRegular->id,
            'tagihan' => 185000,
            'tambahan' => 0,
            'tunggakan' => 0,
            'saldo' => 0,
            'jatuh_tempo' => '2026-11-10',
        ], $overrides));
    }

    public function test_hanya_super_admin_dan_admin_keuangan_yang_boleh_mengakses(): void
    {
        $this->actingAs($this->helpdesk)
            ->postJson(route('pendapatan.broadcast-reminder'), ['mode' => 'count'])
            ->assertStatus(403);

        $this->actingAs($this->adminKeuangan)
            ->postJson(route('pendapatan.broadcast-reminder'), [
                'mode' => 'count',
                'agen_id' => $this->agenA->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->actingAs($this->superAdmin)
            ->postJson(route('pendapatan.broadcast-reminder'), [
                'mode' => 'count',
                'agen_id' => $this->agenA->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);
    }

    public function test_halaman_data_pendapatan_menampilkan_tombol_broadcast_reminder(): void
    {
        $this->actingAs($this->adminKeuangan)
            ->get(route('pendapatan'))
            ->assertOk()
            ->assertSee('id="btnBroadcastReminder"', false)
            ->assertSee('Broadcast Reminder');
    }

    public function test_count_menghormati_filter_aktif_dan_mengecualikan_data_tidak_valid(): void
    {
        // Eligible 1: customer aktif (status 3), invoice belum bayar (7) Nov 2026
        $eligible1 = $this->makeCustomer(['nama_customer' => 'Target Alpha', 'status_id' => 3]);
        $this->makeInvoice($eligible1, ['jatuh_tempo' => '2026-11-05']);

        // Eligible 2: customer isolir (status 9), invoice belum bayar (7) Nov 2026
        $eligible2 = $this->makeCustomer(['nama_customer' => 'Target Beta', 'status_id' => 9]);
        $this->makeInvoice($eligible2, ['jatuh_tempo' => '2026-11-15']);

        // Dikecualikan: sudah bayar (status 8)
        $paid = $this->makeCustomer(['nama_customer' => 'Sudah Lunas']);
        $this->makeInvoice($paid, ['status_id' => 8, 'jatuh_tempo' => '2026-11-10']);

        // Dikecualikan: paket_id 11
        $paket11 = $this->makeCustomer(['nama_customer' => 'Paket Sebelas', 'paket_id' => 11]);
        $this->makeInvoice($paket11, ['paket_id' => 11, 'jatuh_tempo' => '2026-11-10']);

        // Dikecualikan: soft deleted
        $deleted = $this->makeCustomer(['nama_customer' => 'Terhapus']);
        $this->makeInvoice($deleted, ['jatuh_tempo' => '2026-11-10']);
        $deleted->delete();

        // Dikecualikan: status customer belum aktif (1)
        $inactive = $this->makeCustomer(['nama_customer' => 'Calon Baru', 'status_id' => 1]);
        $this->makeInvoice($inactive, ['jatuh_tempo' => '2026-11-10']);

        // Dikecualikan: tanpa no_hp
        $noPhone = $this->makeCustomer(['nama_customer' => 'Tanpa HP', 'no_hp' => '']);
        $this->makeInvoice($noPhone, ['jatuh_tempo' => '2026-11-10']);

        // Dikecualikan saat filter bulan=11: invoice bulan 10
        $otherMonth = $this->makeCustomer(['nama_customer' => 'Bulan Lalu']);
        $this->makeInvoice($otherMonth, ['jatuh_tempo' => '2026-10-10']);

        // Dikecualikan saat filter agenA: customer agenB
        $otherAgen = $this->makeCustomer(['nama_customer' => 'Agen Lain', 'agen_id' => $this->agenB->id]);
        $this->makeInvoice($otherAgen, ['jatuh_tempo' => '2026-11-10']);

        // Filter bulan=11, tahun=2026, agen_id=agenA -> harus tepat 2
        $this->actingAs($this->adminKeuangan)
            ->postJson(route('pendapatan.broadcast-reminder'), [
                'mode' => 'count',
                'bulan' => 11,
                'tahun' => 2026,
                'agen_id' => $this->agenA->id,
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'total' => 2,
                'already_sent_today' => 0,
                'to_send' => 2,
            ]);

        // Tambah filter search="Target Alpha" -> harus tepat 1
        $this->actingAs($this->adminKeuangan)
            ->postJson(route('pendapatan.broadcast-reminder'), [
                'mode' => 'count',
                'bulan' => 11,
                'tahun' => 2026,
                'agen_id' => $this->agenA->id,
                'search' => 'Target Alpha',
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'total' => 1,
                'to_send' => 1,
            ]);

        // Semua Bulan (bulan=null) pada agenA & tahun 2026 -> eligible1, eligible2, otherMonth = 3
        $this->actingAs($this->adminKeuangan)
            ->postJson(route('pendapatan.broadcast-reminder'), [
                'mode' => 'count',
                'bulan' => null,
                'tahun' => 2026,
                'agen_id' => $this->agenA->id,
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'total' => 3,
                'to_send' => 3,
            ]);
    }

    public function test_process_mengirim_total_tagihan_tepat_mencatat_log_dan_mencegah_duplikasi_harian(): void
    {
        $customerBaru = $this->makeCustomer([
            'nama_customer' => 'Budi Reminder',
            'no_hp' => '081234567891',
            'alamat' => "Jl. Melati\nRT 02",
        ]);
        // Tagihan 150000 + tambahan 25000 + tunggakan 50000 - saldo 15000 = 210000
        $invoiceBaru = $this->makeInvoice($customerBaru, [
            'tagihan' => 150000,
            'tambahan' => 25000,
            'tunggakan' => 50000,
            'saldo' => 15000,
            'jatuh_tempo' => '2026-11-12',
        ]);

        // Customer yang sudah terkirim reminder sukses/pending hari ini -> harus dilewati
        $customerSudahHariIni = $this->makeCustomer([
            'nama_customer' => 'Siti Sudah',
            'no_hp' => '081234567892',
        ]);
        $this->makeInvoice($customerSudahHariIni, ['jatuh_tempo' => '2026-11-12']);
        DB::table('whats_log')->insert([
            'customer_id' => $customerSudahHariIni->id,
            'jenis_pesan' => 'reminder_pembayaran',
            'pesan' => 'Reminder sebelumnya hari ini',
            'qontak_broadcast_id' => 'bc-prev-today',
            'status_pengiriman' => 'pending',
            'no_tujuan' => $customerSudahHariIni->no_hp,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Customer yang log hari ini FAILED dan log kemarin SENT -> tetap boleh dikirim ulang
        $customerRetry = $this->makeCustomer([
            'nama_customer' => 'Andi Retry',
            'no_hp' => '081234567893',
        ]);
        $invoiceRetry = $this->makeInvoice($customerRetry, [
            'tagihan' => 100000,
            'tambahan' => 0,
            'tunggakan' => 0,
            'saldo' => 0,
            'jatuh_tempo' => '2026-11-14',
        ]);
        DB::table('whats_log')->insert([
            [
                'customer_id' => $customerRetry->id,
                'jenis_pesan' => 'reminder_pembayaran',
                'pesan' => 'Reminder kemarin',
                'qontak_broadcast_id' => 'bc-yesterday',
                'status_pengiriman' => 'sent',
                'no_tujuan' => $customerRetry->no_hp,
                'created_at' => Carbon::yesterday(),
                'updated_at' => Carbon::yesterday(),
            ],
            [
                'customer_id' => $customerRetry->id,
                'jenis_pesan' => 'reminder_pembayaran',
                'pesan' => 'Reminder gagal hari ini',
                'qontak_broadcast_id' => null,
                'status_pengiriman' => 'failed',
                'no_tujuan' => $customerRetry->no_hp,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        $fakeTemplate = [
            'id' => 'fdfc81b4-8178-4d2c-86b3-05e06fe26f7e',
            'name' => 'reminder_pembayaran',
            'language' => 'id',
            'status' => 'APPROVED',
        ];

        $mockService = Mockery::mock(QontakServices::class);
        $mockService->shouldReceive('resolveTemplateByName')
            ->once()
            ->with('reminder_pembayaran')
            ->andReturn($fakeTemplate);

        $mockService->shouldReceive('reminderPembayaran')
            ->once()
            ->withArgs(function ($to, $customer, $totalTagihan, $paymentUrl, $template) use ($customerBaru, $invoiceBaru, $fakeTemplate) {
                return $to === '081234567891'
                    && $customer->id === $customerBaru->id
                    && (float) $totalTagihan === 210000.0
                    && str_contains($paymentUrl, '/payment/invoice/')
                    && str_contains($paymentUrl, 'invoice_id=' . $invoiceBaru->id)
                    && $template['id'] === $fakeTemplate['id'];
            })
            ->andReturn([
                'success' => true,
                'message_id' => 'bc-budi-1',
                'status' => 'todo',
            ]);

        $mockService->shouldReceive('reminderPembayaran')
            ->once()
            ->withArgs(function ($to, $customer, $totalTagihan, $paymentUrl, $template) use ($customerRetry, $invoiceRetry, $fakeTemplate) {
                return $to === '081234567893'
                    && $customer->id === $customerRetry->id
                    && (float) $totalTagihan === 100000.0
                    && str_contains($paymentUrl, 'invoice_id=' . $invoiceRetry->id)
                    && $template['id'] === $fakeTemplate['id'];
            })
            ->andReturn([
                'success' => true,
                'message_id' => 'bc-andi-1',
                'status' => 'todo',
            ]);

        $this->app->instance(QontakServices::class, $mockService);

        // Cek count sebelum kirim: total=3, already_sent_today=1 (hanya Siti), to_send=2
        $this->actingAs($this->adminKeuangan)
            ->postJson(route('pendapatan.broadcast-reminder'), [
                'mode' => 'count',
                'bulan' => 11,
                'tahun' => 2026,
                'agen_id' => $this->agenA->id,
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'total' => 3,
                'already_sent_today' => 1,
                'to_send' => 2,
            ]);

        // Eksekusi process
        $this->actingAs($this->adminKeuangan)
            ->postJson(route('pendapatan.broadcast-reminder'), [
                'mode' => 'process',
                'bulan' => 11,
                'tahun' => 2026,
                'agen_id' => $this->agenA->id,
                'offset' => 0,
                'limit' => 10,
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'processed' => 3,
                'sent' => 2,
                'skipped' => 1,
                'failed' => 0,
                'offset_next' => 3,
            ]);

        // Pastikan log tercatat di whats_log
        $this->assertDatabaseHas('whats_log', [
            'customer_id' => $customerBaru->id,
            'jenis_pesan' => 'reminder_pembayaran',
            'qontak_broadcast_id' => 'bc-budi-1',
            'status_pengiriman' => 'todo',
            'no_tujuan' => '081234567891',
        ]);

        // Eksekusi ulang di hari yang sama -> semua 3 pelanggan harus dilewati
        $mockServiceRetry = Mockery::mock(QontakServices::class);
        $mockServiceRetry->shouldReceive('resolveTemplateByName')
            ->once()
            ->with('reminder_pembayaran')
            ->andReturn($fakeTemplate);
        $mockServiceRetry->shouldNotReceive('reminderPembayaran');
        $this->app->instance(QontakServices::class, $mockServiceRetry);

        $this->actingAs($this->adminKeuangan)
            ->postJson(route('pendapatan.broadcast-reminder'), [
                'mode' => 'process',
                'bulan' => 11,
                'tahun' => 2026,
                'agen_id' => $this->agenA->id,
                'offset' => 0,
                'limit' => 10,
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'processed' => 3,
                'sent' => 0,
                'skipped' => 3,
                'failed' => 0,
            ]);
    }

    public function test_qontak_service_reminder_pembayaran_menyusun_payload_4_parameter_dengan_benar(): void
    {
        Cache::forget('qontak_template_reminder_pembayaran');
        config([
            'qontak.MEKARI_API_BASE_URL' => 'https://api.mekari.com',
            'qontak.MEKARI_CLIENT_ID' => 'test-client',
            'qontak.MEKARI_SECRET_ID' => 'test-secret',
            'qontak.CHANNEL_ID' => 'channel-test-id',
        ]);

        Http::fake([
            'https://api.mekari.com/qontak/chat/v1/templates/whatsapp' => Http::response([
                'data' => [
                    [
                        'id' => 'fdfc81b4-8178-4d2c-86b3-05e06fe26f7e',
                        'name' => 'reminder_pembayaran',
                        'language' => 'id',
                        'status' => 'APPROVED',
                    ],
                ],
            ], 200),
            'https://api.mekari.com/qontak/chat/v1/broadcasts/whatsapp/direct' => Http::response([
                'data' => [
                    'id' => 'qontak-msg-123',
                    'execute_status' => 'todo',
                ],
            ], 201),
        ]);

        $customer = new Customer([
            'nama_customer' => "  Sari\n  Puspita ",
            'alamat' => "Jl. Kenanga\tNo. 5\nSleman",
            'no_hp' => '081234567890',
        ]);

        $service = new QontakServices();
        $result = $service->reminderPembayaran(
            '081234567890',
            $customer,
            185000,
            'http://127.0.0.1:8000/payment/invoice/abc?invoice_id=10'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('qontak-msg-123', $result['message_id']);
        $this->assertSame('todo', $result['status']);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://api.mekari.com/qontak/chat/v1/broadcasts/whatsapp/direct') {
                return false;
            }

            $data = $request->data();
            $body = $data['parameters']['body'] ?? [];

            return $data['to_number'] === '6281234567890'
                && $data['to_name'] === 'Sari Puspita'
                && $data['message_template_id'] === 'fdfc81b4-8178-4d2c-86b3-05e06fe26f7e'
                && $data['language']['code'] === 'id'
                && count($body) === 4
                && $body[0] === ['key' => '1', 'value' => 'nama', 'value_text' => 'Sari Puspita']
                && $body[1] === ['key' => '2', 'value' => 'alamat', 'value_text' => 'Jl. Kenanga No. 5 Sleman']
                && $body[2] === ['key' => '3', 'value' => 'tagihan', 'value_text' => '185.000']
                && $body[3] === ['key' => '4', 'value' => 'link', 'value_text' => 'http://127.0.0.1:8000/payment/invoice/abc?invoice_id=10'];
        });
    }
}
