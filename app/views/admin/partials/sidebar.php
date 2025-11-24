<?php
// Admin sidebar partial
$uri = $_SERVER['REQUEST_URI'] ?? '';
$active = function(string $needle) use ($uri) {
  return (strpos($uri, $needle) !== false) ? 'active' : '';
};
?>
<div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
  <a href="<?php echo site_url('/admin/dashboard'); ?>" class="navbar-brand">
    <i class="fa-solid fa-car-side"></i> CarRental
  </a>
  <ul class="nav flex-column">
    <li class="nav-item">
      <a class="nav-link <?php echo $active('/admin/dashboard') && !$active('/admin/cars') && !$active('/admin/rentals') && !$active('/admin/payments') ? 'active' : ''; ?>" href="<?php echo site_url('/admin/dashboard'); ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo $active('/admin/cars'); ?>" href="<?php echo site_url('/admin/cars'); ?>">
        <i class="fa-solid fa-car-side"></i> Manage Cars
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo $active('/admin/cars/add'); ?>" href="<?php echo site_url('/admin/cars/add'); ?>">
        <i class="fa-solid fa-plus"></i> Add New Car
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo $active('/admin/rentals'); ?>" href="<?php echo site_url('/admin/rentals'); ?>">
        <i class="fas fa-list"></i> View Rentals
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo $active('/admin/payments'); ?>" href="<?php echo site_url('/admin/payments'); ?>">
        <i class="fas fa-dollar-sign"></i> View Payments
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo $active('/admin/reports'); ?>" href="<?php echo site_url('/admin/reports'); ?>">
        <i class="fas fa-chart-bar"></i> Reports
      </a>
    </li>
    <li class="nav-item mt-3">
      <a class="nav-link text-danger" href="<?php echo site_url('/admin/logout'); ?>">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </li>
  </ul>
</div>
