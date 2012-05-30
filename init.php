<?
	/**
	 * Restricts IP addresses. 
	 */
	$restrict_ip = false;	 // Set it to false to disable IP restriction
	$allowed_ips = array(
		"xxx.xxx.xxx.xxx",
	);
	if($restrict_ip && !in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) die("Coming soon!");
	
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
	 * Configure initial directories and URLs
	 */
	define("MAIN_FOLDER", dirname(__FILE__));	// No trailing slash
	define("BASE_FOLDER", MAIN_FOLDER);
	define("CONFIG_FOLDER", MAIN_FOLDER . "/config");
	define('BASE_URL', protocol() . $_SERVER['HTTP_HOST']); // Do not use a trailing slash
	define('APP_URL', BASE_URL . "/app");
	define('UPLOADS_URL', BASE_URL . "/uploads");
	
	/**
	 * Check if site is on internal domain
	 */
	function dev() {
		return (strpos(BASE_URL, ".int.") !== FALSE); // checks the URL to see if ".int." is contained. You can check server IP, BASE_FOLDER, whatever.
	}
	
	if(dev()) {
		define('CORE_FOLDER', MAIN_FOLDER . "/../dev.phpgenesis.com/_core_dev"); // For csd_sites "/../dev.phpgenesis.com/_core_x.x.x"
	} else {
		define('CORE_FOLDER', MAIN_FOLDER . "/../dev.phpgenesis.com/_core_2.4.0"); // For csd_sites "/../dev.phpgenesis.com/_core_x.x.x"
	}
	define("PLUGINS_FOLDER", MAIN_FOLDER . "/_plugins"); // If you don't have app-specific plugins use "/../dev.phpgenesis.com/_plugins
	define('APP_FOLDER', MAIN_FOLDER . "/app");
	define('UPLOADS_FOLDER', BASE_FOLDER . "/uploads");
	
	define('APP_ID', "changetorandomstring");
	
	/**
	 * Define Application Environment
	 * Possible values: development, testing, production. maintenance
	 *
	 * development = strict, testing = warnings only, production = none, maintenance = redirects to a maintenance page
	 */
	define('APP_STATUS', 'testing');	
	
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