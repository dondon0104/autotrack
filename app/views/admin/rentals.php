<?php defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed'); ?>
<?php $page_title = 'Manage Rentals - Car Rental System'; include __DIR__ . '/partials/head.php'; ?>
        <!-- Sidebar -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <!-- Main content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4 main-content">
            <!-- Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center border-bottom mb-4 pb-3">
                <div>
                    <h1 class="h2 mb-0">Manage Rentals</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 mt-2">
                            <li class="breadcrumb-item"><a href="<?php echo site_url('/admin/dashboard'); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Rentals</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex align-items-center">
                    <div class="dropdown me-3">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">All Rentals</a></li>
                            <li><a class="dropdown-item" href="#">Pending</a></li>
                            <li><a class="dropdown-item" href="#">Active</a></li>
                            <li><a class="dropdown-item" href="#">Completed</a></li>
                            <li><a class="dropdown-item" href="#">Cancelled</a></li>
                        </ul>
                    </div>
                    <div class="user-info text-end">
                        <p class="mb-0"><strong>Welcome, <?php echo isset($username) ? htmlspecialchars($username) : 'Admin'; ?></strong></p>
                        <small class="text-muted">Administrator</small>
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
                                    <h6 class="text-muted mb-1">Total Rentals</h6>
                                    <h3 class="mb-0"><?php echo isset($rental_stats['total']) ? $rental_stats['total'] : '0'; ?></h3>
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
                                    <h6 class="text-muted mb-1">Active Rentals</h6>
                                    <h3 class="mb-0"><?php echo isset($rental_stats['active']) ? $rental_stats['active'] : '0'; ?></h3>
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
                                    <h6 class="text-muted mb-1">Pending</h6>
                                    <h3 class="mb-0"><?php echo isset($rental_stats['pending']) ? $rental_stats['pending'] : '0'; ?></h3>
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
                                    <h6 class="text-muted mb-1">Completed</h6>
                                    <h3 class="mb-0"><?php echo isset($rental_stats['completed']) ? $rental_stats['completed'] : '0'; ?></h3>
                                </div>
                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-flag-checkered text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rentals Table -->
            <?php if (!empty($rentals) && is_array($rentals)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Car Details</th>
                                        <th>Customer</th>
                                        <th>Rental Period</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rentals as $r): ?>
                                        <tr>
                                            <td class="ps-4"><?php echo isset($r['id']) ? (int)$r['id'] : ''; ?></td>
                                            <td>
                                                <strong><?php echo isset($r['make'], $r['model']) ? htmlspecialchars($r['make'] . ' ' . $r['model']) : ''; ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo isset($r['plate_number']) ? htmlspecialchars($r['plate_number']) : ''; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if (isset($r['first_name'], $r['last_name'])): ?>
                                                    <?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($r['rental_start'], $r['rental_end'])): ?>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-calendar-alt text-muted me-2"></i>
                                                        <div>
                                                            <?php 
                                                            $start = new DateTime($r['rental_start']);
                                                            $end = new DateTime($r['rental_end']);
                                                            echo $start->format('M j, Y') . ' - ' . $end->format('M j, Y');
                                                            ?>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?php 
                                                                $days = $start->diff($end)->days + 1;
                                                                echo $days . ' day' . ($days > 1 ? 's' : '');
                                                                ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($r['total_amount'])): ?>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-tag text-muted me-2"></i>
                                                        <div>
                                                            <strong>₱<?php echo number_format($r['total_amount'], 2); ?></strong>
                                                            <?php if (isset($r['daily_rate'])): ?>
                                                                <br>
                                                                <small class="text-muted">
                                                                    ₱<?php echo number_format($r['daily_rate'], 2); ?>/day
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $statusClass = 'secondary';
                                                $statusIcon = 'circle';
                                                if (isset($r['status'])) {
                                                    switch(strtolower($r['status'])) {
                                                        case 'pending':
                                                            $statusClass = 'warning';
                                                            $statusIcon = 'clock';
                                                            break;
                                                        case 'confirmed':
                                                            $statusClass = 'info';
                                                            $statusIcon = 'thumbs-up';
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
                                                    <?php echo isset($r['status']) ? htmlspecialchars(ucfirst($r['status'])) : ''; ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group">
                                                    <a href="<?php echo site_url('/admin/rentals/view/' . (isset($r['id']) ? (int)$r['id'] : '')); ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if (isset($r['status']) && $r['status'] == 'pending'): ?>
                                                    <a href="<?php echo site_url('/admin/rentals/confirm/' . (isset($r['id']) ? (int)$r['id'] : '')); ?>" 
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <?php if (isset($r['status']) && in_array($r['status'], ['pending', 'confirmed'])): ?>
                                                    <a href="<?php echo site_url('/admin/rentals/cancel/' . (isset($r['id']) ? (int)$r['id'] : '')); ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Are you sure you want to cancel this rental?');">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/6598/6598519.png" alt="No rentals" class="mb-3" style="width: 100px; opacity: 0.5;">
                        <h5>No Rentals Found</h5>
                        <p class="text-muted">There are no rental records to display at the moment.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php include __DIR__ . '/partials/footer.php'; ?>
