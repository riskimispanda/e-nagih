# Tripay Callback Debugging Guide

## Masalah: Status tidak berubah di Tripay test callback

### Kemungkinan Penyebab:

1. **Callback URL tidak dapat diakses dari Tripay**
2. **Format data yang diterima tidak sesuai**
3. **Invoice tidak ditemukan**
4. **Signature validation gagal**
5. **Error dalam processing**

### Solusi yang Telah Diimplementasi:

## 1. Multiple Callback Endpoints

### Main Callback (untuk production)
```
POST /payment/callback
```

### Tripay Test Callback (khusus untuk testing)
```
POST /payment/tripay-test-callback
```

## 2. Improved Invoice Lookup

Sistem sekarang mencari invoice dengan 5 metode:
1. Direct invoice ID (untuk test mode)
2. Find by reference
3. Find by merchant_ref
4. Extract ID from merchant_ref pattern (INV-{id}-{timestamp})
5. Tripay API lookup (fallback)

## 3. Enhanced Error Handling

- Comprehensive logging untuk debugging
- Multiple data source handling (JSON, form data)
- Sandbox mode considerations
- Fallback mechanisms

## 4. Testing Tools

### Callback Tester Page
```
GET /payment/callback-tester
```

### Simulation Endpoints
```
GET /payment/sandbox-simulate/{invoice_id}
GET /payment/simulate-by-reference/{reference}
```

## Cara Testing:

### Method 1: Menggunakan Callback Tester
1. Buka `/payment/callback-tester`
2. Pilih tab "Tripay Test"
3. Isi data atau pilih dari invoice yang ada
4. Klik "Send Tripay Test Callback"

### Method 2: Manual cURL Test
```bash
curl -X POST http://your-domain.com/payment/tripay-test-callback \
  -H "Content-Type: application/json" \
  -d '{
    "reference": "your-reference",
    "merchant_ref": "INV-123-1234567890",
    "status": "PAID",
    "amount": 150000
  }'
```

### Method 3: Menggunakan Tripay Dashboard
1. Pastikan callback URL di Tripay dashboard: `http://your-domain.com/payment/callback`
2. Untuk testing khusus: `http://your-domain.com/payment/tripay-test-callback`

## Debugging Steps:

### 1. Check Logs
```bash
tail -f storage/logs/laravel.log | grep "Payment callback"
```

### 2. Verify Invoice Data
Pastikan invoice memiliki:
- `reference` (transaction reference dari Tripay)
- `merchant_ref` (format: INV-{invoice_id}-{timestamp})

### 3. Test Connectivity
```bash
curl -X POST http://your-domain.com/payment/callback \
  -H "Content-Type: application/json" \
  -d '{"test": "connectivity"}'
```

### 4. Check Database
```sql
SELECT id, reference, merchant_ref, status_id 
FROM invoices 
WHERE status_id != 8 
ORDER BY created_at DESC;
```

## Common Issues & Solutions:

### Issue 1: "Invoice not found"
**Solution:** Pastikan invoice memiliki reference atau merchant_ref yang benar

### Issue 2: "Invalid signature"
**Solution:** Untuk sandbox, signature validation di-skip. Untuk production, pastikan private key benar.

### Issue 3: "No JSON content"
**Solution:** Sistem sekarang support form data dan JSON

### Issue 4: Callback URL tidak dapat diakses
**Solution:** 
- Pastikan URL public dan dapat diakses dari internet
- Disable CSRF protection untuk callback routes
- Check firewall/server configuration

## Environment Configuration:

### .env Settings
```
APP_ENV=local  # atau production
APP_DEBUG=true
TRIPAY_PRIVATE_KEY=your-private-key
TRIPAY_MERCHANT_CODE=your-merchant-code
```

### Route Configuration
Routes sudah dikonfigurasi tanpa auth dan CSRF:
```php
Route::post('/payment/callback', [TripayController::class, 'paymentCallback'])
    ->withoutMiddleware(['auth', \App\Http\Middleware\VerifyCsrfToken::class]);

Route::any('/payment/tripay-test-callback', [TripayController::class, 'handleTripayTestCallback'])
    ->withoutMiddleware(['auth', \App\Http\Middleware\VerifyCsrfToken::class]);
```

## Monitoring:

### Log Entries to Watch:
- "Payment callback received"
- "Looking for invoice"
- "Found invoice by [method]"
- "Invoice successfully marked as paid"
- "Invoice not found after all methods"

### Success Indicators:
- Response: `{"success": true, "message": "Payment processed successfully"}`
- Invoice status_id berubah menjadi 8
- Log: "Invoice successfully marked as paid"

### Failure Indicators:
- Response: `{"success": false, "message": "..."}`
- Log: "Invoice not found after all methods"
- Log: "Error processing payment callback"

## Next Steps if Still Not Working:

1. **Check Tripay Dashboard Settings**
   - Callback URL configuration
   - Merchant settings
   - API credentials

2. **Network Debugging**
   - Use ngrok for local testing
   - Check server logs
   - Verify SSL certificate

3. **Manual Database Update**
   ```sql
   UPDATE invoices SET status_id = 8 WHERE id = YOUR_INVOICE_ID;
   ```

4. **Contact Support**
   - Provide logs from `storage/logs/laravel.log`
   - Include callback URL and test data
   - Share Tripay dashboard configuration
