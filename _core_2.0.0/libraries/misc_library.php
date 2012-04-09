<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

// misc_library last edited 12/11/2009 by Jamon Holmgren
//	PLEASE NOTE: functions in this library can not be phpGenesis-dependent! Only PHP framework-agnostic functions.

// TO-DO
//	Nothing for now
	
	
	if(!function_exists("print_pre")) {	
		function print_pre(&$obj, $then_die = false) {
			load_thirdparty_plugin('dBug/dBug.php');
			new dBug($obj);
			if($then_die) die();
		}
	} // end print_pre
	
	if(!function_exists("die_print_pre")) {	
		function die_print_pre(&$obj) {
			print_pre($obj, TRUE);
		}
	} // end die_print_pre
	
	if(!function_exists("pginfo")) {	
		function pginfo() {
			echo "<h3>Globals</h3>";
			print_pre($GLOBALS['globals']);
			echo "<h3>Settings</h3>";
			print_pre($GLOBALS['settings']);
		}
	} // end pginfo
	
	if(!function_exists("var_is_array")) {	
		function var_is_array(&$var, $key) {
			if(isset($var[$key])) {
				return is_array($var[$key]);
			}
			return false;
		}
	} // end var_is_array

	if(!function_exists("var_is_object")) {	
		function var_is_object(&$var, $key) {
			if(isset($var[$key])) {
				return is_object($var[$key]);
			}
			return false;
		}
	} // end var_is_object
	
	if(!function_exists("array_key_relative")) {	
		function array_key_relative($array, $current_key, $offset = 1) {
			$keys = array_keys($array);
			$current_key_index = array_search($current_key, $keys);
			if(isset($keys[$current_key_index + $offset])) return $keys[$current_key_index + $offset];
			return false;
		}
	} // end array_key_relative	
	
	
/**
 * Returns an array with only the $before and $after number of results.
 * This is set to work best with MySQL data results.
 * Use this to find the rows immediately before and after a particular row, as many as you want.
 *
 * Example usage:
 *   $mysql_ar is an array of results from a MySQL query and the current id is $cur_id.
 *   We want to get the row before this one and five rows afterward.
 *
 * $near_rows = array_surround($two_dimensional_array, "id_field", $id_int, 1, 5)
 *
 *   Previous row is now $near_rows[-1]
 *
 *   Current row is $near_rows[0]
 *
 *   Next row(s) is $near_rows[1], $near_rows[2] ... etc
 *
 *   If there is no previous row, $near_rows[-1] will not be set...test for it with is_array($near_rows[-1])
 *
 * @return array
**/
	if(!function_exists("array_row_relative")) {
		function array_row_relative($src_array, $field, $value, $before = 1, $after = 1) {
			if(is_array($src_array)) {
				$before = abs($before);
				$after = abs($after);
				// reset all the keys to 0 through whatever in case they aren't sequential
				$new_array = array_values($src_array);
				// now loop through and find the key in array that matches the criteria in $field and $value
				foreach($new_array as $k => $s) {
					if($s[$field] == $value) {
						// Found the one we wanted
						$ck = $k; // put the key in the $ck (current key)
						break;
					}
				}
				if(isset($ck)) { // Found it!
					$result_start = $ck - $before; // Set the start key
					$result_length = $before + 1 + $after; // Set the number of keys to return
					if($result_start < 0) { // Oops, start key is before first result
						$result_length = $result_length + $result_start; // Reduce the number of keys to return
						$result_start = 0; // Set the start key to the first result
					}
					$result_temp = array_slice($new_array, $result_start, $result_length); // Slice out the results we want
					// Now we have an array, but we want array[-$before] to array[$after] not 0 to whatever.
					foreach($result_temp as $rk => $rt) { // set all the keys to -$before to +$after
						$result[$result_start - $ck + $rk] = $rt;
					}
					return $result;
				} else { // didn't find it!
					return false;
				}
			} else { // They didn't send an array
				return false;
			}
		}
	} // end array_row_relative
	
	if(!function_exists("boolean_title")) {	
		function boolean_title($value, $title_if_true = "True", $title_if_false = "False") {
			if($value) return $title_if_true;
			return $title_if_false;
		}
	} // end boolean_title	
	
	if(!function_exists("is_post")) {	
		function is_post() {
			return $_SERVER['REQUEST_METHOD'] == 'POST';
		}
	} // end is_post	
	
	/**
	 * @return int
	 */
	if(!function_exists("make_int")) {
		function make_int($var) {
			return (int)($var + 0);
		}
	} // end make_int
	/**
	 * @return string
	 */
	if(!function_exists("make_numeric")) {
		function make_numeric($var) {
			return preg_replace('/[^0-9\.]/', '', $var);
		}
	} // end make_numeric
	/**
	 * @return string
	 */
	if(!function_exists("make_alphanumeric")) {
		function make_alphanumeric($var) {
			return preg_replace('/[^a-zA-Z0-9\.]/', '', $var);
		}
	} // end make_alphanumeric
	/**
	 * @return string
	 */
	if(!function_exists("make_alphanumeric_and_space")) {
		function make_alphanumeric_and_space($var) {
			return preg_replace('/[^a-zA-Z0-9\s\.]/', '', $var);
		}
	} // end make_alphanumeric_and_space
	
	if(!function_exists("uuid")) {
		function uuid() {
			return md5(uniqid(mt_rand(), true));
		}
	} // end uuid
	
	if(!function_exists("quid")) {
		function quid($len=100) {
			$uid = uuid();
			return substr($uid, -1 * $len);
		}
	} // end quid
	
	if(!function_exists("str_abbr")) {
		function str_abbr($clipWhat, $howMuch = 10, $showAbbr = true, $eclipse = "...") {
			$newClip = $clipWhat;
			if(strlen($clipWhat) > $howMuch) {
				if($showAbbr) {
					$newClip = "<abbr title=\"" . $clipWhat . "\">" . substr($clipWhat, 0, $howMuch - 3) . "..." . "</abbr>";
				} else {
					$newClip = substr($clipWhat, 0, $howMuch - 3) . $eclipse;
				}
			}
			return $newClip;
		}
	} // str_abbr

	if(!function_exists("safe_unlink")) {
		function safe_unlink($filename) {
			if(file_exists($filename) && !is_dir($filename)) return unlink($filename);
			return false;
		}
	} // safe_unlink
	
	if(!function_exists("rrmdir")) {
		/**
     * Delete a file or recursively delete a directory
     *
     * @param string $str Path to file or directory
     */
    function rrmdir($str){
        if(is_file($str)){
            return @unlink($str);
        }
        elseif(is_dir($str)){
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index=>$path){
                rrmdir($path);
            }
            return @rmdir($str);
        }
    }
	}
	
	if(!function_exists("array_count")) {
		function array_count($ar) {
			if(is_array($ar)) return count($ar);
			return 0;
		}
	} // array_count
	
	if(!function_exists("coordinate_distance")) {
		function coordinate_distance($lat1, $lng1, $lat2, $lng2, $miles = true, $round = 1) {
			$pi80 = M_PI / 180;
			$lat1 *= $pi80;
			$lng1 *= $pi80;
			$lat2 *= $pi80;
			$lng2 *= $pi80;
		
			$r = 6372.797; // mean radius of Earth in km
			$dlat = $lat2 - $lat1;
			$dlng = $lng2 - $lng1;
			$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
			$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
			$km = $r * $c;
		
			return round($miles ? ($km * 0.621371192) : $km, $round);
		}
	} // coordinate_distance
	

	/**
	 * Easy way to display normal USD money format
	 *
	 * @return string
	 */
	if(!function_exists("str_money")) {
		function str_money($mixed, $thousands = "") {
			$mixed = (float)$mixed;
			return "$" . number_format($mixed, 2, ".", $thousands);
		}
	}
	
	if(!function_exists("round_precise")) {
		function round_precise($val, $precision) {
			return round($val * $precision) / $precision;
		}
	} 

	if(!function_exists("ceil_precise")) {
		function ceil_precise($val, $precision) {
			return ceil($val * $precision) / $precision;
		}
	}

	if(!function_exists("floor_precise")) {
		function floor_precise($val, $precision) {
			return floor($val * $precision) / $precision;
		}
	}


	/**
	 * Useful for preselecting an option in a select button based on two values being equal or not
	 *
	 * @return string
	 */
	if(!function_exists("selected")) {
		function selected($a, $b) {
			if($a == $b) return " SELECTED='SELECTED' ";
			return "";
		}
	} // selected

	/**
	 * Useful for checking or not checking a checkbox based on two values being equal or not
	 *
	 * @return string
	 */
	if(!function_exists("checked")) {
		function checked($a, $b) {
			if($a == $b) return " CHECKED='CHECKED' ";
			return "";
		}
	} // checked
	
	/**
	 *	Returns IP of visitor. Needs work, as this is easily spoofed.
	 *	
	 *	@return string
	 */
	if(!function_exists("visitor_ip")) {
		function visitor_ip() {
			if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip=$_SERVER['HTTP_CLIENT_IP']; // check ip from share internet
			} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip=$_SERVER['HTTP_X_FORWARDED_FOR']; // to check ip is pass from proxy
			} else {
				$ip=$_SERVER['REMOTE_ADDR'];
			}
		}
	}
	
	/**
	 *	Returns domain name in URL. Example: if URI is http://www.example.com/test/?arg=5, 
	 *	this would return "www.example.com". If you don't pass in the URL, returns the current domain.
	 *	
	 *	@return string
	 */
	if(!function_exists("domain_name")) {
		function domain_name($url = NULL) {
			if($url === NULL) return $_SERVER['HTTP_HOST']; // current domain name
			$domaininfo = parse_url($url);
			if(!empty($domaininfo["host"])) {
				return $domaininfo["host"];
			} else {
				return $domaininfo["path"];
			}
		}
	}
	
	/** 
	 *	Gets the first key in an array or false if not an array
	 *
	 *	@return multiple
	 */
	if(!function_exists("array_first_key")) {
		function array_first_key($array) {
			if(is_array($array)) return array_shift(array_keys($array));
			return false;
		}
	}
	
	/** 
	 *	Gets the last key in an array or false if not an array
	 *
	 *	@return multiple
	 */
	if(!function_exists("array_last_key")) {
		function array_last_key($array) {
			if(is_array($array)) return array_pop(array_keys($array));
			return false;
		}
	}
	
	/** 
	 *	Gets the first value in an array or false if not an array
	 *
	 *	@return multiple
	 */
	if(!function_exists("array_first")) {
		function array_first($array) {
			if(is_array($array)) return array_shift(array_values($array));
			return false;
		}
	}

	/**
	 * Returns a random value from an array. For returning a random key, use array_rand();
	 *
	 * @return multiple
	 */
	if(!function_exists("array_random")) {
		function array_random($array) {
			if(is_array($array)) return $array[array_rand($array)];
			return false;
		}
	}

	/** 
	 *	Gets the last value in an array or false if not an array
	 *
	 *	@return multiple
	 */
	if(!function_exists("array_last")) {
		function array_last($array) {
			if(is_array($array)) return array_pop(array_values($array));
			return false;
		}
	}
	
	/**
	 *    Split the given array into n number of pieces 
	 *    http://php.net/manual/en/function.array-slice.php
	 *     jamie@jamiechong.ca
	 *    
	 *    @return multidimensional array
	 */
	if(!function_exists("array_split")) {
		function array_split($array, $pieces=2) { 
				if ($pieces < 2 || !is_array($array)) return array($array); 
				$newCount = ceil(count($array)/$pieces); 
				$a = array_slice($array, 0, $newCount); 
				$b = array_split(array_slice($array, $newCount), $pieces-1); 
				return array_merge(array($a),$b); 
		}
	}

	if(!function_exists("core_hash")) {
		/**
		 * Secure hash. Use this in place of md5.
		 * @param string $plain
		 * @param int $trim_length
		 * @return string
		 */
		function core_hash($plain, $trim_length = NULL){
			if(function_exists("hash")) {
				$hash = strtr(rtrim(base64_encode(hash("sha256",$plain.APP_ID)), '='), '+/=', '-_,'); // Safe method
			} elseif(function_exists("mhash")) {
				$hash = strtr(rtrim(base64_encode(mhash(MHASH_SHA224,$plain.APP_ID)), '='), '+/=', '-_,'); // Somewhat safe method
			} else {
				$hash = md5($plain . APP_ID); // Unsafe method
			}
			if($trim_length) $hash = substr($hash, 0, $trim_length);
			return $hash;
		}
	}

	if(!function_exists("json_response")) {
		function json_response($obj) {
			header('Content-type: application/json');
			echo json_encode($obj);
			exit();
		}
	}

	if(!function_exists("_line")) {
		/**
		 * Outputs the line number
		 *
		 * @param string $output
		 * @param int $depth
		 */
		function _line($output = "", $depth = 1) {
			echo _code_line($output, $depth);
		}
	}
	
	if(!function_exists("_code_line")) {
		/**
		 * Outputs the line number
		 *
		 * @param string $output
		 * @param int $depth
		 */
		function _code_line($output = "", $depth = 0) {
			$backtrace = debug_backtrace();
			$path = $backtrace[$depth]['file'];
			$file = basename($path);
			$line = $backtrace[$depth]['line'];

			return "(<a href='#' title='" . $path . "'>" . $file . ":" . $line . "</a>{$output})";
		}
	}
	
	if(!function_exists("calendar_array")) {
		function calendar_array($month, $year) {
			$cal = array();
			$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			
			for($wk = 0; $wk <= 5; $wk += 1) {
				for($wkd = 0; $wkd < 7; $wkd += 1) {
					$cal[$wk][$wkd] = false;
				}
			}
			$week = 0;
			for($d = 1; $d <= $days_in_month; $d += 1) {
				$ts = strtotime("{$month}/{$d}/{$year}");
				$gd = getdate($ts);
				if($gd['wday'] == 0) $week += 1;
				$cal[$week][$gd['wday']] = $ts;
			}
			
			// remove any weeks that are completely empty
			foreach($cal as $w => $wds) {
				$week_empty = true;
				foreach($wds as $d) {
					if($d != FALSE) {
						$week_empty = false;
						break;
					}
				}
				if($week_empty) {
					unset($cal[$w]);
					$cal = array_values($cal);
				}
			}
			
			return $cal;
		}
	} // calendar_array
	
?>