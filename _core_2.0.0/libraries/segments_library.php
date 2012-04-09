<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

	/**
	* Accesses segments by index or key
	* @returns string
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
	 */
	if(!function_exists("segments")) {
		function segments($index) { return segment($index); }
	}
	
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
	
	if(!function_exists("segments_count")) {
		function segments_count() {
			return count(globals('segments', 'array'));
		}
	} // end segments_page
	
	if(!function_exists("segments_page")) {
		function segments_page() {
			return globals('segments', 'page');
		}
	} // end segments_page
	
	if(!function_exists("segments_query")) {
		function segments_query() {	
			return implode("/", globals('segments', 'array'));			
		}		
	} // end segments_query
	
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
	
	if(!function_exists("segments_action")) {
		function segments_action() {	
			return globals('segments', 'action');			
		}
	} // end segments_action
	if(!function_exists("segments_id")) {
		function segments_id() {	
			return globals('segments', 'id');			
		}
	} // end segments_id
	
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
						$crumb_title = ucwords(str_replace("-", " ", $crumb));
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

?>