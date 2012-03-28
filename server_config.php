<?php
	/**
	 * This file contains server-specific information. It should be kept in a shared core's base folder.
	 *
	 * For example, if your shared core is in /home/username/phpgenesis/_core_1.3/, you should keep this file
	 * in /home/username/phpgenesis/server_config.php.
	 *
	 * It is not used if kept in the app's base folder.
	 *
	 * This is so you can have shared cores in a server that have server-wide settings.
	 *
	 */
	/*
		settings("memory", "threshold", 1024);
		settings("memory", "email", "jamon@clearsightdesign.com");
		settings("memory", "display", true);
		load_library("memory");
		register_hook("write_footer", "memory_monitor", "memory");
	*/

	// Set current timezone.
	date_default_timezone_set("America/Los_Angeles");
		
?>