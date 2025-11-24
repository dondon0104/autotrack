<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Registration - AutoTrack</title>
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
                            <span class="tag"><i class="fas fa-shield-alt"></i> Secure Access</span>
                            <div class="headline h3">Create your admin account</div>
                            <ul class="features">
                                <li><span class="ico"><i class="fas fa-user-shield"></i></span> Role: Admin</li>
                                <li><span class="ico"><i class="fas fa-key"></i></span> Strong password required</li>
                                <li><span class="ico"><i class="fas fa-lock"></i></span> Encrypted credentials</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4 p-md-5">
                <div class="brand mb-3 text-center">
                    <div class="logo"><i class="fas fa-car"></i></div>
                    <div class="name h4 mb-0">AutoTrack</div>
                </div>
                <h5 class="form-title text-center mb-4">Admin Registration</h5>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error'); ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success d-flex align-items-center"><i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success'); ?></div>
                <?php endif; ?>

                        <form action="<?= site_url('/admin/registerProcess') ?>" method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="form-control" required placeholder="Enter admin username">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control" required placeholder="Enter password">
                            <span class="input-group-text password-toggle" onclick="togglePwd('password', this)"><i class="fas fa-eye"></i></span>
                        </div>
                                <div id="caps_reg" class="caps-ind d-none mt-1"><i class="fas fa-exclamation-triangle me-1"></i> Caps Lock is on</div>
                                <div class="pw-strength" id="pwBars">
                                    <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                                </div>
                                <div class="pw-hints mt-2" id="pwHints">
                                    <div><i class="fas fa-circle small me-1"></i> At least 8 characters</div>
                                    <div><i class="fas fa-circle small me-1"></i> Contains uppercase and lowercase</div>
                                    <div><i class="fas fa-circle small me-1"></i> Contains a number</div>
                                </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" id="password_confirm" name="password_confirm" class="form-control" required placeholder="Confirm password">
                            <span class="input-group-text password-toggle" onclick="togglePwd('password_confirm', this)"><i class="fas fa-eye"></i></span>
                        </div>
                                <div id="matchInd" class="small mt-1 text-muted"><i class="fas fa-circle small me-1"></i> Passwords must match</div>
                    </div>

                            <button id="btn_register" type="submit" class="btn btn-primary w-100">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                Create Account
                            </button>
                </form>

                <div class="text-center mt-3 small-muted">
                    Already have an account? <a href="<?= site_url('/admin/login') ?>" class="link-muted">Login</a>
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
        function strengthScore(v){
            var s=0; if(!v) return 0; if(v.length>=8) s++; if(/[A-Z]/.test(v)&&/[a-z]/.test(v)) s++; if(/\d/.test(v)) s++; if(/[^A-Za-z0-9]/.test(v)) s++; return s; }
        document.addEventListener('DOMContentLoaded', function(){
            var p = document.getElementById('password');
            var pc = document.getElementById('password_confirm');
            var caps = document.getElementById('caps_reg');
            var bars = document.getElementById('pwBars');
            var hints = document.getElementById('pwHints');
            var match = document.getElementById('matchInd');
            var btn = document.getElementById('btn_register');
            function update(){
                var v = p ? p.value : ''; var sc = strengthScore(v);
                if(bars){ var children = bars.querySelectorAll('.bar'); children.forEach(function(b,i){ b.className='bar'; if(sc>=1) b.classList.add('on-1'); if(sc>=2) b.classList.add('on-2'); if(sc>=3) b.classList.add('on-3'); if(sc>=4) b.classList.add('on-4'); }); }
                if(hints){
                    var items = hints.querySelectorAll('div');
                    if(items[0]) items[0].classList.toggle('ok', v.length>=8);
                    if(items[1]) items[1].classList.toggle('ok', /[A-Z]/.test(v)&&/[a-z]/.test(v));
                    if(items[2]) items[2].classList.toggle('ok', /\d/.test(v));
                }
                if(pc && match){ var same = v && pc.value && v===pc.value; match.classList.toggle('text-success', same); match.classList.toggle('text-danger', !same && pc.value.length>0); match.innerHTML = (same?'<i class="fas fa-check-circle me-1"></i> Passwords match':'<i class="fas fa-times-circle me-1"></i> Passwords must match'); }
            }
            if(p){ p.addEventListener('input', update); p.addEventListener('keydown', function(e){ if(!caps) return; caps.classList.toggle('d-none', !e.getModifierState || !e.getModifierState('CapsLock')); }); p.addEventListener('keyup', function(e){ if(!caps) return; caps.classList.toggle('d-none', !e.getModifierState || !e.getModifierState('CapsLock')); }); }
            if(pc){ pc.addEventListener('input', update); }
            var form = document.forms[0];
            if(form){ form.addEventListener('submit', function(){ if(btn){ var sp=btn.querySelector('.spinner-border'); if(sp) sp.classList.remove('d-none'); btn.setAttribute('disabled','disabled'); } }); }
            update();
        });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
