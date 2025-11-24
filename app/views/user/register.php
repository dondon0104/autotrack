<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — CarRental</title>
    <link rel="icon" href="<?= site_url('/favicon.svg') ?>" type="image/svg+xml">
    <link rel="alternate icon" href="<?= site_url('/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= site_url('/apple-touch-icon.png') ?>">
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
    <link href="<?= site_url('/public/css/auth.css') ?>" rel="stylesheet">
    <style>
        body { background: #eef2f5; }
        .login-wrap { max-width: 980px; margin: 4rem auto; padding: 0 1rem; }
        .login-card { border-radius: 1rem; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,.15); background: #fff; }
        .login-left { padding: 2rem 2.25rem; }
        .brand { font-weight: 700; color: #0ea5a5; letter-spacing: .5px; }
        .title { font-size: 2rem; font-weight: 800; }
        .accent { color: #0ea5a5; }
        .muted { color: #6c757d; }
        .illustration { background: #f8f9fa url('https://images.unsplash.com/photo-1619767886558-efdc259cde1a?q=80&w=1200&auto=format&fit=crop') center/cover no-repeat; min-height: 420px; }
        .form-control { height: 48px; }
        .btn-teal { background: #0ea5a5; border-color: #0ea5a5; }
        .btn-teal:hover { background: #0c8f8f; border-color: #0c8f8f; }
        .msg-area{ min-height: 44px; }
    </style>
</head>
<body>

<div class="login-wrap">
    <div class="row g-0 login-card">
        <div class="col-12 col-md-6 login-left">
            <div class="brand d-flex align-items-center mb-2">
                <i class="fa-solid fa-car-side me-2"></i> CarRental
            </div>
            <div class="title mb-1">Create Your <span class="accent">Account</span></div>
            <div class="muted mb-3">Sign up to book and manage your rentals.</div>

            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger py-2"><i class="fa-solid fa-circle-exclamation me-2"></i><?= $this->session->flashdata('error'); ?></div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success py-2"><i class="fa-solid fa-circle-check me-2"></i><?= $this->session->flashdata('success'); ?></div>
            <?php endif; ?>

            <form id="userRegisterForm" action="<?= site_url('/user/registerProcess') ?>" method="post" class="mt-2">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">First Name *</label>
                        <input id="first_name" type="text" name="first_name" class="form-control" placeholder="Juan" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Last Name *</label>
                        <input id="last_name" type="text" name="last_name" class="form-control" placeholder="Dela Cruz" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small text-muted">Email Address *</label>
                        <input id="email" type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Phone Number</label>
                        <input id="phone" type="tel" name="phone" class="form-control" placeholder="09XXXXXXXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Driver's License Number</label>
                        <input id="license_number" type="text" name="license_number" class="form-control" placeholder="e.g. N1234-56-789012">
                    </div>
                    <div class="col-12">
                        <label class="form-label small text-muted">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3" placeholder="House No., Street, City"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Password *</label>
                        <div class="input-group">
                            <input id="password" type="password" name="password" class="form-control" placeholder="••••••••" required>
                            <span class="input-group-text bg-white password-toggle" style="cursor:pointer"><i class="fa-regular fa-eye-slash"></i></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Confirm Password *</label>
                        <div class="input-group">
                            <input id="password_confirm" type="password" name="password_confirm" class="form-control" placeholder="••••••••" required>
                            <span class="input-group-text bg-white password-toggle" style="cursor:pointer"><i class="fa-regular fa-eye-slash"></i></span>
                        </div>
                    </div>
                </div>
                <div class="d-grid mt-3">
                    <button id="btnSubmit" type="submit" class="btn btn-teal text-white">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        Create account
                    </button>
                </div>
                <div class="msg-area mt-3" id="msgArea"></div>
            </form>

            <div class="mt-3 small">
                Already have an account? <a href="<?= site_url('/user/login') ?>">Login</a>
            </div>
        </div>
        <div class="col-12 col-md-6 illustration"></div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Enhance user register form: submit via fetch with JSON accept header when JS is available.
(function(){
    const form = document.getElementById('userRegisterForm');
    const btn = document.getElementById('btnSubmit');
    const msg = document.getElementById('msgArea');
    const pw = document.getElementById('password');
    const pwc = document.getElementById('password_confirm');

    function setMessage(html, type='info'){
        msg.innerHTML = '<div class="alert alert-'+(type==='error'?'danger':(type==='success'?'success':'info'))+' py-2">'+html+'</div>';
    }

    // Toggle eye icons
    document.querySelectorAll('.password-toggle').forEach(function(t){
        t.addEventListener('click', function(){
            const input = this.parentElement.querySelector('input');
            if (!input) return; const is = input.getAttribute('type') === 'password';
            input.setAttribute('type', is ? 'text' : 'password');
            const i = this.querySelector('i'); if(i){ i.classList.toggle('fa-eye'); i.classList.toggle('fa-eye-slash'); }
        });
    });

    if (!form) return;
    form.addEventListener('submit', function(e){
        // If browser supports fetch, submit via AJAX to provide JSON handling; still allow fallback
        if (!window.fetch) return true;
        e.preventDefault();
        // Basic client-side check
        if (pw && pwc && pw.value !== pwc.value) {
            setMessage('Passwords do not match.', 'error');
            return;
        }
        const fdata = new FormData(form);
        btn.setAttribute('disabled','disabled');
        const spinner = btn.querySelector('.spinner-border'); if (spinner) spinner.classList.remove('d-none');

            // Normalize action URL to avoid malformed base from site_url (some dev setups use :PORT prefixes)
            var actionUrl = form.getAttribute('action') || '';
            var fetchUrl = actionUrl;
            try {
                if (actionUrl.indexOf('://') === -1 && actionUrl.indexOf('//') !== 0) {
                    // If it starts with '/', prefix origin
                    if (actionUrl.charAt(0) === '/') {
                        fetchUrl = window.location.origin + actionUrl;
                    } else if (actionUrl.charAt(0) === ':') {
                        // e.g. ":3000/web2/..." -> construct using hostname
                        fetchUrl = window.location.protocol + '//' + window.location.hostname + actionUrl;
                    } else {
                        // relative path, make absolute relative to current pathname
                        var base = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/');
                        fetchUrl = base + actionUrl;
                    }
                }
            } catch (e) { fetchUrl = form.action; }

            fetch(fetchUrl, {
            method: 'POST',
            body: fdata,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        }).then(function(resp){
                // Attempt to parse JSON; on 404/500 try to extract text for clearer error
                return resp.text().then(function(txt){
                    try { return JSON.parse(txt); } catch(e){ throw new Error('Invalid server response: ' + (txt || resp.status)); }
                });
        }).then(function(json){
            if (json && json.success) {
                setMessage(json.message || 'Account created. Please verify your email.', 'success');
                // On success, optionally redirect to login after short delay
                setTimeout(function(){ window.location.href = '<?= site_url('/user/login') ?>'; }, 1800);
            } else {
                setMessage((json && json.message) ? json.message : 'Registration failed. Please try again.', 'error');
                btn.removeAttribute('disabled'); if (spinner) spinner.classList.add('d-none');
            }
        }).catch(function(err){
            // Network or parse error
            setMessage('Unexpected server response. Please try again later.', 'error');
            btn.removeAttribute('disabled'); if (spinner) spinner.classList.add('d-none');
            console.error('Register error', err);
        });
    });
})();
</script>
</body>
</html>
