<?php $page_title = 'Manage Cars - Car Rental System'; include __DIR__ . '/partials/head.php'; ?>
        <!-- Sidebar -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <!-- Main content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4 main-content">
            <!-- Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center border-bottom mb-4 pb-3">
                <div>
                    <h1 class="h2 mb-0">Manage Cars</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 mt-2">
                            <li class="breadcrumb-item"><a href="<?php echo site_url('/admin/dashboard'); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Cars</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex align-items-center">
                    <a href="<?php echo site_url('/admin/cars/add'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i> Add New Car
                    </a>
                    <div class="ms-3">
                        <div class="user-info text-end">
                            <p class="mb-0"><strong>Welcome, <?php echo isset($username) ? htmlspecialchars($username) : 'Admin'; ?></strong></p>
                            <small class="text-muted">Administrator</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Cars</h6>
                                    <h3 class="mb-0"><?php echo isset($total_cars) ? $total_cars : '0'; ?></h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-car text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Available</h6>
                                    <h3 class="mb-0"><?php echo isset($available_cars) ? $available_cars : '0'; ?></h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Rented</h6>
                                    <h3 class="mb-0"><?php echo isset($rented_cars) ? $rented_cars : '0'; ?></h3>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-clock text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Maintenance</h6>
                                    <h3 class="mb-0"><?php echo isset($maintenance_cars) ? $maintenance_cars : '0'; ?></h3>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-tools text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success d-flex align-items-center border-0 shadow-sm mb-4">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $this->session->flashdata('success'); ?>
                </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger d-flex align-items-center border-0 shadow-sm mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $this->session->flashdata('error'); ?>
                </div>
            <?php endif; ?>

            <!-- Cars List -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <?php if (empty($cars)): ?>
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/3774/3774278.png" alt="No cars" class="mb-3" style="width: 100px; opacity: 0.5;">
                            <h4 class="text-muted">No Cars Found</h4>
                            <p class="text-muted">Start by adding your first car to the system.</p>
                            <a href="<?php echo site_url('/admin/cars/add'); ?>" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i> Add First Car
                            </a>
                        </div>
                    <?php else: ?>
                        <?php
                            // Build unique categories for filter dropdown
                            $__categories = [];
                            foreach ($cars as $__c) {
                                $cat = trim((string)($__c['category'] ?? ''));
                                if ($cat !== '') { $__categories[$cat] = true; }
                            }
                            ksort($__categories);
                        ?>
                        <div class="table-toolbar p-3 border-bottom bg-light">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                        <input type="text" id="carSearch" class="form-control" placeholder="Search make, model, plate...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-signal"></i></span>
                                        <select id="statusFilter" class="form-select">
                                            <option value="">All Statuses</option>
                                            <option value="available">Available</option>
                                            <option value="rented">Rented</option>
                                            <option value="maintenance">Maintenance</option>
                                            <option value="out_of_service">Out of Service</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-tags"></i></span>
                                        <select id="categoryFilter" class="form-select">
                                            <option value="">All Categories</option>
                                            <?php foreach (array_keys($__categories) as $__cat): ?>
                                                <option value="<?= htmlspecialchars($__cat) ?>"><?= htmlspecialchars($__cat) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 text-md-end">
                                    <button type="button" id="clearFilters" class="btn btn-outline-secondary w-100"><i class="fas fa-eraser me-1"></i> Clear</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Car Details</th>
                                        <th>Category</th>
                                        <th>Year</th>
                                        <th>Plate Number</th>
                                        <th>Daily Rate</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cars as $car): ?>
                                        <?php
                                            // Compute thumbnail URL from image_path if present
                                            $thumbUrl = '';
                                            $raw = trim((string)($car['image_path'] ?? ''));
                                            if ($raw !== '') {
                                                if (preg_match('/^https?:\/\//i', $raw) || strpos($raw, 'data:') === 0) { $thumbUrl = $raw; }
                                                else { $thumbUrl = site_url('/' . ltrim($raw, '/')); }
                                            }
                                            $status = strtolower((string)($car['status'] ?? ''));
                                            $category = trim((string)($car['category'] ?? ''));
                                            $searchBlob = strtolower(trim(
                                                ($car['make'] ?? '') . ' ' . ($car['model'] ?? '') . ' ' . ($car['plate_number'] ?? '') . ' ' . ($car['color'] ?? '') . ' ' . ($car['year'] ?? '') . ' ' . $category
                                            ));
                                        ?>
                                        <tr data-status="<?= htmlspecialchars($status) ?>" data-category="<?= htmlspecialchars($category) ?>" data-text="<?= htmlspecialchars($searchBlob) ?>">
                                            <td class="ps-4"><?php echo (int)($car['id'] ?? 0); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($thumbUrl): ?>
                                                        <img src="<?= $thumbUrl ?>" alt="car" class="car-thumb me-3 rounded">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded-circle p-2 me-3">
                                                            <i class="fas fa-car text-primary"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars(($car['make'] ?? '') . ' ' . ($car['model'] ?? '')); ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($car['color'] ?? ''); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($car['category'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($car['year'] ?? ''); ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo htmlspecialchars($car['plate_number'] ?? ''); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong>₱<?php echo number_format((float)($car['daily_rate'] ?? 0), 2); ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                $statusIcon = 'circle';
                                                switch($status) {
                                                    case 'available':
                                                        $statusClass = 'success';
                                                        $statusIcon = 'check-circle';
                                                        break;
                                                    case 'rented':
                                                        $statusClass = 'warning';
                                                        $statusIcon = 'clock';
                                                        break;
                                                    case 'maintenance':
                                                        $statusClass = 'danger';
                                                        $statusIcon = 'tools';
                                                        break;
                                                    case 'out_of_service':
                                                        $statusClass = 'dark';
                                                        $statusIcon = 'ban';
                                                        break;
                                                }
                                                ?>
                                                <span class="status-badge bg-<?php echo $statusClass; ?> bg-opacity-10 text-<?php echo $statusClass; ?>">
                                                    <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                                    <?php echo htmlspecialchars(ucfirst($status)); ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group">
                                                    <a href="<?php echo site_url('/admin/cars/edit/' . (int)($car['id'] ?? 0)); ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?php echo site_url('/admin/cars/images/' . (int)($car['id'] ?? 0)); ?>" class="btn btn-sm btn-outline-secondary" title="Manage Images">
                                                        <i class="fas fa-image"></i>
                                                    </a>
                                                    <a href="<?php echo site_url('/admin/cars/delete/' . (int)($car['id'] ?? 0)); ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this car?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <script>
                        (function(){
                            const $search = document.getElementById('carSearch');
                            const $status = document.getElementById('statusFilter');
                            const $category = document.getElementById('categoryFilter');
                            const $clear = document.getElementById('clearFilters');
                            const rows = Array.from(document.querySelectorAll('table tbody tr'));

                            function applyFilters(){
                                const q = ($search?.value || '').toLowerCase().trim();
                                const st = ($status?.value || '').toLowerCase();
                                const cat = ($category?.value || '').toLowerCase();
                                rows.forEach(r => {
                                    const txt = (r.getAttribute('data-text') || '').toLowerCase();
                                    const rs = (r.getAttribute('data-status') || '').toLowerCase();
                                    const rc = (r.getAttribute('data-category') || '').toLowerCase();
                                    let ok = true;
                                    if (q && !txt.includes(q)) ok = false;
                                    if (st && rs !== st) ok = false;
                                    if (cat && rc !== cat) ok = false;
                                    r.style.display = ok ? '' : 'none';
                                });
                            }

                            [$search, $status, $category].forEach(el => {
                                if (el) el.addEventListener('input', applyFilters);
                                if (el && el.tagName === 'SELECT') el.addEventListener('change', applyFilters);
                            });
                            if ($clear) $clear.addEventListener('click', () => {
                                if ($search) $search.value = '';
                                if ($status) $status.value = '';
                                if ($category) $category.value = '';
                                applyFilters();
                            });
                        })();
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php include __DIR__ . '/partials/footer.php'; ?>
