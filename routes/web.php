<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\Boxicons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\tables\Basic as TablesBasic;
use App\Http\Controllers\Logistik;
use App\Http\Controllers\Customer;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeknisiController;
use App\Http\Controllers\Jaringan;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\HelpdeskController;
use App\Http\Controllers\NocController;
use App\Http\Controllers\Payment\TripayController;
use App\Http\Controllers\Payment\CallbackController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\SuperAdmin;
use App\Http\Controllers\DataController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\RabController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AgenController;
use App\Http\Controllers\KaryawanController;
use App\Services\MikrotikServices;
use Illuminate\Support\Facades\Http;
use App\Models\Router;
use App\Http\Controllers\TiketController;
use App\Http\Controllers\MapController;
use App\Exports\PembayaranExport;
use Maatwebsite\Excel\Facades\Excel;


// Main Page Route
Route::get('/', [LoginBasic::class, 'index'])->name('login')->middleware('guest');

Route::post('/login', [LoginBasic::class, 'login'])->name('login.post')->middleware('guest');

Route::post('/login', [LoginBasic::class, 'login'])->name('login.post');
Route::get('/login', fn () => redirect()->route('login'));


Route::get('/logout', [LoginBasic::class, 'logout'])->name('logout');

// Tripay Payment
Route::get('/payment/invoice/{id}', [TripayController::class, 'showPaymentPage'])->name('payment.show');
// Payment
Route::get('/payment/channels', [TripayController::class, 'getPaymentChannels'])->name('payment.channels');
Route::get('/payment/detail/{reference}', [TripayController::class, 'showPaymentDetail'])->name('payment.detail');
Route::post('/tripay-payment/{id}', [TripayController::class, 'processPayment'])->name('tripay.payment');
Route::post('/payment/callback', [CallbackController::class, 'handle'])->name('payment.callback');
Route::get('/payment/instructions/{code}', [TripayController::class, 'getPaymentInstructions'])->name('payment.instructions');
Route::get('/isolir', [Loginbasic::class, 'isolir'])->name('isolir');

// Debug
Route::get('/router/{id}/test', [MikrotikController::class, 'testKoneksi']);
Route::get('/test-router/{id}', function ($id) {
    $router = Router::findOrFail($id);

    for ($i = 0; $i < 3; $i++) {
        MikrotikServices::connect($router);
    }

    return 'Tes selesai. Cek storage/logs/laravel.log';
});


Route::middleware(['auth'])->group(function () {
    // Setting
    Route::get('/setting', [SettingController::class, 'blokirSetting'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('setting');
    Route::post('/sett/blokir', [SettingController::class, 'settBlokir']);
    Route::get('/visual', [SettingController::class, 'visual'])->name('setting');

    Route::get('/visual', [SettingController::class, 'visual'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('setting');

    // Invoice
    Route::match(['GET', 'POST'],'/manual/invoice', [SuperAdmin::class, 'globalInvoice'])->name('global-invoice');
    Route::get('/kirim/invoice/{id}', [SuperAdmin::class, 'kirimInvoice'])->name('kirim-invoice');
    Route::get('/hapus/user/{id}', [SuperAdmin::class, 'hapusUser'])->name('hapus-user');
    Route::post('/update/password/{id}', [UserController::class, 'updatePassword'])->name('update-password');

    // Customer blocking/unblocking routes
    Route::get('/blokir/{id}', [Analytics::class, 'blokir'])->name('blokir');
    Route::get('/unblokir/{id}', [Analytics::class, 'unblokir'])->name('unblokir');
    Route::get('/detail-pelanggan/{id}', [Analytics::class, 'detailPelanggan'])->name('detail-pelanggan');
    Route::get('/profile-user/{id}', [UserController::class, 'profileUser'])->name('profile-user)');
    Route::post('/update-photo/{id}', [UserController::class, 'updatePhoto'])->name('update-photo');
    Route::post('/update/user/{id}', [UserController::class, 'updateUser'])->name('update-user');
    Route::get('/data/invoice/{name}', [Customer::class, 'dataInvoice'])->name('invoice');

    // SuperAdmin
    Route::get('/payment/approve', [SuperAdmin::class, 'approvalPembayaran'])->name('payment.approve');
    Route::get('/acc/{id}', [SuperAdmin::class, 'acc'])->name('acc');
    Route::get('/log/aktivitas', [SuperAdmin::class, 'logAktivitas'])->middleware('auth', 'roles:Super Admin')->name('log-aktivitas');
    Route::get('/logs-detail/{id}', [SuperAdmin::class, 'logDetail'])->middleware('auth', 'roles:Super Admin')->name('logs-detail');
    Route::get('/rab', [RabController::class, 'index'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('rab');
    Route::get('/rab/detail/{id}', [RabController::class, 'detail'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('rab-detail');
    Route::post('/edit/role/{id}', [UserController::class, 'editRole'])->name('edit-role');
    Route::get('/peta', [MapController::class, 'index']);
    Route::get('/peta/data', [MapController::class, 'data'])->name('peta.data');
    Route::post('/add/tiket-open', [TiketController::class, 'addTiketOpen'])->middleware('auth','roles:Super Admin,NOC,Helpdesk,Admin Keuangan');
    Route::get('/tiket-closed', [TiketController::class, 'closedTiket'])->middleware('auth', 'roles:Super Admin,NOC,Teknisi,Helpdesk')->name('tiket-closed');
    Route::get('/export/pembayaran/{filter}', function ($filter) {
        return Excel::download(new PembayaranExport($filter), "pembayaran_export_{$filter}.xlsx");
    })->name('pembayaran.export');

    // Konfirmasi Tiket Open
    Route::get('/tiket-open/{id}', [TiketController::class, 'tutupTiket'])->middleware('auth', 'roles:Super Admin,NOC,Teknisi')->name('tutup-tiket');
    Route::get('/api/paket/by-router/{routerId}', [TiketController::class, 'getPaketByRouter']);
    Route::post('/tutup-tiket/{id}', [TiketController::class, 'confirmClosedTiket'])->name('confirm-closed-tiket');

    // Import Customer
    Route::post('/customer/import', [DataController::class, 'Import']);
    Route::post('/customer/import/khusus', [DataController::class, 'ImportKhusus']);
    Route::get('/hapus/dataImport', [DataController::class, 'hapusImport'])->name('hapus-import');

    //dashboard
    Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard')->middleware('auth', 'roles:Super Admin,Admin Keuangan,Admin Logistik,NOC,Teknisi,Helpdesk');
    Route::get('/data/pelanggan', [DataController::class, 'pelanggan'])->middleware('auth', 'roles:Super Admin,Admin Keuangan,Admin Logistik,NOC,Teknisi,Helpdesk')->name('pelanggan');
    Route::get('/data/logistik', [Analytics::class, 'logistik'])->middleware('auth', 'roles:Super Admin,Admin Logistik,Admin Keuangan')->name('logistik');
    Route::get('/data/antrian', [Analytics::class, 'antrian'])->name('antrian');

    // User Management
    Route::get('/user/management', [UserController::class, 'index'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('user');
    Route::post('/user/store', [UserController::class, 'store'])->name('user.store');
    // Customer
    Route::post('/customer/store', [Customer::class, 'store'])->name('customer.store');
    Route::get('/dashboard/get-customer-data/{id?}', [Analytics::class, 'getCustomerData'])->name('dashboard.get-customer-data');
    // Customer
    Route::get('/customer', [Customer::class, 'index'])->name('pelanggan');
    Route::get('/customer/pengaduan', [Customer::class, 'pengaduan'])->name('pengaduan');
    Route::get('/customer/history', [Customer::class, 'history'])->name('history');
    Route::get('/customer/request', [Customer::class, 'req'])->name('request');
    Route::post('/customer/add/pengaduan', [Customer::class, 'addPengaduan'])->name('customer.addPengaduan');


    // Logistik
    Route::post('/logistik/store', [Logistik::class, 'store']);
    Route::post('/add-kategori-logistik', [Logistik::class, 'tambahKategori'])->middleware('auth','roles:Super Admin,Admin Logistik');
    Route::get('/hapus-logistik/{id}', [Logistik::class, 'deleteLogistik'])->middleware('auth','roles:Super Admin,Admin Logistik');
    Route::get('/edit-logistik/{id}', [Logistik::class, 'editLogistik'])->middleware('auth','roles:Super Admin,Admin Logistik');
    Route::post('/update-logistik/{id}', [Logistik::class, 'updateLogistik'])->middleware('auth','roles:Super Admin,Admin Logistik');
    Route::get('/tracking', [Logistik::class, 'tracking'])->middleware('auth', 'roles:Super Admin,Admin Logistik')->name('tracking');




    // Teknisi
    Route::get('/teknisi/antrian', [TeknisiController::class, 'index'])->middleware('auth', 'roles:Super Admin,Teknisi,NOC')->name('teknisi');
    Route::get('/teknisi/selesai/{id}', [TeknisiController::class, 'selesai'])->name('teknisi.selesai');
    Route::get('/teknisi/selesai/{id}/print', [TeknisiController::class, 'print'])->name('teknisi.print');
    Route::post('/teknisi/konfirmasi/{id}', [TeknisiController::class, 'konfirmasi'])->name('teknisi.konfirmasi');
    Route::get('/corp/proses/{id}', [PerusahaanController::class, 'prosesCorp']);
    Route::post('/confirm/corp/{id}', [PerusahaanController::class, 'confirm']);
    Route::get('/teknisi/detail-antrian/{id}', [DataController::class, 'detailAntrianPelanggan']);

    // Perusahaan
    Route::get('/corp/pendapatan', [PerusahaanController::class, 'pendapatan'])->name('pendapatan');

    Route::get('/corp/pendapatan', [PerusahaanController::class, 'pendapatan'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('pendapatan');

    // NOC
    Route::get('/noc/data-olt', [Jaringan::class, 'index'])->name('olt');
    Route::get('/noc/data-odp', [Jaringan::class, 'odp'])->name('odp');
    Route::get('/noc/data-odc', [Jaringan::class, 'odc'])->name('odc');
    Route::get('/noc/data-server', [Jaringan::class, 'server'])->name('server');
    Route::post('/olt/add', [Jaringan::class, 'addOlt'])->name('olt.store');
    Route::post('/odc/add', [Jaringan::class, 'addOdc'])->name('odc.store');
    Route::post('/odp/add', [Jaringan::class, 'addOdp'])->name('odp.store');
    Route::post('/server/add', [Jaringan::class, 'addServer'])->name('server.store');
    Route::get('/mindmap', [Jaringan::class, 'mindmap'])->name('mindmap');
    Route::get('/data/antrian-noc', [NocController::class, 'antrian'])->name('antrian-noc');
    Route::get('/noc/proses-antrian/{id}', [NocController::class, 'prosesAntrian'])->name('antrian-noc');
    Route::post('/noc/assign/{id}', [NocController::class, 'assign'])->name('noc.assign');
    Route::get('/perusahaan/{id}', [NocController::class, 'antrianPerusahaan']);
    Route::post('/update/corp/{id}', [PerusahaanController::class, 'update']);
    Route::get('/profile/paket', [NocController::class, 'profilePaket'])->middleware('auth', 'roles:Super Admin,NOC,Admin Keuangan')->name('profile.paket');
    Route::post('/tambah/paket', [NocController::class, 'tambahPaket']);
    Route::get('/hapus/paket/{id}', [NocController::class, 'hapusPaket']);
    Route::get('/laporan', [KeuanganController::class, 'laporan'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('laporan');
    Route::get('/laporan/data', [KeuanganController::class, 'getLaporanData'])->name('laporan.data');
    Route::post('/tambah/router', [NocController::class, 'tambahRouter']);
    Route::get('/edit/router/{id}', [NocController::class, 'editRouter']);
    Route::post('/update/router/{id}', [NocController::class, 'updateRouter']);
    Route::get('/edit/paket/{id}', [NocController::class, 'editPaket']);
    Route::post('/update/paket/{id}', [NocController::class, 'updatePaket']);
    Route::get('/interface/{id}', [NocController::class, 'interface'])->middleware('auth', 'roles:Super Admin,NOC')->name('profile-paket');
// routes/web.php
    Route::get('/paket/data', [NocController::class, 'ajaxPaket'])->name('ajax.paket');
    Route::get('/edit/server/{id}', [NocController::class, 'editServer']);
    Route::post('/update/server/{id}', [NocController::class, 'updateServer']);
    Route::get('/hapus/server/{id}', [NocController::class, 'hapusServer']);
    Route::get('/edit/olt/{id}', [NocController::class, 'editOlt']);
    Route::post('/update/olt/{id}', [NocController::class, 'updateOlt']);
    Route::get('/hapus/olt/{id}', [NocController::class, 'hapusOlt']);
    Route::get('/edit/odc/{id}', [NocController::class, 'editOdc']);
    Route::post('/update/odc/{id}', [NocController::class, 'updateOdc']);
    Route::get('/hapus/odc/{id}', [NocController::class, 'hapusOdc']);
    Route::get('/edit/odp/{id}', [NocController::class, 'editOdp']);
    Route::post('/update/odp/{id}', [NocController::class, 'updateOdp']);
    Route::get('/hapus/odp/{id}', [NocController::class, 'hapusOdp']);
    Route::get('/noc/interface/{id}/realtime', [NocController::class, 'realtime']);
    Route::get('/edit/antrian/{id}/noc', [NocController::class, 'editAntrian']);
    Route::post('/simpan/noc/{id}', [NocController::class, 'simpanEdit']);


    Route::get('/data-karyawan', [KaryawanController::class, 'index'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('data-karyawan');

    // Keuangan
    Route::get('/data/pendapatan', [KeuanganController::class, 'index'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('pendapatan');
    Route::get('/data/pendapatan/filter', [DataController::class, 'filterPendapatan'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('pendapatan.filter');
    Route::get('/data/pembayaran', [KeuanganController::class, 'pembayaran'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('pembayaran');
    Route::get('/dashboard/keuangan', [KeuanganController::class, 'dashboardKeuangan'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('dashboard-keuangan');
    Route::get('/api/dashboard/keuangan', [KeuanganController::class, 'getDashboardData'])->name('api.dashboard.keuangan');
    Route::post('/konfirmasi/pembayaran/{customerId}', [KeuanganController::class, 'approvePayment'])->name('approve-payment');
    Route::get('/pembayaran/daily', [keuanganController::class, 'dailyPembayaran'])->name('daily-pembayaran');
    Route::post('/tambah/pendapatan', [KeuanganController::class, 'tambahPendapatan'])->name('tambah-pendapatan');
    Route::get('/pendapatan/non-langganan', [KeuanganController::class, 'nonLangganan'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('non-langganan');
    Route::get('/pendapatan/non-langganan/search', [KeuanganController::class, 'searchNonLangganan'])->name('non-langganan.search');
    Route::get('/pendapatan/global', [KeuanganController::class, 'globalPendapatan'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('global-pendapatan');
    Route::get('/pengeluaran/global', [PengeluaranController::class, 'index'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('global-pengeluaran');
    Route::post('/pengeluaran/tambah', [PengeluaranController::class, 'tambahPengeluaran'])->name('pengeluaran.tambah');
    Route::get('/kas', [KasController::class, 'index'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('kas');
    Route::post('/tambah/kas/kecil', [KasController::class, 'tambahKas'])->name('tambah');
    Route::post('/rab/store', [RabController::class, 'store'])->name('store-rab');
    Route::get('/rab/search', [RabController::class, 'search'])->name('rab-filter');
    Route::get('/rab/kegiatan', [RabController::class, 'getKegiatan'])->name('rab-kegiatan');
    Route::get('/transaksi/kas-kecil', [KasController::class, 'kecil'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('kas-kecil');
    Route::get('/transaksi/kas-besar', [KasController::class, 'besar'])->middleware('auth', 'roles:Super Admin,Admin Keuangan')->name('kas-besar');
    Route::post('/request/pembayaran/{id}', [KeuanganController::class, 'requestPembayaran']);
    Route::get('/data-agen', [KeuanganController::class, 'agen'])->middleware('auth', 'roles:Super Admin,Admin Keuangan');
    Route::get('/data-agen/search', [KeuanganController::class, 'searchAgen'])->name('data-agen-search');
    Route::get('/agen/pelanggan/{id}', [KeuanganController::class, 'pelangganAgen'])->name('agen-pelanggan');
    Route::post('/pengeluaran/hapus/{id}', [PengeluaranController::class, 'hapusPengeluaran'])->name('pengeluaran.hapus');
    Route::get('/request/hapus/pengeluaran', [PengeluaranController::class, 'requestHapus'])->name('request-hapus-pengeluaran');
    Route::get('/tolak/hapus/pengeluaran/{id}', [PengeluaranController::class, 'tolakHapus'])->name('tolak-hapus-pengeluaran');
    Route::get('/konfirmasi/hapus/pengeluaran/{id}', [PengeluaranController::class, 'konfirmasiHapus'])->name('konfirmasi-hapus-pengeluaran');
    Route::get('/riwayatPembayaran/{customerId}', [Customer::class, 'history']);
    Route::get('/edit-pelanggan/{id}',[DataController::class,'editPelanggan'])->middleware('auth','roles:Super Admin,Admin Keuangan,NOC,Teknisi,Helpdesk,Admin Logistik');
    Route::post('/update-pelanggan/{id}', [DataController::class, 'updatePelanggan'])->middleware('auth','roles:Super Admin,Admin Keuangan,NOC,Teknisi,Helpdesk,Admin Logistik');
    Route::get('/edit-rab/{id}',[RabController::class, 'editRab'])->middleware('auth','roles:Super Admin,Admin Keuangan');
    Route::post('/update-rab/{id}', [RabController::class, 'updateRab'])->middleware('auth','roles:Super Admin,Admin Keuangan');
    Route::get('/delete-rab/{id}',[RabController::class, 'hapusRab'])->middleware('auth','roles:Super Admin,Admin Keuangan');
    Route::get('/edit-pengeluaran/{id}',[PengeluaranController::class, 'editPengeluaran'])->middleware('auth','roles:Super Admin,Admin Keuangan');
    Route::post('/update-pengeluaran/{id}', [PengeluaranController::class, 'updatePengeluaran'])->middleware('auth','roles:Super Admin,Admin Keuangan');
    Route::post('/edit/pembayaran/{id}', [Customer::class, 'editPembayaran'])->middleware('auth','roles:Admin Keuangan,Super Admin');
    Route::get('/requestEdit/pembayaran', [SuperAdmin::class, 'requestEdit'])->middleware('auth','roles:Super Admin,Admin Keuangan');
    Route::get('/konfirmasiEditPembayaran/{id}', [SuperAdmin::class, 'konfirmasiEditPembayaran'])->middleware('auth','roles:Super Admin');
    Route::get('/rejectEditPembayaran/{id}', [SuperAdmin::class, 'rejectEditPembayaran'])->middleware('auth','roles:Super Admin');





    // Agen
    Route::get('/agen/data-pelanggan', [AgenController::class, 'index'])->middleware('auth', 'roles:Super Admin,Agen')->name('data-pembayaran');
    Route::get('/agen/data-pelanggan/search', [AgenController::class, 'search'])->middleware('auth', 'roles:Super Admin,Agen')->name('data-pelanggan-agen-search');
    Route::get('/agen/data-pelanggan/statistics', [AgenController::class, 'getStatistics'])->middleware('auth', 'roles:Super Admin,Agen')->name('data-pelanggan-agen-statistics');
    Route::post('/request/pembayaran/agen/{id}', [AgenController::class, 'requestPembayaran'])->name('request-pembayaran-agen');
    Route::get('/pelanggan-agen', [AgenController::class, 'pelanggan'])->middleware('auth', 'roles:Super Admin,Agen')->name('pelanggan-agen');


    // Mikrotik API
    Route::get('/mikrotik', [MikrotikController::class, 'index'])->name('mikrotik');

    // Helpdesk
    Route::get('/helpdesk/data-pengaduan', [HelpdeskController::class, 'dataPengaduan'])->middleware('auth', 'roles:Helpdesk')->name('data-pengaduan');
    Route::get('/helpdesk/get-pengaduan-data', [HelpdeskController::class, 'getPengaduanData'])->name('get-pengaduan-data');
    Route::get('/helpdesk/data-antrian', [HelpdeskController::class, 'antrian'])->middleware('auth', 'roles:Helpdesk,Agen')->name('antrian-helpdesk');
    Route::get('/helpdesk/detail-antrian/{id}', [HelpdeskController::class, 'detailAntrian'])->name('antrian-helpdesk');
    Route::put('/helpdesk/update-antrian/{id}', [HelpdeskController::class, 'updateAntrian'])->name('update-antrian-helpdesk');
    Route::post('/helpdesk/store', [HelpdeskController::class, 'addAntrian'])->name('helpdesk.store');
    Route::get('/corp/detail/{id}', [HelpdeskController::class, 'corpDetail']);
    Route::get('/helpdesk/hapus-antrian/{id}', [HelpdeskController::class, 'hapusAntrian'])->name('hapus-antrian-helpdesk');
    Route::get('/tiket-open', [TiketController::class, 'TiketOpen'])->name('tiket-open');
    Route::get('/open-tiket/{id}', [TiketController::class, 'formOpenTiket'])->name('open-tiket');
});

// Payment callback routes (outside auth middleware and CSRF protection)

// Tripay test callback route (specific for Tripay test feature)
Route::any('/payment/tripay-test-callback', [TripayController::class, 'handleTripayTestCallback'])->name('payment.tripay.test.callback')->withoutMiddleware(['auth', \App\Http\Middleware\VerifyCsrfToken::class]);

// Payment callback tester routes (outside auth middleware for easier testing)
Route::get('/payment/callback-tester', [TripayController::class, 'showCallbackTester'])
->name('payment.callback.tester');

Route::post('/payment/callback-test', [TripayController::class, 'processCallbackTest'])
->name('payment.callback.test')
->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Direct test route for easier testing (can be accessed directly from browser)
Route::get('/payment/test/{invoice_id}', function($invoice_id) {
    // Create a request with test_mode and invoice_id
    $request = new \Illuminate\Http\Request();
    $request->merge(['test_mode' => true, 'invoice_id' => $invoice_id]);

    // Call the callback handler directly
    $controller = new \App\Http\Controllers\Payment\TripayController();
    return $controller->paymentCallback($request);
})
->name('payment.direct.test')
->withoutMiddleware(['auth', \App\Http\Middleware\VerifyCsrfToken::class]);

// Sandbox payment simulation routes
Route::get('/payment/sandbox-simulate/{invoice_id}', [TripayController::class, 'simulateSandboxPayment'])
->name('payment.sandbox.simulate')
->withoutMiddleware(['auth', \App\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/payment/simulate-by-reference/{reference}', [TripayController::class, 'simulatePaymentByReference'])
->name('payment.simulate.reference')
->withoutMiddleware(['auth', \App\Http\Middleware\VerifyCsrfToken::class]);

// Check payment status from Tripay API
Route::get('/payment/check-status/{invoice_id}', [TripayController::class, 'checkPaymentStatus'])
->name('payment.check.status')
->withoutMiddleware(['auth', \App\Http\Middleware\VerifyCsrfToken::class]);

// Fallback route for Tripay callback - accepts any method (GET, POST, etc.)
Route::any('/tripay-callback', function(\Illuminate\Http\Request $request) {
    // Log the request
    \Log::info('Tripay fallback callback received', [
        'method' => $request->method(),
        'url' => $request->url(),
        'all' => $request->all(),
        'content' => $request->getContent()
    ]);

    // Forward to the callback handler
    $controller = new \App\Http\Controllers\Payment\TripayController();
    return $controller->paymentCallback($request);
})
->name('payment.fallback')
->middleware('api')
->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/api/olt/by-server/{server}', [TeknisiController::class, 'getByServer']);
Route::get('/api/odc/by-olt/{odc}', [TeknisiController::class, 'getByOdc']);
Route::get('/api/odp/by-odc/{odp}', [TeknisiController::class, 'getByOdp']);

// WebSocket test route
Route::get('/test-websocket', function() {
    return view('test-websocket');
});

// layout
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// pages
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');

// cards
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

// User Interface
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// extended ui
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

// icons
Route::get('/icons/boxicons', [Boxicons::class, 'index'])->name('icons-boxicons');

// form elements
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// form layouts
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

// tables
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');