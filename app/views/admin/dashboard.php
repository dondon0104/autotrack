<?php $page_title = 'Dashboard - Car Rental System'; include __DIR__ . '/partials/head.php'; ?>
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
        <!-- Main content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4 main-content">
            <!-- Hero -->
            <div class="dash-hero mb-4">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                  <div class="title">Welcome back, <?php echo isset($username) ? htmlspecialchars($username) : 'Admin'; ?> 👋</div>
                  <div class="subtitle mt-1">Quick overview of your fleet, rentals, and revenue.</div>
                </div>
                <div class="d-flex gap-2">
                  <span class="badge">Cars: <strong class="ms-1"><?php echo (int)($car_stats['total'] ?? 0); ?></strong></span>
                  <span class="badge">Active Rentals: <strong class="ms-1"><?php echo (int)($rental_stats['active'] ?? 0); ?></strong></span>
                  <span class="badge">Revenue: <strong class="ms-1">₱<?php echo isset($payment_stats['total_amount']) ? number_format($payment_stats['total_amount'], 2) : '0.00'; ?></strong></span>
                </div>
              </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4 g-3">
                <div class="col-md-3 col-sm-6">
                    <div class="kpi p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="label mb-1">Total Cars</p>
                                <p class="value mb-0"><?php echo (int)($car_stats['total'] ?? 0); ?></p>
                            </div>
                            <div class="icon icon-primary"><i class="fas fa-car"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="kpi p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="label mb-1">Available Cars</p>
                                <p class="value mb-0"><?php echo (int)($car_stats['available'] ?? 0); ?></p>
                            </div>
                            <div class="icon icon-success"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="kpi p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="label mb-1">Active Rentals</p>
                                <p class="value mb-0"><?php echo (int)($rental_stats['active'] ?? 0); ?></p>
                            </div>
                            <div class="icon icon-warning"><i class="fas fa-clock"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="kpi p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="label mb-1">Total Revenue</p>
                                <p class="value mb-0">₱<?php echo isset($payment_stats['total_amount']) ? number_format($payment_stats['total_amount'], 2) : '0.00'; ?></p>
                            </div>
                            <div class="icon icon-info"><i class="fas fa-peso-sign"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row g-3 mb-4">
              <div class="col-lg-6">
                <div class="card chart-card">
                  <div class="card-header">Car Availability Breakdown</div>
                  <div class="card-body">
                    <div class="chart-wrap"><canvas id="carAvailabilityChart"></canvas></div>
                  </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="card chart-card">
                  <div class="card-header">Rental Status Overview</div>
                  <div class="card-body">
                    <div class="chart-wrap"><canvas id="rentalStatusChart"></canvas></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Activities -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Recent Rentals</h5>
                                <a href="<?php echo site_url('/admin/rentals'); ?>" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <?php if (empty($recent_rentals)): ?>
                                <div class="text-center py-4">
                                    <img src="https://cdn-icons-png.flaticon.com/512/6598/6598519.png" alt="No rentals" style="width: 80px; opacity: 0.5;">
                                    <p class="text-muted mt-3">No recent rentals found</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Customer</th>
                                                <th>Car</th>
                                                <th>Status</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_rentals as $rental): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-light rounded-circle p-2 me-2">
                                                                <i class="fas fa-user text-primary"></i>
                                                            </div>
                                                            <?php echo isset($rental['first_name'], $rental['last_name']) ? 
                                                                htmlspecialchars($rental['first_name'] . ' ' . $rental['last_name']) : 'N/A'; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php echo isset($rental['make'], $rental['model']) ? 
                                                            htmlspecialchars($rental['make'] . ' ' . $rental['model']) : 'N/A'; ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $statusClass = 'secondary';
                                                        $statusIcon = 'circle';
                                                        if (isset($rental['status'])) {
                                                            switch(strtolower($rental['status'])) {
                                                                case 'pending':
                                                                    $statusClass = 'warning';
                                                                    $statusIcon = 'clock';
                                                                    break;
                                                                case 'active':
                                                                    $statusClass = 'primary';
                                                                    $statusIcon = 'car';
                                                                    break;
                                                                case 'completed':
                                                                    $statusClass = 'success';
                                                                    $statusIcon = 'check-circle';
                                                                    break;
                                                                case 'cancelled':
                                                                    $statusClass = 'danger';
                                                                    $statusIcon = 'times-circle';
                                                                    break;
                                                            }
                                                        }
                                                        ?>
                                                        <span class="status-badge bg-<?php echo $statusClass; ?> bg-opacity-10 text-<?php echo $statusClass; ?>">
                                                            <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                                            <?php echo isset($rental['status']) ? htmlspecialchars(ucfirst($rental['status'])) : 'Unknown'; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        ₱<?php echo isset($rental['total_amount']) ? 
                                                            number_format($rental['total_amount'], 2) : '0.00'; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Recent Payments</h5>
                                <a href="<?php echo site_url('/admin/payments'); ?>" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <?php if (empty($recent_payments)): ?>
                                <div class="text-center py-4">
                                    <img src="https://cdn-icons-png.flaticon.com/512/4076/4076478.png" alt="No payments" style="width: 80px; opacity: 0.5;">
                                    <p class="text-muted mt-3">No recent payments found</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Customer</th>
                                                <th>Car</th>
                                                <th>Method</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_payments as $payment): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-light rounded-circle p-2 me-2">
                                                                <i class="fas fa-user text-primary"></i>
                                                            </div>
                                                            <?php echo isset($payment['first_name'], $payment['last_name']) ? 
                                                                htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) : 'N/A'; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php echo isset($payment['make'], $payment['model']) ? 
                                                            htmlspecialchars($payment['make'] . ' ' . $payment['model']) : 'N/A'; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info bg-opacity-10 text-info">
                                                            <i class="fas fa-credit-card me-1"></i>
                                                            <?php echo isset($payment['payment_method']) ? 
                                                                htmlspecialchars(ucfirst($payment['payment_method'])) : 'N/A'; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong>₱<?php echo isset($payment['amount']) ? 
                                                            number_format($payment['amount'], 2) : '0.00'; ?></strong>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
                </div>
        </div>

        <!-- Charts (Dashboard only) -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            (function(){
                if (!window.Chart) return;
                var carStats = {
                    available: <?php echo json_encode((int)($car_stats['available'] ?? 0)); ?>,
                    rented: <?php echo json_encode((int)($car_stats['rented'] ?? 0)); ?>,
                    maintenance: <?php echo json_encode((int)($car_stats['maintenance'] ?? 0)); ?>
                };
                var rentalStats = {
                    pending: <?php echo json_encode((int)($rental_stats['pending'] ?? 0)); ?>,
                    active: <?php echo json_encode((int)($rental_stats['active'] ?? 0)); ?>,
                    confirmed: <?php echo json_encode((int)($rental_stats['confirmed'] ?? 0)); ?>,
                    completed: <?php echo json_encode((int)($rental_stats['completed'] ?? 0)); ?>,
                    cancelled: <?php echo json_encode((int)($rental_stats['cancelled'] ?? 0)); ?>
                };

                var availCtx = document.getElementById('carAvailabilityChart');
                if (availCtx) {
                    new Chart(availCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Available','Rented','Maintenance'],
                            datasets: [{
                                data: [carStats.available, carStats.rented, carStats.maintenance],
                                backgroundColor: ['#198754','#0d6efd','#dc3545'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            plugins: { legend: { position: 'bottom' } },
                            cutout: '60%'
                        }
                    });
                }

                var statusCtx = document.getElementById('rentalStatusChart');
                if (statusCtx) {
                    new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Pending','Active','Confirmed','Completed','Cancelled'],
                            datasets: [{
                                data: [rentalStats.pending, rentalStats.active, rentalStats.confirmed, rentalStats.completed, rentalStats.cancelled],
                                backgroundColor: ['#ffc107','#0d6efd','#0dcaf0','#198754','#dc3545'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            plugins: { legend: { position: 'bottom' } },
                            cutout: '60%'
                        }
                    });
                }
            })();
        </script>

<?php include __DIR__ . '/partials/footer.php'; ?>