<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->id }} - Niscala Network Media</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #1e293b;
            background: #ffffff;
            line-height: 1.6;
        }

        .page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            background: white;
            position: relative;
            overflow: hidden;
            border: 1px solid #EAEAEA;
            display: flex;
            flex-direction: column;
        }

        /* Decorative Elements */
        .header-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 50%, #0a58ca 100%);
            clip-path: polygon(0 0, 100% 0, 100% 70%, 0 100%);
            z-index: 0;
        }

        .pattern-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 200px;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 50%);
            z-index: 1;
        }

        .content {
            position: relative;
            z-index: 2;
            padding: 20px 25px 10px 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Header Section */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            color: white;
        }

        .company-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-container {
            background: white;
            padding: 18px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .company-logo {
            height: 80px;
            width: auto;
            display: block;
        }

        .company-details h1 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 5px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .company-details p {
            font-size: 11px;
            opacity: 0.95;
            margin: 2px 0;
            font-weight: 500;
        }

        .invoice-meta {
            text-align: right;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(25px);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #EAEAEA;
        }

        .invoice-meta h2 {
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -1px;
        }

        .invoice-number {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
            opacity: 0.95;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin: 6px 0;
            font-size: 12px;
        }

        .meta-label {
            opacity: 0.9;
            font-weight: 600;
        }

        .meta-value {
            font-weight: 700;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 11px;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-paid {
            background: #10b981;
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        .status-unpaid {
            background: #ef4444;
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }

        /* Info Cards */
        .info-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 15px;
        }

        .info-card {
            background: white;
            border-radius: 8px;
            padding: 12px;
            /*box-shadow: 0 4px 20px rgba(0,0,0,0.08);*/
            border: 1px solid #e2e8f0;
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        .info-icon {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            font-weight: bold;
        }

        .info-card-header h3 {
            font-size: 12px;
            font-weight: 700;
            color: #0d6efd;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-card p {
            font-size: 11px;
            margin: 6px 0;
            color: #475569;
        }

        .info-card strong {
            color: #1e293b;
            font-weight: 700;
            font-size: 13px;
        }

        .info-label {
            color: #64748b;
            font-weight: 600;
            margin-right: 8px;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            /*box-shadow: 0 4px 20px rgba(0,0,0,0.08);*/
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-table thead {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }

        .invoice-table th {
            padding: 14px 18px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
            color: white;
        }

        .invoice-table tbody td {
            padding: 16px 18px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }

        .invoice-table tbody tr:last-child td {
            border-bottom: none;
        }

        .item-name {
            font-weight: 700;
            color: #1e293b;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .item-description {
            color: #64748b;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .qty-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 4px 12px;
            border-radius: 50px;
            font-weight: 700;
            display: inline-block;
            font-size: 11px;
        }

        .price {
            font-weight: 700;
            color: #1e293b;
            font-size: 13px;
        }

        /* Summary Section */
        .summary-section {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .notes-card {
            /*background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);*/
            border-radius: 10px;
            padding: 18px;
            border: 1px solid #e2e8f0;
        }

        .notes-card h4 {
            color: #0d6efd;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .note-item {
            display: flex;
            gap: 8px;
            margin: 8px 0;
            font-size: 11px;
            color: #475569;
        }

        .note-icon {
            color: #0d6efd;
            font-weight: bold;
            flex-shrink: 0;
        }

        .payment-status-box {
            background: linear-gradient(135deg, #d1e7dd 0%, #badbcc 100%);
            border-left: 4px solid #10b981;
            padding: 12px;
            border-radius: 8px;
            margin-top: 12px;
        }

        .payment-status-box p {
            color: #0f5132;
            font-weight: 700;
            font-size: 12px;
            margin: 0;
        }

        .payment-status-box small {
            color: #0f5132;
            opacity: 0.8;
            font-size: 10px;
        }

        .summary-card {
            background: white;
            border-radius: 8px;
            padding: 12px;
            /*box-shadow: 0 4px 20px rgba(0,0,0,0.08);*/
            border: 1px solid #e2e8f0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 12px;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: #64748b;
            font-weight: 600;
        }

        .summary-value {
            font-weight: 700;
            color: #1e293b;
        }

        .text-danger {
            color: #ef4444;
        }

        .text-success {
            color: #10b981;
        }

        .summary-total {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            margin: 12px -13px -13px -13px;
            padding: 16px 18px;
            border-radius: 0 0 10px 10px;
        }

        .summary-total .summary-label,
        .summary-total .summary-value {
            color: white;
            font-size: 15px;
            font-weight: 800;
        }



        /* Footer */
        .invoice-footer {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 12px 30px;
            text-align: center;
            margin-top: auto;
        }

        .footer-warning {
            background: rgba(255, 193, 7, 0.2);
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            color: #ffc107;
            font-weight: 600;
            font-size: 11px;
        }

        .footer-info {
            font-size: 9px;
            opacity: 0.8;
            line-height: 1.6;
        }

        /* Print Button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(13, 110, 253, 0.4);
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(239, 68, 68, 0.08);
            font-weight: 900;
            letter-spacing: 10px;
            z-index: 1;
            pointer-events: none;
            text-transform: uppercase;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .page {
                margin: 0;
                box-shadow: none;
                page-break-after: avoid;
                page-break-inside: avoid;
            }

            .print-button {
                display: none;
            }

            .no-print {
                display: none !important;
            }

            .header-bg,
            .pattern-overlay,
            .invoice-header,
            .info-cards,
            .table-container,
            .summary-section,
            .invoice-footer {
                page-break-inside: avoid;
            }

            /* Pastikan warna tetap muncul saat print */
            .header-bg {
                background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 50%, #0a58ca 100%) !important;
            }

            .invoice-table thead {
                background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
            }

            .summary-total {
                background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
            }

            .invoice-footer {
                background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
            }

            .status-paid {
                background: #10b981 !important;
            }

            .status-unpaid {
                background: #ef4444 !important;
            }

            .info-icon {
                background: linear-gradient(135deg, #0d6efd, #0b5ed7) !important;
            }

            .invoice-meta {
                background: rgba(255,255,255,0.15);
                backdrop-filter: blur(25px);
                padding: 20px;
                border-radius: 10px;
                border: 1px solid #EAEAEA;
            }

            .invoice-meta h2 {
                color: black !important;
            }

            .invoice-meta .invoice-number {
                color: black !important;
            }

            .invoice-meta .meta-label {
                color: black !important;
            }

            .invoice-meta .meta-value {
                color: black !important;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        🖨️ Print / Save PDF
    </button>

    <div class="page">
        <!-- Watermark for unpaid -->
        @if ($invoice->status->id != 8)
        <div class="watermark">UNPAID</div>
        @endif

        <!-- Decorative Background -->
        <div class="header-bg"></div>
        <div class="pattern-overlay"></div>

        <div class="content">
            <!-- Header -->
            <div class="invoice-header">
                <div class="company-section">
                    {{-- <div class="logo-container">
                        <img src="/assets/logo_new.png" alt="Logo" class="company-logo">
                    </div> --}}
                    <div class="company-details">
                        <h1>Niscala Network Media</h1>
                        <p>📍 Temanggung, Jetis, Saptosari</p>
                        <p>📍 Gunungkidul, DIYogyakarta</p>
                        <p>📞 +62 XXX XXXX XXXX</p>
                        <p>✉️ info@niscalanetwork.com</p>
                    </div>
                </div>
                <div class="invoice-meta fw-bold" style="color: #222831">
                  <div class="logo-container">
                      <img src="/assets/logo_new.png" alt="Logo" class="company-logo">
                  </div>
                </div>
            </div>

            <!-- Info Cards -->
            <div class="info-cards">
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon"><i class="fa fa-user"></i></div>
                        <h3>Tagihan Kepada</h3>
                    </div>
                    <p><span class="info-label">Nama Customer:</span>{{ $invoice->customer->nama_customer }}</p>
                    <p><span class="info-label">Alamat:</span>{{ $invoice->customer->alamat }}</p>
                    <p><span class="info-label">Email:</span>{{ $invoice->customer->email }}</p>
                    <p><span class="info-label">Telp:</span>{{ $invoice->customer->no_hp }}</p>
                </div>
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon"><i class="fa fa-box"></i></div>
                        <h3>Detail Layanan</h3>
                    </div>
                    <p><span class="info-label">Paket:</span>{{ $invoice->customer->paket->nama_paket }}</p>
                    <p><span class="info-label">Periode:</span>{{ date('F', strtotime($invoice->jatuh_tempo)) }}</p>
                    <p><span class="info-label">Sistem:</span>NBilling</p>
                    <p><span class="info-label">Kategori:</span>Layanan Internet</p>
                </div>
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon"><i class="fa fa-money-bill"></i></div>
                        <h3>Detail Pembayaran</h3>
                    </div>
                    <p><span class="info-label">Tanggal Bayar:</span>{{ $tanggal }}</p>
                    <p><span class="info-label">Metode Pembayaran:</span>{{ $pembayaran->metode_bayar }}</p>
                    <p><span class="info-label">Admin:</span>{{ $pembayaran->user->name ?? 'Tripay' }}</p>
                    <p><span class="info-label">Total Pembayaran:</span>Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-container">
                <table class="invoice-table">
                    <thead>
                        <tr class="text-center">
                            <th class="text-center" style="width: 50%;">DESKRIPSI</th>
                            <th class="text-center" style="width: 20%;">HARGA</th>
                            <th class="text-center" style="width: 10%;">QTY</th>
                            <th class="text-center" style="width: 20%;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-center">
                            <td>
                                <div class="item-name">{{ $invoice->customer->paket->nama_paket }}</div>
                                <div class="item-description">Layanan Internet - Periode {{ date('F Y', strtotime($invoice->created_at)) }}</div>
                            </td>
                            <td class="text-center">
                                <span class="price">Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-center">
                                <span class="qty-badge">1</span>
                            </td>
                            <td class="text-center">
                                <span class="price">Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="summary-section">
                <div class="notes-card">
                    <h4>💬 Catatan & Informasi</h4>
                    <div class="note-item">
                        <span class="note-icon">✓</span>
                        <span>Terima kasih atas kepercayaan Anda menggunakan layanan kami.</span>
                    </div>
                    <div class="note-item">
                        <span class="note-icon">✓</span>
                        <span>Mohon lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari pemutusan layanan.</span>
                    </div>
                    <div class="note-item">
                        <span class="note-icon">✓</span>
                        <span>Untuk pertanyaan lebih lanjut, silakan hubungi customer service kami.</span>
                    </div>

                    @if ($invoice->status->id == 8)
                    <div class="payment-status-box">
                        <p>✓ PEMBAYARAN TELAH LUNAS</p>
                        <small>Diterima pada {{ date('d M Y') }}</small>
                    </div>
                    @endif
                </div>

                <div class="summary-card">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value">Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Biaya Tambahan</span>
                        <span class="summary-value">Rp {{ number_format($invoice->tambahan, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Sisa Saldo</span>
                        <span class="summary-value text-success">- Rp {{ number_format($invoice->saldo, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Tunggakan</span>
                        <span class="summary-value text-danger">+ Rp {{ number_format($invoice->tunggakan, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-total">
                      <div class="summary-row">
                          <span class="summary-label">TOTAL TAGIHAN</span>
                          <span class="summary-value">Rp {{ number_format($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan - $invoice->saldo, 0, ',', '.') }}</span>
                      </div>
                    </div>
                </div>
            </div>


        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-warning">
                <code>⚠️ Dokumen ini adalah kwitansi resmi. Mohon simpan sebagai bukti pembayaran yang sah.</code>
            </div>
            <div class="footer-info">
                Dokumen ini dicetak secara otomatis oleh sistem NBilling<br>
                Dicetak pada: {{ date('d M Y H:i:s') }}
            </div>
        </div>
    </div>
    <script>
        // Auto trigger print dialog when page loads
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });

        // Close window after printing (optional)
        window.addEventListener('afterprint', function() {
            window.close();
        });
    </script>
</body>
</html>
