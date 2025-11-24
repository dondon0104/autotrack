<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Rentals</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
  <style>
    body { background: #f6f7fb; }
    .page-wrap { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }
    .card { border: 0; border-radius: 1rem; box-shadow: 0 12px 28px rgba(0,0,0,.08); }
    .status-pill { padding: .25rem .5rem; border-radius: 999px; font-size: .75rem; }
  </style>
</head>
<body>
<?php require APP_DIR . 'views/partials/navbar.php'; ?>
<div class="page-wrap">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="m-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> My Rentals</h3>
    <a class="btn btn-outline-secondary" href="<?= site_url('/user/dashboard') ?>"><i class="fa-solid fa-car-side me-1"></i> Browse Cars</a>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Car</th>
              <th>Plate</th>
              <th>Period</th>
              <th>Days</th>
              <th>Total</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($rentals)): ?>
              <?php foreach ($rentals as $r): ?>
                <tr>
                  <td><?= (int)($r['id'] ?? 0) ?></td>
                  <td><?= htmlspecialchars(($r['make'] ?? '')) ?> <?= htmlspecialchars(($r['model'] ?? '')) ?><?= isset($r['year']) ? ' ('.htmlspecialchars((string)$r['year']).')' : '' ?></td>
                  <td><?= htmlspecialchars(($r['plate_number'] ?? '')) ?></td>
                  <td>
                    <div class="small text-muted">From</div>
                    <div><?= htmlspecialchars(($r['rental_start'] ?? '')) ?></div>
                    <div class="small text-muted mt-1">To</div>
                    <div><?= htmlspecialchars(($r['rental_end'] ?? '')) ?></div>
                  </td>
                  <td><?= (int)($r['total_days'] ?? 0) ?></td>
                  <td>₱<?= number_format((float)($r['total_amount'] ?? 0), 2) ?></td>
                  <td>
                    <?php $status = strtolower((string)($r['status'] ?? '')); ?>
                    <?php
                      $cls = 'bg-secondary text-white';
                      if ($status === 'pending') $cls = 'bg-warning text-dark';
                      elseif ($status === 'confirmed' || $status === 'active') $cls = 'bg-info text-dark';
                      elseif ($status === 'completed') $cls = 'bg-success text-white';
                      elseif ($status === 'cancelled') $cls = 'bg-danger text-white';
                    ?>
                    <span class="status-pill <?= $cls ?> text-capitalize"><?= htmlspecialchars($status) ?></span>
                  </td>
                  <td class="text-end">
                    <div class="btn-group">
                      <a class="btn btn-sm btn-outline-primary" href="<?= site_url('/user/payment/' . (int)($r['id'] ?? 0)) ?>">
                        <i class="fa-solid fa-credit-card me-1"></i> Pay / Details
                      </a>
                      <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('/user/contract/' . (int)($r['id'] ?? 0)) ?>">
                        <i class="fa-solid fa-file-signature me-1"></i> Contract
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">You have no rentals yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
