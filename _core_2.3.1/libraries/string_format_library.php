<?php
/**
 *	String Format Library
 *	
 *	Various string formatting functions.
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
	 *	Returns a friendly date format, like Sep 1, 2010. Only adds a year if it's not the current year.
	 *	Returns a dash if the date isn't set. You can set what it will return if not set and the date 
	 *	formats for this year and other years.
	 *
	 *	@return string
	 */
	if(!function_exists("string_friendly_date")) {
		function string_friendly_date($timestamp, $not_set = "-", $this_year_format = "M j", $other_year_format = "M j, Y") {
			if($timestamp > strtotime("1/1/1970")) {
				if($timestamp >= strtotime("January 1") && $timestamp <= strtotime("Dec 31 11:59:59 PM")) {
					// This year
					return date($this_year_format, $timestamp);
				} else {
					return date($other_year_format, $timestamp);
				}
			} else {
				return $not_set;
			}
		}
	}

	
	/**
	 * Works out the time since the entry post, takes a an argument in unix time (seconds)
	 * Taken from:http://www.dreamincode.net/code/snippet86.htm
	 * @param string $original
	 * @return string
	 */
	if(!function_exists("string_friendly_date")) {
		function string_format_time_since($original, $depth = 2, $today = NULL) {
			// array of time period chunks
			$chunks = array(
					array(60 * 60 * 24 * 365 , 'year'),
					array(60 * 60 * 24 * 30 , 'month'),
					array(60 * 60 * 24 * 7, 'week'),
					array(60 * 60 * 24 , 'day'),
					array(60 * 60 , 'hour'),
					array(60 , 'min'),
			array(1 , 'sec')
			);

			if($today === NULL) $today = time(); /* Current unix time  */
			if ($original>$today) $original = $today;
				$since = $today - $original;
		
				// $j saves performing the count function each time around the loop
				for ($i = 0, $j = count($chunks); $i < $j; $i++) {
		
						$seconds = $chunks[$i][0];
						$name = $chunks[$i][1];
		
						// finding the biggest chunk (if the chunk fits, break)
						if (($count = floor($since / $seconds)) != 0) {
								// DEBUG print "<!-- It's $name -->\n";
								break;
						}
				}
		
				$print = ($count == 1) ? '1 '.$name : "$count {$name}s";
		
			if ($depth > 1) {
				if ($i + 1 < $j) {
					// now getting the second item
					$seconds2 = $chunks[$i + 1][0];
					$name2 = $chunks[$i + 1][1];
		
					// add second item if it's greater than 0
					if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0) {
						$print .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 {$name2}s";
					}
				}
			}
		
				return $print;
		}
	}
	
	/**
	 * Format numbers into short hand: 10000 becomes 10K
	 *	
	 * @param int $count
	 * @return string
	 */
	if(!function_exists("string_format_number")) {
		function string_format_number($count, $thousands = "K", $millions = "M") {

			if ($count < 10000) {
				return number_format($count);
			} elseif ($count < 1000000)  {
				$count = floor($count / 1000);
				return number_format($count) . $thousands;
			} else {
				$count = floor($count / 1000000);
				return number_format($count) . $millions;
			}

		}
	}

	/**
	 * Easy way to display normal USD money format
	 *
	 * @return string
	 */
	if(!function_exists("string_format_money")) {
		function string_format_money($money, $symbol = "$", $thousands = ",", $decimal = ".") {
			$m1 = make_numeric($money);
			$m2 = $symbol . number_format($m1, 2, $decimal, $thousands);
			return $m2;
		}

	} // string_format_money


?>
