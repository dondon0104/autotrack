<?php
// Make sure certain variables exist to avoid notices when we reuse the customer/car_details view
$is_logged_in = $is_logged_in ?? true;
$selected_variant_color = $selected_variant_color ?? null;
$variant_colors = $variant_colors ?? [];
$variant_image_path = $variant_image_path ?? null;

// Reuse the rich car details template from the customer side
require APP_DIR . 'views/customer/car_details.php';
