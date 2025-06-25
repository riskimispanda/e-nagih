@extends('layouts.contentNavbarLayout')

@section('title', 'Tripay Callback Tester')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Payment /</span> Tripay Callback Tester
        </h4>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <h5 class="card-header">Test Tripay Payment Callback</h5>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <h6>Callback URL Information</h6>
                            <p>Your callback URLs are:</p>
                            <ul>
                                <li>Standard: <code>{{ $callbackUrl }}</code></li>
                                <li>API: <code>{{ url('/api/payment/callback') }}</code></li>
                                <li>Fallback: <code>{{ url('/tripay-callback') }}</code></li>
                            </ul>
                            <p>This tool allows you to simulate a payment callback from Tripay to mark an invoice as paid
                                without actually making a payment.</p>
                        </div>

                        <ul class="nav nav-tabs mb-3" id="callbackTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="standard-tab" data-bs-toggle="tab"
                                    data-bs-target="#standard" type="button" role="tab" aria-controls="standard"
                                    aria-selected="true">Standard Callback</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="fallback-tab" data-bs-toggle="tab" data-bs-target="#fallback"
                                    type="button" role="tab" aria-controls="fallback" aria-selected="false">Fallback
                                    Callback</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="direct-tab" data-bs-toggle="tab" data-bs-target="#direct"
                                    type="button" role="tab" aria-controls="direct" aria-selected="false">Direct
                                    Test</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="sandbox-tab" data-bs-toggle="tab" data-bs-target="#sandbox"
                                    type="button" role="tab" aria-controls="sandbox" aria-selected="false">Sandbox
                                    Simulation</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tripay-test-tab" data-bs-toggle="tab"
                                    data-bs-target="#tripay-test" type="button" role="tab" aria-controls="tripay-test"
                                    aria-selected="false">Tripay Test</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="status-tab" data-bs-toggle="tab" data-bs-target="#status"
                                    type="button" role="tab" aria-controls="status" aria-selected="false">Check
                                    Status</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="callbackTabsContent">
                            <div class="tab-pane fade show active" id="standard" role="tabpanel"
                                aria-labelledby="standard-tab">
                                <form action="{{ route('payment.callback.test') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="invoice_id" class="form-label">Select Invoice to Mark as Paid</label>
                                        <select class="form-select" id="invoice_id" name="invoice_id" required>
                                            <option value="">-- Select an Invoice --</option>
                                            @foreach ($invoices as $invoice)
                                                <option value="{{ $invoice->id }}">
                                                    Invoice #{{ $invoice->id }} - {{ $invoice->customer->nama_customer }} -
                                                    Rp
                                                    {{ number_format($invoice->tagihan, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Simulate Standard Callback</button>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="fallback" role="tabpanel" aria-labelledby="fallback-tab">
                                <div class="mb-3">
                                    <label for="fallback_invoice_id" class="form-label">Select Invoice to Mark as
                                        Paid</label>
                                    <select class="form-select" id="fallback_invoice_id">
                                        <option value="">-- Select an Invoice --</option>
                                        @foreach ($invoices as $invoice)
                                            <option value="{{ $invoice->id }}">
                                                Invoice #{{ $invoice->id }} - {{ $invoice->customer->nama_customer }} -
                                                Rp
                                                {{ number_format($invoice->tagihan, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" id="testFallbackBtn" class="btn btn-primary">Test Fallback
                                    Callback</button>
                            </div>

                            <div class="tab-pane fade" id="direct" role="tabpanel" aria-labelledby="direct-tab">
                                <div class="mb-3">
                                    <label for="direct_invoice_id" class="form-label">Select Invoice to Mark as
                                        Paid</label>
                                    <select class="form-select" id="direct_invoice_id">
                                        <option value="">-- Select an Invoice --</option>
                                        @foreach ($invoices as $invoice)
                                            <option value="{{ $invoice->id }}">
                                                Invoice #{{ $invoice->id }} - {{ $invoice->customer->nama_customer }} -
                                                Rp
                                                {{ number_format($invoice->tagihan, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" id="testDirectBtn" class="btn btn-primary">Test Direct
                                    Callback</button>
                            </div>

                            <div class="tab-pane fade" id="sandbox" role="tabpanel" aria-labelledby="sandbox-tab">
                                <div class="alert alert-warning">
                                    <strong>Sandbox Simulation:</strong> This simulates a complete Tripay sandbox payment
                                    flow.
                                </div>
                                <div class="mb-3">
                                    <label for="sandbox_invoice_id" class="form-label">Select Invoice for Sandbox
                                        Simulation</label>
                                    <select class="form-select" id="sandbox_invoice_id">
                                        <option value="">-- Select an Invoice --</option>
                                        @foreach ($invoices as $invoice)
                                            <option value="{{ $invoice->id }}">
                                                Invoice #{{ $invoice->id }} - {{ $invoice->customer->nama_customer }} -
                                                Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}
                                                @if ($invoice->reference)
                                                    (Ref: {{ $invoice->reference }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" id="testSandboxBtn" class="btn btn-warning">Simulate Sandbox
                                    Payment</button>
                            </div>

                            <div class="tab-pane fade" id="tripay-test" role="tabpanel"
                                aria-labelledby="tripay-test-tab">
                                <div class="alert alert-warning">
                                    <strong>Tripay Test Callback:</strong> This simulates the exact callback that Tripay
                                    test feature sends.
                                    <br><strong>Test Callback URL:</strong>
                                    <code>{{ url('/payment/tripay-test-callback') }}</code>
                                </div>

                                <form id="tripayTestForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="test_reference" class="form-label">Reference</label>
                                                <input type="text" class="form-control" id="test_reference"
                                                    name="reference" placeholder="Enter transaction reference" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="test_merchant_ref" class="form-label">Merchant
                                                    Reference</label>
                                                <input type="text" class="form-control" id="test_merchant_ref"
                                                    name="merchant_ref" placeholder="INV-123-1234567890" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="test_status" class="form-label">Status</label>
                                                <select class="form-select" id="test_status" name="status">
                                                    <option value="PAID">PAID</option>
                                                    <option value="UNPAID">UNPAID</option>
                                                    <option value="EXPIRED">EXPIRED</option>
                                                    <option value="FAILED">FAILED</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="test_amount" class="form-label">Amount</label>
                                                <input type="number" class="form-control" id="test_amount"
                                                    name="amount" placeholder="150000">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="test_invoice_select" class="form-label">Or Select from Existing
                                            Invoices</label>
                                        <select class="form-select" id="test_invoice_select"
                                            onchange="fillTripayTestForm(this.value)">
                                            <option value="">-- Select an Invoice to Auto-fill --</option>
                                            @foreach ($invoices as $invoice)
                                                <option value="{{ $invoice->id }}"
                                                    data-reference="{{ $invoice->reference }}"
                                                    data-merchant-ref="{{ $invoice->merchant_ref }}"
                                                    data-amount="{{ $invoice->tagihan }}">
                                                    Invoice #{{ $invoice->id }} - {{ $invoice->customer->nama_customer }}
                                                    -
                                                    Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}
                                                    @if ($invoice->reference)
                                                        ({{ $invoice->reference }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-warning">
                                        <i class="bx bx-send me-1"></i>
                                        Send Tripay Test Callback
                                    </button>
                                </form>

                                <div id="tripayTestResult" class="mt-3" style="display: none;"></div>
                            </div>

                            <div class="tab-pane fade" id="status" role="tabpanel" aria-labelledby="status-tab">
                                <div class="alert alert-info">
                                    <strong>Status Check:</strong> Check real payment status from Tripay API and update
                                    invoice accordingly.
                                </div>
                                <div class="mb-3">
                                    <label for="status_invoice_id" class="form-label">Select Invoice to Check
                                        Status</label>
                                    <select class="form-select" id="status_invoice_id">
                                        <option value="">-- Select an Invoice --</option>
                                        @foreach ($invoices as $invoice)
                                            <option value="{{ $invoice->id }}">
                                                Invoice #{{ $invoice->id }} - {{ $invoice->customer->nama_customer }} -
                                                Rp {{ number_format($invoice->tagihan, 0, ',', '.') }}
                                                @if ($invoice->reference)
                                                    (Ref: {{ $invoice->reference }})
                                                @else
                                                    (No Reference)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" id="checkStatusBtn" class="btn btn-info">Check Payment
                                    Status</button>
                                <div id="statusResult" class="mt-3" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h5 class="card-header">How It Works</h5>
                    <div class="card-body">
                        <p>This tool simulates a payment callback from Tripay by:</p>
                        <ol>
                            <li>Creating a test payload similar to what Tripay would send</li>
                            <li>Sending it directly to your callback handler</li>
                            <li>Updating the invoice status to paid (status_id = 8)</li>
                        </ol>
                        <p>This is useful for testing your payment flow without having to make actual payments through
                            Tripay.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Test Fallback Callback
            document.getElementById('testFallbackBtn').addEventListener('click', function() {
                const invoiceId = document.getElementById('fallback_invoice_id').value;
                if (!invoiceId) {
                    alert('Please select an invoice');
                    return;
                }

                // Open the fallback URL in a new tab
                const fallbackUrl = '{{ url('/tripay-callback') }}?test_mode=1&invoice_id=' + invoiceId;
                window.open(fallbackUrl, '_blank');
            });

            // Test Direct Callback
            document.getElementById('testDirectBtn').addEventListener('click', function() {
                const invoiceId = document.getElementById('direct_invoice_id').value;
                if (!invoiceId) {
                    alert('Please select an invoice');
                    return;
                }

                // Open the direct test URL in a new tab
                const directUrl = '{{ url('/payment/test') }}/' + invoiceId;
                window.open(directUrl, '_blank');
            });

            // Test Sandbox Simulation
            document.getElementById('testSandboxBtn').addEventListener('click', function() {
                const invoiceId = document.getElementById('sandbox_invoice_id').value;
                if (!invoiceId) {
                    alert('Please select an invoice');
                    return;
                }

                // Open the sandbox simulation URL in a new tab
                const sandboxUrl = '{{ url('/payment/sandbox-simulate') }}/' + invoiceId;
                window.open(sandboxUrl, '_blank');
            });

            // Check Payment Status
            document.getElementById('checkStatusBtn').addEventListener('click', function() {
                const invoiceId = document.getElementById('status_invoice_id').value;
                if (!invoiceId) {
                    alert('Please select an invoice');
                    return;
                }

                const resultDiv = document.getElementById('statusResult');
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = '<div class="alert alert-info">Checking payment status...</div>';

                // Make AJAX request to check status
                fetch('{{ url('/payment/check-status') }}/' + invoiceId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const alertClass = data.invoice_status === 'paid' ? 'alert-success' :
                                'alert-warning';
                            resultDiv.innerHTML = `
                                <div class="alert ${alertClass}">
                                    <strong>Status Check Result:</strong><br>
                                    ${data.message}<br>
                                    <strong>Tripay Status:</strong> ${data.status || 'Unknown'}<br>
                                    <strong>Invoice Status:</strong> ${data.invoice_status}
                                </div>
                            `;
                        } else {
                            resultDiv.innerHTML = `
                                <div class="alert alert-danger">
                                    <strong>Error:</strong> ${data.message}
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        resultDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <strong>Error:</strong> Failed to check payment status
                            </div>
                        `;
                    });
            });

            // Handle Tripay Test Form
            document.getElementById('tripayTestForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const data = Object.fromEntries(formData);

                const resultDiv = document.getElementById('tripayTestResult');
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = '<div class="alert alert-info">Sending test callback...</div>';

                // Send to Tripay test callback endpoint
                fetch('{{ url('/payment/tripay-test-callback') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <strong>Test callback successful!</strong><br>
                                <strong>Message:</strong> ${data.message}<br>
                                <strong>Invoice ID:</strong> ${data.data.invoice_id}<br>
                                <strong>Reference:</strong> ${data.data.reference}<br>
                                <strong>Status:</strong> ${data.data.invoice_status}
                            </div>
                        `;

                            // Refresh page after 2 seconds to show updated status
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            resultDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <strong>Test callback failed!</strong><br>
                                <strong>Error:</strong> ${data.message}
                                ${data.debug ? '<br><strong>Debug:</strong> ' + JSON.stringify(data.debug) : ''}
                            </div>
                        `;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>Test callback failed!</strong><br>
                            <strong>Error:</strong> ${error.message}
                        </div>
                    `;
                    });
            });
        });

        // Function to fill Tripay test form from selected invoice
        function fillTripayTestForm(invoiceId) {
            if (!invoiceId) return;

            const select = document.getElementById('test_invoice_select');
            const option = select.querySelector(`option[value="${invoiceId}"]`);

            if (option) {
                document.getElementById('test_reference').value = option.dataset.reference || '';
                document.getElementById('test_merchant_ref').value = option.dataset.merchantRef || '';
                document.getElementById('test_amount').value = option.dataset.amount || '';
            }
        }
    </script>
@endsection
