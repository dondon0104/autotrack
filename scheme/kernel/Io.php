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
* ------------------------------------------------------
*  Class IO
* ------------------------------------------------------
 */
Class Io {

	/**
	 * If CSRF Protection is enables, csrf_verify() will
	 * run
	 *
	 * @var boolean
	 */
	private $_enable_csrf = FALSE;

	/**
	 * Securty instance
	 *
	 * @var class
	 */
	private $security;

	/**
	 * Status Code
	 *
	 * @var int
	 */
	private $status_code;

	/**
	 * Request Headers
	 *
	 * @var array
	 */
    private $headers = [];

	/**
	 * Content
	 *
	 * @var mixed
	 */
    private $content;

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		/**
		 * Load Security Instance
		 *
		 * @var class
		 */
		$this->security = load_class('Security', 'kernel');

		/**
		 * Check CSRF Protection if enabled
		 *
		 * @var boolean
		 */
		$this->_enable_csrf	= (config_item('csrf_protection') === TRUE);

		/**
		 * Check CSRF Protection
		 *
		 * @var
		 */
		if ($this->_enable_csrf === TRUE)
		{
			$this->security->csrf_validate();
		}
	}

	/**
	* POST Variable
	*
	* @param  string $index
	* @param  mixed  $default
	* @return mixed
	*/
	public function post($index = NULL, $default = '')
	{
		if ($index === NULL) {
			return !empty($_POST) ? (array) $_POST : [];
		}
		return isset($_POST[$index]) ? $_POST[$index] : $default;
	}

	/**
	* GET Variable
	*
	* @param  string $index
	* @param  mixed  $default
	* @return mixed
	*/
	public function get($index = NULL, $default = '')
	{
		if ($index === NULL) {
			return !empty($_GET) ? (array) $_GET : [];
		}
		return isset($_GET[$index]) ? $_GET[$index] : $default;
	}

	/**
	 * POST and GET
	 *
	 * @param string $index
	 * @return string
	 */
	public function post_get($index = NULL, $default = NULL)
	{
		$output = $this->post($index, NULL);
		if ($output !== NULL) return $output;
		return $this->get($index, $default);
	}

		/**
	 * GET and POST
	 *
	 * @param string $index
	 * @return string
	 */
	public function get_post($index = NULL, $default = NULL)
	{
		$output = $this->get($index, NULL);
		if ($output !== NULL) return $output;
		return $this->post($index, $default);
	}

	/**
	* Cookie Variable
	*
	* @param string $index
	* @param mixed  $default
	* @return mixed
	*/
	public function cookie($index = NULL, $default = NULL)
	{
		if ($index === NULL) {
			return !empty($_COOKIE) ? (array) $_COOKIE : [];
		}
		return isset($_COOKIE[$index]) ? $_COOKIE[$index] : $default;
	}

	/**
	 * Set cookie in your application
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $expiration
	 * @param array $options
	 * @return void
	 */
	public function set_cookie($name, $value = '', $expiration = 0, $options = array())
	{
		//list of defaults
		$lists = array('prefix', 'path', 'domain', 'secure', 'httponly', 'samesite');

		//hold options elements
		$arr = array();

		if(is_array($options))
		{
			if(count($options) > 0)
			{
				foreach($options as $key => $val)
				{
					if(isset($options[$key]) && $options[$key] != 'expiration')
					{
						$arr[$key] = $val;
					} else {
						$arr[$key] = config_item('cookie_' . $key);
					}
					$pos = array_search($key, $lists);
					unset($lists[$pos]);
				}
			}
		}

		if(! is_numeric($expiration) || $expiration < 0)
		{
			$arr['expiration'] = 1;
		} else {
			$arr['expiration'] =  ($expiration > 0) ? time() + $expiration : 0;
		}

		foreach($lists as $key)
		{
			$arr[$key] = config_item('cookie_' . $key);
		}

		setcookie($arr['prefix'].$name, $value,
			array(
				'expires' => $arr['expiration'],
				'path' => $arr['path'],
				'domain' => $arr['domain'],
				'secure' => (bool) $arr['secure'],
				'httponly' => (bool) $arr['httponly'],
				'samesite' => $arr['samesite']
			));
	}

	/**
	* Server
	*
	* @param string $index
	* @param mixed  $default
	* @return mixed
	*/
	public function server($index = NULL, $default = NULL)
	{
		if ($index === NULL) {
			return !empty($_SERVER) ? (array) $_SERVER : [];
		}
		return isset($_SERVER[$index]) ? $_SERVER[$index] : $default;
	}

	/**
	 * Method
	 *
	 * @param boolean $upper	Whether to return in upper or lower case
	 *				(default: FALSE)
	 * @return string
	 */
	public function method($upper = FALSE)
	{
		return ($upper)
			? strtoupper($this->server('REQUEST_METHOD'))
			: strtolower($this->server('REQUEST_METHOD'));
	}

	/**
	 * Get IP Address
	 *
	 * @return string
	 */
	public function ip_address() {
		$trustedHeaders = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_REAL_IP'];

		foreach ($trustedHeaders as $header) {
			if (isset($_SERVER[$header]) && filter_var($_SERVER[$header], FILTER_VALIDATE_IP)) {
				return $_SERVER[$header];
			}
		}

		// Fallback to REMOTE_ADDR if no trusted headers found
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Validate IP Address
	 *
	 * @param	string	$ip	IP address
	 * @param	string	$which	IP protocol: 'ipv4' or 'ipv6'
	 * @return	boolean
	 */
	public function valid_ip($ip, $which = '')
	{
		switch (strtolower($which))
		{
			case 'ipv4':
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6':
				$which = FILTER_FLAG_IPV6;
				break;
			default:
				$which = 0;
				break;
		}

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $which);
	}

	/**
	 * Is Ajax
	 *
	 * @return boolean
	 */
	public function is_ajax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	/**
	 * Set Status Code
	 *
	 * @param int $status_code
	 * @return void
	 */
	public function set_status_code($status_code) {
        $this->status_code = $status_code;
    }

	/**
	 * Add header
	 *
	 * @param mixed $name
	 * @param string $value
	 * @return void
	 */
	public function add_header($name, $value) {
		if(is_array($name)) {
			foreach($name as $key => $value) {
				$this->headers[$key] = $value;
			}
		} else {
			$this->headers[$name] = $value;
		}
    }

	/**
	 * Set Content
	 *
	 * @param mixed $content
	 * @return void
	 */
    public function set_content($content) {
        $this->content = $content;
    }

	/**
	 * HTML Content
	 *
	 * @param mixed $content
	 * @return mixed
	 */
	public function set_html_content($content) {
        $this->add_header('Content-Type', 'text/html');
        return $this->set_content($content);
    }

	/**
	 * Send Response
	 *
	 * @return void
	 */
    public function send() {
        http_response_code($this->status_code);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->content;
    }

	/**
	 * Json Encode
	 *
	 * @param string $data
	 * @return void
	 */
    public function send_json($data) {
        $this->add_header('Content-Type', 'application/json');
        $this->set_content(json_encode($data));
        $this->send();
    }
}

?>
