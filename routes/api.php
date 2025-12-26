<?php

use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DataControllerApi;
use App\Http\Controllers\Api\CustomerControllerApi;
use App\Http\Controllers\Api\TeknisiControllerApi;
use App\Http\Controllers\Api\MikrotikControllerApi;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/getByMonth', [CustomerControllerApi::class, 'getInvoicesByMonthOptimized']);
Route::get('/dev', [DataControllerApi::class, 'debugging']);
  Route::get('/generate-preview', [DataControllerApi::class, 'getFasumCount']);
  Route::get('/generate-preview-monthly', [DataControllerApi::class, 'previewGenerateInvoicesMonthly']);
  Route::post('/generate-invoices', [DataControllerApi::class, 'generateFasumInvoices']);
  Route::get('/customer-paket11', [DataControllerApi::class, 'getCustomerPaket11WithInvoices']);
  Route::get('/preview-update-invoices', [DataControllerApi::class, 'previewUpdateInvoice']);
  Route::post('/update-invoices', [DataControllerApi::class, 'updateInvoice']);
  Route::get('/check-invoices-without-payment', [CustomerControllerApi::class, 'checkInvoiceWithoutPayment']);
  Route::post('/fix-invoice-without-payment', [CustomerControllerApi::class, 'fixInvoiceWithoutPayment']);
  Route::get('/analyze-customer-invoice-gap', [DataControllerApi::class, 'analyzeCustomerInvoiceGap']);
  Route::post('/fix-invoice-payment-status', [DataControllerApi::class, 'fixInvoicePaymentStatus']);
  Route::get('/analyze-status-mismatch', [DataControllerApi::class, 'analyzeStatusMismatchCustomers']);
  Route::get('/verify-fix', [DataControllerApi::class, 'verifyFix']);
  Route::get('/analyze-gap-42', [DataControllerApi::class, 'analyzeGap42']);
  Route::get('/testInvoice', [CustomerControllerApi::class, 'testInvoice']);
  Route::get('/findMissing', [CustomerControllerApi::class, 'findMissingCustomers']);
  Route::get('/verified-fix', [DataControllerApi::class, 'verifyFixSimplified']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::get('/user', [AuthController::class, 'user']);
  Route::get('/invoice', [AuthController::class, 'invoiceCustomer']);
  Route::get('/customer', [AuthController::class, 'allCustomer'])->middleware('auth:sanctum', 'roles:Super Admin');
  Route::get('/getCustomerAll', [DataControllerApi::class, 'getCustomerAll']);
  Route::get('/getProfileUser', [AuthController::class, 'getProfileUser']);

  // Roles Teknisi
  Route::get('/queueCustomer', [TeknisiControllerApi::class, 'getCustomerQueue']);

  // Roles NOC
  Route::get('/getNetwork', [MikrotikControllerApi::class, 'getNetwork']);
  Route::post('/postAssignment/{id}', [MikrotikControllerApi::class, 'postAssign']);

  //Ambil data invoice
  Route::get('/invoicePaid', [DataControllerApi::class, 'getInvoicePaid']);
  Route::get('/invoiceUnpaid', [DataControllerApi::class, 'getInvoiceUnpaid']);
  Route::get('/history', [DataControllerApi::class, 'historyPayment']);
  Route::get('/getMonthly', [DataControllerApi::class, 'getMonthlyPayment']);

  //Ambil invoice by id
  Route::get('/invoiceCustomer/{id}', [CustomerControllerApi::class, 'getInvoiceCustomer']);

  //Ambil payment history by customer id
  Route::get('/paymentHistory/{id}', [CustomerControllerApi::class, 'paymentHistory']);
  // Fix Issues
  Route::get('/invoice/{id}/proses-otomatis', [CustomerControllerApi::class, 'prosesOtomatisPembayaran']);

});
