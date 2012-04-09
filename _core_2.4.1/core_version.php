<?
	/**
	 *	Explanation of version number.
	 *
	 *	First number: Major release, indicates new libraries and changed workflow (e.g. ActiveRecord instead of db_library)
	 *	Second number: Minor release, indicates additional functions within libraries, larger changes to how they work.
	 *	Third number: Bugfixes only.
	 *	
	 */
	$beta = ""; if(function_exists("dev") && dev()) $beta = " BETA";
	define("CORE_VERSION", "2.4.1" . $beta);
	
	// CHANGELOG: Add a one-line version note below here on each release. Short and sweet.
	// Version 2.4.1: Bugfix to use cookies in addition to sessions for Social User Library to prevent CSRF errors
	// Version 2.4.0: Added a new forms library
	// Version 2.3.5: Maintenance version for CE App
	// Version 2.3.4: Small changes to forms library
	// Version 2.3.2: Maintenance update with a few new functions
	// Version 2.3.1: Maintenance update to fix error handling
	// Version 2.3.0: Rewrote the session library to allow database sessions. Several smaller updates, bugfixes.
	// Version 2.2.1: Minor, non-essential release. Only deployed to certain servers. Added folder_size() for example.
	// Version 2.2.0: Multiple small functions added. ActiveRecord extended. A few small bugs fixed.
	// Version 2.1.0: Added layouts, ActiveRecord phpGenesisModel class, documentation, upgraded ActiveRecord class.
	// Version 2.0.0: Added ActiveRecord, version pinging, CSRF security library
?>