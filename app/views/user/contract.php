<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rental Contract</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
  <style>
    body { background:#f6f7fb; }
    .wrap { max-width: 980px; margin: 2rem auto; padding: 0 1rem; }
    .sig-pad { border: 1px dashed #bbb; border-radius: .5rem; background: #fff; }
    .contract-box { background:#fff; border-radius: 1rem; box-shadow: 0 12px 28px rgba(0,0,0,.08); }
    .meta small { color:#6c757d; }
  </style>
</head>
<body>
<?php require APP_DIR . 'views/partials/navbar.php'; ?>
<div class="wrap">
  <div class="mb-3 d-flex align-items-center gap-2">
    <i class="fa-solid fa-file-signature text-primary"></i>
    <h3 class="m-0">Rental Contract</h3>
    <span class="ms-auto">
      <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('/user/my-rentals') ?>"><i class="fa-solid fa-clock-rotate-left me-1"></i> My Rentals</a>
    </span>
  </div>

  <div class="contract-box p-4">
    <div class="row g-4">
      <div class="col-12 col-lg-7">
        <?php $r = $rental ?? []; ?>
        <h5 class="mb-3">Agreement</h5>
        <div class="meta mb-3">
          <div><small>Renter</small><div><strong><?= htmlspecialchars(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?></strong> (<?= htmlspecialchars(($r['email'] ?? '')) ?>)</div></div>
          <div class="mt-2"><small>Vehicle</small><div><strong><?= htmlspecialchars(($r['make'] ?? '')) ?> <?= htmlspecialchars(($r['model'] ?? '')) ?></strong> — Plate <?= htmlspecialchars(($r['plate_number'] ?? '')) ?></div></div>
          <div class="mt-2"><small>Period</small><div><?= htmlspecialchars(($r['rental_start'] ?? '')) ?> to <?= htmlspecialchars(($r['rental_end'] ?? '')) ?></div></div>
          <div class="mt-2"><small>Total Amount</small><div>₱<?= number_format((float)($r['total_amount'] ?? 0), 2) ?></div></div>
        </div>
        <p>By signing below, the Renter agrees to the terms of this Rental Agreement, including responsibility for the vehicle during the rental period, compliance with traffic laws, and payment of fees/damages per policy. The Renter acknowledges receipt of the vehicle in good condition and agrees to return it at the scheduled date/time and location.</p>
        <ul>
          <li>No smoking or illicit use of the vehicle.</li>
          <li>Renter is liable for fines, tolls, and damage during the rental.</li>
          <li>Fuel policy: return with the same fuel level as received.</li>
          <li>Late return fees may apply per hour/day overage.</li>
          <li>Company’s standard insurance terms apply where applicable.</li>
        </ul>
      </div>
      <div class="col-12 col-lg-5">
        <?php $signed = !empty($r['is_contract_signed']); ?>
        <?php if ($signed): ?>
          <div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i>Contract signed on <?= htmlspecialchars(($r['contract_signed_at'] ?? '')) ?>.</div>
          <?php if (!empty($r['contract_pdf'])): ?>
            <a class="btn btn-primary" href="<?= site_url('/user/contract/pdf/' . (int)($r['id'] ?? 0)) ?>" target="_blank"><i class="fa-solid fa-download me-1"></i>View/Download PDF</a>
          <?php else: ?>
            <a class="btn btn-outline-primary" href="<?= site_url('/user/contract/pdf/' . (int)($r['id'] ?? 0)) ?>" target="_blank"><i class="fa-solid fa-print me-1"></i>Print-friendly</a>
          <?php endif; ?>
        <?php else: ?>
          <h6 class="mb-2">Sign here</h6>
          <canvas id="sig" class="sig-pad" height="220"></canvas>
          <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-outline-secondary" id="btnClear"><i class="fa-solid fa-eraser me-1"></i>Clear</button>
            <button type="button" class="btn btn-primary" id="btnSign"><i class="fa-solid fa-pen-nib me-1"></i>Sign & Save</button>
          </div>
          <div class="form-text mt-2">Use your mouse (desktop) or finger (mobile) to sign.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
(function(){
  var canvas = document.getElementById('sig');
  if (!canvas) return;
  // Resize to container width and preserve drawing
  var pad = null;
  function fit(){
    var p = canvas.parentElement; if (!p) return;
    var ratio = Math.max(window.devicePixelRatio || 1, 1);

    // preserve current drawing
    var dataUrl = null;
    try { if (pad && !pad.isEmpty()) dataUrl = pad.toDataURL('image/png'); } catch(e) { dataUrl = null; }

    // set real pixel size
    canvas.width = Math.round(p.clientWidth * ratio);
    canvas.height = Math.round(220 * ratio);

    // reset transform and scale to avoid accumulating transforms
    var ctx = canvas.getContext('2d');
    if (typeof ctx.setTransform === 'function') {
      ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    } else {
      ctx.setTransform(1,0,0,1,0,0);
      ctx.scale(ratio, ratio);
    }

    // set CSS display size
    canvas.style.width = p.clientWidth + 'px';
    canvas.style.height = '220px';

    // redraw preserved drawing scaled to CSS size
    if (dataUrl) {
      var img = new Image();
      img.onload = function(){
        try { ctx.clearRect(0,0,canvas.width,canvas.height); } catch(e) {}
        ctx.drawImage(img, 0, 0, p.clientWidth, 220);
      };
      img.src = dataUrl;
    }

    // recreate SignaturePad instance so internal state matches canvas
    try { pad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,1)' }); } catch(e) { pad = null; }
  }
  window.addEventListener('resize', fit);
  fit();
  document.getElementById('btnClear').addEventListener('click', function(){ if (pad) pad.clear(); });
  document.getElementById('btnSign').addEventListener('click', function(){
    if (pad.isEmpty()) { alert('Please provide a signature.'); return; }
    var dataURL = pad.toDataURL('image/png');
    var fd = new FormData();
    fd.append('rental_id', '<?= (int)($rental['id'] ?? 0) ?>');
    fd.append('signature', dataURL);
    // Use same-origin relative URL to avoid cross-origin fetch rejections
    var target = (function(){ try { return new URL('<?= site_url('/user/contract/sign') ?>', window.location.href); } catch(e){ return null; } })();
    var reqUrl = target ? (target.pathname + target.search) : '<?= site_url('/user/contract/sign') ?>';
    fetch(reqUrl, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      body: fd
    }).then(function(resp){
      return resp.text().then(function(txt){
        var clean = (txt||'').trim();
        if (clean.charCodeAt(0) === 0xFEFF) clean = clean.slice(1);
        var m = clean.match(/[\[{]/);
        if (m && m.index > 0) clean = clean.slice(m.index);
        try { return JSON.parse(clean); } catch(e) { return null; }
      });
    }).then(function(d){
      if (!d || !d.ok) throw new Error(d && d.message || 'Failed to sign');
      // Prefer redirecting the user straight to payment when provided
      if (d.next_url) {
        // small success notice then redirect
        try { alert(d.message || 'Contract signed. Redirecting to payment...'); } catch(e){}
        window.location.href = d.next_url;
        return;
      }
      // Fallback: reload to show signed state
      try { alert(d.message || 'Contract signed successfully.'); } catch(e){}
      location.reload();
    }).catch(function(err){
      alert(err && err.message ? err.message : 'Failed to sign.');
    });
  });
})();
</script>
</body>
</html>
