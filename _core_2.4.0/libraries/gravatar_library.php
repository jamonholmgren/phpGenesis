<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Gravatar Library
 *	
 *	Miscellaneous, but very useful, functions. No phpGenesis-dependent functions are allowed in this library.
 *	
 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	Maintained by ClearSight Studio
 *
 * @package phpGenesis
 */
	
	/**
	 * Generates a URL for the user's avatar based on a hash of their email
	 * 
	 * @return string
	 */
	if(!function_exists("gravatar_image_url")) {
		function gravatar_image_url($email, $size = NULL) {
			if($size === NULL) $size = 80;
			// d = Default, may be defined as an image on your server or one of several provided by Gravatar
			// r = Rating
			$url = "https://secure.gravatar.com/avatar/" . md5(strtolower(trim($email))) . ".jpg?s=" . $size . "&d=mm&r=g";
			return $url;
		}
	}
?>