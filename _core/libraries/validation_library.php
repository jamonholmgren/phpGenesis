<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Validation Library
 *	
 *	Functions for validating user entries against standard rules.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *
 * @package phpGenesis
 */

	/**
	 *	Legacy function that checks if the passed value is a string and not an empty string
	 *	
	 *	Use val_required() instead
	 *	
	 *	@return bool
	 */
	if(!function_exists("required")) {
		function required($value) {
			trigger_error('required() is a legacy phpGenesis function.');
			return (bool)(is_string($value) && $value <> '');
		}
	}
	
	/**
	 *	Legacy function that checks if a string is shorter than the passed length
	 *	
	 *	Use val_maxlength() instead
	 *	
	 *	@return bool
	 */
	if(!function_exists("maxlength")) {
		function maxlength($value, $length) {
			trigger_error('maxlength() is a legacy phpGenesis function.');
			return (bool)(strlen($value) <= intval($length));
		}
	}
	
	/**
	 *	Legacy function that checks if a string is longer than the passed length
	 *	
	 *	Use val_minlength() instead
	 *	
	 *	@return bool
	 */
	if(!function_exists("minlength")) {
		function minlength($value, $length) {
			trigger_error('minlength() is a legacy phpGenesis function.');
			return (bool)(strlen($value) >= intval($length));
		}
	}

	/**
	 *	Legacy function that checks if an int is between the passed range
	 *	
	 *	Use val_int_range() instead
	 *	
	 *	@return bool
	 */
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
	
	/**
	 *	Legacy Function that checks if a passed value is decimal(?)
	 *	
	 *	Use val_decimal() instead
	 *	
	 *	@return bool
	 */
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

	/**
	 * example: $enum_strings = cat:dog:mouse
	 * @param string $value
	 * @param string $enum_strings
	 * @return boolean
	 */
	if(!function_exists("enum")) {
		function enum($value, $enum_strings) {
			if (in_array($value, explode(":", $enum_strings))) { 
				return $value;
			} else {
				return false;
			}
		}
	}
	
	/**
	 *	Checks if a passed email address follows standard email address format. Returns email if valid, FALSE if not.
	 *	
	 *	@return mixed
	 */
	if(!function_exists("valid_email")) {
		function valid_email($data, $strict = false) {
			return filter_var('bob@example.com', FILTER_VALIDATE_EMAIL);
		}
	}
	
	/**
	 *	Checks if a passed value is a valid int
	 *	
	 *	@return int
	 */
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
	
	/**
	 *	Checks if a passed URL follows standard formatting rules
	 *	
	 *	@return bool
	 */
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

	/**
	 *	Checks that the passed value is a string and is not empty. Returns $value.
	 *	
	 *	@return string
	 */
	function val_required($value) {
		if ((is_string($value) && $value <> '')) {
			return $value;
		} else {
			return false;
		}
	}
	
	/**
	 *	Checks if the passed value is shorter than the passed length
	 *	
	 *	@retrun string
	 */
	function val_maxlength($value, $length) {
		if (strlen($value) <= intval($length)) {
			return $value;
		} else {
			return false;
		}		
	}
	
	/**
	 *	Checks if the passed value is longer than the passed length
	 *	
	 *	@return string
	 */
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
	
	/**
	 *	Runs strtotime() on the passed value. Returns empty string if the passed-in value is an empty string. Returns null if unable to convert to a date.
	 *	
	 *	@return string
	 */
	function val_strtotime($value) {
		if($value == "") return "";
		$date = strtotime($value);
		if($date) return $date;
		return NULL;
	}
	
	/**
	 *	Checks if the passed value follows standard zip code rules
	 *	
	 *	@return string
	 */
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
	 *	
	 *	@return bool
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
	 *	
	 *	@return string
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