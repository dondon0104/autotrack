<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
 * Required to execute neccessary functions
 */
require_once SYSTEM_DIR . 'kernel/Registry.php';
require_once SYSTEM_DIR . 'kernel/Routine.php';

/**
 * LavaLust BASE URL of your APPLICATION
 */
define('BASE_URL', config_item('base_url'));

/**
 * Composer (Autoload)
 */
if ($composer_autoload = config_item('composer_autoload'))
{
	if ($composer_autoload === TRUE)
	{
		if (file_exists(APP_DIR . 'vendor/autoload.php')) {
			require_once(APP_DIR . 'vendor/autoload.php');
		} else {
			// If app/vendor/autoload.php is missing, don't abort the whole request by showing a 404.
			// Some deployments don't install Composer packages during build or the vendor folder may be
			// excluded. Log the situation and continue — the framework can still work without the
			// Composer autoloader for many routes.
			$req = $_SERVER['REQUEST_URI'] ?? 'unknown';
			error_log("[LavaLust] Composer autoload missing. APP_DIR=" . APP_DIR . " vendor/autoload.php exists?=" . (file_exists(APP_DIR . 'vendor/autoload.php') ? 'yes' : 'no') . " REQUEST_URI=" . $req);
			// keep going without Composer autoload
			// In development show a visible, non-fatal banner to help developers diagnose missing dependencies.
			try {
				$env = strtolower(config_item('ENVIRONMENT')) ?: '';
			} catch (Throwable $e) {
				$env = '';
			}
			if ($env === 'development' && php_sapi_name() !== 'cli') {
				// Lightweight visual hint — avoid breaking non-HTML responses: wrap in HTML comment if content type unknown.
				$msg = "Composer autoload missing for app/vendor/autoload.php — run `composer install --working-dir=app` to install dependencies.";
				// Best-effort: print small fixed-position banner for HTML responses
				echo "\n<!-- " . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . " -->\n";
				echo "<style>#ll-composer-missing{position:fixed;right:12px;bottom:12px;z-index:99999;padding:10px 14px;background:#fff3cd;border:1px solid #ffeeba;border-radius:6px;color:#856404;font-family:Arial,Helvetica,sans-serif;font-size:13px;box-shadow:0 2px 6px rgba(0,0,0,0.08)}</style>";
				echo "<div id='ll-composer-missing'>Composer autoload missing — run <code>composer install --working-dir=app</code></div>\n";
			}
		}
	}
	elseif (file_exists($composer_autoload))
	{
		require_once($composer_autoload);
	}
	else
	{
		// Specific path logging: log which $composer_autoload value caused this branch and continue.
		// Treat invalid/absent custom paths as a non-fatal issue — the framework will continue
		// to use its own autoloading and other components where possible.
		error_log("[LavaLust] Composer autoload path invalid. composer_autoload=" . var_export($composer_autoload, TRUE) . " REQUEST_URI=" . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
		// continue without Composer autoload
		// Display a development banner if running in development environment
		try {
			$env = strtolower(config_item('ENVIRONMENT')) ?: '';
		} catch (Throwable $e) {
			$env = '';
		}
		if ($env === 'development' && php_sapi_name() !== 'cli') {
			echo "\n<!-- composer_autoload invalid: " . htmlspecialchars(var_export($composer_autoload, TRUE)) . " -->\n";
			echo "<style>#ll-composer-missing{position:fixed;right:12px;bottom:12px;z-index:99999;padding:10px 14px;background:#fff3cd;border:1px solid #ffeeba;border-radius:6px;color:#856404;font-family:Arial,Helvetica,sans-serif;font-size:13px;box-shadow:0 2px 6px rgba(0,0,0,0.08)}</style>";
			echo "<div id='ll-composer-missing'>Composer autoload path invalid: " . htmlspecialchars($composer_autoload) . " — update `app/config/config.php` or run composer install.</div>\n";
		}
	}
}

/**
 * Instantiate the Benchmark class
 */
$performance = load_class('performance', 'kernel');
$performance->start('lavalust');

/**
 * Deployment Environment
 */
switch (strtolower(config_item('ENVIRONMENT')))
{
	case 'development':
		_handlers();
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'production':		
		ini_set('display_errors', 0);
		error_reporting(0);
		_handlers();
	break;

	default :
		_handlers();
		error_reporting(-1);
		ini_set('display_errors', 1);
}

/**
 * Error Classes to show errors
 *
 * @return void
 */
function _handlers()
{
	set_error_handler('_error_handler');
	set_exception_handler('_exception_handler');
	register_shutdown_function('_shutdown_handler');
}

/**
 * Instantiate the config class
 */
$config = load_class('config', 'kernel');

/**
 * Instantiate the logger class
 */
$logger = load_class('logger', 'kernel');

/**
 * Instantiate the security class for xss and csrf support
 */
$security = load_class('security', 'kernel');

/**
 * Instantiate the Input/Ouput class
 */
$io = load_class('io', 'kernel');

/**
 * Instantiate the Language class
 */
$lang = load_class('lang', 'kernel');

/**
 * Load BaseController
 */
require_once SYSTEM_DIR . 'kernel/Controller.php';

/**
 * Instantiate the routing class and set the routing
 */
$router = load_class('router', 'kernel', array(new Controller));
require_once APP_DIR . 'config/routes.php';

/**
 * Instantiate LavaLust Controller
 *
 * @return object
 */
function lava_instance()
{
  	return Controller::instance();
}
$performance->stop('lavalust');

// Handle the request
// Resolve URL robustly using PATH_INFO, then REQUEST_URI, then PHP_SELF fallback.
$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '';

// Preferred: PATH_INFO (set by webserver when using index.php/segment)
$resolved = '';
if (!empty($_SERVER['PATH_INFO'])) {
	$resolved = $_SERVER['PATH_INFO'];
} else {
	// REQUEST_URI contains the path + query — strip query
	$req = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
	$script = $_SERVER['SCRIPT_NAME'] ?? '';

	// If request starts with script name, strip it
	if ($script && strpos($req, $script) === 0) {
		$resolved = substr($req, strlen($script));
	} else {
		// Try stripping directory of script (useful when index.php is in web root)
		$script_dir = rtrim(dirname($script), '\\/');
		if ($script_dir !== '' && $script_dir !== '/' && strpos($req, $script_dir) === 0) {
			$resolved = substr($req, strlen($script_dir));
		} else {
			// Fallback to PHP_SELF subtraction as last resort
			$php_self = $_SERVER['PHP_SELF'] ?? '';
			if ($php_self && strpos($php_self, $script) === 0) {
				$resolved = str_replace($script, '', $php_self);
			} else {
				$resolved = $req;
			}
		}
	}
}

$url = $router->sanitize_url($resolved ?: '/');
error_log("[LavaLust] Resolved URL='" . $url . "' METHOD=" . $method . " SCRIPT_NAME='" . ($_SERVER['SCRIPT_NAME'] ?? '') . "' PHP_SELF='" . ($_SERVER['PHP_SELF'] ?? '') . "' REQUEST_URI='" . ($_SERVER['REQUEST_URI'] ?? '') . "' PATH_INFO='" . ($_SERVER['PATH_INFO'] ?? '') . "'");
$router->initiate($url, $method);
?>