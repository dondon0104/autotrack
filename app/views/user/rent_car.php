<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rent Car</title>
  <link rel="icon" href="<?= site_url('/favicon.svg') ?>" type="image/svg+xml" />
  <link rel="alternate icon" href="<?= site_url('/favicon.ico') ?>" />
  <link rel="apple-touch-icon" href="<?= site_url('/apple-touch-icon.png') ?>" />
  <meta name="theme-color" content="#0d6efd" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
  <!-- Leaflet Maps -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <style>
    .car-photo { width:100%; height: 200px; object-fit: cover; border-radius:.5rem; }
    #map { height: 300px; border-radius: .5rem; border: 1px solid #e5e7eb; }
    .map-legend small { color:#6c757d; }
  </style>
  </head>
<body class="bg-light">
<?php require APP_DIR . 'views/partials/navbar.php'; ?>

<div class="container my-4">
  <div class="row g-4">
    <div class="col-md-5">
      <?php
        $make = htmlspecialchars($car['make'] ?? '');
        $model = htmlspecialchars($car['model'] ?? '');
        $year = htmlspecialchars($car['year'] ?? '');
        $dailyRate = (float)($car['daily_rate'] ?? 0);
        // Resolve image
        $img = '';
        $rawCandidate = $car['image_path'] ?? '';
        if (!empty($rawCandidate)) {
          $raw = trim($rawCandidate);
          if (preg_match('/^https?:\/\//i', $raw) || strpos($raw, 'data:') === 0) {
            $img = htmlspecialchars($raw);
          } else {
            $img = htmlspecialchars(site_url('/' . ltrim($raw, '/')));
          }
        }
        if ($img === '') {
          $img = 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?q=80&w=1200&auto=format&fit=crop';
        }
      ?>
      <div class="card shadow-sm">
        <img src="<?= $img ?>" class="car-photo" alt="Car photo" loading="lazy" />
        <div class="card-body">
          <h5 class="card-title mb-1"><?= $make ?> <?= $model ?> (<?= $year ?>)</h5>
          <div class="text-muted">Daily rate: <strong>₱<?= number_format($dailyRate, 2) ?></strong></div>
        </div>
      </div>
    </div>

    <div class="col-md-7">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0"><i class="fa-solid fa-clipboard-list me-2"></i>Rental Details</h5>
        </div>
        <div class="card-body">
          <form action="<?= site_url('/user/rent') ?>" method="post">
            <input type="hidden" name="car_id" value="<?= (int)($car['id'] ?? 0) ?>" />
            <!-- Hidden fields to carry coordinates if set via map -->
            <input type="hidden" name="pickup_lat" value="" />
            <input type="hidden" name="pickup_lng" value="" />
            <input type="hidden" name="return_lat" value="" />
            <input type="hidden" name="return_lng" value="" />

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Rental start</label>
                <input type="datetime-local" id="rental_start" name="rental_start" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Rental end</label>
                <input type="datetime-local" id="rental_end" name="rental_end" class="form-control" required />
              </div>

              <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="map_target" id="mapSetPickup" value="pickup" checked>
                    <label class="form-check-label" for="mapSetPickup"><i class="fa-solid fa-location-dot text-success me-1"></i>Set Pickup</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="map_target" id="mapSetReturn" value="return">
                    <label class="form-check-label" for="mapSetReturn"><i class="fa-solid fa-flag-checkered text-primary me-1"></i>Set Return</label>
                  </div>
                  <div class="map-legend small text-muted">
                    Click on the map to set the selected point. Drag markers to adjust.
                  </div>
                </div>
                <div id="map" class="mt-2"></div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Pickup location</label>
                <input type="text" name="pickup_location" class="form-control" placeholder="e.g. Main Office or map-selected address" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Return location</label>
                <input type="text" name="return_location" class="form-control" placeholder="e.g. Same as pickup or map-selected address" required />
              </div>
              <div class="col-12">
                <label class="form-label">Notes (optional)</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Any special requests..."></textarea>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
              <a href="<?= site_url('/user/dashboard') ?>" class="btn btn-outline-secondary">Back</a>
              <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i>Confirm Rental</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-credit-card me-2 text-primary"></i>Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="paymentSummary" class="mb-3"></div>
        <form id="paymentForm">
          <input type="hidden" name="rental_id" id="pm_rental_id" value="">
          <div class="mb-3">
            <label class="form-label">Amount to pay</label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input type="number" step="0.01" min="0.01" class="form-control" name="amount" id="pm_amount" required>
            </div>
            <div class="form-text" id="pm_helper"></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Payment Method</label>
            <select class="form-select" name="payment_method" id="pm_method" required>
              <option value="" disabled selected>Select a method</option>
              <option value="cash">Cash</option>
              <option value="credit_card">Credit Card</option>
              <option value="debit_card">Debit Card</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="gcash">GCash</option>
            </select>
          </div>
          <div id="pm_gcash_block" class="border rounded p-3 mb-3" style="display:none;">
            <div class="mb-2">
              <strong>GCash details:</strong><br>
              <span>Account name: <?= htmlspecialchars(config_item('gcash_name') ?: 'N/A') ?></span><br>
              <span>GCash number: <?= htmlspecialchars(config_item('gcash_number') ?: 'N/A') ?></span>
            </div>
            <div class="mb-2">
              <label class="form-label">GCash Reference No.</label>
              <input type="text" class="form-control" name="gcash_reference" id="pm_gcash_ref" placeholder="e.g. 1234 5678 9012" />
              <div class="form-text">Enter the reference number from your GCash confirmation.</div>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-lock me-1"></i> Pay Now</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
  (function(){
    var mapEl = document.getElementById('map');
    if (!mapEl || !window.L) return;
    // Default to Manila if geolocation not available
    var defaultCenter = [14.5995, 120.9842], defaultZoom = 12;
    var map = L.map('map').setView(defaultCenter, defaultZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var pickupMarker = null, returnMarker = null;
    var pickupIcon = L.icon({ iconUrl: 'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-green.png', shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png', iconSize: [25,41], iconAnchor:[12,41], popupAnchor:[1,-34], shadowSize:[41,41] });
    var returnIcon = L.icon({ iconUrl: 'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-blue.png', shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png', iconSize: [25,41], iconAnchor:[12,41], popupAnchor:[1,-34], shadowSize:[41,41] });

    var pickupInput = document.querySelector('input[name="pickup_location"]');
    var returnInput = document.querySelector('input[name="return_location"]');
    var pLat = document.querySelector('input[name="pickup_lat"]');
    var pLng = document.querySelector('input[name="pickup_lng"]');
    var rLat = document.querySelector('input[name="return_lat"]');
    var rLng = document.querySelector('input[name="return_lng"]');

    function activeTarget(){
      var v = document.querySelector('input[name="map_target"]:checked');
      return v ? v.value : 'pickup';
    }

    function setLocation(target, latlng){
      var lat = latlng.lat.toFixed(6), lng = latlng.lng.toFixed(6);
      if (target === 'pickup') {
        if (!pickupMarker) {
          pickupMarker = L.marker(latlng, { draggable:true, icon: pickupIcon }).addTo(map);
          pickupMarker.on('dragend', function(){ setLocation('pickup', pickupMarker.getLatLng()); });
        } else { pickupMarker.setLatLng(latlng); }
        if (pLat) pLat.value = lat; if (pLng) pLng.value = lng;
        reverseGeocode(lat, lng, function(addr){ if (pickupInput) pickupInput.value = addr || (lat + ', ' + lng); });
      } else {
        if (!returnMarker) {
          returnMarker = L.marker(latlng, { draggable:true, icon: returnIcon }).addTo(map);
          returnMarker.on('dragend', function(){ setLocation('return', returnMarker.getLatLng()); });
        } else { returnMarker.setLatLng(latlng); }
        if (rLat) rLat.value = lat; if (rLng) rLng.value = lng;
        reverseGeocode(lat, lng, function(addr){ if (returnInput) returnInput.value = addr || (lat + ', ' + lng); });
      }
    }

    function reverseGeocode(lat, lng, cb){
      try {
        fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lng, { headers: { 'Accept':'application/json' }})
          .then(function(r){ return r.json(); })
          .then(function(d){ var addr = (d && (d.display_name || (d.address && d.address.road))) || ''; cb(addr); })
          .catch(function(){ cb('' + lat + ', ' + lng); });
      } catch(e) { cb('' + lat + ', ' + lng); }
    }

    map.on('click', function(e){ setLocation(activeTarget(), e.latlng); });

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(pos){
        var c = [pos.coords.latitude, pos.coords.longitude];
        map.setView(c, 14);
      });
    }
  })();
</script>
<script>
  // Enforce that rental cannot start in the past; set min to now and keep end >= start
  (function(){
    var start = document.getElementById('rental_start');
    var end = document.getElementById('rental_end');
    if (!start || !end) return;
    function toLocalInputValue(d){
      // Format as YYYY-MM-DDTHH:MM
      var pad = function(n){ return (n<10?'0':'') + n; };
      return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
    var now = new Date();
    var minVal = toLocalInputValue(now);
    start.min = minVal;
    end.min = minVal;
    start.addEventListener('change', function(){
      if (start.value) {
        end.min = start.value;
        if (end.value && end.value < start.value) {
          end.value = start.value;
        }
      }
    });
  })();
</script>
<script>
  // AJAX submit rental -> open payment modal; and AJAX submit payment in modal
  (function(){
    var rentForm = document.querySelector('form[action="<?= site_url('/user/rent') ?>"]');
    if (!rentForm) return;
    var paymentModalEl = document.getElementById('paymentModal');
    var paymentModal = paymentModalEl ? new bootstrap.Modal(paymentModalEl) : null;
    var pmAmount = document.getElementById('pm_amount');
    var pmHelper = document.getElementById('pm_helper');
    var pmMethod = document.getElementById('pm_method');
    var pmRental = document.getElementById('pm_rental_id');
    var paymentSummaryEl = document.getElementById('paymentSummary');
    var paymentForm = document.getElementById('paymentForm');

    function fmt(n){ return new Intl.NumberFormat('en-PH',{minimumFractionDigits:2, maximumFractionDigits:2}).format(n||0); }

    function renderSummary(rental, ps){
      var html = ''+
        '<div class="row g-3">'+
          '<div class="col-12 col-md-6">'+
            '<ul class="list-group list-group-flush">'+
              '<li class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Car</span><span><strong>' + (rental.make||'') + ' ' + (rental.model||'') + '</strong></span></li>'+
              '<li class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Plate</span><span>' + (rental.plate_number||'') + '</span></li>'+
              '<li class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Period</span><span>' + (rental.rental_start||'') + ' to ' + (rental.rental_end||'') + '</span></li>'+
            '</ul>'+
          '</div>'+
          '<div class="col-12 col-md-6">'+
            '<ul class="list-group list-group-flush">'+
              '<li class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Total</span><span class="fw-semibold">₱'+ fmt(ps.total) +'</span></li>'+
              '<li class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Paid</span><span>₱'+ fmt(ps.paid) +'</span></li>'+
              '<li class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Remaining</span><span class="text-warning">₱'+ fmt(ps.remaining) +'</span></li>'+
            '</ul>'+
          '</div>'+
        '</div>';
      paymentSummaryEl.innerHTML = html;
    }

    function applyDepositRules(rental, ps){
      var isPending = (rental.status||'') === 'pending';
      var depositNotMet = ps.allow_partial && ps.needed_for_deposit > 0;
      var minPay = (isPending && depositNotMet) ? ps.required_deposit : 0.01;
      var suggest = (isPending && depositNotMet) ? Math.min(ps.remaining, ps.required_deposit) : ps.remaining;
      pmAmount.min = (Math.round(minPay*100)/100).toFixed(2);
      pmAmount.max = ps.remaining;
      pmAmount.value = (Math.round(suggest*100)/100).toFixed(2);
      if (isPending && depositNotMet) {
        pmHelper.innerHTML = 'Minimum payment is <strong>₱' + fmt(ps.required_deposit) + '</strong> (' + Math.round(ps.deposit_rate*100) + '% deposit). You can pay more, but not less.';
      } else if (!ps.allow_partial) {
        pmHelper.textContent = 'Full payment required.';
      } else {
        pmHelper.textContent = 'You can pay any amount up to the remaining balance.';
      }
    }

    rentForm.addEventListener('submit', function(ev){
      ev.preventDefault();
      var fd = new FormData(rentForm);
      fd.append('ajax','1');
      fetch('<?= site_url('/user/rent') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept':'application/json' },
        body: fd
      }).then(function(r){ return r.json(); })
        .then(function(d){
          if (!d || !d.ok) throw new Error(d && d.message || 'Failed');
          // If contract is required before payment, redirect to contract page
          if (d.contract_needed && d.contract_url) {
            alert('Please sign the digital contract first. We\'ll take you to the contract page.');
            window.location.href = d.contract_url;
            return;
          }
          pmRental.value = d.rental_id;
          renderSummary(d.rental, d.payment_summary);
          applyDepositRules(d.rental, d.payment_summary);
          if (paymentModal) paymentModal.show();
        })
        .catch(function(err){ alert(err.message || 'Failed to submit rental.'); });
    });

    if (paymentForm) {
      paymentForm.addEventListener('submit', function(ev){
        ev.preventDefault();
        var fd = new FormData(paymentForm);
        fd.append('ajax','1');
        fetch('<?= site_url('/user/payment') ?>', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept':'application/json' },
          body: fd
        }).then(function(r){ return r.json(); })
          .then(function(d){
            if (!d) throw new Error('Payment failed');
            if (d.redirect_url) {
              // Open provider checkout (GCash via API)
              window.open(d.redirect_url, '_blank');
              alert('We opened the GCash checkout in a new tab. After completing the payment, return here to see updated status.');
              return;
            }
            if (!d.ok) throw new Error(d && d.message || 'Payment failed');
            if (d.rental && d.payment_summary) {
              renderSummary(d.rental, d.payment_summary);
              applyDepositRules(d.rental, d.payment_summary);
              if (d.payment_summary.remaining <= 0.01) {
                if (paymentModal) paymentModal.hide();
                window.location.href = d.next_url || '<?= site_url('/user/my-rentals') ?>';
              } else {
                alert(d.message);
              }
            } else if (d.message) {
              alert(d.message);
            }
          })
          .catch(function(err){ alert(err.message || 'Payment failed.'); });
      });
    }

    function updateGCashUI(){
      if (!pmMethod) return;
      var isGCash = pmMethod.value === 'gcash';
      var block = document.getElementById('pm_gcash_block');
      var ref = document.getElementById('pm_gcash_ref');
      // Show manual reference fields only when gcash_mode is 'manual'
      var mode = '<?= htmlspecialchars((string)(config_item('gcash_mode') ?: 'manual')) ?>';
      var showRef = isGCash && mode === 'manual';
      if (block) block.style.display = showRef ? '' : 'none';
      if (ref) {
        if (showRef) ref.setAttribute('required','required');
        else ref.removeAttribute('required');
      }
    }
    if (pmMethod) {
      pmMethod.addEventListener('change', updateGCashUI);
      updateGCashUI();
    }
  })();
</script>
</body>
</html>
