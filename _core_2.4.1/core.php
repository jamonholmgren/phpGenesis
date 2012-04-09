<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com
	$GLOBALS['memory']['start'] = memory_get_usage(false);
	$GLOBALS['memory']['true_start'] = memory_get_usage(true);

	// phpGenesis core version.
	include(CORE_FOLDER . "/core_version.php");
	
	ob_start();
	
	// all the magic here
	define("CORE_LOADED", true);
	
	// Change site behavior based on the application's status
	$route_to_maintenance = false;
	switch (APP_STATUS) {
		case 'development':
			error_reporting(E_ALL | E_STRICT);
			ini_set('display_errors','On');
			break;
		case 'testing':
			// Leave it as the server's default
			// error_reporting(E_WARNING);
			// ini_set('display_errors','On');
			break;
		case 'maintenance':
			// route visitors to a maintenance page, if it's set
			$route_to_maintenance = true;
			break;
		case 'production':		
		default:
			ini_set('display_errors','Off');
	}
	
	// load core libraries
	include(CORE_FOLDER . "/libraries/core_library.php");
	include(CORE_FOLDER . "/libraries/loader_library.php");
	load_library('hooks');
	
	// load core configs
	if(file_exists(CORE_FOLDER . "/core_config.php")) include_once(CORE_FOLDER . "/core_config.php");

	// load server configs 
	if(file_exists(CORE_FOLDER . "/../server_config.php")) include_once(CORE_FOLDER . "/../server_config.php");

	// load app configs
	if(defined("CONFIG_FOLDER")) {
		if(file_exists(CONFIG_FOLDER . "/config.php")) include_once(CONFIG_FOLDER . "/config.php");
		if(file_exists(CONFIG_FOLDER . "/routes.php")) include_once(CONFIG_FOLDER . "/routes.php");
	}
	
	// Backwards compatibility
	if(function_exists("app_config")) app_config();
		
	// phpGenesis version
	settings('core', 'version', CORE_VERSION);
	
	// load other required libraries
	load_library("misc");
	load_library('resources');
	load_library("error");

	// preload custom libraries per /config.php
	if(is_array(settings('preload', 'libraries'))) {
		foreach(settings('preload', 'libraries') as $library) {
			load_library($library);
		}
	}
	
	// Pings 1 out of 100 times the core is loaded OR if there is ?ping-version=true in the url
	if(rand(0, 100) == 1 || $_GET['ping-version'] == "true") register_javascript("core-ping", "http://ping.int.devcsd.com/ping/version.js?base_url=" . urlencode(BASE_URL) . "&ver=" . urlencode(core_version()) . "&app=" . urlencode(app_version()) . "&server_ip=" . urlencode($_SERVER[SERVER_ADDR]));
	
	// find correct page/module to load based on URL and include it
	call_hook('route');
	$request_array = explode("?", _get_request_uri());
	
	if($route_to_maintenance) {
		route_url(settings('pages', 'maintenance_page'));
	} else {
		route_url($request_array[0]); 
	}
	
	call_hook('after_route');
	
	call_hook('before_ob_flush');
	ob_end_flush();
	call_hook('after_ob_flush');
	
	call_hook('before_shutdown');
?>