<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Validation Library
 *	
 *	Functions for validating user entries against standard rules.
 *	
 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	Maintained by ClearSight Studio
 *
 * @package phpGenesis
 */

	if(!function_exists("required")) {
		function required($value) {
			trigger_error('required() is a legacy phpGenesis function.');
			return (bool)(is_string($value) && $value <> '');
		}
	}

	if(!function_exists("maxlength")) {
		function maxlength($value, $length) {
			trigger_error('maxlength() is a legacy phpGenesis function.');
			return (bool)(strlen($value) <= intval($length));
		}
	}

	if(!function_exists("minlength")) {
		function minlength($value, $length) {
			trigger_error('minlength() is a legacy phpGenesis function.');
			return (bool)(strlen($value) >= intval($length));
		}
	}

	if(!function_exists("int_range")) {
		function int_range($value, $min, $max, $allow_null) {
			trigger_error('int_range() is a legacy phpGenesis function.');
			$int = (int)$value;

			if ($allow_null == 1 && $value == "") { return true; }

			if ($int === 0 && !($value == "0")) {
				return false;
			}

			if ($int < $min || $int > $max) {
				return false;
			}

			return true;
		}
	}

	if(!function_exists("decimal")) {
		function decimal($value, $precision, $scale, $allow_null) {
			trigger_error('decimal() is a legacy phpGenesis function.');
			$float = (float)$value;

			if ($allow_null == 1 && $value == "") { return true; }

			if ($float == 0 && $value != "0") {
				return false;
			}

			$str = (string) $float;

			$pstr = preg_replace("/[^0-9]/", "", $str);
			if (strlen($pstr) > $precision) { return false; }

			$this_scale = 0;
			$decpos = strpos($str, ".");
			if ($decpos === false) {
				$this_scale = 0;
			} else {
				$this_scale = strlen($str)-$decpos-1;
			}
			if ($this_scale > $scale) { return false; }

			return true;
		}
	}

	if(!function_exists("enum")) {
		/**
		 * example: $enum_strings = cat:dog:mouse
		 * @param string $value
		 * @param string $enum_strings
		 * @return boolean
		 */
		function enum($value, $enum_strings) {
			if (in_array($value, explode(":", $enum_strings))) { 
				return $value;
			} else {
				return false;
			}
		}
	}

	if(!function_exists("valid_email")) {
		function valid_email($data, $strict = false) {
			if($strict) {
				$regex = '/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';
			} else {
				$regex = '/^([*+!.&#$�\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';
			}
			if(preg_match($regex, trim($data), $matches)) {
				return array($matches[1], $matches[2]);
			} else {
				return false;
			}
		}
	}

	if(!function_exists("valid_int")) {
		function valid_int($value, $allow_null = true) {
			if ($allow_null == true && $value == "") { return null; }
			$intval = (int)$value;
			if ((string)$intval == $value) {
				return $intval;
			} else { 
				return false;
			}
		}
	}

	if(!function_exists("valid_url")) {
		function valid_url($url, $absolute = FALSE) {
		  if ($absolute) {
			 return (bool)preg_match("
				/^                                                      # Start at the beginning of the text
				(?:ftp|https?):\/\/                                     # Look for ftp, http, or https schemes
				(?:                                                     # Userinfo (optional) which is typically
				  (?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*      # a username or a username and password
				  (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@          # combination
				)?
				(?:
				  (?:[a-z0-9\-\.]|%[0-9a-f]{2})+                        # A domain name or a IPv4 address
				  |(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\])         # or a well formed IPv6 address
				)
				(?::[0-9]+)?                                            # Server port number (optional)
				(?:[\/|\?]
				  (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})   # The path and query (optional)
				*)?
			 $/xi", $url);
		  } else {
			 return (bool)preg_match("/^(?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})+$/i", $url);
		  }
		}
	}

	/* NEW VALIDATION FUNTIONS */

	function val_required($value) {
		if ((is_string($value) && $value <> '')) {
			return $value;
		} else {
			return false;
		}
	}

	function val_maxlength($value, $length) {
		if (strlen($value) <= intval($length)) {
			return $value;
		} else {
			return false;
		}		
	}

	function val_minlength($value, $length, $allow_null = false) {
		if (($value == '' && $allow_null == true) || (strlen($value) >= intval($length))) {
			return $value;
		} else {
			return false;
		}
	}

	/**
	 * Returns a sanitzed email or FALSE
	 * @param mixed $value
	 * @param boolean $allow_null
	 * @return mixed
	 */
	function val_email($value, $allow_null = true) {
		if ($allow_null == 1 && $value == "") { return null; }
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Returns a sanitzed integer or FALSE
	 * @param mixed $value
	 * @param boolean $allow_null
	 * @return mixed
	 */
	function val_int($value, $allow_null = true) {
		if ($allow_null == 1 && $value == "") { return null; }
		return filter_var($value, FILTER_VALIDATE_INT);
	}

	/**
	 * Returns a sanitzed integer or FALSE
	 * @param mixed $value
	 * @param boolean $allow_null
	 * @return mixed
	 */
	function val_int_range($value, $min=null, $max=null, $allow_null = true) {
		if ($allow_null == 1 && $value == "") { return null; }
		$val = filter_var($value, FILTER_VALIDATE_INT);
		if ($val===false) return false;
		if ($min !== null && $val < $min) return false;
		if ($max !== null && $val > $max) return false;
		return $val;
	}

	/**
	 * Returns a sanitzed decimal or FALSE
	 * @param mixed $value
	 * @param boolean $allow_null
	 * @return mixed
	 */
	function val_decimal($value, $precision=20, $scale=6, $allow_null = true) {
		if ($allow_null == 1 && $value == "") { return null; }

		$float = (float)$value;

		if ($allow_null == 1 && $value == "") { return true; }

		if ($float == 0 && $value != "0") {
			return false;
		}

		$str = (string) $float;

		$pstr = preg_replace("/[^0-9]/", "", $str);
		if (strlen($pstr) > $precision) {
			settings('form', 'errormsg_val_decimal', 'The value entered has a precision greater than '.$precision.'.');
			return false;
		}

		$this_scale = 0;
		$decpos = strpos($str, ".");
		if ($decpos === false) {
			$this_scale = 0;
		} else {
			$this_scale = strlen($str)-$decpos-1;
		}
		if ($this_scale > $scale) { 
			settings('form', 'errormsg_val_decimal', 'The value entered has a scale greater than '.$scale.'.');
			return false;
		}

		return filter_var($value, FILTER_VALIDATE_FLOAT, array('flags'=>FILTER_FLAG_ALLOW_THOUSAND));
	}

	/**
	 * Returns a sanitzed decimal or FALSE
	 * @param mixed $value
	 * @param boolean $allow_null
	 * @return mixed
	 */
	function val_decimal_range($value, $min=null, $max=null, $allow_null = true) {
		if ($allow_null == 1 && $value == "") { return ""; }
		$val =filter_var($value, FILTER_VALIDATE_FLOAT, array('flags'=>FILTER_FLAG_ALLOW_THOUSAND));
		if ($val===false) return false;
		if ($min !== null && $val < $min) return false;
		if ($max !== null && $val > $max) return false;
		return $val;
	}

	/**
	 * Returns a sanitzed date or FALSE
	 * @param mixed $value
	 * @param boolean $allow_null
	 * @return mixed
	 */
	function val_date($value, $date_format=null, $allow_null = true) {
		if ($allow_null == 1 && $value == "") { return NULL; }
		$val = strtotime($value);
		if ($val === false) return false;
		if ($date_format === NULL) $date_format = "m/d/Y";
		return date($date_format, $val);
	}
	
	function val_strtotime($value) {
		if($value == "") return "";
		$date = strtotime($value);
		if($date) return $date;
		return NULL;
	}

	function val_zip($value) {
		$value = str_replace(" ", "", $value);
		if(preg_match("/^([0-9]{5})(-[0-9]{4})?$/i",$value)) {
			return $value;
		} else {
			return false;
		}
	}
	
	/**
	 *	Doesn't validate, but allows checking the value of a checkbox.
	 */
	function val_checkbox($value, $true = 1, $false = 0) {
		if($value == "yes" || $value == "on" || $value == "checked") {
			return $true;
		} else {
			return $false;
		}
	}
	
	/**
	 *	Doesn't validate anything. Just returns NULL if set to zero.
	 */
	function nullify_zero($value) {
		if($value) return $value;
		return NULL;
	}

	if(settings('form', 'errormsg_default') === NULL) settings('form', 'errormsg_default', 'This field is invalid.');

	if(settings('form', 'errormsg_val_required') === NULL) settings('form', 'errormsg_val_required', 'This field is required.');
	if(settings('form', 'errormsg_val_maxlength') === NULL) settings('form', 'errormsg_val_maxlength', 'This field did not meet the maximum length requirement.');
	if(settings('form', 'errormsg_val_minlength') === NULL) settings('form', 'errormsg_val_minlength', 'This field did not meet the minimum length requirement.');
	if(settings('form', 'errormsg_val_email') === NULL) settings('form', 'errormsg_val_email', 'Invalid email address.');
	if(settings('form', 'errormsg_val_int') === NULL) settings('form', 'errormsg_val_int', 'The value entered is not an integer.');
	if(settings('form', 'errormsg_val_int_range') === NULL) settings('form', 'errormsg_val_int_range', 'Not in required range.');
	if(settings('form', 'errormsg_val_decimal') === NULL) settings('form', 'errormsg_val_decimal', 'The value entered is not a decimal.');
	if(settings('form', 'errormsg_val_decimal_range') === NULL) settings('form', 'errormsg_val_decimal_range', 'Not in required range.');
	if(settings('form', 'errormsg_val_date') === NULL) settings('form', 'errormsg_val_date', 'Invalid date.');
	if(settings('form', 'errormsg_val_zip') === NULL) settings('form', 'errormsg_val_zip', 'Invalid zip code format. (##### or #####-####)');

?>