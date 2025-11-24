<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CarRental — Plan Your Trip</title>
  <meta name="description" content="Rent cars online with the best deals. Economy, SUVs, vans, and luxury vehicles. 24/7 support and free cancellation." />
  <link rel="canonical" href="<?= site_url('/') ?>" />
  <meta property="og:title" content="CarRental — Plan Your Trip" />
  <meta property="og:description" content="Rent cars online with the best deals. Economy, SUVs, vans, and luxury vehicles." />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?= site_url('/') ?>" />
  <meta property="og:image" content="https://images.unsplash.com/photo-1503376780353-7e6692767b70?q=80&w=1200&auto=format&fit=crop" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="CarRental — Plan Your Trip" />
  <meta name="twitter:description" content="Rent cars online with the best deals. Economy, SUVs, vans, and luxury vehicles." />
  <meta name="twitter:image" content="https://images.unsplash.com/photo-1503376780353-7e6692767b70?q=80&w=1200&auto=format&fit=crop" />
  <link rel="icon" href="<?= site_url('/favicon.svg') ?>" type="image/svg+xml" />
  <link rel="alternate icon" href="<?= site_url('/favicon.ico') ?>" />
  <link rel="apple-touch-icon" href="<?= site_url('/apple-touch-icon.png') ?>" />
  <meta name="theme-color" content="#0d6efd" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
  <style>
    .topbar { background:#2b2b2b; color:#fff; font-size:0.9rem; }
    .topbar a { color:#ff4d4f; text-decoration:none; }
    .hero { position: relative; color:#fff; min-height: 60vh; display:flex; align-items:center; overflow:hidden; }
    .hero::before { content:""; position:absolute; inset:0; background: linear-gradient( rgba(0,0,0,.55), rgba(0,0,0,.55) ); z-index:1; }
    .hero .bg-layer { position:absolute; inset:0; background-size:cover; background-position:center; background-repeat:no-repeat; opacity:0; transition: opacity 1s ease-in-out; z-index:0; }
    .hero .bg-layer.active { opacity:1; }
    .hero .container { position: relative; z-index:2; }
    .hero h1 { font-size:3rem; font-weight:700; }
    .hero p { font-size:1.1rem; opacity:.95; }
    .cta-btn { background:#e63946; border:none; padding:.75rem 1.25rem; }
    .category-card img { height: 190px; object-fit: cover; }
  .quick-search { background:#ffffffd9; border-radius:.5rem; padding:1rem; }
  .how-step i { width: 44px; height:44px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; background:#f1f3f5; }
  .trust-badges i { font-size:1.5rem; }
  </style>
</head>
<body>
  <div class="topbar py-2">
    <div class="container d-flex justify-content-between">
      <span>We are open 24/7 including major holidays</span>
      <span>Book online now. Call <a href="tel:+63234567890">09496232060</a></span>
    </div>
  </div>

  <?php require APP_DIR . 'views/partials/navbar.php'; ?>

  <header class="hero">
    <div class="bg-layer active" style="background-image:url('<?= site_url('/public/hero/slide1.jpg') ?>');"></div>
    <div class="bg-layer" style="background-image:url('<?= site_url('/public/hero/slide2.jpg') ?>');"></div>
    <div class="container">
      <div class="row g-4 align-items-start">
        <div class="col-lg-7">
        <p class="text-uppercase mb-2" style="letter-spacing:.08em;">Plan your trip with CarRental</p>
        <h1>Rent a Car Online Today</h1>
        <p>Enjoy the best deals, rates, and accessories for your next ride — from compact cars to luxury SUVs.</p>
        <a href="<?= site_url('/customer') ?>" class="btn btn-lg cta-btn text-white mt-2">Browse the Fleet</a>
        </div>
        <div class="col-lg-5">
          <form class="quick-search shadow" action="<?= site_url('/customer') ?>" method="get">
            <div class="row g-2">
              <div class="col-12"><strong class="text-dark">Quick Search</strong></div>
              <div class="col-6">
                <label class="form-label mb-1 small">Car Make</label>
                <input type="text" class="form-control" name="make" placeholder="e.g. Toyota" />
              </div>
              <div class="col-6">
                <label class="form-label mb-1 small">Model</label>
                <input type="text" class="form-control" name="model" placeholder="e.g. Vios" />
              </div>
              <div class="col-6">
                <label class="form-label mb-1 small">Transmission</label>
                <select class="form-select" name="transmission">
                  <option value="">Any</option>
                  <option value="automatic">Automatic</option>
                  <option value="manual">Manual</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label mb-1 small">Seats</label>
                <select class="form-select" name="seating_capacity">
                  <option value="">Any</option>
                  <option value="4">4+</option>
                  <option value="5">5+</option>
                  <option value="7">7+</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label mb-1 small">Min Price</label>
                <input type="number" class="form-control" name="min_price" min="0" step="100" placeholder="0" />
              </div>
              <div class="col-6">
                <label class="form-label mb-1 small">Max Price</label>
                <input type="number" class="form-control" name="max_price" min="0" step="100" placeholder="5000" />
              </div>
              <div class="col-12 d-grid">
                <button type="submit" class="btn btn-danger">Search Cars</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </header>

  <main class="py-5">
    <div class="container">
      <h2 class="text-center mb-4">Rental Vehicles</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card category-card shadow-sm">
            <img src="<?= site_url('/public/category/cars.jpg') ?>" class="card-img-top" alt="Cars & Crossovers" />
            <div class="card-body">
              <h5 class="card-title">Cars & Crossovers</h5>
              <p class="card-text text-muted">Efficient city driving and family-friendly options.</p>
              <a href="<?= site_url('/customer?transmission=automatic') ?>" class="stretched-link">Explore</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card category-card shadow-sm">
            <img src="<?= site_url('/public/category/luxury.jpg') ?>" class="card-img-top" alt="Luxury Cars" />
            <div class="card-body">
              <h5 class="card-title">Luxury Cars</h5>
              <p class="card-text text-muted">Premium comfort and style for special occasions.</p>
              <a href="<?= site_url('/customer?min_price=5000') ?>" class="stretched-link">Explore</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card category-card shadow-sm">
            <img src="<?= site_url('/public/category/van.jpg') ?>" class="card-img-top" alt="Vans & SUV" />
            <div class="card-body">
              <h5 class="card-title">Vans & SUV</h5>
              <p class="card-text text-muted">Space and power for every adventure.</p>
              <a href="<?= site_url('/customer?seating_capacity=7') ?>" class="stretched-link">Explore</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php if (!empty($featured_cars)): ?>
    <section class="container py-5">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Featured Cars</h3>
        <a href="<?= site_url('/customer') ?>" class="btn btn-sm btn-outline-secondary">See all</a>
      </div>
      <div class="row g-4">
        <?php foreach ($featured_cars as $car): ?>
          <?php
            $make = htmlspecialchars($car['make'] ?? '');
            $model = htmlspecialchars($car['model'] ?? '');
            $year = htmlspecialchars($car['year'] ?? '');
            $rate = number_format((float)($car['daily_rate'] ?? 0), 2);
            $trans = htmlspecialchars($car['transmission'] ?? '');
            $seats = htmlspecialchars($car['seating_capacity'] ?? '');
            $id = (int)($car['id'] ?? 0);
            // Compute image URL with fallbacks; support absolute and relative paths
            $img = '';
            if (!empty($car['image_path'])) {
              $raw = trim($car['image_path'] ?? '');
              if (preg_match('/^https?:\\/\\//i', $raw) || strpos($raw, 'data:') === 0) {
                $img = htmlspecialchars($raw);
              } else {
                $img = htmlspecialchars(site_url('/' . ltrim($raw, '/')));
              }
            }
            if ($img === '') {
              $img = 'https://images.unsplash.com/photo-1511918984145-48de785d4c4d?q=80&w=1200&auto=format&fit=crop';
            }
          ?>
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <img src="<?= $img ?>" class="card-img-top" alt="<?= trim($make . ' ' . $model) ?: 'Car image' ?>" loading="lazy" style="height:190px;object-fit:cover;" />
              <div class="card-body d-flex flex-column">
                <h5 class="card-title mb-1"><?= $make ?> <?= $model ?> <small class="text-muted"><?= $year ?></small></h5>
                <div class="text-muted small mb-2">
                  <span class="me-3"><i class="fa-solid fa-gear me-1"></i><?= $trans ?></span>
                  <span><i class="fa-solid fa-users me-1"></i><?= $seats ?> seats</span>
                </div>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                  <span class="fw-semibold">₱<?= $rate ?>/day</span>
                  <a href="<?= site_url('/customer/car/' . $id) ?>" class="btn btn-sm btn-primary">View</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <section class="bg-light py-5">
      <div class="container">
        <div class="row text-center mb-4">
          <h3>How it works</h3>
          <p class="text-muted">Book your ride in minutes.</p>
        </div>
        <div class="row g-4 text-center">
          <div class="col-md-3 how-step">
            <i class="fa-solid fa-magnifying-glass"></i>
            <h6 class="mt-2">Search</h6>
            <p class="text-muted small">Find the right car by price, seats, and features.</p>
          </div>
          <div class="col-md-3 how-step">
            <i class="fa-solid fa-car-side"></i>
            <h6 class="mt-2">Choose</h6>
            <p class="text-muted small">Compare options and view full details and photos.</p>
          </div>
          <div class="col-md-3 how-step">
            <i class="fa-solid fa-credit-card"></i>
            <h6 class="mt-2">Book</h6>
            <p class="text-muted small">Reserve online and secure with payment.</p>
          </div>
          <div class="col-md-3 how-step">
            <i class="fa-solid fa-key"></i>
            <h6 class="mt-2">Pick up</h6>
            <p class="text-muted small">Pick up at your chosen location and enjoy the ride.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="container py-4 trust-badges text-center">
      <div class="row g-4">
        <div class="col-6 col-md-3"><i class="fa-solid fa-shield-halved text-success"></i><div class="small">Insurance Included</div></div>
        <div class="col-6 col-md-3"><i class="fa-solid fa-rotate-left text-primary"></i><div class="small">Free Cancellation</div></div>
        <div class="col-6 col-md-3"><i class="fa-solid fa-headset text-info"></i><div class="small">24/7 Support</div></div>
        <div class="col-6 col-md-3"><i class="fa-solid fa-star text-warning"></i><div class="small">Top Rated Fleet</div></div>
      </div>
    </section>

    <section id="deals" class="container py-5">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Hot Deals</h3>
        <a href="<?= site_url('/customer') ?>" class="btn btn-sm btn-outline-primary">Browse all cars</a>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <span class="badge bg-danger mb-2">WEEKEND</span>
              <h5 class="card-title mb-1">Weekend Promo</h5>
              <p class="text-muted small mb-2">Up to 15% off Fri–Sun bookings.</p>
              <div class="small">Code: <code>WEEKEND15</code></div>
            </div>
            <div class="card-footer bg-white border-0 pt-0">
              <a href="<?= site_url('/customer?promo=WEEKEND15') ?>" class="btn btn-outline-danger btn-sm">Use deal</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <span class="badge bg-success mb-2">LONG STAY</span>
              <h5 class="card-title mb-1">7+ Days Saver</h5>
              <p class="text-muted small mb-2">Special rate for long-term rentals.</p>
              <div class="small">Code: <code>7DAYSLONG</code></div>
            </div>
            <div class="card-footer bg-white border-0 pt-0">
              <a href="<?= site_url('/customer?promo=7DAYSLONG') ?>" class="btn btn-outline-success btn-sm">Use deal</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <span class="badge bg-primary mb-2">EARLY BIRD</span>
              <h5 class="card-title mb-1">Advance Booking</h5>
              <p class="text-muted small mb-2">10% off when booking 14+ days ahead.</p>
              <div class="small">Code: <code>EARLY10</code></div>
            </div>
            <div class="card-footer bg-white border-0 pt-0">
              <a href="<?= site_url('/customer?promo=EARLY10') ?>" class="btn btn-outline-primary btn-sm">Use deal</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <span class="badge bg-dark mb-2">LAST MINUTE</span>
              <h5 class="card-title mb-1">Rush Saver</h5>
              <p class="text-muted small mb-2">₱300 off when booking within 48h.</p>
              <div class="small">Code: <code>RUSH300</code></div>
            </div>
            <div class="card-footer bg-white border-0 pt-0">
              <a href="<?= site_url('/customer?promo=RUSH300') ?>" class="btn btn-outline-dark btn-sm">Use deal</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <span class="badge bg-warning text-dark mb-2">SUV WEEK</span>
              <h5 class="card-title mb-1">SUVs 12% Off</h5>
              <p class="text-muted small mb-2">Save on spacious SUVs.</p>
              <div class="small">Code: <code>SUV12</code></div>
            </div>
            <div class="card-footer bg-white border-0 pt-0">
              <a href="<?= site_url('/customer?promo=SUV12&seating_capacity=7') ?>" class="btn btn-outline-warning btn-sm">Use deal</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <span class="badge bg-info text-dark mb-2">NEW USER</span>
              <h5 class="card-title mb-1">First Ride ₱300 Off</h5>
              <p class="text-muted small mb-2">Welcome discount for first booking.</p>
              <div class="small">Code: <code>FIRSTRIDE300</code></div>
            </div>
            <div class="card-footer bg-white border-0 pt-0">
              <a href="<?= site_url('/customer?promo=FIRSTRIDE300') ?>" class="btn btn-outline-info btn-sm">Use deal</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="container py-5">
      <div class="text-center mb-4">
        <h3>What our customers say</h3>
        <p class="text-muted">Real reviews from happy renters.</p>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <div class="text-warning mb-2">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star-half-stroke"></i>
              </div>
              <p class="mb-2">Smooth booking and clean car. Pick-up was on time and the staff was friendly!</p>
              <small class="text-muted">— Alex R.</small>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <div class="text-warning mb-2">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
              </div>
              <p class="mb-2">Best rates I found. The SUV was perfect for our family trip.</p>
              <small class="text-muted">— Joy M.</small>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <div class="text-warning mb-2">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
              </div>
              <p class="mb-2">Customer support helped us change dates easily. Highly recommended.</p>
              <small class="text-muted">— Marco D.</small>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="container py-5">
      <div class="row">
        <div class="col-md-6">
          <h3>FAQs</h3>
          <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header" id="faq1">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#a1">What documents do I need?</button>
              </h2>
              <div id="a1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Valid driver’s license and a government ID. A credit/debit card may be required for deposit.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="faq2">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a2">Is there a deposit?</button>
              </h2>
              <div id="a2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Some rentals require a refundable security deposit depending on the vehicle and duration.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="faq3">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a3">Can I cancel my booking?</button>
              </h2>
              <div id="a3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Yes, free cancellation is available within the specified window prior to pick-up.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="faq4">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a4">Do you offer delivery?</button>
              </h2>
              <div id="a4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Delivery or alternative pick-up locations may be arranged. Contact support for details.</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="p-4 bg-light rounded h-100">
            <h5>Need more help?</h5>
            <p class="text-muted">Our team is available 24/7 to answer your questions.</p>
            <a href="#contact" class="btn btn-outline-primary">Contact us</a>
          </div>
        </div>
      </div>
    </section>

    <section class="py-4" style="background:linear-gradient(90deg,#0d6efd,#6ea8fe);">
      <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between text-white">
        <h5 class="mb-3 mb-md-0">Ready to hit the road?</h5>
        <?php if (!empty($is_logged_in)): ?>
          <a href="<?= site_url('/user/dashboard') ?>" class="btn btn-light">Go to Dashboard</a>
        <?php else: ?>
          <div>
            <a href="<?= site_url('/customer') ?>" class="btn btn-outline-light me-2">Browse Cars</a>
            <a href="<?= site_url('/user/login') ?>" class="btn btn-light">Login to Rent</a>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <section id="contact" class="bg-light py-5">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <h4>Contact Us</h4>
            <p class="text-muted">Have questions? Our team is here 24/7.</p>
            <p><i class="fa-solid fa-phone me-2"></i> 09496232060</p>
            <p><i class="fa-solid fa-envelope me-2"></i> support@carrental.com</p>
          </div>
          <div class="col-md-6">
            <h4>Ready to book?</h4>
            <a href="<?= site_url('/customer') ?>" class="btn btn-primary">Browse available cars</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="py-4 text-center text-muted">
    <small>&copy; <?= date('Y') ?> CarRental. All rights reserved.</small>
    
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function(){
      const hero = document.querySelector('header.hero');
      const layers = hero ? hero.querySelectorAll('.bg-layer') : [];
      if (!hero || layers.length < 2) return;
      const images = [
        '<?= site_url('/public/hero/slide1.jpg') ?>',
        '<?= site_url('/public/hero/slide2.jpg') ?>',
        '<?= site_url('/public/hero/slide3.jpg') ?>',
        '<?= site_url('/public/hero/slide4.jpg') ?>'
      ];

      // Preload images
      images.forEach(src => { const img = new Image(); img.src = src; });

      let current = 0;
      // Ensure first layer is current image
      layers[0].style.backgroundImage = `url('${images[0]}')`;
      layers[0].classList.add('active');

      setInterval(() => {
        const next = (current + 1) % images.length;
        const activeLayer = hero.querySelector('.bg-layer.active');
        const inactiveLayer = (activeLayer === layers[0]) ? layers[1] : layers[0];
        // Set next image on the inactive layer and fade it in
        inactiveLayer.style.backgroundImage = `url('${images[next]}')`;
        requestAnimationFrame(() => {
          inactiveLayer.classList.add('active');
          activeLayer.classList.remove('active');
        });
        current = next;
      }, 7000); // change every 7 seconds
    })();
  </script>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "CarRental",
    "url": "<?= site_url('/') ?>",
    "logo": "<?= site_url('/public/css/logo.png') ?>",
    "sameAs": []
  }
  </script>
</body>
</html>
