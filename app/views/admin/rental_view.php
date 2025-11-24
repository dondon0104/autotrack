<?php defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed'); ?>
<?php $page_title = 'Rental Details - Car Rental System'; include __DIR__ . '/partials/head.php'; ?>
                <!-- Sidebar -->
                <?php include __DIR__ . '/partials/sidebar.php'; ?>

                <!-- Main content -->
                <div class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4 main-content">
                        <?php 
                            $status = strtolower($rental['status']);
                            $statusMap = [
                                'pending'   => ['class' => 'badge-soft-warning', 'icon' => 'clock', 'label' => 'Pending'],
                                'confirmed' => ['class' => 'badge-soft-info',    'icon' => 'thumbs-up', 'label' => 'Confirmed'],
                                'active'    => ['class' => 'badge-soft-primary', 'icon' => 'car', 'label' => 'Active'],
                                'completed' => ['class' => 'badge-soft-success', 'icon' => 'check-circle', 'label' => 'Completed'],
                                'cancelled' => ['class' => 'badge-soft-danger',  'icon' => 'times-circle', 'label' => 'Cancelled'],
                            ];
                            $s = $statusMap[$status] ?? ['class' => 'badge-soft-secondary', 'icon' => 'circle', 'label' => ucfirst($status)];
                            $canConfirm = in_array($status, ['pending','confirmed']);
                            $canCancel  = in_array($status, ['pending','confirmed','active']);
                            $start = new DateTime($rental['rental_start']);
                            $end   = new DateTime($rental['rental_end']);
                        ?>

                        <!-- Hero header -->
                        <div class="rental-hero mb-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <div class="title">Rental #<?php echo (int)$rental['id']; ?> • <span class="fw-normal"><?php echo htmlspecialchars($rental['make'] . ' ' . $rental['model'] . ' (' . $rental['year'] . ')'); ?></span></div>
                                    <div class="meta mt-1">
                                        <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($rental['plate_number']); ?>
                                        <span class="ms-3"><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($rental['first_name'] . ' ' . $rental['last_name']); ?></span>
                                        <span class="ms-3"><i class="fas fa-calendar-alt me-1"></i><?php echo $start->format('M j, Y') . ' - ' . $end->format('M j, Y'); ?></span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 actions">
                                    <span class="badge <?php echo $s['class']; ?> d-inline-flex align-items-center px-3 py-2">
                                        <i class="fas fa-<?php echo $s['icon']; ?> me-2"></i><?php echo $s['label']; ?>
                                    </span>
                                    <?php if ($canConfirm): ?>
                                        <a href="<?php echo site_url('/admin/rentals/confirm/' . (int)$rental['id']); ?>" class="btn btn-sm btn-outline-light">
                                            <i class="fas fa-check me-1"></i>Confirm
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($canCancel): ?>
                                        <a href="<?php echo site_url('/admin/rentals/cancel/' . (int)$rental['id']); ?>" class="btn btn-sm btn-outline-light" onclick="return confirm('Cancel this rental?');">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($this->session->flashdata('error')): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($this->session->flashdata('error')); ?></div>
                        <?php endif; ?>
                        <?php if ($this->session->flashdata('success')): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($this->session->flashdata('success')); ?></div>
                        <?php endif; ?>

                        <div class="row g-4">
                                <div class="col-lg-7">
                                        <div class="card border-0 shadow-sm section-card mb-4">
                                                <div class="card-header bg-white">
                                                    Rental Information
                                                </div>
                                                <div class="card-body">
                                                    <div class="kv">
                                                        <div class="k"><i class="fas fa-badge-check fa-fw"></i>Status</div>
                                                        <div class="v"><span class="badge <?php echo $s['class']; ?>"><i class="fas fa-<?php echo $s['icon']; ?> me-1"></i><?php echo $s['label']; ?></span></div>
                                                    </div>
                                                    <div class="kv">
                                                        <div class="k"><i class="fas fa-car-side fa-fw"></i>Car</div>
                                                        <div class="v"><?php echo htmlspecialchars($rental['make'] . ' ' . $rental['model'] . ' (' . $rental['year'] . ')'); ?><small><?php echo htmlspecialchars($rental['plate_number']); ?></small></div>
                                                    </div>
                                                    <div class="kv">
                                                        <div class="k"><i class="fas fa-calendar-alt fa-fw"></i>Dates</div>
                                                        <div class="v"><?php echo $start->format('M j, Y') . ' - ' . $end->format('M j, Y'); ?><small><?php echo (int)$rental['total_days']; ?> day(s)</small></div>
                                                    </div>
                                                    <div class="kv">
                                                        <div class="k"><i class="fas fa-location-dot fa-fw"></i>Pickup</div>
                                                        <div class="v"><?php echo htmlspecialchars($rental['pickup_location'] ?? '-'); ?></div>
                                                    </div>
                                                    <div class="kv">
                                                        <div class="k"><i class="fas fa-location-dot fa-fw"></i>Return</div>
                                                        <div class="v"><?php echo htmlspecialchars($rental['return_location'] ?? '-'); ?></div>
                                                    </div>
                                                    <div class="kv">
                                                        <div class="k"><i class="fas fa-file-signature fa-fw"></i>Contract</div>
                                                        <div class="v">
                                                            <?php if (!empty($rental['is_contract_signed'])): ?>
                                                                <span class="badge badge-soft-success"><i class="fas fa-file-signature me-1"></i>Signed</span>
                                                                <a href="<?php echo site_url('/user/contract/pdf/' . (int)$rental['id']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary ms-2">View PDF</a>
                                                            <?php else: ?>
                                                                <span class="badge badge-soft-warning text-warning"><i class="fas fa-circle-exclamation me-1"></i>Not Signed</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>

                                        <div class="card border-0 shadow-sm">
                                                <div class="card-header bg-white">Customer</div>
                                                <div class="card-body">
                                                    <div class="kv">
                                                        <div class="k"><i class="fas fa-user fa-fw"></i>Name</div>
                                                        <div class="v"><?php echo htmlspecialchars($rental['first_name'] . ' ' . $rental['last_name']); ?></div>
                                                    </div>
                                                    <div class="kv">
                                                        <div class="k"><i class="fas fa-envelope fa-fw"></i>Email</div>
                                                        <div class="v"><?php echo htmlspecialchars($rental['email']); ?></div>
                                                    </div>
                                                    <div class="kv">
                                                        <div class="k"><i class="fas fa-phone fa-fw"></i>Phone</div>
                                                        <div class="v"><?php echo htmlspecialchars($rental['phone'] ?? '-'); ?></div>
                                                    </div>
                                                </div>
                                        </div>
                                </div>

                                <div class="col-lg-5">
                                        <div class="card border-0 shadow-sm section-card mb-4">
                                                <div class="card-header bg-white">Payment Summary</div>
                                                <div class="card-body">
                                                    <div class="kv">
                                                        <div class="k">Subtotal</div>
                                                        <div class="v">₱<?php echo number_format((float)$rental['subtotal'], 2); ?></div>
                                                    </div>
                                                    <div class="kv">
                                                        <div class="k">Tax (<?php echo number_format((float)$rental['tax_rate'], 2); ?>%)</div>
                                                        <div class="v">₱<?php echo number_format((float)$rental['tax_amount'], 2); ?></div>
                                                    </div>
                                                    <div class="kv">
                                                        <div class="k">Total</div>
                                                        <div class="v">₱<?php echo number_format((float)$rental['total_amount'], 2); ?></div>
                                                    </div>
                                                    <div class="kv">
                                                        <div class="k">Paid</div>
                                                        <div class="v text-success">₱<?php echo number_format((float)$paid_total, 2); ?></div>
                                                    </div>
                                                    <div class="kv">
                                                        <div class="k">Required Deposit</div>
                                                        <div class="v text-primary">₱<?php echo number_format((float)$required_deposit, 2); ?></div>
                                                    </div>
                                                </div>
                                        </div>

                                        <div class="card border-0 shadow-sm">
                                                <div class="card-header bg-white">Payments</div>
                                                <div class="card-body">
                                                        <?php if (!empty($payments)): ?>
                                                                <div class="table-responsive">
                                                                        <table class="table table-sm">
                                                                                <thead>
                                                                                        <tr>
                                                                                                <th>Date</th>
                                                                                                <th>Method</th>
                                                                                                <th>Status</th>
                                                                                                <th class="text-end">Amount</th>
                                                                                        </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                        <?php foreach ($payments as $p): ?>
                                                                                        <tr>
                                                                                                <td><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($p['created_at']))); ?></td>
                                                                                                <td><?php echo htmlspecialchars(ucfirst($p['payment_method'])); ?></td>
                                                                                                <td>
                                                                                                        <?php $cls = $p['payment_status'] === 'completed' ? 'success' : ($p['payment_status'] === 'failed' ? 'danger' : 'secondary'); ?>
                                                                                                        <span class="badge bg-<?php echo $cls; ?>"><?php echo htmlspecialchars(ucfirst($p['payment_status'])); ?></span>
                                                                                                </td>
                                                                                                <td class="text-end">₱<?php echo number_format((float)$p['amount'], 2); ?></td>
                                                                                        </tr>
                                                                                        <?php endforeach; ?>
                                                                                </tbody>
                                                                        </table>
                                                                </div>
                                                        <?php else: ?>
                                                                <p class="text-muted mb-0">No payments yet.</p>
                                                        <?php endif; ?>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>
                <?php include __DIR__ . '/partials/footer.php'; ?>
