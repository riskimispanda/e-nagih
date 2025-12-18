<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi #{{ $invoice->id }} - Niscala Network</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #000;
            font-size: 12px;
            line-height: 1.5;
        }

        .page {
            width: 210mm;
            min-height: 290mm;
            margin: 20px auto;
            background: #fff;
            padding: 40px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-logo {
            height: 50px;
            width: auto;
            margin-bottom: 10px;
            filter: grayscale(100%);
        }

        .company-info h1 {
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .company-info p {
            font-size: 11px;
            color: #444;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .invoice-number {
            font-size: 14px;
            font-weight: 600;
            color: #000;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .info-box h3 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .info-row {
            display: flex;
            margin-bottom: 6px;
        }

        .info-label {
            width: 100px;
            font-weight: 600;
            color: #555;
            flex-shrink: 0;
        }

        .info-value {
            font-weight: 500;
            color: #000;
        }

        /* Table */
        .table-container {
            margin-bottom: 30px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-table th {
            background: #000;
            color: #fff;
            padding: 12px 15px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .invoice-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .invoice-table tr:last-child td {
            border-bottom: 2px solid #000;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Summary */
        .summary-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 50px;
        }

        .summary-table {
            width: 350px;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 0;
        }

        .summary-label {
            font-weight: 600;
            color: #555;
        }

        .summary-value {
            text-align: right;
            font-weight: 600;
            color: #000;
        }

        .total-row td {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 15px 0;
            font-size: 16px;
            font-weight: 800;
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 40px;
            left: 40px;
            right: 40px;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 20px;
            color: #666;
            font-size: 10px;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 100px;
            font-weight: 900;
            color: rgba(0,0,0,0.03);
            border: 5px solid rgba(0,0,0,0.03);
            padding: 10px 50px;
            text-transform: uppercase;
            pointer-events: none;
            z-index: 0;
        }

        /* Print Button */
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #000;
            color: #fff;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.2s;
            z-index: 100;
        }

        .print-btn:hover {
            transform: translateY(-2px);
        }

        /* Responsive */
        @media screen and (max-width: 768px) {
            .page {
                width: 100%;
                margin: 0;
                padding: 20px;
                min-height: auto;
            }
            .header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
            .invoice-meta {
                text-align: left;
            }
            .info-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            .summary-section {
                justify-content: flex-start;
            }
            .summary-table {
                width: 100%;
            }
            .footer {
                position: relative;
                bottom: auto;
                left: auto;
                right: auto;
                margin-top: 40px;
            }
        }

        @media print {
            body {
                background: #fff;
            }
            .page {
                margin: 0;
                box-shadow: none;
                width: 100%;
                height: auto;
                min-height: auto;
                padding: 20px 20px 80px 20px;
            }
            .print-btn {
                display: none;
            }
            .footer {
                position: fixed;
                bottom: 20px;
                left: 20px;
                right: 20px;
            }
            /* Ensure background colors print */
            .invoice-table th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .header {
                flex-direction: row !important;
                align-items: center !important;
                gap: 0 !important;
            }
            .invoice-meta {
                text-align: right !important;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-btn">
        Print Kwitansi
    </button>

    <div class="page">
        @if ($invoice->status->id != 8)
            <div class="watermark">BELUM LUNAS</div>
        @else
            <div class="watermark">LUNAS</div>
        @endif

        <div class="header">
            <div class="company-info">
                <h1>Niscala Network Media</h1>
                <p>Jasa Layanan Internet & Teknologi Informasi</p>
                <p>Gunungkidul, DIYogyakarta</p>
                <p>Email: info@niscalanetwork.com</p>
            </div>
            <div class="invoice-meta">
                <div class="invoice-title">KWITANSI</div>
                <div class="invoice-number">#{{ $invoice->id }}</div>
                <div class="invoice-number" style="margin-top: 5px;"><br></div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>Diterima Dari</h3>
                <div class="info-row">
                    <div class="info-label">Nama : </div>
                    <div class="info-value">{{ $invoice->customer->nama_customer }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Alamat : </div>
                    <div class="info-value">{{ Str::limit($invoice->customer->alamat, 50) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">No. HP : </div>
                    <div class="info-value">{{ $invoice->customer->no_hp }}</div>
                </div>
            </div>
            <div class="info-box">
                <h3>Rincian Pembayaran</h3>
                <div class="info-row">
                    <div class="info-label">Metode : </div>
                    <div class="info-value">{{ $pembayaran->metode_bayar }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Bayar : </div>
                    <div class="info-value">{{ $tanggal  }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Admin : </div>
                    <div class="info-value">{{ $pembayaran->user->name ?? 'Tripay' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Untuk Periode : </div>
                    <div class="info-value">{{ date('F', strtotime($invoice->jatuh_tempo)) }}</div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50%">Keterangan</th>
                        <th class="text-center" style="width: 15%">Qty</th>
                        <th class="text-center" style="width: 35%">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $invoice->customer->paket->nama_paket }}</div>
                            <div style="font-size: 11px; color: #666; margin-top: 2px;">Layanan Internet Bulanan</div>
                        </td>
                        <td class="text-center">1</td>
                        <td class="text-center">Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}</td>
                    </tr>
                    @if($invoice->tambahan > 0)
                    <tr>
                        <td>Biaya Tambahan</td>
                        <td class="text-center">1</td>
                        <td class="text-center">Rp {{ number_format($invoice->tambahan, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($invoice->tunggakan > 0)
                    <tr>
                        <td>Tunggakan</td>
                        <td class="text-center">-</td>
                        <td class="text-right">Rp {{ number_format($invoice->tunggakan, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <td class="summary-label">Subtotal</td>
                    <td class="summary-value">Rp {{ number_format($invoice->tagihan + $invoice->tambahan + $invoice->tunggakan, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->saldo > 0)
                <tr>
                    <td class="summary-label">Potongan Saldo</td>
                    <td class="summary-value">- Rp {{ number_format($invoice->saldo, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="text-right">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Terima kasih atas kepercayaan Anda.</p>
            <p style="margin-top: 5px;">Dokumen ini adalah bukti pembayaran yang sah dan diterbitkan oleh sistem komputer.</p>
            <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
