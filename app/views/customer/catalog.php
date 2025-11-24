<?php
// Ensure search_criteria exists with defaults to avoid undefined key warnings in view
if (!isset($search_criteria) || !is_array($search_criteria)) {
    $search_criteria = [];
}
$search_criteria = array_merge([
    'make' => '', 'model' => '', 'year' => '', 'fuel_type' => '',
    'transmission' => '', 'min_price' => '', 'max_price' => '', 'seating_capacity' => ''
], $search_criteria);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Cars - Customer Catalog</title>
    <link rel="icon" href="<?= site_url('/favicon.svg') ?>" type="image/svg+xml">
    <link rel="alternate icon" href="<?= site_url('/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= site_url('/apple-touch-icon.png') ?>">
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php require APP_DIR . 'views/partials/navbar.php'; ?>

<div class="container mt-4">
        <?php if (!empty($promo_code ?? '')): ?>
                <?php
                    $promo = strtoupper(trim($promo_code));
                    $desc = [
                        'WEEKEND15' => 'Up to 15% off on Fri–Sun bookings.',
                        '7DAYSLONG' => 'Special rate for rentals 7 days or longer.',
                        'EARLY10' => '10% off when booking 14+ days in advance.',
                        'RUSH300' => '₱300 off when booking within 48 hours.',
                        'SUV12' => '12% off on SUVs for a limited time.',
                        'FIRSTRIDE300' => '₱300 off your first booking.'
                    ];
                    $text = $desc[$promo] ?? 'Promo applied.';
                ?>
                <div class="alert alert-success d-flex justify-content-between align-items-center">
                        <div>
                                <strong>Promo:</strong> <code><?= htmlspecialchars($promo) ?></code>
                                <span class="ms-2"><?= htmlspecialchars($text) ?></span>
                        </div>
                        <a class="btn btn-sm btn-outline-success" href="<?= site_url('/customer') ?>">Clear</a>
                </div>
        <?php endif; ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-search"></i> Search Cars</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= site_url('/customer') ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Make</label>
                        <input type="text" name="make" class="form-control" value="<?= $search_criteria['make'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-control" value="<?= $search_criteria['model'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" class="form-control" value="<?= $search_criteria['year'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fuel</label>
                        <select name="fuel_type" class="form-select">
                            <option value="">All</option>
                            <option value="gasoline" <?= ($search_criteria['fuel_type'] ?? '') === 'gasoline' ? 'selected' : '' ?>>Gasoline</option>
                            <option value="diesel" <?= ($search_criteria['fuel_type'] ?? '') === 'diesel' ? 'selected' : '' ?>>Diesel</option>
                            <option value="hybrid" <?= ($search_criteria['fuel_type'] ?? '') === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                            <option value="electric" <?= ($search_criteria['fuel_type'] ?? '') === 'electric' ? 'selected' : '' ?>>Electric</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Transmission</label>
                        <select name="transmission" class="form-select">
                            <option value="">All</option>
                            <option value="automatic" <?= ($search_criteria['transmission'] ?? '') === 'automatic' ? 'selected' : '' ?>>Automatic</option>
                            <option value="manual" <?= ($search_criteria['transmission'] ?? '') === 'manual' ? 'selected' : '' ?>>Manual</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label">Min Price (₱)</label>
                        <input type="number" name="min_price" class="form-control" value="<?= $search_criteria['min_price'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max Price (₱)</label>
                        <input type="number" name="max_price" class="form-control" value="<?= $search_criteria['max_price'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Seats</label>
                        <input type="number" name="seating_capacity" class="form-control" value="<?= $search_criteria['seating_capacity'] ?? '' ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Search</button>
                    </div>
                </div>
                <div class="text-end mt-2">
                    <a href="<?= site_url('/customer') ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <?php if (empty($cars)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No cars available matching your criteria.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($cars as $car): ?>
                <?php
                    $make = htmlspecialchars($car['make'] ?? '');
                    $model = htmlspecialchars($car['model'] ?? '');
                    // Compute image URL with fallbacks; support absolute and relative paths
                    $img = '';
                    if (!empty($car['image_path'])) {
                        $raw = trim($car['image_path'] ?? '');
                        if (preg_match('/^https?:\\/\\//i', $raw) || strpos($raw, 'data:') === 0) {
                            $img = htmlspecialchars($raw);
                        } else {
                            $img = htmlspecialchars(site_url('/' . ltrim($raw, '/')));
                        }
                    }
                    if ($img === '') {
                        $img = 'https://images.unsplash.com/photo-1511918984145-48de785d4c4d?q=80&w=1200&auto=format&fit=crop';
                    }
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?= $img ?>" class="card-img-top" alt="<?= trim($make . ' ' . $model) ?: 'Car image' ?>" loading="lazy" style="height:190px;object-fit:cover;" />
                        <div class="card-body">
                            <h5 class="card-title"><?= $make ?> <?= $model ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($car['year'] ?? '') ?> • <?= htmlspecialchars($car['color'] ?? '') ?></h6>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-id-card"></i> <?= htmlspecialchars($car['plate_number'] ?? '') ?><br>
                                    <i class="fas fa-gas-pump"></i> <?= htmlspecialchars(isset($car['fuel_type']) ? ucfirst($car['fuel_type']) : '') ?><br>
                                    <i class="fas fa-cog"></i> <?= htmlspecialchars(isset($car['transmission']) ? ucfirst($car['transmission']) : '') ?><br>
                                    <i class="fas fa-users"></i> <?= (int)($car['seating_capacity'] ?? 0) ?> seats<br>
                                    <i class="fas fa-tachometer-alt"></i> <?= number_format((int)($car['mileage'] ?? 0)) ?> km
                                </small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="text-primary">₱<?= number_format((float)($car['daily_rate'] ?? 0), 2) ?>/day</h4>
                                <div>
                                    <a href="<?= site_url('/customer/car/' . (int)($car['id'] ?? 0)) ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if (!empty($is_logged_in)): ?>
                                        <a href="<?= site_url('/user/rent/' . (int)($car['id'] ?? 0)) ?>" class="btn btn-success btn-sm">
                                            <i class="fas fa-car"></i> Rent
                                        </a>
                                        <?php else: ?>
                                            <a href="<?= site_url('/user/login?redirect_to=' . rawurlencode(site_url('/user/rent/' . (int)($car['id'] ?? 0)))) ?>" class="btn btn-success btn-sm" title="Login to rent" data-bs-toggle="modal" data-bs-target="#loginModal">
                                            <i class="fas fa-car"></i> Rent
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
