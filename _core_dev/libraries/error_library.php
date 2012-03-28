<?
/**
 *	Error Library
 *	
 *	This library is loaded manually by the core to handle errors.
 *	
 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	Maintained by ClearSight Studio
 *	
 *	@package phpGenesis
 */
	
	/**
	 *	Handles exceptions by returning a print_pre() on the exception(s)
	 *	
	 *	@return string
	 */
	if(!function_exists("exception_handler")) {
		function exception_handler($exception) {
			if(is_object($exception)) {
				$ar = (array)$exception;
				try {
					print_pre($ar);
				} catch(Exception $e) {
					echo "<pre>";
					print_r($ar);
					echo "</pre>";
				}
			} else {
				echo "Unknown exception. Here's a print_pre:";
				// print_pre($exception);
				echo "<pre>";
				print_r($exception);
				echo "</pre>";
			}
		}
	}
	
	/**
	 *	Sets the exception handler before page load.
	 *	
	 *	@return string
	 */
	if(!function_exists("phpgenesis_set_exception_handler")) {
		function phpgenesis_set_exception_handler() {
			set_exception_handler("exception_handler");
		}
		register_hook("before_page_load", "phpgenesis_set_exception_handler");
	}

?>