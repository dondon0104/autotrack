<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if (function_exists('config_item') && config_item('ENVIRONMENT') !== 'production'): ?>
<div style="position:fixed;left:8px;bottom:8px;padding:6px 10px;background:rgba(0,0,0,0.7);color:#fff;font-size:12px;border-radius:6px;z-index:9999;opacity:0.95">
	<strong>Dev:</strong>
	vendor=<?= (defined('APP_DIR') && file_exists(APP_DIR . 'vendor/autoload.php')) ? '<span style="color:#8ef">yes</span>' : '<span style="color:#f88">no</span>' ?>
	&nbsp;|&nbsp; base=<?= htmlspecialchars(config_item('base_url') ?: '', ENT_QUOTES, 'UTF-8') ?>
</div>
</div>
</div>
</body>
</html>
