<?
	
	/**
	 * Check if site is on internal domain
	 */
	if(!function_exists("dev")) {
		function dev() {
			return (strpos(BASE_URL, ".int.") !== FALSE || strpos(BASE_URL, ".site") !== false); // checks the URL to see if ".int." is contained. You can check server IP, BASE_FOLDER, whatever.
		}
	}

	/**
	 * Configure initial directories and URLs
	 */
	define("MAIN_FOLDER", dirname(__FILE__));	// Path to app and core folders here - no trailing slash
	define("BASE_FOLDER", MAIN_FOLDER);
	
	/**
	 * Get the Request Protocol
	 */
	function protocol() {
		if($_SERVER['HTTPS'] == 'on') {
			$protocol = "https://";
		} else {
			$protocol = "http://";
		}
		return $protocol;
	}
	
	
	/**
	 * Restricts IP addresses. 
	 */
	$restrict_ip = false;	 // Set it to false to disable IP restriction
	$allowed_ips = array(
		"xxx.xxx.xxx.xxx",
	);
	if($restrict_ip && !in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) die("Coming soon!");

	define("PLUGINS_FOLDER", MAIN_FOLDER . "/_plugins"); // If you don't have app-specific plugins use "/../dev.phpgenesis.com/_plugins
	define('APP_FOLDER', MAIN_FOLDER . "/app");
	define('UPLOADS_FOLDER', MAIN_FOLDER . "/uploads");	
	define('CONFIG_FOLDER', MAIN_FOLDER . "/config");
	
	define('BASE_URL', "http://" . $_SERVER['HTTP_HOST']); // Do not use a trailing slash
	define('APP_URL', BASE_URL . "/app");
	define('UPLOADS_URL', BASE_URL . "/uploads");
	define('APP_ID', "change-this-id-to-some-random-string");


	define('CORE_FOLDER', MAIN_FOLDER . "/_core");
	
	/**
	 * Define Application Environment
	 * Possible values: development, testing, production. maintenance
	 *
	 * development = strict, testing = warnings only, production = none, maintenance = redirects to a maintenance page
	 */
	define('APP_STATUS', 'development');	
	
	/**
	 * Date/Time Settings
	 */
	date_default_timezone_set("America/Los_Angeles");
		
	/**
	 * WKHTML Configuration
	 */
	// define('WKHTMLTOPDF', 'xvfb-run -a -s "-screen 0 640x480x16" wkhtmltopdf --dpi 200');	// Use on Linux Servers
	// define('WKHTMLTOPDF', '\"c:\\Program Files\\wkhtmltopdf\\wkhtmltopdf\"'); 							// Use on Windows Servers
	
	/**
	 * Email
	 */
	define('EMAIL', "test@clearsightstudio.com");	
	
	

?>