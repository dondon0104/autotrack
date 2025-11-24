<?php
// Shared Navbar Partial
// Expects optional: $is_logged_in (bool), $user_name (string)
// Fallbacks if not provided by controller
if (!isset($is_logged_in)) {
    $is_logged_in = !empty($_SESSION['isUserLoggedIn']);
}
if (!isset($user_name) || $user_name === '') {
    $user_name = $is_logged_in && !empty($_SESSION['first_name'].$_SESSION['last_name'])
        ? trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''))
        : 'Guest';
}
?>
<nav class="navbar navbar-expand-lg bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= site_url('/') ?>">
      <i class="fa-solid fa-car-side text-danger me-2"></i>CarRental
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="<?= site_url('/customer') ?>">Car Rentals</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= site_url('/#deals') ?>">Deals</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= site_url('/#contact') ?>">Contact</a></li>
        <?php if (!empty($is_logged_in)): ?>
          <li class="nav-item"><span class="nav-link text-muted d-none d-lg-inline">Hi, <?= htmlspecialchars($user_name) ?></span></li>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('/user/my-rentals') ?>">My Rentals</a></li>
          <li class="nav-item"><a class="btn btn-outline-danger ms-lg-2" href="<?= site_url('/user/logout') ?>">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="btn btn-outline-primary ms-lg-2" href="<?= site_url('/user/login') ?>" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('/user/register') ?>" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Login Modal (shared) -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Login to CarRental</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
    <div class="modal-body p-0">
    <style>
      /* Ensure the Bootstrap modal container itself is wide enough */
      #loginModal .modal-dialog { max-width: 1100px; }
      /* Increase modal card width/spacing to match standalone large layout */
            .login-wrap { max-width: 1020px; margin: 0 auto; }
            .login-card { border-radius: 1rem; overflow: hidden; box-shadow: 0 28px 70px rgba(0,0,0,.20); background: #fff; display:flex; gap:0; border:0; align-items: stretch; }
            /* Use flex-basis to avoid subpixel gap lines between columns */
            .login-left { padding: 3.5rem 4rem; flex: 1 1 auto; min-width:0; }
      .brand { font-weight: 700; color: #06b2a9; letter-spacing: .4px; }
      .title { font-size: 34px; font-weight: 800; line-height:1.02; }
      .accent { color: #06b2a9; }
      .muted { color: #6c757d; font-size:1rem }
            .illustration { background: #f8f9fa; min-height: 460px; flex: 0 0 480px; border-left: 0; margin-left: 0; display:flex; align-items:stretch; }
            .illustration .login-hero-img { width:100%; height:100%; object-fit:cover; display:block; }
            .form-control.login-full { height: 56px; border-radius:10px; padding:14px 16px; }
      .input-group .input-group-text, .input-group .btn { border-radius:0 8px 8px 0; }
      .btn-teal { background: #06b2a9; border-color: #06b2a9; color:#fff; border-radius:10px; padding:12px 18px; font-size:16px }
      .btn-teal:hover { background: #048b7d; border-color: #048b7d }
            @media (max-width:991px){ .login-wrap{ max-width:720px } }
      @media (max-width:767px){ .illustration{ display:none } .login-card{ flex-direction:column } .login-left{ width:100%; padding:20px } }
    </style>

    <div class="login-wrap">
      <div class="login-card">
        <div class="login-left">
          <div class="brand d-flex align-items-center mb-2"><i class="fa-solid fa-car-side me-2"></i> CarRental</div>
          <div class="title mb-1">Enter Your <span class="accent">Email</span> & <span class="accent">Password</span></div>
          <div class="muted mb-3">Welcome back! Please login to continue.</div>

          <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger py-2"><i class="fa-solid fa-circle-exclamation me-2"></i><?= $this->session->flashdata('error'); ?></div>
          <?php endif; ?>
          <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success py-2"><i class="fa-solid fa-circle-check me-2"></i><?= $this->session->flashdata('success'); ?></div>
          <?php endif; ?>

          <form action="<?= site_url('/user/loginProcess') ?>" method="post" class="mt-2" id="modalLoginForm">
            <input type="hidden" name="redirect_to" value="">
            <div class="mb-3">
              <label class="form-label small text-muted">Email Address</label>
              <input type="email" name="email" class="form-control login-full" placeholder="example@gmail.com" required>
            </div>
            <div class="mb-3">
              <label class="form-label small text-muted">Password</label>
              <div class="input-group">
                <input type="password" name="password" class="form-control login-full" placeholder="••••••••" required>
                <button type="button" class="input-group-text bg-white toggle-password" tabindex="-1"><i class="fa-regular fa-eye-slash"></i></button>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="rememberMeModal" name="remember">
                <label class="form-check-label small" for="rememberMeModal">Keep me logged in</label>
              </div>
              <a href="<?= site_url('/user/login') ?>" class="small text-decoration-none">Forgot Password?</a>
              <span class="mx-2 text-muted">•</span>
              <a href="#" id="resendVerificationLink" class="small text-decoration-none">Resend verification email</a>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-teal">Login</button>
            </div>
          </form>

          <div class="mt-3 small">Don’t have an account? <a href="#" id="openRegisterFromLogin" role="button" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Register</a> <span class="mx-2">•</span></div>
        </div>
        <div class="illustration">
          <img class="login-hero-img" src="https://images.unsplash.com/photo-1619767886558-efdc259cde1a?q=80&w=1200&auto=format&fit=crop" alt="Car">
        </div>
      </div>
    </div>

    <script>
      // capture redirect_to when modal opens (existing handler elsewhere should also work)
      (function(){
        var modal = document.getElementById('loginModal');
        if (!modal) return;
        modal.addEventListener('show.bs.modal', function(e){
          var trigger = e.relatedTarget;
          var redirect = '';
          try { var url = new URL(trigger.getAttribute('href')||'', window.location.origin); redirect = url.searchParams.get('redirect_to') || ''; } catch (er){}
          var input = modal.querySelector('input[name="redirect_to"]'); if (input) input.value = redirect;
        });
      })();

      // small toggle for password
      document.addEventListener('click', function(e){
        var btn = e.target.closest('.toggle-password'); if (!btn) return;
        var grp = btn.closest('.input-group'); if (!grp) return;
        var input = grp.querySelector('input[type="password"]'); if (!input) return;
        if (input.type === 'password') { input.type = 'text'; btn.innerHTML = '<i class="fa-regular fa-eye"></i>'; } else { input.type = 'password'; btn.innerHTML = '<i class="fa-regular fa-eye-slash"></i>'; }
      });
    </script>

        <script>
          // Password toggle (single handler)
          (function(){
            var toggleBtn = document.getElementById('navTogglePassword');
            if (!toggleBtn) return;
            toggleBtn.addEventListener('click', function (){
              var input = document.getElementById('navPasswordInput');
              if (!input) return;
              if (input.type === 'password') { input.type = 'text'; toggleBtn.innerHTML = '<i class="fa-regular fa-eye"></i>'; }
              else { input.type = 'password'; toggleBtn.innerHTML = '<i class="fa-regular fa-eye-slash"></i>'; }
            });
          })();
        </script>
        <script>
          // Resend verification email from login modal using the email input
          (function(){
            var link = document.getElementById('resendVerificationLink');
            if (!link || !window.fetch) return;
            link.addEventListener('click', function(e){
              e.preventDefault();
              var loginModal = document.getElementById('loginModal');
              var emailInput = loginModal ? loginModal.querySelector('input[name="email"]') : null;
              var email = emailInput ? emailInput.value.trim() : '';
              if (!email) {
                alert('Please enter your email in the form first.');
                if (emailInput) emailInput.focus();
                return;
              }
              var fd = new FormData();
              fd.set('email', email);
              // Use same-origin relative URL to avoid cross-origin issues
              (function(){
                var u = null;
                try { u = new URL('<?= site_url('/user/resend-verification') ?>', window.location.href); } catch(e) {}
                var url = u ? (u.pathname + u.search) : '<?= site_url('/user/resend-verification') ?>';
                fetch(url, { method:'POST', body: fd })
                .then(function(){
                  // Show a small inline success
                  var container = loginModal ? loginModal.querySelector('.login-left') : null;
                  if (container) {
                    var prev = container.querySelector('.alert'); if (prev) prev.remove();
                    var alertBox = document.createElement('div');
                    alertBox.className = 'alert alert-success py-2 mt-2';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i>If an account exists, a verification email has been sent.';
                    container.insertBefore(alertBox, container.firstChild);
                  }
                })
                .catch(function(){ /* ignore */ });
              })();
            });
          })();
        </script>
      </div>
      <div class="modal-footer">
        
      </div>
    </div>
  </div>
</div>

<script>
  (function(){
    var loginModal = document.getElementById('loginModal');
    if (!loginModal) return;
    loginModal.addEventListener('show.bs.modal', function (event) {
      var trigger = event.relatedTarget;
      var redirectInput = loginModal.querySelector('input[name="redirect_to"]');
      var href = trigger && trigger.getAttribute('href') ? trigger.getAttribute('href') : '';
      var redirect = '';
      try {
        // Support absolute and relative URLs
        var url = new URL(href, window.location.origin);
        redirect = url.searchParams.get('redirect_to') || '';
      } catch (e) {
        redirect = '';
      }
      if (redirectInput) redirectInput.value = redirect;
    });
  })();
</script>

<script>
  // Seamless switch between Login and Register modals (delegated for timing/DOM readiness)
  (function(){
    if (!window.bootstrap) return;
    document.addEventListener('click', function(ev){
      var regLink = ev.target && ev.target.closest('#openRegisterFromLogin');
      var logLink = ev.target && ev.target.closest('#openLoginFromRegister');

      if (regLink) {
        ev.preventDefault();
        var loginEl = document.getElementById('loginModal');
        var registerEl = document.getElementById('registerModal');
        if (!(loginEl && registerEl)) return;
        var lm = bootstrap.Modal.getOrCreateInstance(loginEl);
        var rm = bootstrap.Modal.getOrCreateInstance(registerEl);
        var onHidden = function(){
          loginEl.removeEventListener('hidden.bs.modal', onHidden);
          rm.show();
          registerEl.addEventListener('shown.bs.modal', function onShown(){
            registerEl.removeEventListener('shown.bs.modal', onShown);
            var first = registerEl.querySelector('input, textarea, select, button');
            if (first && typeof first.focus === 'function') first.focus();
          });
        };
        loginEl.addEventListener('hidden.bs.modal', onHidden);
        lm.hide();
      }

      if (logLink) {
        ev.preventDefault();
        var loginEl2 = document.getElementById('loginModal');
        var registerEl2 = document.getElementById('registerModal');
        if (!(loginEl2 && registerEl2)) return;
        var lm2 = bootstrap.Modal.getOrCreateInstance(loginEl2);
        var rm2 = bootstrap.Modal.getOrCreateInstance(registerEl2);
        var onHidden2 = function(){
          registerEl2.removeEventListener('hidden.bs.modal', onHidden2);
          lm2.show();
          loginEl2.addEventListener('shown.bs.modal', function onShown2(){
            loginEl2.removeEventListener('shown.bs.modal', onShown2);
            var email = loginEl2.querySelector('input[name="email"]') || loginEl2.querySelector('input, textarea, select, button');
            if (email && typeof email.focus === 'function') email.focus();
          });
        };
        registerEl2.addEventListener('hidden.bs.modal', onHidden2);
        rm2.hide();
      }
    });
  })();
</script>

<!-- Register Modal (shared) -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create an Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <style>
          /* Scope styles to register modal only */
          #registerModal .modal-dialog { max-width: 980px; }
          #registerModal .login-wrap { max-width: 900px; margin: 0 auto; }
          #registerModal .login-card { border-radius: 1rem; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,.18); background: #fff; display:flex; gap:0; border:0; align-items: stretch; }
          #registerModal .login-left { padding: 2rem 2.25rem; flex: 1 1 auto; min-width:0; }
          #registerModal .brand { font-weight: 700; color: #06b2a9; letter-spacing: .4px; }
          #registerModal .title { font-size: 28px; font-weight: 800; line-height:1.1; }
          #registerModal .accent { color: #06b2a9; }
          #registerModal .muted { color: #6c757d; font-size:.975rem }
          #registerModal .illustration { min-height: 420px; flex: 0 0 420px; display:flex; align-items:stretch; }
          #registerModal .login-hero-img { width:100%; height:100%; object-fit:cover; display:block; }
          #registerModal .form-control.login-full { height: 46px; border-radius:10px; padding:10px 12px; }
          #registerModal .btn-teal { background: #06b2a9; border-color: #06b2a9; color:#fff; border-radius:10px; padding:10px 16px; font-size:15px }
          #registerModal .btn-teal:hover { background: #048b7d; border-color: #048b7d }
          @media (max-width:767px){ #registerModal .illustration{ display:none } #registerModal .login-card{ flex-direction:column } #registerModal .login-left{ width:100%; padding:18px } }
        </style>

        <div class="login-wrap">
          <div class="login-card">
            <div class="login-left">
              <div class="brand d-flex align-items-center mb-2"><i class="fa-solid fa-car-side me-2"></i> CarRental</div>
              <div class="title mb-1">Create Your <span class="accent">Account</span></div>
              <div class="muted mb-3">Sign up to book and manage your rentals.</div>

              <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger py-2"><i class="fa-solid fa-circle-exclamation me-2"></i><?= $this->session->flashdata('error'); ?></div>
              <?php endif; ?>
              <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success py-2"><i class="fa-solid fa-circle-check me-2"></i><?= $this->session->flashdata('success'); ?></div>
              <?php endif; ?>

              <form action="<?= site_url('/user/registerProcess') ?>" method="post" class="mt-2" id="modalRegisterForm">
                <input type="hidden" name="from_modal" value="1">
                <input type="hidden" name="return_to" value="">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label small text-muted">First Name *</label>
                    <input type="text" name="first_name" class="form-control login-full" placeholder="Juan" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small text-muted">Last Name *</label>
                    <input type="text" name="last_name" class="form-control login-full" placeholder="Dela Cruz" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label small text-muted">Email Address *</label>
                    <input type="email" name="email" class="form-control login-full" placeholder="example@gmail.com" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small text-muted">Phone Number</label>
                    <input type="tel" name="phone" class="form-control login-full" placeholder="09XXXXXXXXX">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small text-muted">Driver's License Number</label>
                    <input type="text" name="license_number" class="form-control login-full" placeholder="e.g. N1234-56-789012">
                  </div>
                  <div class="col-12">
                    <label class="form-label small text-muted">Address</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="House No., Street, City"></textarea>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small text-muted">Password *</label>
                    <div class="input-group">
                      <input type="password" name="password" class="form-control login-full" placeholder="••••••••" required>
                      <button type="button" class="input-group-text bg-white toggle-password" tabindex="-1"><i class="fa-regular fa-eye-slash"></i></button>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small text-muted">Confirm Password *</label>
                    <div class="input-group">
                      <input type="password" name="password_confirm" class="form-control login-full" placeholder="••••••••" required>
                      <button type="button" class="input-group-text bg-white toggle-password" tabindex="-1"><i class="fa-regular fa-eye-slash"></i></button>
                    </div>
                  </div>
                </div>
                <div class="d-grid mt-3">
                  <button type="submit" class="btn btn-teal">Create account</button>
                </div>
              </form>

              <div class="mt-3 small">Already have an account? <a href="#" id="openLoginFromRegister" role="button" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a></div>
            </div>
            <div class="illustration">
              <img class="login-hero-img" src="https://images.unsplash.com/photo-1619767886558-efdc259cde1a?q=80&w=1200&auto=format&fit=crop" alt="Car">
            </div>
          </div>
          <div class="modal-footer">
        
      </div>
        </div>

        <script>
          // Password toggles for the two password fields in register modal
          document.addEventListener('click', function(e){
            var btn = e.target.closest('#registerModal .toggle-password'); if (!btn) return;
            var grp = btn.closest('.input-group'); if (!grp) return;
            var input = grp.querySelector('input'); if (!input) return;
            if (input.type === 'password') { input.type = 'text'; btn.innerHTML = '<i class="fa-regular fa-eye"></i>'; }
            else { input.type = 'password'; btn.innerHTML = '<i class="fa-regular fa-eye-slash"></i>'; }
          });
        </script>

        <script>
          // AJAX submit for Register modal: on success, switch to Login modal and show success message
          (function(){
            var form = document.getElementById('modalRegisterForm');
            if (!form || !window.fetch) return;

            form.addEventListener('submit', function(ev){
              ev.preventDefault();
              var fd = new FormData(form);
              // Ensure flags present
              fd.set('from_modal', '1');
              if (!fd.get('return_to')) {
                try { fd.set('return_to', window.location.pathname + window.location.search); } catch(e) {}
              }

              var submitBtn = form.querySelector('button[type="submit"]');
              if (submitBtn) { submitBtn.disabled = true; submitBtn.dataset._orig = submitBtn.textContent; submitBtn.textContent = 'Creating account…'; }

              // Clear previous alerts
              var regLeft = form.closest('.login-left');
              if (regLeft) {
                var prev = regLeft.querySelector('.alert');
                if (prev) prev.remove();
              }

              // Use same-origin relative URL to avoid cross-origin (localhost vs 127.0.0.1) issues
              var target = (function(){ try { return new URL(form.action, window.location.href); } catch(e){ return null; } })();
              var reqUrl = target ? (target.pathname + target.search) : form.action;
              fetch(reqUrl, { method:'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }})
                .then(function(res){
                  return res.text().then(function(txt){
                    var data;
                    try {
                      var clean = (txt || '').trim();
                      // Strip UTF-8 BOM if present
                      if (clean.charCodeAt(0) === 0xFEFF) { clean = clean.slice(1); }
                      // If some notices preceded JSON, drop any leading junk before the first { or [
                      var m = clean.match(/[\[{]/);
                      if (m && m.index > 0) { clean = clean.slice(m.index); }
                      data = JSON.parse(clean);
                    } catch(e){
                      data = { success:false, message:'Unexpected server response.' };
                    }
                    return { ok: res.ok, data: data };
                  });
                })
                .then(function(resp){
                  if (resp.ok && resp.data && resp.data.success) {
                    var loginEl = document.getElementById('loginModal');
                    var registerEl = document.getElementById('registerModal');
                    if (registerEl && loginEl && window.bootstrap) {
                      var rm = bootstrap.Modal.getOrCreateInstance(registerEl);
                      var lm = bootstrap.Modal.getOrCreateInstance(loginEl);
                      registerEl.addEventListener('hidden.bs.modal', function onHidden(){
                        registerEl.removeEventListener('hidden.bs.modal', onHidden);
                        // Inject success alert into login modal
                        var container = loginEl.querySelector('.login-left');
                        if (container) {
                          var alert = document.createElement('div');
                          alert.className = 'alert alert-success py-2';
                          alert.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i>' + (resp.data.message || 'Account created successfully. Please login.');
                          container.insertBefore(alert, container.firstChild);
                        }
                        lm.show();
                        // Focus email field
                        loginEl.addEventListener('shown.bs.modal', function onShown(){
                          loginEl.removeEventListener('shown.bs.modal', onShown);
                          var email = loginEl.querySelector('input[name="email"]');
                          if (email && email.focus) email.focus();
                        });
                      });
                      rm.hide();
                      try { form.reset(); } catch(e) {}
                    }
                  } else {
                    // Dev aid: log any debug raw output from server
                    if (resp && resp.data && resp.data._debug_raw) {
                      try { console.debug('Server raw output before JSON:', resp.data._debug_raw); } catch(e) {}
                    }
                    // Show error in register modal
                    var msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Registration failed. Please try again.';
                    var container2 = form.closest('.login-left');
                    if (container2) {
                      var alert2 = document.createElement('div');
                      alert2.className = 'alert alert-danger py-2';
                      alert2.innerHTML = '<i class="fa-solid fa-circle-exclamation me-2"></i>' + msg;
                      container2.insertBefore(alert2, container2.firstChild);
                    }
                  }
                })
                .catch(function(){
                  var container3 = form.closest('.login-left');
                  if (container3) {
                    var alert3 = document.createElement('div');
                    alert3.className = 'alert alert-danger py-2';
                    alert3.innerHTML = '<i class="fa-solid fa-circle-exclamation me-2"></i>Network error. Please try again.';
                    container3.insertBefore(alert3, container3.firstChild);
                  }
                })
                .finally(function(){
                  if (submitBtn) { submitBtn.disabled = false; if (submitBtn.dataset._orig) submitBtn.textContent = submitBtn.dataset._orig; }
                });
            });
          })();
        </script>
      </div>
    </div>
  </div>
</div>

<?php if ($this->session->flashdata('open_login_modal')): ?>
<script>
  // Auto-open login modal after page load, waiting for Bootstrap if necessary
  (function(){
    var tried = 0;
    var openWhenReady = function(){
      var el = document.getElementById('loginModal');
      if (!el) return; // nothing to do if modal not in DOM
      if (window.bootstrap && bootstrap.Modal) {
        try { bootstrap.Modal.getOrCreateInstance(el).show(); } catch(e) {}
      } else if (tried < 50) {
        tried++;
        setTimeout(openWhenReady, 100); // retry up to ~5s
      }
    };
    if (document.readyState === 'complete') {
      openWhenReady();
    } else {
      window.addEventListener('load', openWhenReady);
    }
  })();
  </script>
<?php endif; ?>

<script>
  // Fill return_to with current path so server can redirect back and open login modal
  (function(){
    var setReturnTo = function(){
      var field = document.querySelector('#modalRegisterForm input[name="return_to"]');
      if (!field) return;
      try {
        var loc = window.location;
        // Use path + search only (avoid full origin)
        field.value = loc.pathname + loc.search;
      } catch (e) { /* noop */ }
    };
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', setReturnTo);
    } else {
      setReturnTo();
    }
    // Also set when switching to register from login to be safe
    document.addEventListener('click', function(ev){
      if (ev.target && ev.target.closest('#openRegisterFromLogin')) {
        setReturnTo();
      }
    });
  })();
</script>
