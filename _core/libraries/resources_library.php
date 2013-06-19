<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Resources Library
 *	
 *	These functions are used almost exclusively in the core and /app/includes/header.php
 *	
 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	Maintained by ClearSight Studio
 *	
 *	@todo add function_exists() lines
 * @package phpGenesis
 */

// mvc_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	See above
	
	/**
	 *	Takes a given url and adds a trailing slash
	 *	
	 *	@return string
	 */
	function page_url($page = "/") { // adds trailing slash
		$url = trim($page, "/");
		if(strlen($url > 0)) $url = $url . "/";
		return rtrim(BASE_URL, "/") . "/" . $url;
	}
	
	/**
	 *	Parses the given url so that there are the correct number of slashes and makes it an absolute URL
	 *	
	 *	@return string
	 */
	function base_url($url = "/") { // no trailing slash
		return rtrim(BASE_URL, "/") . "/" . ltrim($url, '/');
	}
	
	/**
	 *	Parses the given url so that there are the correct number of slashes and makes it a relative URL
	 *	
	 *	@return string
	 */
	function app_url($url = "/") {
		return rtrim(APP_URL, "/") . "/" . ltrim($url, '/');
	}
	
	/**
	 *	Inserts <link rel="stylesheet"> into your header with the given priority and other passed information
	 *	
	 *	@return NULL
	 */
	function register_css($key, $path, $media = "screen", $priority = 0, $ie_tag = false, $head = true) {
		if(strpos("NULL" . $path, "://") < 1) $path = app_url($path);
		$conditional_start = ""; $conditional_end = "";
		if($ie_tag !== false) {
			$conditional_start = "<!--[if {$ie_tag}]>";
			$conditional_end = "<![endif]-->";
		}
		if($head) register_head_block($key, "{$conditional_start} <link rel='stylesheet' href='" . $path . "' type='text/css' media='{$media}' /> {$conditional_end}", $priority);
		if(!$head) register_foot_block($key, "{$conditional_start} <link rel='stylesheet' href='" . $path . "' type='text/css' media='{$media}' /> {$conditional_end}", $priority);
	}

	/**
	 *	Inserts <script type="text/javascript"> link into your header with the given priority and other passed information
	 *	
	 *	@return NULL
	 */
	function register_javascript($key, $path, $priority = 1000, $ie_tag = false, $head = false) {
		if(strpos("NULL" . $path, "://") < 1) $path = app_url($path);
		$conditional_start = ""; $conditional_end = "";
		if($ie_tag !== false) {
			$conditional_start = "<!--[if {$ie_tag}]>";
			$conditional_end = "<![endif]-->";
		}
		if($head) register_head_block($key, "{$conditional_start} <script type='text/javascript' src='{$path}'></script> {$conditional_end}", $priority);
		if(!$head) register_foot_block($key, "{$conditional_start} <script type='text/javascript' src='{$path}'></script> {$conditional_end}", $priority);
	}
	
	/**
	 *	Inserts a <script type="text/javascript"> block into your header with the given priority and other passed information
	 *	
	 *	@return null
	 */
	function register_javascript_block($key, $script, $priority = 1000, $ie_tag = false, $head = true) {
		$conditional_start = ""; $conditional_end = "";
		if($ie_tag !== false) {
			$conditional_start = "<!--[if {$ie_tag}]>";
			$conditional_end = "<![endif]-->";
		}
		if($head) register_head_block($key, "{$conditional_start} <script type='text/javascript'>{$script}</script> {$conditional_end}", $priority);
		if(!$head) register_foot_block($key, "{$conditional_start} <script type='text/javascript'>{$script}</script> {$conditional_end}", $priority);
	}
	
	/**
	 *	Registers the Head Block?
	 *	
	 *	@return null
	 */
	function register_head_block($key, $block, $priority = 0) {
		if(!global_isset('head', 'blocks')) { globals('head', 'blocks', array()); }
		if(!global_isset('head', 'sequence')) { globals('head', 'sequence', 0); }
		$blocks = &globals('head', 'blocks'); 
		$sequence = &globals('head', 'sequence'); 
		$sequence += 1;
		$block_obj = array('block'=>$block , 'priority'=>$priority, 'sequence'=>$sequence);
		$blocks[$key] = $block_obj; // blocks can be overwriten by using an existing key
	}
	
	/**
	 *	Private function that renders Head Blocks
	 *	
	 *	@return null
	 */
	function _render_header_blocks() { 		
		if(global_isset('head', 'blocks')) {
			$blocks =  globals('head', 'blocks');
			uasort($blocks, '_sort_blocks');
			foreach ($blocks as $block_obj) {				
				echo $block_obj['block'] . "\n";
			}	
		}	
	} register_hook('write_head', '_render_header_blocks');

	/**
	 *	Private function that sorts the blocks. Returns 1, 0, or -1.
	 *	
	 *	@return int
	 */
	function _sort_blocks($b1, $b2) {
		if ($b1['priority'] == $b2['priority']) {
			if ($b1['sequence'] == $b2['sequence']) {
				return 0;
			}
			return ($b1['sequence'] < $b2['sequence']) ? -1 : 1;
		}
		return ($b1['priority'] < $b2['priority']) ? -1 : 1;
	}
	
	/**
	 *	Registers foot blocks
	 *	
	 *	@return null
	 */
	function register_foot_block($key, $block, $priority = 0) {
		if(!global_isset('foot', 'blocks')) { globals('foot', 'blocks', array()); }
		if(!global_isset('foot', 'sequence')) { globals('foot', 'sequence', 0); }
		$blocks = &globals('foot', 'blocks');
		$sequence = &globals('foot', 'sequence');
		$sequence += 1;
		$block_obj = array('block'=>$block , 'priority'=>$priority, 'sequence'=>$sequence);
		$blocks[$key] = $block_obj; // blocks can be overwriten by using an existing key
	}
	
	/**
	 *	Private function that renders foot blocks
	 *	
	 *	@return null
	 */
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