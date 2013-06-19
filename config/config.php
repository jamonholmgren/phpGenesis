<?
	/**
	 * phpGenesis Config
	 *
	 * @author Jamon Holmgren and the ClearSight Team
	 * @link http://www.phpgenesis.com
	 *
	 */

	// Application Version
	settings('app', 'version', "0.0.1");


	/**
	 * Preloaded Libraries
	 *
	 * (you can load them on the fly too using load_library("library_name"))
	 */
	settings('preload', 'libraries', array(
		'segments', 
		'input', 
		'seo', 
		'security', 
		"layout", 
		"activerecord"
	));


	// Website Name
	settings("site", "name", "phpGenesis");


	// Database Configuration db db_name value
	settings('db', 'enabled', false);								// db connection enabled (true|false)
	if(dev()) {
		settings('db', 'log_queries', true);						// log all queries
		settings('db', 'host', "localhost"); 						// localhost or mysql.example.com
		settings('db', 'username', "root");							// root or other
		settings('db', 'password', "samplepassword");		// password
		settings('db', 'database', "phpgenesis");				// database name
	} else {
		settings('db', 'log_queries', false);						// log all queries
		settings('db', 'host', "localhost"); 						// localhost or mysql.example.com
		settings('db', 'username', "root");							// root or other
		settings('db', 'password', "samplepassword");		// password
		settings('db', 'database', "phpgenesis");				// database name
	}
	settings("activerecord", "models", APP_FOLDER . '/models/');

	
	// Default pages
	settings('pages', 'home_page', 'home');								// usually 'home'
	settings('pages', '404_page', '404');									// usually '404'
	settings('pages', 'access_denied', 'access-denied');	// usually 'access-denied'
	settings('pages', 'maintenance_page', 'maintenance');	// usually 'maintenance'


	// Input sanitizer - sanitizes data when input is requested
	// Available options: 'filter' (default), 'htmlpurifier' (requires thirdparty plugin), or 'simple' (not recommended)
	settings("input", "sanitizer", "filter");
	settings("input", "htmlsanitizer", "htmlpurifier");
	settings("input", "config", array(
		'HTML.Doctype' => 'HTML 4.01 Strict', // HTML 5 not supported yet?
		'Attr.EnableID' => true,
	));


	// form library
	settings("form", "submit_unchecked_checkboxes", true);		// checkboxes, if unchecked, will still submit if this is TRUE.
	
	
	// Cookies (load the "cookie" library to use)
	settings('cookie', 'path', '/');								// usually '/'
	settings('cookie', 'domain', '');								// usually ''
	settings('cookie', 'default_expire', 30);				// usually 30


	// Session (load the "session" library to use)
	settings('session', 'name', 'PHPGENESIS');			// change this
	settings('session', 'timeout', 3600); 					// usually 3600
	settings("session", "allowed_referrers", array(
		/* "username.rpxnow.com", // For Janrain Engage */
	));
	// settings('session', 'save_path', BASE_FOLDER . "/../sessions"); // optional, untested
	
	// SEO settings
	settings("seo", "track_referrer", TRUE); 				// Track the URL the visitor initially came from (if any). Use seo_get_referrer() to access.
	
	// reCaptcha
	settings('recaptcha', 'publickey', '6Ld4JwgAAAAAAKLrtWx_mR4DqzioDTfW7ZBr9tka');
	settings('recaptcha', 'privatekey', '6Ld4JwgAAAAAAO2W5MC8VXkPuy7tUEhuY7f_h_O8');


	// Simple CMS
	settings('simplecms', 'table', 'simplecms_pages');	// database table that has the simplecms info
	
	
	// Simplelogin
	settings("simplelogin", "adminusers", array("username" => "password"));
	settings("simplelogin", "adminroot", "admin");
	settings("simplelogin", "adminlogin", "admin");
	settings("simplelogin", "adminhome", ADMIN_PATH . "/dashboard");
	settings("simplelogin", "adminusernamefield", "username"); // formname_fieldname (login_username)
	settings("simplelogin", "adminpasswordfield", "password"); // formname_fieldname (login_password)
	// load_library("simplelogin"); // Can also be put as a preloaded library
	
	
	// authorize.net settings
	settings("authorize", "login", "");
	settings("authorize", "transkey", "");
	settings("authorize", "test", true);
	
	// User Library
	settings('user', 'levels', array("Admin", "Manager", "User", "Limited"));
	
	// Social Login through Janrain Engage
	settings("social_login", "api_key", "xxxxxxxxx");
	settings("social_login", "username", "janrainusername");
	settings("social_login", "pro", FALSE);

	// Notification Settings
	settings("notice", "html", "<div class='notice %type%'>%notice%</div>");
?>