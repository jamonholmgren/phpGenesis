<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Segments Library
 *	
 *	This library probably should have been called URL_library. These functions are useful for checking various parts of the URL after the TLD
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
	 *	Accesses segments by index or key
	 *	
	 *	@return string
	 */
	if(!function_exists("segment")) {
		function segment($index) {
			if(is_int($index)) {
				$seg_array = globals('segments', 'array');
				if ($index < count($seg_array)) return $seg_array[$index];
			} else {
				$seg_array = globals('segments', 'key_segments');
				if(isset($seg_array[$index])) return $seg_array[$index];
			}	
			return NULL; 	
		}
	} // end segment
	
	/**
	 *	Alias for segment()
	 *	
	 *	@return string
	 */
	if(!function_exists("segments")) {
		function segments($index) { return segment($index); }
	}
	
	/**
	 *	Checks if the segment exists and returns true/false
	 *	
	 *	@return bool
	 */
	if(!function_exists("segment_exists")) {
		function segment_exists($index) {
			$seg_array = globals('segments', 'array');
			if(is_int($index)) {
				if ($index < count($seg_array)) return true;
			} else {
				for ($i = 0; $i < count($seg_array)-1; $i++) {
					if ($seg_array[$i] == $index) { return true; }
				}			
			}
			return false;
		}
	} // end segment_exists
	
	/**
	 *	Counts how many segments exist and then returns that number
	 *	
	 *	return int
	 */
	if(!function_exists("segments_count")) {
		function segments_count() {
			return count(globals('segments', 'array'));
		}
	} // end segments_page
	
	/**
	 *	Returns the current page segment
	 *	
	 *	@return array
	 */
	if(!function_exists("segments_page")) {
		function segments_page() {
			return globals('segments', 'page');
		}
	} // end segments_page
	
	/**
	 *	Returns the current pages' segments(s) as a string
	 *	
	 *	@return string
	 */
	if(!function_exists("segments_query")) {
		function segments_query() {	
			return implode("/", globals('segments', 'array'));			
		}		
	} // end segments_query
	
	/**
	 *	Stores the current pages' segments and then returns them all as a string
	 *	
	 *	@return string
	 */
	if(!function_exists("segments_full")) {
		function segments_full($index = NULL) {
			if($index === NULL) {
				if(global_isset("segments", "full")) return globals('segments', 'full');
			} else {
				if(global_isset("segments", "full_array")) {
					$seg = globals("segments", "full_array");
				} else {
					$seg = array_filter(explode("/", _get_request_uri()));
				}
				if(isset($seg[$index])) return $seg[$index];
				return NULL;
			}
			return _get_request_uri();
		}
	} // end segments_full
	
	/**
	 *	Returns the original segments string (or a part of that, if index is specified). Useful when
	 *	you're doing custom routing where the segments_full() gets changed but you want to access the original.
	 *
	 *	@return string
	 */
	if(!function_exists("segments_original")) {
		function segments_original($index = NULL) {
			if($index === NULL) {
				if(global_isset("segments", "original")) return globals('segments', 'original');
			} else {
				if(global_isset("segments", "original_array")) {
					$seg = globals("segments", "original_array");
				} else {
					$seg = array_filter(explode("/", _get_request_uri()));
				}
				if(isset($seg[$index])) return $seg[$index];
				return NULL;
			}
		}
	} // end segments_original
	
	/**
	 *	Returns the current action segment
	 *	
	 *	@return string
	 */
	if(!function_exists("segments_action")) {
		function segments_action() {	
			return globals('segments', 'action');			
		}
	} // end segments_action
	
	/**
	 *	Alias for segments_action()
	 *	
	 *	@return string
	 */
	if(!function_exists("segment_action")) {
		function segment_action() {
			return segments_action();
		}
	} // end segment_action (alias for segments_action)
	
	/**
	 *	Returns the current id segment
	 *	
	 *	@return string
	 */
	if(!function_exists("segments_id")) {
		function segments_id() {	
			return globals('segments', 'id');			
		}
	} // end segments_id
	
	/**
	 *	Alias for segments_id()
	 *	
	 *	@return string
	 */
	if(!function_exists("segment_id")) {
		function segment_id() {
			return segments_id();
		}
	} // end segment_id (alias for segments_id)
	
	/**
	 *	Automatically returns breadcrumbs from segments_full(). In the unusual case that you do not want to 
	 *	include the homepage in your breadcrumbs, set $show_home = FALSE.
	 *
	 *	The $breadcrumbs_names array allows you to set specific titles for specific segments. E.g. array("contact-us", "Contact Our Team").
	 *	
	 *	Example: <div class="breadcrumbs"><?=segments_breadcrumbs("&raquo;")?></div>
	 *
	 *	@return string
	 */
	if(!function_exists("segments_breadcrumbs")) {
		function segments_breadcrumbs($separator, $show_home = TRUE, $breadcrumb_names = array()) {
			$crumbs = segments_full();
			$return = "";
			if($crumbs != settings('pages', 'home_page')) {
				if($show_home) $return = '<a href="/" class="breadcrumb-link">' . ucwords(settings('pages', 'home_page')) . '</a> ' . $separator . ' ';
				$crumbs = explode("/", $crumbs);
				if($count = array_count($crumbs)) { // should always be true unless explode isn't working
					foreach($crumbs as $k => $crumb) {
						$crumb_title = ucwords(str_replace("-", " ", urldecode($crumb)));
						if(isset($breadcrumb_names[$crumb])) {
							if($breadcrumb_names[$crumb] === FALSE) {
								$prev_crumb .= $crumb . "/";
								continue; // no need to display anything.
							}
							$crumb_title = $breadcrumb_names[$crumb];
						}
						if($k + 1 == $count) { // last layer -- $k starts at zero so add one
							$return .= $crumb_title;
						} else {
							if($prev_crumb) { // more than two layers
								$return .= '<a href="/' . $prev_crumb . $crumb . '/" class="breadcrumb-link">' . $crumb_title . '</a> ' . $separator . ' ';
							} else { // just two layers
								$return .= '<a href="/' . $crumb . '/" class="breadcrumb-link">' . $crumb_title . '</a> ' . $separator . ' ';
							}
						}
						$prev_crumb .= $crumb . "/";
					}
				} else {
					// should never happen. There should always be something here.
				}
			} elseif($show_home) {
				$return = ucwords($crumbs);
			}
			return $return;
		}
	}
	
	/**
	 *	Returns a page heading based on segments_full() - Untested on multi-level pages
	 *
	 *	@return string
	 */
	if(!function_exists("segments_heading")) {
		function segments_heading() {
			return deslugify(segments_full());
		}
	}

	/**
	 *	Friendly alias for segments_heading but adds the site name from the config for use in <title> tags
	 *
	 *	@return string
	 */	
	if(!function_exists("segments_title")) {
		function segments_title($separator = "|", $reverse = FALSE) {
			$sitename = "";
			if($reverse === FALSE) {
				if(settings('site', 'name')) $sitename = " " . $separator . " " . settings('site', 'name');
				return segments_heading() . $sitename;
			} else {
				if(settings('site', 'name')) $sitename = settings('site', 'name') . " " . $separator . " ";
				return $sitename . segments_heading();				
			}
		}
	}
	
	/**
	 *	Returns a class for a menu link if it is the current or parent page
	 *
	 *	@return string
	 */		
	if(!function_exists("segments_current")) {
		function segments_current($string, $class = "current", $parent = "parent") {
			if($string == segments_full()) {
				return $class;
			} elseif(strpos(segments_full(), $string) === 0) {
				return $parent;
			} else {
				return "";
			}
		}
	}
	
	/**
	 *	Returns the last segment from segments_full()
	 *	
	 *	@return string
	 */
	if(!function_exists("segments_last")) {
		function segments_last() {
			$segments = segments_full();
			$trim = rtrim($segments, "/"); // trim the last / so $last returns a segment instead of nothing
			$last = strrchr($trim, "/");
			$segments_last = str_replace("/", "", $last); // strip out any remaining / before returning
			//die($segments_last);
			return $segments_last;
		}
	}
	
	
?>