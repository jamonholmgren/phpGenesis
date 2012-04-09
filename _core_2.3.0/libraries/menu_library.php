<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

// menu_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	Nothing for now

	// prerequisites
	load_library("segments");

	/**
	 * Add item to the Menu array
	 *
	 * @return NULL
	 */
	if(!function_exists("menu_add_item")) {
		function menu_add_item($menu_levels, $title, $link) {
			$eval_string = "\$" . "GLOBALS['globals']['menus']";
			$menus = explode(">", $menu_levels);
			foreach($menus as $m) {
				$eval_string .= "['" . $m . "']";
			}
			$eval_string .= " = array(\"title\" => \"{$title}\", \"link\" => \"{$link}\");";
			eval($eval_string);
		}
	} // menu_add_item
	
	/**
	 * Returns all saved menu items
	 *
	 * @return string
	 */
	if(!function_exists("menu_display")) {
		function menu_display($menu, $include_ul_tag = false) {
			if(global_isset('menus', $menu)) return _menu_build(globals('menus', $menu), $include_ul_tag);
			return false;
		}
	} // menu_display
	
	if(!function_exists("_menu_build")) {
		function _menu_build($menu_array, $include_ul_tag = false) {
			$html = "";
			if(is_array($menu_array)) {
				$contains_menu_items = false;
				foreach($menu_array as $menu_level => $menu_item) {
					if(is_array($menu_item)) {
						$contains_menu_items = true;
						break;
					}
				}
				if($contains_menu_items && $include_ul_tag) $html .= "\n<ul class=\"menu_" . key($menu_array) . "\">\n";
				foreach($menu_array as $menu_level => $menu_item) {
					if(is_array($menu_item)) {
						$menu_current = "";
						if(isset($menu_item['link']) && ($menu_item['link'] == segments_page() || trim($menu_item['link'], "/") == segments_page())) $menu_current = "menu_current";
						$html .= "	<li class=\"menu_item_{$menu_level} {$menu_current}\">";
						if(isset($menu_item['title'], $menu_item['link'])) {
							$html .= "<a href=\"{$menu_item['link']}\">{$menu_item['title']}</a>";
						}
						if(is_array($menu_item) && $menu_level != "link" && $menu_level != "title") $html .= _menu_build($menu_item, true);
						$html .= "</li>\n";
					}
				}
				if($contains_menu_items && $include_ul_tag) $html .= "</ul>\n\n";
			}
			return $html;
		}
	} // _menu_build
	
	/**
	 * Old breadcrumbs function - see segments_breadcrumbs for a 
	 * more automated breadcrumbs system
	 * 
	 * This function foreaches through an array and returns an HTML
	 * div with the breadcrumbs and links
	 *
	 * @return string
	 */
	if(!function_exists("menu_breadcrumb")) {
		function menu_breadcrumb($breadcrumbs, $home_link = "/", $separator = "") {
			$breadcrumb = "<div class=\"breadcrumbs\"><a href=\"{$home_link}\" class=\"homelink\">Home</a>";
				if(is_array($breadcrumbs)) {
					$breadcrumb .= "<span class=\"separator\">{$separator}</span>";
					foreach($breadcrumbs as $title => $link) {
						if($link != "") {
							$breadcrumb .= "<a href=\"{$link}\">{$title}</a><span class=\"separator\">{$separator}</span>";
						} else {
							$breadcrumb .= "<strong>{$title}</strong>";
						}
					}
				} else {
					return "Error: breadcrumb function not given an array!";
				}
			$breadcrumb .= "</div>";
			return $breadcrumb;
		}
	} // menu_breadcrumb
?>