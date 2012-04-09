<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

// pager_library last edited 3/21/2011 by Jamon Holmgren
// TO-DO
//	Resolve duplicate issue with pager_page_size() and pager_limit()

	/* 	
	
		// Basic Usage: echo pager(6850, "&sort=date");
	
		// Template Pager CSS Style
	
		.pager { overflow:hidden; margin-bottom:8px; }
		.pager .page-buttons { float:left; width:auto; }
		.pager .page-sizes { float:right; }
		.pager .page-number {
			font-size:130%;
			padding:0px 5px;
			border: solid 1px #888;
			margin-right:6px;
		}
		.pager .page-number {
			display:block;
			float:left;
			width:auto;
		}
		.pager a { text-decoration:none;	}
		.pager a:active, .pager a:focus{ outline:none; }  
		.pager .page-prev, .pager .page-next, .pager .page-dots, .pager .per-page {
			border:none;
			padding-top:1px;
		}
		.pager .page-dots { font-size:166%; line-height:25px; }
			
	*/
	
	/**
	 *	Returns the current page. Returns the default page if none selected.
	 *	
	 *	@return int
	 */
	if(!function_exists("pager_page")) {
		function pager_page($default = 1, $page_key = "page") {
			$request_page = 0;
			if (isset($_REQUEST[$page_key])) { $request_page = (int)$_REQUEST[$page_key]; }
			return $request_page > 0 ? $request_page : $default;
		}
	}
		
	/**
	 *	Returns the current page size. Pass in a string of sizes, e.g. "25|50|100". Returns the first size if none selected.
	 *	
	 *	@return int
	 */
	if(!function_exists("pager_page_size")) {
		function pager_page_size($page_sizes_str = "25|50", $size_key = "size") {
			$page_sizes = explode("|", $page_sizes_str);
			$request_size = 0;
			if(isset($_REQUEST['size'])) { $request_size = (int)$_REQUEST['size']; }
			return in_array($request_size, $page_sizes) ? $request_size : $page_sizes[0];
		}
	}
		
	/**
	 *	Returns the current page offset for use in MySQL queries.
	 *	
	 *	@return int
	 */
	if(!function_exists("pager_offset")) {
		function pager_offset($default_page = 1, $page_sizes_str = "25|50", $page_key = "page", $size_key = "size") {
			$size = pager_page_size($page_sizes_str, $size_key);
			$start = (pager_page($default_page, $page_key) - 1) * $size;	 	
			return $start;
		}
	}

	/**
	 *	Returns a MySQL string for limiting queries. Example: " LIMIT 50,25 "
	 *	
	 *	@return string
	 */
	if(!function_exists("pager_mysql_limit")) {
		function pager_mysql_limit($default_page = 1, $page_sizes_str = "25|50", $page_key = "page", $size_key = "size") {
			return " LIMIT " . pager_offset($default_page, $page_sizes_str, $page_key, $size_key) . "," . pager_page_size($page_sizes_str, $size_key);
		}
	}

	/**
	 *	Returns HTML controls for the pager. Output using <?=pager()?>.
	 *
	 *	Options: buttons_before, buttons_after, page_key, size_key, show_single_page
	 *	
	 *	@return string
	 */
	if(!function_exists("pager")) {
		function pager($total_records, $query_string = "", $page_sizes_str = "25|50", $options = array()) {		
			$buttons_before = 1;
			$buttons_after = 3;
			$page_key = "page";
			$size_key = "size";
			$show_single_page = true;
			
			if(isset($options['buttons_before'])) $buttons_before = $options['buttons_before'];
			if(isset($options['buttons_after'])) $buttons_after = $options['buttons_after'];
			if(isset($options['page_key'])) $page_key = $options['page_key'];
			if(isset($options['size_key'])) $size_key = $options['size_key'];
			if(isset($options['show_single_page'])) $show_single_page = $options['show_single_page'];
			
			$page_sizes = explode("|", $page_sizes_str);
			$page_size = pager_page_size($page_sizes_str, $size_key);
			$total_pages = ceil($total_records / $page_size);
			$current_page = pager_page();
			$current_page = $current_page <= $total_records ? $current_page : $total_records;
			$buttons = "";
			$button_count = ($total_records >= 5) ? 5 : $total_records;	
			
			if($query_string === TRUE) {
				$query_string = "";
				if(count($_GET) > 0) {
					foreach($_GET as $k => $v) {
						if($k != $page_key && $k != $size_key && $v != "") {
							$query_string .= "&" . urlencode($k) . "=" . urlencode(input_get($k)) . "";
						}
					}
				}
			} elseif($query_string <> "" && substr($query_string, 0, 1) <> "&") {
				$query_string = "&" . $query_string;
			}
			
				// Adjust buttons
			$bb = $current_page - $buttons_before - 1; 
			$ba = $current_page + $buttons_after - $total_pages;  
			if ($bb < 0) {
				$buttons_before += $bb;
				$buttons_after -= $bb;
			} elseif ($ba > 0 ) {
				$buttons_before += $ba;
				$buttons_after -= $ba;
			}		
			
			// Prev Button
			if ($current_page > 1) {
				$prev_index = $current_page - 1;
				$buttons .= "<a title='Page {$prev_index}' href='?{$page_key}={$prev_index}&{$size_key}={$page_size}{$query_string}'><span class='page-number page-prev'>Prev</span></a>";
			}
			
			// First
			if ($current_page > $buttons_before + 1) {
				$buttons .= "<a title='Page 1' href='?{$page_key}=1&{$size_key}={$page_size}{$query_string}'><span class='page-number'>1</span></a>";
			}
			
			// Spacer
			if ($current_page > $buttons_before + 3) {
				$buttons .= "<span class='page-number page-dots'>...</span>";		
			} elseif ($current_page - $buttons_before - 1 == 2) { 
				 $buttons_before++;
			}	
			
			// Buttons before current
			for ($button_index = $current_page - $buttons_before; $button_index < $current_page; $button_index++) {
				if ($button_index > 0) {
					$buttons .= "<a title='Page {$button_index}' href='?{$page_key}={$button_index}&{$size_key}={$page_size}{$query_string}'><span class='page-number'>{$button_index}</span></a>";
				}
			} 
			
			// Current
			$buttons .= "<span class='page-number'>{$button_index}</span>";	
			
			if ($current_page + $buttons_after == $total_pages - 2) { 
				$buttons_after++;
			}	
			
			// Buttons after current
			for ($button_index = $current_page + 1; $button_index <= $current_page + $buttons_after; $button_index++) {
				if ($button_index <= $total_pages) {
					$buttons .= "<a title='Page {$button_index}' href='?{$page_key}={$button_index}&{$size_key}={$page_size}{$query_string}'><span class='page-number'>{$button_index}</span></a>";
				}
			} 
	
			// Spacer
			if ($current_page + $buttons_after < $total_pages - 2) {		
				$buttons .= "<span class='page-number page-dots'>...</span>";		
			}
			
			// Last
			if ($current_page <= $total_pages - $buttons_after-1) {		
				$buttons .= "<a title='Page {$total_pages}' href='?{$page_key}={$total_pages}&{$size_key}={$page_size}{$query_string}'><span class='page-number'>{$total_pages}</span></a>";		
			}
			
			// Next Button
			if ($current_page < $total_pages) {
				$button_page = $current_page + 1;
				$buttons .= "<a title='Page {$button_page}' href='?{$page_key}={$button_page}&{$size_key}={$page_size}{$query_string}'><span class='page-number page-next'>Next</span></a>";
			}
			
			// Page Sizes
			$page_size_buttons = "";
			if (count($page_sizes) > 1) {
				foreach ($page_sizes as $size) {
					if ($size == $page_size) { 
						$page_size_buttons .= "<span class='page-number current'>$size</span>";
					} else { 
						$page_size_buttons .= "<a title='$size items per page' href='?{$size_key}={$size}{$query_string}'><span class='page-number'>$size</span></a>";		
					}
				}
				$page_size_buttons .= "<span class='page-number per-page'>Per Page</span>";
			}
			if($total_pages > 1 || $show_single_page) return "<div class='pager'><div class='page-buttons'>{$buttons}</div><div class='page-sizes'>{$page_size_buttons}</div></div>";
			return "";
		}
	}
?>