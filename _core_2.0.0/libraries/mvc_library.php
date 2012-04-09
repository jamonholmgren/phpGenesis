<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

// mvc_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	Nothing for now

	/***
	/*ALLOWS CALLING A PHP FUNCTION THROUGH THE URL
	/*Built by Michael Berkompas
	/*
	/*Instructions:
	/*Include in your page above every other piece of code
	/*and create a function to be called from the url by
	/*preceding its name with an "_public_" string.
	/*To call this function from the url just insert its
	/*name after the page without an underscore.
	/*Any uri segments you have after the function name will
	/*be passed in as variables in the order they appear.
	/*
	/*This function works just like CodeIgniter
	/*
	/*Last Edited: 11-30-2009
	/**/
	
	load_library("segments");
	if(function_exists("_public_". segment(0))) {
		if(!segment(0) == NULL) {
			$var_array = explode('/', segments_query());
			unset ($var_array[0]);
			call_user_func_array("_public_" . segment(0),$var_array);
			die();
		}
	} 
	
	abstract class controller {
		function controller() {
			$method = segment(0);
			if ($method <> '' && substr($method,0,1) <> '_' && method_exists(get_class($this), $method)) {
				$this->$method();
			} elseif (method_exists($this, 'index')) {
				$this->index();
			}
		}
	}

?>