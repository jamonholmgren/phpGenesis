<?
	// This is the default home page.
	// meta title |.....................................................................| 70 characters max
	meta("title", "phpGenesis Home Page");
	// meta description |.....................................................................................................................................................| 150 characters max
	meta("description", "Welcome to our website!");
	
	/* For setting up the user library:
		load_library("user");
		user_library_setup("admin@example.com", "admin@example.com", "somepassword");
	*/
?>
<? layout_open("default"); ?>
	<? layout_section("header"); ?>
		<h1>Welcome to phpGenesis!</h1>
	<? layout_section_close(); ?>
	<? layout_section("content"); ?>
		<p>
			This is the default home page, located in /pages/home.php. You can change this file
			or specify a different home page in your config.php file.
		</p>
	<? layout_section_close(); ?>
	<? layout_section("footer"); ?>
		<p>phpGenesis version <?=core_version()?></p>
	<? layout_section_close(); ?>
<? layout_close(); ?>