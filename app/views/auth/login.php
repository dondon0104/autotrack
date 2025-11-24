<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - AutoTrack</title>
        <link rel="icon" href="<?= site_url('/favicon.svg') ?>" type="image/svg+xml">
        <link rel="alternate icon" href="<?= site_url('/favicon.ico') ?>">
        <link rel="apple-touch-icon" href="<?= site_url('/apple-touch-icon.png') ?>">
        <meta name="theme-color" content="#0d6efd">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link href="<?= site_url('/public/css/auth.css') ?>" rel="stylesheet">
        <style> .link-muted{ text-decoration:none; } .link-muted:hover{ text-decoration:underline; } </style>
</head>
<body class="auth-bg">

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
            <div class="card auth-card">
                <div class="row g-0">
                    <div class="col-md-5 d-none d-md-block">
                        <div class="auth-hero">
                            <span class="tag"><i class="fas fa-shield-alt"></i> Admin Portal</span>
                            <div class="headline h3">Manage your fleet with ease</div>
                            <ul class="features">
                                <li><span class="ico"><i class="fas fa-car"></i></span> Centralized car management</li>
                                <li><span class="ico"><i class="fas fa-clipboard-check"></i></span> Rentals and payments overview</li>
                                <li><span class="ico"><i class="fas fa-chart-pie"></i></span> Live dashboard insights</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4 p-md-5">
                <div class="brand mb-3 text-center">
                    <div class="logo"><i class="fas fa-car"></i></div>
                    <div class="name h4 mb-0">AutoTrack</div>
                </div>
                <h5 class="form-title text-center mb-4">Admin Login</h5>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error'); ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success d-flex align-items-center"><i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success'); ?></div>
                <?php endif; ?>

                <form action="<?= site_url('/admin/loginProcess') ?>" method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="form-control" required placeholder="Enter admin username">
                        </div>
                    </div>

                                <div class="mb-2">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control" required placeholder="Enter password">
                            <span class="input-group-text password-toggle" onclick="togglePwd('password', this)"><i class="fas fa-eye"></i></span>
                        </div>
                                    <div id="caps_login" class="caps-ind d-none mt-1"><i class="fas fa-exclamation-triangle me-1"></i> Caps Lock is on</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 small-muted">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" disabled>
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <span class="text-muted">Admin access only</span>
                    </div>

                                <button id="btn_login" type="submit" class="btn btn-primary w-100">
                                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                    Sign in
                                </button>
                </form>

                <div class="text-center mt-3 small-muted">
                    Don’t have an account? <a class="link-muted" href="<?= site_url('/admin/register') ?>">Register</a>
                </div>
                            </div>
                        </div>
                    </div>
        </div>
        <div class="text-center mt-3 auth-footer">© <?= date('Y') ?> AutoTrack • Admin Portal</div>
    </div>
</div>

<script>
function togglePwd(id, el){
    var i = document.getElementById(id);
    if(!i) return; var is = i.getAttribute('type') === 'password';
    i.setAttribute('type', is ? 'text' : 'password');
    var icon = el && el.querySelector('i');
    if(icon){ icon.classList.toggle('fa-eye'); icon.classList.toggle('fa-eye-slash'); }
}
        // Caps Lock detection and submit spinner
        document.addEventListener('DOMContentLoaded', function(){
            var pwd = document.getElementById('password');
            var caps = document.getElementById('caps_login');
            var btn = document.getElementById('btn_login');
            if(pwd){
                pwd.addEventListener('keydown', function(e){ if(!caps) return; caps.classList.toggle('d-none', !e.getModifierState || !e.getModifierState('CapsLock')); });
                pwd.addEventListener('keyup', function(e){ if(!caps) return; caps.classList.toggle('d-none', !e.getModifierState || !e.getModifierState('CapsLock')); });
            }
            var form = document.forms[0];
            if(form){ form.addEventListener('submit', function(){ if(btn){ var sp=btn.querySelector('.spinner-border'); if(sp) sp.classList.remove('d-none'); btn.setAttribute('disabled','disabled'); } }); }
        });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
