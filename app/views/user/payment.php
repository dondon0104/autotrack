<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment — CarRental</title>
    <link rel="icon" href="<?= site_url('/favicon.svg') ?>" type="image/svg+xml" />
    <link rel="alternate icon" href="<?= site_url('/favicon.ico') ?>" />
    <link rel="apple-touch-icon" href="<?= site_url('/apple-touch-icon.png') ?>" />
    <meta name="theme-color" content="#0d6efd" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
    <style>
        body { background: #f6f7fb; }
        .wrap { max-width: 980px; margin: 2rem auto; padding: 0 1rem; }
        .card { border: 0; border-radius: 1rem; box-shadow: 0 20px 40px rgba(0,0,0,.08); }
        .card-header { background: transparent; border-bottom: 0; padding-bottom: 0; }
        .brand { font-weight: 700; color: #0ea5a5; }
        .accent { color: #0ea5a5; }
        .label-sm { font-size: .85rem; color: #6c757d; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="mb-3 d-flex align-items-center gap-2">
        <i class="fa-solid fa-credit-card text-primary"></i>
        <h3 class="m-0">Payment</h3>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?= $this->session->flashdata('error'); ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i><?= $this->session->flashdata('success'); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <h5 class="mb-3">Rental Summary</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="label-sm">Car</span>
                            <span><strong><?= htmlspecialchars(($rental['make'] ?? '')) ?> <?= htmlspecialchars(($rental['model'] ?? '')) ?></strong></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="label-sm">Plate</span>
                            <span><?= htmlspecialchars(($rental['plate_number'] ?? '')) ?></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="label-sm">Rental Period</span>
                            <span><?= htmlspecialchars(($rental['rental_start'] ?? '')) ?> to <?= htmlspecialchars(($rental['rental_end'] ?? '')) ?></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="label-sm">Days</span>
                            <span><?= (int)($rental['total_days'] ?? 0) ?></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="label-sm">Daily Rate</span>
                            <span>₱<?= number_format((float)($rental['daily_rate'] ?? 0), 2) ?></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="label-sm">Subtotal</span>
                            <span>₱<?= number_format((float)($rental['subtotal'] ?? 0), 2) ?></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="label-sm">Tax (<?= htmlspecialchars((string)($rental['tax_rate'] ?? 0)) ?>%)</span>
                            <span>₱<?= number_format((float)($rental['tax_amount'] ?? 0), 2) ?></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="label-sm">Total</span>
                            <span class="fw-bold text-success">₱<?= number_format((float)($rental['total_amount'] ?? 0), 2) ?></span>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-lg-6">
                    <h5 class="mb-3">Payment Details</h5>
                                        <?php
                                            $ps = $payment_summary ?? [
                                                'total' => (float)($rental['total_amount'] ?? 0),
                                                'paid' => 0,
                                                'remaining' => (float)($rental['total_amount'] ?? 0),
                                                'allow_partial' => false,
                                                'deposit_rate' => 0,
                                                'required_deposit' => (float)($rental['total_amount'] ?? 0),
                                                'needed_for_deposit' => (float)($rental['total_amount'] ?? 0)
                                            ];
                                            $isPending = ($rental['status'] ?? '') === 'pending';
                                            $depositNotMet = $ps['allow_partial'] && ($ps['needed_for_deposit'] > 0);
                                            $minPay = ($isPending && $depositNotMet) ? $ps['required_deposit'] : 0.01;
                                            // Suggested amount: fixed deposit when pending & deposit not met; else remaining
                                            $suggest = ($isPending && $depositNotMet) ? min($ps['remaining'], $ps['required_deposit']) : $ps['remaining'];
                                        ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between"><span class="label-sm">Total</span><span class="fw-semibold">₱<?= number_format($ps['total'], 2) ?></span></div>
                        <div class="d-flex justify-content-between"><span class="label-sm">Paid</span><span>₱<?= number_format($ps['paid'], 2) ?></span></div>
                        <div class="d-flex justify-content-between"><span class="label-sm">Remaining</span><span class="text-warning">₱<?= number_format($ps['remaining'], 2) ?></span></div>
                        <?php if ($ps['allow_partial'] && $ps['remaining'] > 0): ?>
                        <div class="d-flex justify-content-between"><span class="label-sm">Required deposit (<?= (int)round($ps['deposit_rate']*100) ?>%)</span><span>₱<?= number_format($ps['required_deposit'], 2) ?></span></div>
                        <div class="d-flex justify-content-between"><span class="label-sm">Needed to confirm</span><span class="text-success">₱<?= number_format(max(0,$ps['needed_for_deposit']), 2) ?></span></div>
                        <?php endif; ?>
                    </div>
                                        <form action="<?= site_url('/user/payment') ?>" method="post">
                        <input type="hidden" name="rental_id" value="<?= (int)($rental['id'] ?? 0) ?>">
                        <div class="mb-3">
                            <label class="form-label">Amount to pay</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                                                <input type="number" step="0.01" min="<?= htmlspecialchars(number_format($minPay, 2, '.', '')) ?>" max="<?= htmlspecialchars((string)$ps['remaining']) ?>" class="form-control" name="amount" value="<?= htmlspecialchars(number_format($suggest, 2, '.', '')) ?>" required>
                            </div>
                                                        <?php if ($ps['allow_partial'] && $isPending && $depositNotMet): ?>
                                                            <div class="form-text">
                                                                Minimum payment is <strong>₱<?= number_format($ps['required_deposit'], 2) ?></strong> (<?= (int)round($ps['deposit_rate']*100) ?>% deposit). You can pay more, but not less.
                                                            </div>
                                                        <?php elseif (!$ps['allow_partial']): ?>
                                                            <div class="form-text">Full payment required.</div>
                                                        <?php else: ?>
                                                            <div class="form-text">You can pay any amount up to the remaining balance.</div>
                                                        <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" id="page_payment_method" required>
                                <option value="" disabled selected>Select a method</option>
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="gcash">GCash</option>
                            </select>
                        </div>
                        <div id="page_gcash_block" class="border rounded p-3 mb-3" style="display:none;">
                            <div class="mb-2">
                                <strong>GCash details:</strong><br>
                                <span>Account name: <?= htmlspecialchars(config_item('gcash_name') ?: 'N/A') ?></span><br>
                                <span>GCash number: <?= htmlspecialchars(config_item('gcash_number') ?: 'N/A') ?></span>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">GCash Reference No.</label>
                                <input type="text" class="form-control" name="gcash_reference" id="page_gcash_ref" placeholder="e.g. 1234 5678 9012" />
                                <div class="form-text">Enter the reference number from your GCash confirmation.</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a class="btn btn-outline-secondary" href="<?= site_url('/user/my-rentals') ?>"><i class="fa-solid fa-clock-rotate-left me-1"></i> My Rentals</a>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-lock me-1"></i> Pay Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
        (function(){
            var sel = document.getElementById('page_payment_method');
            var block = document.getElementById('page_gcash_block');
            var ref = document.getElementById('page_gcash_ref');
            var mode = '<?= htmlspecialchars((string)(config_item('gcash_mode') ?: 'manual')) ?>';
            if (!sel) return;
            function update(){
                var isGCash = sel.value === 'gcash';
                // Show manual reference only if manual mode
                var showRef = isGCash && mode === 'manual';
                if (block) block.style.display = showRef ? '' : 'none';
                if (ref) {
                    if (showRef) ref.setAttribute('required','required');
                    else ref.removeAttribute('required');
                }
            }
            sel.addEventListener('change', update);
            update();
        })();
</script>
</body>
</html>
