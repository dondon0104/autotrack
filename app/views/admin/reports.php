<?php $page_title = 'Reports - Car Rental System'; include __DIR__ . '/partials/head.php'; ?>
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <!-- Main content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4 main-content">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1"><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h2>
                    <p class="text-muted mb-0">Comprehensive business insights and reports</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="<?php echo site_url('/admin/reports'); ?>" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label"><i class="fas fa-filter me-1"></i>Report Type</label>
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="overview" <?php echo $report_type === 'overview' ? 'selected' : ''; ?>>Overview</option>
                                <option value="revenue" <?php echo $report_type === 'revenue' ? 'selected' : ''; ?>>Revenue</option>
                                <option value="rentals" <?php echo $report_type === 'rentals' ? 'selected' : ''; ?>>Rentals</option>
                                <option value="cars" <?php echo $report_type === 'cars' ? 'selected' : ''; ?>>Car Utilization</option>
                                <option value="customers" <?php echo $report_type === 'customers' ? 'selected' : ''; ?>>Customers</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="fas fa-calendar me-1"></i>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="fas fa-calendar me-1"></i>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        <?php if ($report_type === 'revenue'): ?>
                        <div class="col-md-2">
                            <label class="form-label"><i class="fas fa-clock me-1"></i>Period</label>
                            <select name="period" class="form-select">
                                <option value="daily" <?php echo $period === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                <option value="weekly" <?php echo $period === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                <option value="monthly" <?php echo $period === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                <option value="yearly" <?php echo $period === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sync-alt me-1"></i>Generate Report
                            </button>
                            <a href="<?php echo site_url('/admin/reports/export?' . http_build_query(['type' => $report_type, 'start_date' => $start_date, 'end_date' => $end_date, 'period' => $period])); ?>" class="btn btn-success">
                                <i class="fas fa-file-csv me-1"></i>Export CSV
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Content -->
            <?php if ($report_type === 'overview'): ?>
                <!-- Overview Report -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="kpi p-3">
                            <p class="label mb-1">Total Revenue</p>
                            <p class="value mb-0">₱<?php echo number_format($report_data['stats']['total_revenue'] ?? 0, 2); ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi p-3">
                            <p class="label mb-1">Total Rentals</p>
                            <p class="value mb-0"><?php echo $report_data['stats']['total_rentals'] ?? 0; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi p-3">
                            <p class="label mb-1">Completed Rentals</p>
                            <p class="value mb-0"><?php echo $report_data['stats']['completed_rentals'] ?? 0; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi p-3">
                            <p class="label mb-1">Avg Rental Value</p>
                            <p class="value mb-0">₱<?php echo number_format($report_data['stats']['avg_rental_value'] ?? 0, 2); ?></p>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Top Performing Cars -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-trophy text-warning me-2"></i>Top Performing Cars</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Car</th>
                                                <th>Rentals</th>
                                                <th class="text-end">Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($report_data['stats']['top_cars'])): ?>
                                                <?php foreach ($report_data['stats']['top_cars'] as $car): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></strong><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($car['plate_number']); ?></small>
                                                        </td>
                                                        <td><span class="badge bg-primary"><?php echo $car['rental_count']; ?></span></td>
                                                        <td class="text-end"><strong>₱<?php echo number_format($car['total_revenue'], 2); ?></strong></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Customers -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-users text-success me-2"></i>Top Customers</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Customer</th>
                                                <th>Rentals</th>
                                                <th class="text-end">Total Spent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($report_data['stats']['top_customers'])): ?>
                                                <?php foreach ($report_data['stats']['top_customers'] as $customer): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></strong><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($customer['email']); ?></small>
                                                        </td>
                                                        <td><span class="badge bg-info"><?php echo $customer['rental_count']; ?></span></td>
                                                        <td class="text-end"><strong>₱<?php echo number_format($customer['total_spent'], 2); ?></strong></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($report_type === 'revenue'): ?>
                <!-- Revenue Report -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="kpi p-3">
                            <p class="label mb-1">Total Revenue</p>
                            <p class="value mb-0 text-success">₱<?php echo number_format($report_data['stats']['total_revenue'] ?? 0, 2); ?></p>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Revenue by Payment Method</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php if (!empty($report_data['stats']['by_method'])): ?>
                                        <?php foreach ($report_data['stats']['by_method'] as $method): ?>
                                            <div class="col-md-4 mb-2">
                                                <div class="p-2 bg-light rounded">
                                                    <small class="text-muted"><?php echo htmlspecialchars(ucfirst($method['payment_method'])); ?></small>
                                                    <div><strong>₱<?php echo number_format($method['total'], 2); ?></strong> <small class="text-muted">(<?php echo $method['count']; ?> txns)</small></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12 text-center text-muted">No payment data available</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Revenue Over Time</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>

            <?php elseif ($report_type === 'rentals'): ?>
                <!-- Rentals Report -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="kpi p-3">
                            <p class="label mb-1">Total Rentals</p>
                            <p class="value mb-0"><?php echo $report_data['stats']['total'] ?? 0; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi p-3">
                            <p class="label mb-1">Avg Duration</p>
                            <p class="value mb-0"><?php echo number_format($report_data['stats']['avg_duration'] ?? 0, 1); ?> <small>days</small></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Status Breakdown</h6>
                                <div class="row g-2">
                                    <?php 
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'confirmed' => 'info',
                                        'active' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    foreach ($report_data['stats']['status_breakdown'] ?? [] as $status => $count): 
                                    ?>
                                        <div class="col-6 col-md-4">
                                            <div class="p-2 bg-<?php echo $statusColors[$status] ?? 'secondary'; ?> bg-opacity-10 rounded text-center">
                                                <small class="text-<?php echo $statusColors[$status] ?? 'secondary'; ?>"><?php echo ucfirst($status); ?></small>
                                                <div><strong><?php echo $count; ?></strong></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rentals Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Rental Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Car</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($report_data['stats']['rentals'])): ?>
                                        <?php foreach ($report_data['stats']['rentals'] as $rental): ?>
                                            <tr>
                                                <td><small>#<?php echo $rental['id']; ?></small></td>
                                                <td><?php echo htmlspecialchars($rental['first_name'] . ' ' . $rental['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($rental['make'] . ' ' . $rental['model']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($rental['rental_start'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($rental['rental_end'])); ?></td>
                                                <td><?php echo $rental['total_days']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $statusColors[$rental['status']] ?? 'secondary'; ?>">
                                                        <?php echo ucfirst($rental['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">₱<?php echo number_format($rental['total_amount'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No rentals found in this period</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($report_type === 'cars'): ?>
                <!-- Car Utilization Report -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="kpi p-3">
                            <p class="label mb-1">Total Cars</p>
                            <p class="value mb-0"><?php echo $report_data['stats']['total_cars'] ?? 0; ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kpi p-3">
                            <p class="label mb-1">Days in Period</p>
                            <p class="value mb-0"><?php echo $report_data['stats']['total_days_in_period'] ?? 0; ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kpi p-3">
                            <p class="label mb-1">Fleet Avg Utilization</p>
                            <p class="value mb-0">
                                <?php 
                                $totalUtil = 0;
                                $carCount = count($report_data['stats']['cars'] ?? []);
                                if ($carCount > 0) {
                                    foreach ($report_data['stats']['cars'] as $car) {
                                        $totalUtil += $car['utilization_rate'] ?? 0;
                                    }
                                    echo number_format($totalUtil / $carCount, 1);
                                } else {
                                    echo '0.0';
                                }
                                ?>%
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Car Utilization Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-car me-2"></i>Car Utilization Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Car</th>
                                        <th>Plate Number</th>
                                        <th>Status</th>
                                        <th>Rentals</th>
                                        <th>Days Rented</th>
                                        <th>Utilization</th>
                                        <th class="text-end">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($report_data['stats']['cars'])): ?>
                                        <?php foreach ($report_data['stats']['cars'] as $car): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($car['year']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($car['plate_number']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $car['status'] === 'available' ? 'success' : ($car['status'] === 'rented' ? 'primary' : 'warning'); ?>">
                                                        <?php echo ucfirst($car['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $car['rental_count'] ?? 0; ?></td>
                                                <td><?php echo $car['total_days_rented'] ?? 0; ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-<?php echo $car['utilization_rate'] > 70 ? 'success' : ($car['utilization_rate'] > 40 ? 'warning' : 'danger'); ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo min($car['utilization_rate'], 100); ?>%">
                                                            <?php echo number_format($car['utilization_rate'], 1); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">₱<?php echo number_format($car['total_revenue'] ?? 0, 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No car data available</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($report_type === 'customers'): ?>
                <!-- Customer Report -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="kpi p-3">
                            <p class="label mb-1">Active Customers</p>
                            <p class="value mb-0"><?php echo $report_data['stats']['total_customers'] ?? 0; ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kpi p-3">
                            <p class="label mb-1">New Customers</p>
                            <p class="value mb-0 text-success"><?php echo $report_data['stats']['new_customers'] ?? 0; ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kpi p-3">
                            <p class="label mb-1">Avg Customer Value</p>
                            <p class="value mb-0">
                                ₱<?php 
                                $totalSpent = 0;
                                $custCount = count($report_data['stats']['customers'] ?? []);
                                if ($custCount > 0) {
                                    foreach ($report_data['stats']['customers'] as $cust) {
                                        $totalSpent += $cust['total_spent'] ?? 0;
                                    }
                                    echo number_format($totalSpent / $custCount, 2);
                                } else {
                                    echo '0.00';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Customer Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Customer Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Total Rentals</th>
                                        <th>Completed</th>
                                        <th>Cancelled</th>
                                        <th>Last Rental</th>
                                        <th class="text-end">Total Spent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($report_data['stats']['customers'])): ?>
                                        <?php foreach ($report_data['stats']['customers'] as $customer): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></strong>
                                                </td>
                                                <td><small><?php echo htmlspecialchars($customer['email']); ?></small></td>
                                                <td><small><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></small></td>
                                                <td><span class="badge bg-primary"><?php echo $customer['total_rentals']; ?></span></td>
                                                <td><span class="badge bg-success"><?php echo $customer['completed_rentals']; ?></span></td>
                                                <td><span class="badge bg-danger"><?php echo $customer['cancelled_rentals']; ?></span></td>
                                                <td><?php echo date('M d, Y', strtotime($customer['last_rental_date'])); ?></td>
                                                <td class="text-end"><strong>₱<?php echo number_format($customer['total_spent'], 2); ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No customer data available</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chart.js for revenue chart -->
    <?php if ($report_type === 'revenue' && !empty($report_data['stats']['over_time'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        (function(){
            var ctx = document.getElementById('revenueChart');
            if (ctx) {
                var chartData = <?php echo json_encode($report_data['stats']['over_time']); ?>;
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.map(d => d.period),
                        datasets: [{
                            label: 'Revenue (₱)',
                            data: chartData.map(d => parseFloat(d.revenue)),
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Revenue: ₱' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString('en-US');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        })();
    </script>
    <?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
