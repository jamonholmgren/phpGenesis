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
//	add function_exists() lines
	
	function page_url($page = "/") { // adds trailing slash
		$url = trim($page, "/");
		if(strlen($url > 0)) $url = $url . "/";
		return rtrim(BASE_URL, "/") . "/" . $url;
	}
	function base_url($url = "/") { // no trailing slash
		return rtrim(BASE_URL, "/") . "/" . ltrim($url, '/');
	}
	function app_url($url = "/") {
		return rtrim(APP_URL, "/") . "/" . ltrim($url, '/');
	}
	
	function register_css($key, $path, $media = "screen", $priority = 0, $ie_tag = false, $head = true) {
		if(strpos("NULL" . $path, "http://") < 1) $path = app_url($path);
		$conditional_start = ""; $conditional_end = "";
		if($ie_tag !== false) {
			$conditional_start = "<!--[if {$ie_tag}]>";
			$conditional_end = "<![endif]-->";
		}
		if($head) register_head_block($key, "{$conditional_start} <link rel='stylesheet' href='" . $path . "' type='text/css' media='{$media}' /> {$conditional_end}", $priority);
		if(!$head) register_foot_block($key, "{$conditional_start} <link rel='stylesheet' href='" . $path . "' type='text/css' media='{$media}' /> {$conditional_end}", $priority);
	}
	
	function register_javascript($key, $path, $priority = 1000, $ie_tag = false, $head = false) {
		if(strpos("NULL" . $path, "http://") < 1) $path = app_url($path);
		$conditional_start = ""; $conditional_end = "";
		if($ie_tag !== false) {
			$conditional_start = "<!--[if {$ie_tag}]>";
			$conditional_end = "<![endif]-->";
		}
		if($head) register_head_block($key, "{$conditional_start} <script type='text/javascript' src='{$path}'></script> {$conditional_end}", $priority);
		if(!$head) register_foot_block($key, "{$conditional_start} <script type='text/javascript' src='{$path}'></script> {$conditional_end}", $priority);
	}
	
	function register_javascript_block($key, $script, $priority = 1000, $ie_tag = false, $head = true) {
		$conditional_start = ""; $conditional_end = "";
		if($ie_tag !== false) {
			$conditional_start = "<!--[if {$ie_tag}]>";
			$conditional_end = "<![endif]-->";
		}
		if($head) register_head_block($key, "{$conditional_start} <script type='text/javascript'>{$script}</script> {$conditional_end}", $priority);
		if(!$head) register_foot_block($key, "{$conditional_start} <script type='text/javascript'>{$script}</script> {$conditional_end}", $priority);
	}
	
	function register_head_block($key, $block, $priority = 0) {
		if(!global_isset('head', 'blocks')) { globals('head', 'blocks', array()); }
		if(!global_isset('head', 'sequence')) { globals('head', 'sequence', 0); }
		$blocks = &globals('head', 'blocks'); 
		$sequence = &globals('head', 'sequence'); 
		$sequence += 1;
		$block_obj = array('block'=>$block , 'priority'=>$priority, 'sequence'=>$sequence);
		$blocks[$key] = $block_obj; // blocks can be overwriten by using an existing key
	}
		
	function _render_header_blocks() { 		
		if(global_isset('head', 'blocks')) {
			$blocks =  globals('head', 'blocks');
			uasort($blocks, '_sort_blocks');
			foreach ($blocks as $block_obj) {				
				echo $block_obj['block'] . "\n";
			}	
		}	
	} register_hook('write_head', '_render_header_blocks');
	
	function _sort_blocks($b1, $b2) {
		if ($b1['priority'] == $b2['priority']) {
			if ($b1['sequence'] == $b2['sequence']) {
				return 0;
			}
			return ($b1['sequence'] < $b2['sequence']) ? -1 : 1;
		}
		return ($b1['priority'] < $b2['priority']) ? -1 : 1;
	}

	function register_foot_block($key, $block, $priority = 0) {
		if(!global_isset('foot', 'blocks')) { globals('foot', 'blocks', array()); }
		if(!global_isset('foot', 'sequence')) { globals('foot', 'sequence', 0); }
		$blocks = &globals('foot', 'blocks');
		$sequence = &globals('foot', 'sequence');
		$sequence += 1;
		$block_obj = array('block'=>$block , 'priority'=>$priority, 'sequence'=>$sequence);
		$blocks[$key] = $block_obj; // blocks can be overwriten by using an existing key
	}

	function _render_footer_blocks() {
		if(global_isset('foot', 'blocks')) {
			$blocks =  globals('foot', 'blocks');
			uasort($blocks, '_sort_blocks');
			foreach ($blocks as $block_obj) {
				echo $block_obj['block'] . "\n";
			}
		}
	} register_hook('write_foot', '_render_footer_blocks');

?>