    <!-- Search (Model only) -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Search Cars</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= site_url('/user/dashboard') ?>" class="row g-2 align-items-end">
                <div class="col-12 col-md-8">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control" value="<?= $search_criteria['model'] ?? '' ?>" placeholder="e.g., Vios, Altis, Civic">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-search me-1"></i>Search</button>
                    <a href="<?= site_url('/user/dashboard') ?>" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Clear</a>
                </div>
            </form>
        </div>
    </div>
                            <option value="automatic" <?= ($search_criteria['transmission'] ?? '') === 'automatic' ? 'selected' : '' ?>>Automatic</option>
                            <option value="manual" <?= ($search_criteria['transmission'] ?? '') === 'manual' ? 'selected' : '' ?>>Manual</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Min Price (₱)</label>
                        <input type="number" name="min_price" class="form-control" value="<?= $search_criteria['min_price'] ?? '' ?>" placeholder="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Max Price (₱)</label>
                        <input type="number" name="max_price" class="form-control" value="<?= $search_criteria['max_price'] ?? '' ?>" placeholder="10000">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Seating Capacity</label>
                        <input type="number" name="seating_capacity" class="form-control" value="<?= $search_criteria['seating_capacity'] ?? '' ?>" placeholder="5">
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="/user/dashboard" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Available Cars -->
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
                        $img = 'https://images.unsplash.com/photo-1483721310020-03333e577078?q=80&w=1200&auto=format&fit=crop';
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

                            <?php if (!empty($car['description'] ?? '')): ?>
                                <p class="card-text"><?= htmlspecialchars($car['description'] ?? '') ?></p>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="text-primary">₱<?= number_format((float)($car['daily_rate'] ?? 0), 2) ?>/day</h4>
                                <div>
                                    <a href="<?= site_url('/user/car/' . (int)($car['id'] ?? 0)) ?>" class="btn btn-outline-primary btn-sm">
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
