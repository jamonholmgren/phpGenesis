<?php
/**
 *	Notice Library
 *	
 *	Stores and returns notices. Useful in conjunction with the form_library to give users feedback through error/success messages.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *
 * @package phpGenesis
 */
 
	// Prerequisite Libraries: session
	load_library("session");
	
	/** 
	 *	Returns an HTML block for a notice using the passed type and content.
	 *	Uses notice template settings set in config.php or a default layout.
	 *	settings("notice", "html", "<div class='notice type-%type%'><p>%notice%</p></div>")
	 *	
	 *	@return string
	 */
	if(!function_exists("notice_show")) {
		function notice_show($type, $notice) { 
			if(setting_isset("notice", "html")) {
				$html = settings("notice", "html");
				$html = str_replace("%type%", $type, $html);
				$html = str_replace("%notice%", $notice, $html);
				return $html;
			} else {
				return "<div class='notice type-{$type}'><p>{$notice}</p></div>";
			}
		}
	}

	/** 
	 * Adds a notice to an array for display later. Type can be anything, but generally is info, error, warning, etc.
	 * 
	 * @return NULL
	 */
	if(!function_exists("notice_add")) {
		function notice_add($type, $notice, $group = 0) {
			$notices = session("notices");
			$notices[$group][] = array("type" => $type, "notice" => $notice);
			session("notices", $notices);
		}
	}
	
	/** 
	 * Return number of notices in a group.
	 * 
	 * @return int
	 */
	if(!function_exists("notices_count")) {
		function notices_count($group = 0) {
			$notices = session("notices");
			return array_count($notices[$group]);
		}
	}
	
	/**
	 * Returns all set notices then clears the array
	 * Use $show_group or $show_type to filter results -- see notice_add()
	 * 
	 * @return string
	 */
	if(!function_exists("notices_show")) {
		function notices_show($show_type = NULL, $show_group = NULL) {
			$shown_notices = array();
			$notices_html = "";
			
			$session = session("notices");
			
			if(is_array($session)) {
				foreach($session as $group => $notices) {
					if(is_array($notices) && ($show_group === NULL || $group === $show_group)) {
						foreach($notices as $index => $notice) {
							if(($show_type === NULL || $notice['type'] === $show_type)) {
								// Verify we haven't seen this notice already.
								if(!isset($shown_notices[$notice['type']][$notice['notice']])) {
									$notices_html .= notice_show($notice['type'], $notice['notice']);
									$shown_notices[$notice['type']][$notice['notice']] = true;
								}
								notice_clear($group, $index);
							}
						}
					}
				}
				if($show_type === NULL && $show_group === NULL) notices_clear(); // only clear notices if we have already displayed them all.
			}
			return $notices_html;
		}
	}
	
	/** 
	 *	Clears all set notices. This function is run on the write_foot hook so you don't end up with undisplayed notices
	 *	suddenly popping up in random places.
	 *	
	 *	@return NULL
	 */
	if(!function_exists("notices_clear")) {
		function notices_clear() {
			unset_session("notices");
			// unset($_SESSION['notices']);
		}
	}
	
	/** 
	 *	Clears a particular notice.
	 *	
	 *	@return NULL
	 */
	if(!function_exists("notice_clear")) {
		function notice_clear($group, $key) {
			$session = session("notices");
			unset($session[$group][$key]);
			session("notices", $session);
		}
	}

	// If the footer loads, clear all undisplayed notices
	register_hook("write_foot", "notices_clear");
	
?>