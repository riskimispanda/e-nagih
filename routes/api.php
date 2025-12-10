<?php

use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DataControllerApi;
use App\Http\Controllers\Api\CustomerControllerApi;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::get('/user', [AuthController::class, 'user']);
  Route::get('/invoice', [AuthController::class, 'invoiceCustomer']);
  Route::get('/customer', [AuthController::class, 'allCustomer'])->middleware('auth:sanctum', 'roles:Super Admin');


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
