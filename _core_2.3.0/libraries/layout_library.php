<?php
	/**
	 *	layout_library.php allows you to use layouts that are contained in APP_FOLDER/includes/layouts/.
	 *	
	 */
	 
	
	/**
	 *	Preps a layout for input.
	 *	
	 */
	if(!function_exists("layout_open")) {
		function layout_open($name = "default") {
			// catch un-closed layouts.
			if(globals("layout", "_open")) {
				$orphaned = globals("layout", "_open");
				ob_end_clean();
				die("Can't start layout {$name}. {$orphaned} layout is still open.");
			}
			
			globals("layout", "_open", $name);	// set it to open
			
			ob_start();
		}
	}
		
	/**
	 *	Closes the layout and renders it.
	 *	
	 */
	if(!function_exists("layout_close")) {
		function layout_close() {
			if(globals("layout", "_open")) {
				$default = ob_get_contents();
				ob_end_clean();
				
				layout_set_section("default", $default);
				
				$name = globals("layout", "_open");
				unset_global("layout", "_open");
				
				// Render the layout
				app_include("layouts/{$name}_layout");
			} else {
				die("Can't close layout {$name} -- it's not open.");
			}
		}
	}
	
	/**
	 *	Sets section contents
	 *	
	 */
	if(!function_exists("layout_set_section")) {
		function layout_set_section($section, $contents) {
			globals("layout_sections", $section, $contents);
		}
	}
	
	/**
	 *	Opens a layout section for content (if a layout is open) or outputs it if it's closed.
	 *	
	 */
	if(!function_exists("layout_section")) {
		function layout_section($section) {
			if($layout = globals("layout", "_open")) {
				// catch un-closed layouts.
				if(globals("layout_sections", "_open")) {
					$orphaned = globals("layout_sections", "_open");
					ob_end_clean();
					die("Can't start layout section {$section}. {$orphaned} layout section is still open.");
				}
				
				globals("layout_sections", "_open", $section);	// set it to open
				
				ob_start();
			} else {
				return globals("layout_sections", $section);
			}
		}
	}
	
	/**
	 *	Closes the layout section and saves it.
	 *	
	 */
	if(!function_exists("layout_section_close")) {
		function layout_section_close() {
			$section = globals("layout_sections", "_open");
			if(!$section) die("Can't close layout section -- no layout section is open.");
			unset_global("layout_sections", "_open");
			
			$content = ob_get_contents();
			ob_end_clean();
			
			layout_set_section($section, $content);
		}
	}
	
?>