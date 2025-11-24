<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<style>
  body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color:#111; }
  h2 { margin:0 0 6px 0; }
  .muted { color:#666; }
  .box { border:1px solid #ddd; padding:12px; border-radius:6px; }
  .row { display:flex; gap:12px; }
  .col { flex:1; }
  .mt { margin-top:10px; }
  .signed { margin-top: 30px; }
  .sign-img { width: 240px; height: auto; border-bottom: 1px solid #333; }
</style>
</head>
<body>
  <?php $r = $rental ?? []; ?>
  <h2>AutoTrack Car Rental Agreement</h2>
  <div class="muted">Contract #: <?= (int)($r['id'] ?? 0) ?> • Date: <?= date('Y-m-d') ?></div>
  <div class="row mt">
    <div class="col box">
      <strong>Renter</strong><br>
      <?= htmlspecialchars(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?><br>
      <?= htmlspecialchars(($r['email'] ?? '')) ?>
    </div>
    <div class="col box">
      <strong>Vehicle</strong><br>
      <?= htmlspecialchars(($r['make'] ?? '')) ?> <?= htmlspecialchars(($r['model'] ?? '')) ?> — Plate <?= htmlspecialchars(($r['plate_number'] ?? '')) ?><br>
      Period: <?= htmlspecialchars(($r['rental_start'] ?? '')) ?> to <?= htmlspecialchars(($r['rental_end'] ?? '')) ?><br>
      Total: ₱<?= number_format((float)($r['total_amount'] ?? 0), 2) ?>
    </div>
  </div>
  <div class="box mt">
    <strong>Terms</strong>
    <ol>
      <li>Vehicle is provided in good condition and shall be returned as scheduled.</li>
      <li>Renter is responsible for fines, tolls, and damage during rental.</li>
      <li>No smoking or illicit use of the vehicle.</li>
      <li>Fuel policy: return with the same fuel level as received.</li>
      <li>Late return fees may apply per hour/day overage.</li>
      <li>Company’s standard insurance terms apply where applicable.</li>
    </ol>
  </div>
  <div class="signed">
    <div>Signed by Renter:</div>
    <?php if (!empty($r['contract_signature'])): ?>
      <img class="sign-img" src="<?= site_url('/' . ltrim($r['contract_signature'], '/')) ?>" alt="Signature" />
      <div class="muted">Signed at: <?= htmlspecialchars(($r['contract_signed_at'] ?? '')) ?></div>
    <?php else: ?>
      <div class="muted">Pending signature</div>
    <?php endif; ?>
  </div>
  <div class="muted" style="margin-top:20px;">AutoTrack CarRental • Thank you for your business.</div>
</body>
</html>
