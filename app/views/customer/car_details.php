<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Details</title>
  <link rel="icon" href="<?= site_url('/favicon.svg') ?>" type="image/svg+xml">
  <link rel="alternate icon" href="<?= site_url('/favicon.ico') ?>">
  <link rel="apple-touch-icon" href="<?= site_url('/apple-touch-icon.png') ?>">
  <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
      /* Make car photo fully visible (no crop) while staying responsive */
      .car-photo {
        width: 100%;
        height: 420px;              /* desktop height */
        object-fit: contain;        /* show full image, no cropping */
        background-color: #fff;
        border: 1px solid #e9ecef;
      }
      @media (max-width: 576px) {
        .car-photo { height: 260px; }
      }
    </style>
</head>
<body class="bg-light">
<?php require APP_DIR . 'views/partials/navbar.php'; ?>

<div class="container mt-4">
    <div class="card">
                <div class="card-body">
                        <?php
                            $make = htmlspecialchars($car['make'] ?? '');
                            $model = htmlspecialchars($car['model'] ?? '');
                            $year = htmlspecialchars($car['year'] ?? '');
                            $color = htmlspecialchars($car['color'] ?? '');
                            $category = htmlspecialchars($car['category'] ?? '');
                            $plate = htmlspecialchars($car['plate_number'] ?? '');
                            $fuel = htmlspecialchars(isset($car['fuel_type']) ? ucfirst($car['fuel_type']) : '');
                            $trans = htmlspecialchars(isset($car['transmission']) ? ucfirst($car['transmission']) : '');
                            $seats = (int)($car['seating_capacity'] ?? 0);
                            $mileage = number_format((int)($car['mileage'] ?? 0));
                            $dailyRate = (float)($car['daily_rate'] ?? 0);
                            $status = strtolower($car['status'] ?? 'available');
                            $vin = trim($car['vin'] ?? '');
                            $vinMasked = $vin ? str_repeat('•', max(0, strlen($vin) - 4)) . substr($vin, -4) : '';
                      // Prefer variant-specific image if provided by controller
                      $img = '';
                      $rawCandidate = $variant_image_path ?? $car['image_path'] ?? '';
                      if (!empty($rawCandidate)) {
                        $raw = trim($rawCandidate);
                        if (preg_match('/^https?:\\/\\//i', $raw) || strpos($raw, 'data:') === 0) {
                          $img = htmlspecialchars($raw);
                        } else {
                          $img = htmlspecialchars(site_url('/' . ltrim($raw, '/')));
                        }
                      }
                            if ($img === '') {
                                    $img = 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?q=80&w=1600&auto=format&fit=crop';
                            }
                            $statusBadge = 'secondary';
                            if ($status === 'available') $statusBadge = 'success';
                            elseif ($status === 'rented') $statusBadge = 'warning';
                            elseif ($status === 'maintenance') $statusBadge = 'danger';
                        ?>
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <h3 class="mb-2 mb-sm-0"><?= $make ?> <?= $model ?> (<?= $year ?>)</h3>
                            <span class="badge bg-<?= $statusBadge ?> text-uppercase"><?= htmlspecialchars($status) ?></span>
                        </div>
                        <p class="text-muted mb-2">Category: <?= $category ?: 'N/A' ?> • Color: <?= $color ?> • Plate: <?= $plate ?><?= $vin ? ' • VIN: ' . htmlspecialchars($vinMasked) : '' ?></p>

                        <div class="mb-3">
                          <a id="carImageLink" href="<?= $img ?>" target="_blank" rel="noopener">
                            <img id="carImage" src="<?= $img ?>" alt="<?= trim($make . ' ' . $model) ?: 'Car image' ?>" class="img-fluid rounded car-photo d-block" loading="lazy" />
                          </a>
                          <div class="text-muted small mt-1">Click the image to view full size</div>
                        </div>

                        <div class="row g-3 mb-3 text-muted small">
                            <div class="col-6 col-md-3"><i class="fa-solid fa-gear me-1"></i><?= $trans ?></div>
                            <div class="col-6 col-md-3"><i class="fa-solid fa-gas-pump me-1"></i><?= $fuel ?></div>
                            <div class="col-6 col-md-3"><i class="fa-solid fa-users me-1"></i><?= $seats ?> seats</div>
                            <div class="col-6 col-md-3"><i class="fa-solid fa-tachometer-alt me-1"></i><?= $mileage ?> km</div>
                        </div>

                        <?php
                          // Simple color variant selector scaffold.
                          // For now we use known neutral palette and highlight the current color.
                          // If you store multiple variants later, you can replace this list with dynamic options.
                          // Use variant colors from DB if available; otherwise fallback to a common palette
                          $knownColors = !empty($variant_colors) ? $variant_colors : ['White','Black','Silver','Gray','Blue','Red'];
                          $currentColor = $selected_variant_color ? htmlspecialchars($selected_variant_color) : $color;
                        ?>
                        <div class="mb-3">
                          <div class="d-flex align-items-center mb-2">
                            <strong class="me-2">Color variant:</strong>
                            <span class="badge bg-secondary"><?= $currentColor ?: 'N/A' ?></span>
                          </div>
                          <div class="d-flex flex-wrap gap-2" id="colorSelector" data-car-id="<?= (int)($car['id'] ?? 0) ?>">
                            <?php foreach ($knownColors as $c): 
                              $isActive = (strcasecmp($currentColor, $c) === 0);
                              $btnClass = $isActive ? 'btn-dark' : 'btn-outline-secondary';
                            ?>
                              <button type="button" class="btn btn-sm <?= $btnClass ?> color-btn" data-color="<?= htmlspecialchars($c) ?>" aria-pressed="<?= $isActive ? 'true' : 'false' ?>" title="<?= htmlspecialchars($c) ?>">
                                <?= htmlspecialchars($c) ?>
                              </button>
                            <?php endforeach; ?>
                          </div>
                        </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card border-0">
                      <div class="card-body p-0">
                        <h5 class="mb-2">Specs</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Fuel:</strong> <?= $fuel ?></li>
                            <li class="list-group-item"><strong>Transmission:</strong> <?= $trans ?></li>
                            <li class="list-group-item"><strong>Seats:</strong> <?= $seats ?></li>
                            <li class="list-group-item"><strong>Mileage:</strong> <?= $mileage ?> km</li>
                            <li class="list-group-item"><strong>Daily Rate:</strong> ₱<?= number_format($dailyRate, 2) ?></li>
              <?php if (!empty($category)): ?>
                <li class="list-group-item"><strong>Category:</strong> <?= $category ?></li>
              <?php endif; ?>
                        </ul>
                        <h6 class="mt-3">What’s included</h6>
                        <ul class="small text-muted mb-0">
                          <li>Basic insurance coverage</li>
                          <li>24/7 roadside assistance</li>
                          <li>Free cancellation within policy window</li>
                        </ul>
                      </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                      <h5>About this car</h5>
                      <?php if (!empty($car['description'] ?? '')): ?>
                          <p><?= nl2br(htmlspecialchars($car['description'] ?? '')) ?></p>
                      <?php else: ?>
                          <p class="text-muted">No additional description provided.</p>
                      <?php endif; ?>
                    </div>
                    <div class="p-3 border rounded mb-3 bg-light">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <div class="small text-muted">Daily rate</div>
                          <div class="h4 mb-0">₱<?= number_format($dailyRate, 2) ?>/day</div>
                        </div>
                        <div>
                          <?php $estimate3 = $dailyRate * 3; ?>
                          <div class="small text-muted">Sample (3 days)</div>
                          <div class="fw-semibold">₱<?= number_format($estimate3, 2) ?></div>
                        </div>
                      </div>
                    </div>
                    <div class="mb-3">
                      <h6>Important notes</h6>
                      <ul class="small text-muted mb-0">
                        <li>Valid driver’s license and government ID required</li>
                        <li>Security deposit may apply depending on vehicle</li>
                        <li>Subject to availability and standard rental policy</li>
                      </ul>
                    </div>
                    <div class="mt-2">
                        <?php if (!empty($is_logged_in)): ?>
                            <a href="<?= site_url('/user/rent/' . (int)($car['id'] ?? 0)) ?>" class="btn btn-success">
                                <i class="fas fa-car"></i> Rent this car
                            </a>
                        <?php else: ?>
              <a href="<?= site_url('/user/login?redirect_to=' . rawurlencode(site_url('/user/rent/' . (int)($car['id'] ?? 0)))) ?>" class="btn btn-success" title="Login to rent" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fas fa-car"></i> Login to rent
                            </a>
                        <?php endif; ?>
                        <a href="<?= site_url('/customer') ?>" class="btn btn-secondary ms-2">
                            Back to catalog
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Enhance: when clicking a color, fetch variant image via API and swap without page reload
(function(){
  const selector = document.getElementById('colorSelector');
  if (!selector) return;
  const carId = selector.getAttribute('data-car-id');
  const imgEl = document.getElementById('carImage');
  const linkEl = document.getElementById('carImageLink');
  const colorSection = selector.closest('.mb-3');
  let badge = null;
  if (colorSection) {
    const labelRow = colorSection.previousElementSibling;
    if (labelRow) badge = labelRow.querySelector('.badge');
  }

  selector.addEventListener('click', async (e) => {
    const btn = e.target.closest('.color-btn');
    if (!btn) return;
    const color = btn.getAttribute('data-color');
    try {
      // Toggle active styles
      selector.querySelectorAll('.color-btn').forEach(b => {
        b.classList.remove('btn-dark');
        b.classList.add('btn-outline-secondary');
        b.setAttribute('aria-pressed', 'false');
      });
      btn.classList.remove('btn-outline-secondary');
      btn.classList.add('btn-dark');
      btn.setAttribute('aria-pressed', 'true');

  // Use proper encoding for path segment
  const url = `<?= site_url('/api/cars') ?>/${encodeURIComponent(carId)}/variant/${encodeURIComponent(color)}`;
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('Network error');
      const data = await res.json();
      if (data && data.ok && data.image_url) {
        const bust = (data.image_url.indexOf('?') >= 0 ? '&' : '?') + 'v=' + Date.now();
        imgEl.src = data.image_url + bust;
        linkEl.href = data.image_url;
      }
      // Update current color badge text if present
      if (badge) { badge.textContent = color; }
    } catch (err) {
      console.error('Failed to load variant image', err);
    }
  });
})();
</script>
</body>
</html>
