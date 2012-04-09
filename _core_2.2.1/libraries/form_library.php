<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

	load_library('input');
	load_library('session');
	
	if(!defined("DEFAULT_FIELD_TEMPLATE")) {
		define("DEFAULT_FIELD_TEMPLATE", "
			<div id=\"field_%field_name%\" class=\"%field_classes%\">
				<label for=\"%field_name%\">%field_label%%required_html%</label>
				%control%
				%error_html%
				%help_html%		
			</div>
		");
	}
	
	if(!defined("DEFAULT_CHECKBOX_FIELD_TEMPLATE")) {
		define("DEFAULT_CHECKBOX_FIELD_TEMPLATE", "
			<div id=\"field_%field_name%\" class=\"%field_classes%\">			
				%control%
				<label for=\"%field_name%\">%field_label%%required_html%</label>
				%error_html%
				%help_html%		
			</div>	
		");
	}
	
	if(!defined("SIMPLE_FIELD_TEMPLATE")) {
		define("SIMPLE_FIELD_TEMPLATE", "
			<div id=\"field_%field_name%\" class=\"%field_classes%\">
				%control%
				%error_html%
				%help_html%	
			</div>		
		");	
	}
	
	if(!defined("ONLY_FIELD_TEMPLATE")) {
		define("ONLY_FIELD_TEMPLATE", "%control%");	
	}

	if(!defined("FORM_DISABLED")) define("FORM_DISABLED", -1);
	if(!defined("FORM_READONLY")) define("FORM_READONLY", 0);
	if(!defined("FORM_ENABLED")) define("FORM_ENABLED", 1);
	
	/**
	 * Returns HTML to open a form including a hidden field with verification data.
	 * 
	 * @return string
	 */
	if(!function_exists("form_open")) {
		function form_open($name, $action='', $method=NULL, $options = array(), $enabled_default = FORM_ENABLED) {
			globals('form', 'current_form', $name);
			globals('form', 'current_form_enabled', $enabled_default);
			$form_key = form_key();

			if ($method == NULL) $method = 'POST';
			$method = strtoupper($method);

			$form_classes = "";
			if(isset($options['class']) && $options['class'] != '') {
				$form_classes = $options['class'];
			}
			$form_classes_html = " class=\"$form_classes\"";

			$form_open_html = "<form id='{$name}' method='{$method}' enctype='multipart/form-data' action='{$action}' {$form_classes_html}>\n";
			if($method == "POST") {
				$key_name = form_field_name("key");
				$form_open_html .= "<input type=\"hidden\" name=\"{$key_name}\" value=\"{$form_key}\" />\n";
			}
			return $form_open_html;
		}
	} // end form_open
	
	/**
	 * Returns HTML to close a form with some sort of fancy javascript limit
	 * 
	 * @return string
	 */
	if(!function_exists("form_close")) {
		function form_close() {
			$html = "";
			$focuskey = globals('form', 'current_form') . '_focused_field';
			
			if (global_isset('form', $focuskey)) {
				$field = globals('form', $focuskey);
				$html = "<script type=\"text/javascript\">var obj = document.getElementById('{$field}'); obj.focus();</script>";
			}
			
			if (global_isset('form', 'textarea-limit')) {
				textarea_limit_js();
			}
			
			globals('form', 'current_form').'_focused_field';
			$html .= "</form>";
			unset_global('form', 'current_form');
			return $html;
		}
	} // end form_close
	
	/**
	 * Returns form key based on user session
	 * 
	 * @return string
	 */
	if(!function_exists("form_key")) {
		function form_key() {
			load_library("session");
			if(session_isset('FORM_KEY')) {
				return session('FORM_KEY');
			} else {
				$form_key = uuid();
				session('FORM_KEY', $form_key);
				return $form_key;
			}
		}
	} // end form_key

	/**
	 * Returns value of a form input via input_post()
	 * 
	 * @return string
	 */
	if(!function_exists("form_value")) {
		function form_value($name, $value = NULL, $html = false) {
			$fname = form_field_name($name);
			if($value === NULL) {
				if($html) return input_post_html($fname);
				return input_post($fname);
			} else {
				$_POST[$fname] = $value;
			}
		}
	}

	/**
	 * Friendly alias for form_value() that returns HTML
	 * 
	 * @return string
	 */
	if(!function_exists("form_value_html")) {
		function form_value_html($name, $value = NULL) {
			return form_value($name, $value, true);
		}
	}

	/**
	 * Returns input value as array using specified $key via input_post()
	 * 
	 * @return array
	 */
	if(!function_exists("form_array_value")) {
		function form_array_value($name, $key, $value = NULL, $html = false) {
			$fname = form_field_name($name);
			if($value === NULL) {
				if(!$html) $arry = input_post($fname);
				if($html) $arry = input_post_html($fname);
				return $arry[$key];
			} else {
				$_POST[$fname][$key] = $value;
			}
		}
	}
	/**
	 * Friendly alias of form_array_value() that returns HTML
	 * 
	 * @return array
	 */
	if(!function_exists("form_array_value_html")) {
		function form_array_value_html($name, $key, $value = NULL) {
			return form_array_value($name, $key, $value, true);
		}
	}

	/**
	 * Returns string with HTML Special Charaters encoded
	 * 
	 * @return string
	 */
	if(!function_exists("form_escape_html")) {
		function form_escape_html($value, $charset = "UTF-8") {
			return htmlspecialchars($value, ENT_COMPAT, $charset, false);
		}
	}

	/**
	 * Returns HTML of an input type=textbox
	 * 
	 * Allowed $options = length, maxlength, size, control_class, required, style, autocomplete, placeholder, custom
	 * 
	 * @return string
	 */
	if(!function_exists("form_textbox")) {
		function form_textbox($name = '', $label = '', $value = NULL, $options = array(), $enabled = FORM_ENABLED) {
			$classes = array('textbox');
			$fname = form_field_name($name);
			$value = form_escape_html($value);
			
			/********/
				$attributes = "";
				
				foreach($options as $k => $v) {
					switch ($k) {	
						case "error":
							break;
						case "custom":
							$attributes .= $v . " ";
							unset($options[$k]);
							break;
						case "required":
							$classes[] = 'required';
							unset($options[$k]);
							break;
						case "help": 
							break;
						case "control_class":
							$control_class = $v . " ";
							unset($options[$k]);
							break;
						case "template":
							break;
						default: // autocomplete, max_length, size, style, placeholder, custom
							$attributes .= "{$k}=\"{$v}\" ";
							unset($options[$k]);
							break;
					}
				}
			/********/
			/*
				$max_length_html = '';
				$style_html = '';
				if (isset($options['length']) && $options['length'] > 0) { $max_length_html = " maxlength=\"" . $options['length'] . "\" "; }
				if (isset($options['maxlength']) && $options['maxlength'] > 0) { $max_length_html = " maxlength=\"" . $options['maxlength'] . "\" "; }
				if (isset($options['size']) && $options['size'] > 0) { $size_html = " size=\"" . $options['size'] . "\" "; }
				
				// Check for form validation
				if(!isset($options['error'])) $options['error'] = form_validate_msg($name);
				if ($options['error'] != '') $classes[] = 'error';
				$custom_attr = (isset($options['custom'])) ? $options['custom'] : '';
				$control_class = (isset($options['control_class'])) ? $options['control_class'] : '';
				if (isset($options['required']) && $options['required'] != '') { $classes[] = 'required'; }
				if (isset($options['style']) && $options['style'] != '') { $style_html = "style=\"" . $options['style'] . "\" "; }
				$autocomplete = ""; if (isset($options['autocomplete']) && $options['autocomplete'] == "off") $autocomplete = " autocomplete=\"off\" ";
				$placeholder = ""; if (isset($options['placeholder'])) $placeholder = " placeholder=\"{$options['placeholder']}\" ";
			*/
			$readonly_html="";
			if ($enabled == FORM_ENABLED) {
			} elseif($enabled == FORM_READONLY || globals('form', 'current_form_enabled') == FORM_READONLY) {
				$classes[] = 'readonly'; $readonly_html=" READONLY='READONLY' ";
			} elseif($enabled == FORM_DISABLED || globals('form', 'current_form_enabled') == FORM_DISABLED) {
				$classes[] = 'disabled'; $readonly_html=" DISABLED='DISABLED' ";
			}

			
			if($value === NULL && form_value($name) !== NULL) $value = form_escape_html(form_value($name));

			return _form_field("<input type=\"text\" id=\"{$fname}\" name=\"{$fname}\" class=\"" . implode(" ", $classes) . " {$control_class}\" value=\"{$value}\" {$readonly_html} {$attributes} />", $fname, $label, $options);
		}
	}
	
	/**
	 * Returns HTML of an input type=password
	 * 
	 * Allowed $options = control_class, autocomplete, required, max_length, custom
	 * 
	 * @return string
	 */
	if(!function_exists("form_password")) {
		function form_password($name='', $label = '', $value = NULL, $options = array(), $enabled = FORM_ENABLED) {
			$classes = array('password');
			$fname = form_field_name($name);
			$value = form_escape_html($value);

			/********/
				$attributes = "";
				
				foreach($options as $k => $v) {
					switch ($k) {	
						case "error":
							break;
						case "custom":
							$attributes .= $v . " ";
							unset($options[$k]);
							break;
						case "required":
							$classes[] = 'required';
							unset($options[$k]);
							break;
						case "control_class":
							$control_class = $v . " ";
							unset($options[$k]);
							break;
						case "help": 
							break;
						case "template":
							break;
						default: // autocomplete, max_length, custom
							$attributes .= "{$k}=\"{$v}\" ";
							unset($options[$k]);
							break;
					}
				}
			/********/
			/*
				$custom_attr = (isset($options['custom'])) ? $options['custom'] : '';
				$control_class = (isset($options['control_class'])) ? $options['control_class'] : '';
				$max_length_html = '';
				if (isset($options['length']) && $options['length'] > 0) { $max_length_html = "maxlength='" . $options['length'] . "' "; }
				if(!isset($options['error'])) $options['error'] = form_validate_msg($name);
				if ($options['error'] != '') $classes[] = 'error';
				if (isset($options['required']) && $options['required'] != '') { $classes[] = 'required'; }
				$autocomplete = " autocomplete='off' ";
				if (isset($options['autocomplete']) && $options['autocomplete'] == "on") $autocomplete = " autocomplete='on' ";
			*/
			$readonly_html = "";
			if ($enabled == FORM_ENABLED) {
				// do nothing
			} elseif ($enabled == FORM_READONLY || globals('form', 'current_form_enabled') == FORM_READONLY) {
				$classes[] = 'readonly'; $readonly_html=" READONLY='READONLY' ";
			} elseif($enabled == FORM_DISABLED || globals('form', 'current_form_enabled') == FORM_DISABLED) {
				$classes[] = 'disabled'; $readonly_html=" DISABLED='DISABLED' ";
			}

			return _form_field("<input type=\"password\" id=\"{$fname}\" name=\"{$fname}\" class=\"" . implode(" ", $classes) . " {$control_class}\" value=\"{$value}\" {$readonly_html} {$attributes} />", $fname, $label, $options);
		}
	}

	/**
	 * Returns HTML of a textarea
	 * 
	 * Allowed $options = control_class, custom, length, rows, cols, required, 
	 * 
	 * @return string
	 */
	if(!function_exists("form_textarea")) {
		function form_textarea($name = '', $label = '', $value = NULL, $options = array(), $enabled = FORM_ENABLED) {
			$fname = form_field_name($name);
			$classes = array('textarea');
			$value = form_escape_html($value);
			
			/********/
				$attributes = "";
				
				foreach($options as $k => $v) {
					switch ($k) {	
						case "error":
							break;
						case "custom":
							$attributes .= $v . " ";
							unset($options[$k]);
							break;
						case "required":
							$classes[] = 'required';
							unset($options[$k]);
							break;
						case "control_class":
							$control_class = $v . " ";
							unset($options[$k]);
							break;
						case "template":
							break;
						case "help": 
							break;
						default: // rows, cols, max_length, placeholder
							$attributes .= "{$k}=\"{$v}\" ";
							unset($options[$k]);
							break;
					}
				}
			/********/
			
			/*
				$readonly_html=""; $rows_html = ""; $cols_html = "";
				$custom_attr = (isset($options['custom'])) ? $options['custom'] : '';
				$control_class = (isset($options['control_class'])) ? $options['control_class'] : '';
				$max_length_html = "";
				if (isset($options['length']) && $options['length'] > 0) { $max_length_html = " data-maxlength=\"" . $options['length'] . "\" "; globals('form', 'textarea-limit', true); }
				if (isset($options['rows']) && $options['rows'] > 0) { $rows_html = " rows=\"" . $options['rows'] . "\""; }
				if (isset($options['cols']) && $options['cols'] > 0) { $cols_html = " cols=\"" . $options['cols'] . "\""; }
				$placeholder = ""; if (isset($options['placeholder'])) $placeholder = " placeholder=\"{$options['placeholder']}\" ";
				if(!isset($options['error'])) $options['error'] = form_validate_msg($name);
				if ($options['error'] != '') $classes[] = 'error';
				if (isset($options['required']) && $options['required'] != '') { $classes[] = 'required'; }
			*/
			
			$readonly_html = "";
			if ($enabled == FORM_ENABLED) {
				// do nothing
			} elseif ($enabled == FORM_READONLY || globals('form', 'current_form_enabled') == FORM_READONLY) {
				$classes[] = 'readonly'; $readonly_html=" READONLY='READONLY' ";
			} elseif($enabled == FORM_DISABLED || globals('form', 'current_form_enabled') == FORM_DISABLED) {
				$classes[] = 'disabled'; $readonly_html=" DISABLED='DISABLED' ";
			}
			if($value === NULL && form_value($name) !== NULL) $value = form_value($name);
			
			return _form_field("<textarea id=\"{$fname}\" name=\"{$fname}\" class=\"" . implode(" ", $classes) . " {$control_class}\" {$attributes} {$readonly_html} {$custom_attr}>{$value}</textarea>", $fname, $label, $options);
		}
	}


	/**
	 * Creates a checkbox control. If the $value is non-zero, will be checked. The default input value is "yes"
	 * and you can override it with the option "checked_value". "unchecked_value" defaults to "no" but can also
	 * be overrode.
	 * 
	 * Allowed $options = template, checked_value, unchecked_value, custom, control_class
	 *
	 * @return string
	 */
	if(!function_exists("form_checkbox")) {
		function form_checkbox($name = '', $label = '', $value = NULL, $options = array(), $enabled = FORM_ENABLED) {
			$fname = form_field_name($name);
			$classes = array('checkbox');
			
			if(!isset($options['template'])) $options['template'] = DEFAULT_CHECKBOX_FIELD_TEMPLATE;
			
			$checked_value = "on"; if(isset($options['checked_value'])) $checked_value = $options['checked_value'];
			$unchecked_value = ""; if(isset($options['unchecked_value'])) $unchecked_value = $options['unchecked_value'];

			if(!isset($options['error'])) $options['error'] = form_validate_msg($name);
			if ($options['error'] != '') $classes[] = 'error';
			
			if($value === NULL && form_value($name) !== NULL) $value = form_value($name);
			$checked = ($value === false || $value === 0 || $value === NULL || $value == '0' || $value == '') ? '' : ' checked="checked" ';
			
			$custom_attr = (isset($options['custom'])) ? $options['custom'] : '';
			$control_class = (isset($options['control_class'])) ? $options['control_class'] : '';
			$readonly_html = "";
			if ($enabled == FORM_ENABLED) {
			} elseif ($enabled == FORM_READONLY || globals('form', 'current_form_enabled') == FORM_READONLY) {
				$classes[] = 'readonly'; $readonly_html=" READONLY='READONLY' ";
			} elseif($enabled == FORM_DISABLED || globals('form', 'current_form_enabled') == FORM_DISABLED) {
				$classes[] = 'disabled'; $readonly_html=" DISABLED='DISABLED' ";
			}
			
			if(settings("form", "submit_unchecked_checkboxes") == true) $unchecked = "<input type=\"hidden\" name=\"{$fname}\" id=\"{$fname}_no_value\" value=\"{$unchecked_value}\" {$readonly_html} />";
			
			return _form_field("{$unchecked}<input type=\"checkbox\" id=\"{$fname}\" name=\"{$fname}\" value=\"{$checked_value}\" class=\"" . implode(" ", $classes) . " {$control_class}\" {$checked} {$readonly_html} {$custom_attr} />", $fname, $label, $options);
		}
	}

	/**
	 * Returns HTML for input type=radio
	 * 
	 * Allowed $options = template, custom, control_class, ignore_error
	 * 
	 * @return string
	 */
	if(!function_exists("form_radio")) {
		function form_radio($name='', $values=array(), $checked_value=NULL, $options = array(), $enabled = FORM_ENABLED) {
			$fname = form_field_name($name);
			$classes = array('radio');
			$html = "";

			if(!isset($options['template'])) {
				$options['template'] = DEFAULT_CHECKBOX_FIELD_TEMPLATE;
			}

			$custom_attr = (isset($options['custom'])) ? $options['custom'] : '';
			$control_class = (isset($options['control_class'])) ? $options['control_class'] : '';
			$readonly_html = "";
			if ($enabled == FORM_ENABLED) {
			} elseif ($enabled == FORM_READONLY || globals('form', 'current_form_enabled') == FORM_READONLY) {
				$classes[] = 'readonly'; $readonly_html=" READONLY='READONLY' ";
			} elseif($enabled == FORM_DISABLED || globals('form', 'current_form_enabled') == FORM_DISABLED) {
				$classes[] = 'disabled'; $readonly_html=" DISABLED='DISABLED' ";
			}

			$keys = array_keys($values);
			for ($index = 0; $index < count($values); $index++) {
				$value = $keys[$index];
				$label = $values[$value];

				if($value === NULL && form_value($name) !== NULL) $checked_value = form_escape_html(form_value($name));
				
				if(!isset($options['error'])) $options['error'] = form_validate_msg($name);
				if ($options['error'] != '') $classes[] = 'error';

				$options['ignore_error'] = ($index < count($values) - 1);			

				$checked = ""; if($value == $checked_value) $checked = " CHECKED=\"CHECKED\" ";
				$html .= _form_field("<input type=\"radio\" id=\"{$fname}_{$value}\" name=\"{$fname}\" {$readonly_html} value=\"{$value}\" class=\"" . implode(" ", $classes) . " {$control_class}\" {$checked} {$custom_attr} />", "{$fname}_{$value}", $label, $options);
			}

			return $html;
		}
	}

	/**
	 * Creates a select element. For $values, use an array either like this:
	 * array("Value of the option such as an id" => "Label or name of the option")
	 * or a multidimensional array. Put in the options array what fields you want to use
	 * for the label and value of the options, using for example
	 * array("option_id" => "id", "option_label" => "name")
	 *
	 * Additionally, option_label can be set to an array:
	 * "option_label" => array("item", " - $", "price")
	 * If a label is not found in the data array, it will be used as a separator (" - $" in
	 * example above)
	 * 
	 * Here's a way to add an option at the beginning of the select element:
	 * "option_default" => "None Selected", "option_default_value" => ""
	 *
	 * To use the array's label (or name) as the option value, set the option "key_same_as_label" to true.
	 * 
	 * Allowed $options = required, size, custom, control_class, option_default_value, option_default, 
	 * option_id, option_label, key_same_as_label, 
	 * 
	 * @return string
	 */
	if(!function_exists("form_select")) {
		function form_select($name='', $label='', $value=NULL, $values=NULL, $options = array(), $enabled = FORM_ENABLED) {
			$classes = array('select');
			$fname = form_field_name($name);

			$max_length_html = '';
			if(!isset($options['error'])) $options['error'] = form_validate_msg($name);
			if ($options['error'] != '') $classes[] = 'error';

			if (isset($options['required']) && $options['required'] != '') { $classes[] = 'required'; }

			$size_html = "";
			if (isset($options['size']) && $options['size'] != '') { $size_html = ' size="' . $options['size']  . '"'; }

			$custom_attr = (isset($options['custom'])) ? $options['custom'] : '';
			$control_class = (isset($options['control_class'])) ? $options['control_class'] : '';
			$readonly_html = "";
			if ($enabled == FORM_ENABLED) {
			} elseif ($enabled == FORM_READONLY || globals('form', 'current_form_enabled') == FORM_READONLY) {
				$classes[] = 'readonly'; $readonly_html=" READONLY='READONLY' ";
			} elseif($enabled == FORM_DISABLED || globals('form', 'current_form_enabled') == FORM_DISABLED) {
				$classes[] = 'disabled'; $readonly_html=" DISABLED='DISABLED' ";
			}

			if($value === NULL && form_value($name) !== NULL) $value = form_value($name);

			$option_html = "";
			if(isset($options['option_default'])) $option_html .= "<option value=\"{$options['option_default_value']}\">{$options['option_default']}</option><option>---------</option>";
			if(is_array($values)) {
				foreach($values as $val => $name) {
					// PHP AR Object Handling
					if(is_object($name)) {
						$name = $name->attributes();
					}
					
					if(is_array($name)) {
						if(isset($options['option_id'])) $sel_val = $name[$options['option_id']];
						if(isset($options['option_label'])) {
							if(is_array($options['option_label'])) {
								unset($sel_name);
								foreach($options['option_label'] as $option_label) {
									if(isset($name[$option_label])) {
										$sel_name .= $name[$option_label];
									} else {
										$sel_name .= $option_label;
									}
								}
							} else {
								$sel_name = $name[$options['option_label']];
							}
						}
						// if(isset($options['option_label_extension'])) $sel_name .= $options['option_label_divider'] . $name[$options['option_label_extension']];
					} else {
						$sel_name = $name;
						$sel_val = form_escape_html($val);
					}
					
					if($options['key_same_as_label']) $sel_val = $name;
					
					$selected = "";
					if("$sel_val" == $value) { $selected = "selected=\"selected\""; } // Note: the cast to string is necessary in order to prevent 0 from equaling empty string.
					$option_html .= "<option value=\"{$sel_val}\" {$selected}>{$sel_name}</option>";
				}
			}

			return _form_field("<select id='{$fname}' name='{$fname}' class=\"" . implode(" ", $classes) . " {$control_class}\" {$readonly_html} {$custom_attr}{$size_html}>{$option_html}</select>", $fname, $label, $options);
		}
	}
	
	/**
	 * Returns HTML for input type=submit
	 * 
	 * Allowed $options = custom, control_class, control_id
	 * 
	 * @return string
	 */
	if(!function_exists("form_submit")) {
		function form_submit($name='', $label='', $value = NULL, $options = array(), $enabled = FORM_ENABLED) {
			$fname = form_field_name($name);
			$value = form_escape_html($value);
			
			$custom_attr = (isset($options['custom'])) ? $options['custom'] : '';
			$control_class = (isset($options['control_class'])) ? $options['control_class'] : '';
			$control_id = (isset($options['control_id'])) ? $options['control_id'] : '';
			$readonly_html = ""; $classes = array();
			
			if ($enabled == FORM_ENABLED) {
				// do nothing
			} elseif ($enabled == FORM_READONLY || globals('form', 'current_form_enabled') == FORM_READONLY) {
				$classes[] = 'readonly'; $readonly_html=" READONLY='READONLY' ";
			} elseif($enabled == FORM_DISABLED || globals('form', 'current_form_enabled') == FORM_DISABLED) {
				$classes[] = 'disabled'; $readonly_html=" DISABLED='DISABLED' ";
			}
			return _form_field("<input type=\"submit\" id=\"{$fname} {$control_id}\" name=\"{$fname}\" class=\"submit " . implode(" ", $classes) . " {$control_class}\" value=\"{$value}\" {$readonly_html} {$custom_attr} />", $fname, $label, $options);
		}
	}
	
	/**
	 * Returns HTML for button
	 * 
	 * Allowed $options = custom, control_class, control_id
	 * 
	 * @return string
	 */
	if(!function_exists("form_button")) {
		function form_button($name = '', $label = '', $value = NULL, $options = array(), $enabled = FORM_ENABLED) {
			$fname = form_field_name($name);
			$value = form_escape_html($value);

			$custom_attr = (isset($options['custom'])) ? $options['custom'] : '';
			$control_class = (isset($options['control_class'])) ? $options['control_class'] : '';
			$control_id = (isset($options['control_id'])) ? $options['control_id'] : '';
			$readonly_html = ""; $classes = array();
			
			if ($enabled == FORM_ENABLED) {
				// do nothing
			} elseif ($enabled == FORM_READONLY || globals('form', 'current_form_enabled') == FORM_READONLY) {
				$classes[] = 'readonly'; $readonly_html=" READONLY='READONLY' ";
			} elseif($enabled == FORM_DISABLED || globals('form', 'current_form_enabled') == FORM_DISABLED) {
				$classes[] = 'disabled'; $readonly_html=" DISABLED='DISABLED' ";
			}
			return _form_field("<button type=\"submit\" id=\"{$fname} {$control_id}\" name=\"{$fname}\" class=\"submit " . implode(" ", $classes) . " {$control_class}\" {$readonly_html} {$custom_attr}><span>{$value}</span></button>", $fname, $label, $options);
		}
	}
	
	/**
	 * Returns HTML for a link that submits form via javascript onclick
	 * 
	 * Allowed $options = custom, control_class
	 * 
	 * @return string
	 */
	if(!function_exists("form_link")) {
		function form_link($name='', $label='', $value = NULL, $options = array(), $enabled = FORM_ENABLED) {
			$fname = form_field_name($name);
			$value = form_escape_html($value);
			
			$custom_attr = (isset($options['custom'])) ? $options['custom'] : '';
			$control_class = (isset($options['control_class'])) ? $options['control_class'] : '';
			$readonly_html = ""; $classes = array();
			
			if ($enabled == FORM_ENABLED) {
				// do nothing
			} elseif ($enabled == FORM_READONLY || globals('form', 'current_form_enabled') == FORM_READONLY) {
				$classes[] = 'readonly'; $readonly_html=" READONLY='READONLY' ";
			} elseif($enabled == FORM_DISABLED || globals('form', 'current_form_enabled') == FORM_DISABLED) {
				$classes[] = 'disabled'; $readonly_html=" DISABLED='DISABLED' ";
			}

			return _form_field("<a href=\"javascript:void(0);\" {$readonly_html} {$custom_attr} onclick=\"$(this).parents('form')[0].submit(); return false;\"  class=\"". implode(" ", $classes) . " {$control_class}\">{$label}</a>", $fname, $label, $options);
		}
	}

	
	/**
	 * Returns HTML for input type=hidden
	 * 
	 * @return string
	 */
	if(!function_exists("form_hidden")) {
		function form_hidden($name = '', $value = '', $attributes = array()) {
			$fname = form_field_name($name);
			$value = form_escape_html($value);
			
			$attributes_html = "";
			foreach ($attributes as $key => $val) {
				$attributes_html .= " {$key} = '{$val}' ";
			}
			
			return "<input type=\"hidden\" name=\"{$fname}\" id=\"{$fname}\" value=\"{$value}\" {$attributes_html} />";
		}
	}
	
	/**
	 * Returns HTML for input type=file
	 * 
	 * Allowed $options = custom, control_class
	 * 
	 * @return string
	 */
	if(!function_exists("form_file")) {
		function form_file($name='', $label='', $options = array(), $enabled = FORM_ENABLED) {
			$fname = form_field_name($name);
			
			$custom_attr = (isset($options['custom'])) ? $options['custom'] : '';
			$control_class = (isset($options['control_class'])) ? $options['control_class'] : '';
			$readonly_html = ""; $classes = array();
			if ($enabled == FORM_ENABLED) {
			} elseif ($enabled == FORM_READONLY || globals('form', 'current_form_enabled') == FORM_READONLY) {
				$classes[] = 'readonly'; $readonly_html=" READONLY='READONLY' ";
			} elseif($enabled == FORM_DISABLED || globals('form', 'current_form_enabled') == FORM_DISABLED) {
				$classes[] = 'disabled'; $readonly_html=" DISABLED='DISABLED' ";
			}
			return  _form_field("<input name=\"{$fname}\" type=\"file\" class=\"file " . implode(" ", $classes) . " {$control_class}\" {$readonly_html} {$custom_attr} />", $fname, $label, $options);
		}
	}
	
	/**
	 * Wraps $value in a div.custom instead of a form element. Read only, of course.
	 * 
	 * @return string
	 */
	if(!function_exists("form_custom")) {
		function form_custom($name = "", $label = "", $value = NULL, $options = array()) {
			$fname = form_field_name($name);
			//$value = form_escape_html($value); // we should let the developer do this;
			$custom_attr = (isset($options['custom'])) ? $options['custom'] : '';
			$control_class = (isset($options['control_class'])) ? $options['control_class'] : '';

			return _form_field("<div id=\"{$fname}\" name=\"{$fname}\" class=\"custom {$control_class}\" {$custom_attr}>{$value}</div>", $fname, $label, $options);
		}
	}
		
	/**
	 * Returns array with information on uploaded file
	 * 
	 * @return array
	 */
	if(!function_exists("form_get_file")) {
		function form_get_file($name) {
			$fname = form_field_name($name);
			return input_file_array($fname);
		}
	}
	
	
	/**
	 *	Saves uploaded file. Do not include extension in filename, it will be set automatically.
	 *	If you need to set a custom extension, use $options['extension'].
	 *	
	 *	$options['filetypes'] can be "jpg|png|gif" or array("jpg", "png", "gif")
	 *	
	 *	Example: form_file_save("field", BASE_FOLDER . "/uploads/folder/", "%filename%", array());
	 *	
	 * @return string
	 */
	if(!function_exists("form_file_save")) {
		function form_file_save($field, $path, $filename = "%filename%", $options = array()) {
			$file = form_get_file($field);
			// If no file, return false
			if(strlen($file['tmp_name']) == 0) return FALSE;
			
			// Gather file information and set filename
			$fileinfo = pathinfo($file['name']);
			$filename = str_replace("%filename%", $fileinfo['filename'], $filename);
			
			// Options
			if($options['filetypes']) {
				if(is_array($options['filetypes'])) {
					$valid_extensions = $options['filetypes'];
				} else {
					$valid_extensions = explode("|", $options['filetypes']);
				}
			} else {
				$valid_extensions = FALSE;
			}
			if($options['extension']) {
				$fileinfo['extension'] = $options['extension'];
			}
			
			// Check extension
			if($valid_extensions === FALSE || in_array(strtolower($fileinfo['extension']), (array)$valid_extensions)) {
				// Save file
				$new_path_and_file = $path . $filename . "." . $fileinfo['extension'];
				safe_unlink($new_path_and_file);
				move_uploaded_file($file['tmp_name'], $new_path_and_file);
				if($options['width'] && $options['height']) {
					load_library("image");
					image_resize($new_path_and_file, $new_path_and_file, $options['width'], $options['height'], $options['type']);
				}
			} else {
				globals('form', globals('form', 'current_form') . '_valid', false);
				globals('form', 'val_' . $field, "Invalid extension.");
				return FALSE;
			}
			return $filename . "." . $fileinfo['extension'];
		}
	}
	
	if(!function_exists("_form_field")) {
		function _form_field($control='', $field_name='', $field_label='', $options = array()) {
			$required_html = "";
			if(isset($options['required']) && $options['required'] != '') {
				$required_text = "*";
				if(isset($options['required_text'])) $required_text = $options['required_text'];
				$required_html = "<abbr title=\"" . $options['required'] . "\">{$required_text}</abbr>";
			}

			$field_classes = "field";
			if(isset($options['class']) && $options['class'] != '') {
				$field_classes .= " " . $options['class'];
			}

			$error_html = "";
			if (!(isset($options['ignore_error']) && $options['ignore_error'] === true)) {
				if(isset($options['error']) && $options['error'] != '') {
					$error_html = "<p class=\"error\">" . $options['error'] . "</p>";
				} elseif(form_validate_msg($field_name) !== NULL) {
					$error_html = "<p class=\"error\">" . form_validate_msg($field_name) . "</p>";
					if(strpos($options['class'], "error") == 0) {
						$field_classes .= " error ";
					}
				}
			}

			$help_html = "";
			if(isset($options['help']) && $options['help'] != '') {
				$help_html = "<p class=\"help\">" . $options['help'] . "</p>";
			}

			$template = "";
			if(isset($options['template']) && $options['template'] != '') {
				$template = $options['template'];
			} else {
				$template = DEFAULT_FIELD_TEMPLATE;
			}

			$template = str_replace("%field_name%", $field_name, $template);
			$template = str_replace("%field_label%", $field_label, $template);
			$template = str_replace("%required_html%", $required_html, $template);
			$template = str_replace("%field_classes%", $field_classes, $template);
			$template = str_replace("%error_html%", $error_html, $template);
			$template = str_replace("%help_html%", $help_html, $template);
			$template = str_replace("%control%", $control, $template); // has to be last

			return $template;
		}
	}
	
	/**
	 * Returns the long-form field name (formname_fieldname)
	 * 
	 * @return string
	 */
	if(!function_exists("form_field_name")) {
		function form_field_name($name) {
			$form_name = globals("form", "current_form");
			if(is_ajax() || strlen($form_name) < 1) return $name;
			return $form_name . "_" . $name;
		}
	}

	/**
	 * Set Global with name of current form. Important for processing.
	 * 
	 * @return NULL
	 */
	if(!function_exists("form_set_focus")) {
		function form_set_focus($name) {
			globals('form', globals('form', 'current_form') . '_focused_field', form_field_name($name));
		}
	}
	
	// Form Validation
	
	/**
	 * Checks if the form was posted and if the hidden form key is valid and sets a Global
	 * 
	 * @return bool
	 */
	if(!function_exists("form_posted")) {
		function form_posted($name, $check_key_is_valid = true) {
			globals('form', 'current_form', $name);
			if(input_post("{$name}_key") !== NULL) {
				if ($check_key_is_valid == false || form_key() == input_post("{$name}_key")) {
					globals('form', $name . '_valid', true);
					return TRUE;
				}
				die("
					<p class='error'>
						There was an error with the form submittal. The form keys don't match.
						Developer: check BASE_URL matches actual URL and that there is no trailing slash.
						This is usually a session issue.
					</p>
				");
			} elseif(is_ajax() && array_count($_POST) > 0) {
				load_library("security");
				if(security_check()) {
					globals("form", "current_form", "");
					return TRUE;
				}
			}
			return false;
		}
	}
	
	/**
	 * Returns HTML of form status (saved in a Global)
	 * 
	 * @return string
	 */
	if(!function_exists("form_status")) {
		function form_status($status='') {
			$form_name = globals('form', 'current_form');
			if ($status<>'') {
				globals('form', "form_status_" . $form_name, $status);
			} else if (global_isset('form', "form_status_" . $form_name)) {
				return "<p class=\"form-status\">" . globals('form', "form_status_" . $form_name)."</p>";
			} else {
				return "";
			}
		}
	}
	
	/**
	 * Unset current form in Globals
	 * 
	 * @return NULL
	 */
	if(!function_exists("form_end_validation")) {
		function form_end_validation() {
			unset_global('form', 'current_form');
		}
	}
	
	/**
	 * Returns Global form valid/invalid
	 * 
	 * @return bool
	 */
	if(!function_exists("form_is_valid")) {
		function form_is_valid() {
			return globals('form', globals('form', 'current_form').'_valid');
		}
	}
	
	/**
	 *	Friendly alias for form_validate() but allows HTML (uses HTMLPurifier by default)
	 *	
	 *	@return string
	 */
	if(!function_exists("form_validate_html")) {
		function form_validate_html($name, $rulestring='trim') {
			return form_validate($name, $rulestring, TRUE);
		}
	}
	
	/**
	 * Cleans, validates, and returns input value
	 * 
	 * @return string
	 */
	if(!function_exists("form_validate")) {
		function form_validate($name, $rulestring='trim', $allow_html = false) {
			load_library("validation");

			$old_validation = array("required", "maxlength", "int_range", "decimal", "enum");

			if($allow_html) {
				$valueobj = form_value_html($name);
			} else {
				$valueobj = form_value($name);
			}
			
			$fname = form_field_name($name);

			$rules = explode("|", $rulestring);
			$ruleArgs = array();

			/* Build arg list first */
			foreach ((array)$rules as $rule) {
				preg_match('/(.*)\[(.*?)\]/i', $rule, $matches);
				$ruleArgs[$rule] = $matches;
			}

			if (!is_array($valueobj)) {
				foreach ($rules as $rule) {
					// Check for 1.3 validation and alert if found
					if(in_array($rule, $old_validation)) die("Old validation rules found! Upgrade validation to 1.4-compatible.");

					$matches = $ruleArgs[$rule];
					if(count($matches) > 2) {
						$rule = $matches[1];
						$argstring = $matches[2];
						$args = explode(",", $argstring);
						switch(count($args)) {
							case 1: $result = $rule($valueobj, $args[0]); break;
							case 2: $result = $rule($valueobj, $args[0], $args[1]); break;
							case 3: $result = $rule($valueobj, $args[0], $args[1], $args[2]); break;
						}
					} else {
						$result = $rule($valueobj);
					}

					if ($result === false) { 
						globals('form', globals('form', 'current_form').'_valid', false);
						globals('form', 'val_'.$fname, $rule);
						return false;
					} else { //elseif (is_string($result)) {
						$valueobj = $result;
					}
				}

				$_POST[$fname] = $valueobj;

			} else {
				$form_valid = true;
				foreach ($valueobj as $key => $value) {
					foreach ($rules as $rule) {
						$matches = $ruleArgs[$rule];
						if (count($matches) > 2) {
							$rule = $matches[1];
							$argstring = $matches[2];
							$args = explode(",", $argstring);
							switch(count($args)) {
								case 1: $result = $rule($value, $args[0]); break;
								case 2: $result = $rule($value, $args[0], $args[1]); break;
								case 3: $result = $rule($value, $args[0], $args[1], $args[2]); break;
							}
						} else {
							$result = $rule($value);
						}

						if ($result === false) {
							$form_valid = false;
							globals('form', "val_".$fname."[{$key}]", $rule);
						} else { //if (is_string($result)) {
							$value = $result;
						}
					}

					$_POST[$fname][$key] = $value;
				}

				if ($form_valid == false) {
					globals('form', globals('form', 'current_form').'_valid', false);
					return false;
				}
			}
			
			return $valueobj;
		}
	}
	
	/**
	 *	Returns set value from a checkbox based on checked status.
	 *	Assumes "yes" or "on" or "checked" as checked value.
	 *	
	 *	Returns the true and false variables set in $true and $false.
	 *	
	 *	@return boolean
	 */
	if(!function_exists("form_validate_checkbox")) {
		function form_validate_checkbox($name, $true = 1, $false = 0) {
			if(settings("form", "submit_unchecked_checkboxes") && form_value($name) === NULL) return NULL;
			$value = form_value($name);
			return val_checkbox($value, $true, $false);
		}
	}
	
	/**
	 * Returns set error message if form_validate() failed.
	 * 
	 * @return string
	 */
	if(!function_exists("form_validate_msg")) {
		function form_validate_msg($name) {
			$fname = form_field_name($name);

			if(global_isset('form', 'val_'.$fname)) {
				$rule = globals('form', 'val_'.$fname);
				
				if (setting_isset('form', 'errormsg_'.$rule)) {
					return settings('form', 'errormsg_'.$rule);
				} else {
					return settings('form', 'errormsg_default');
				}
			}
		}
	}
	
	/**
	 * Returns an array containing all of the form elements submitted in a form, matched to a table in the database.
	 * 
	 * If you don't want this to access a particular field, set the $custom array value to false for that field.
	 * 
	 * $allow_html is an array where you can pass in fieldnames that HTML should be allowed in.
	 * 
	 * @return array
	 */
	if(!function_exists("form_validate_all")) {
		function form_validate_all($db_fields, $custom = array(), $allow_html = array()) {
			$result = array();
			if (is_array($db_fields)) {
				foreach($db_fields as $db_field) {
					if(form_value($db_field) !== NULL) {
						if(isset($custom[$db_field]) && $custom[$db_field] !== FALSE) { // run custom validation, if any
							$result[$db_field] = form_validate($db_field, $custom[$db_field], isset($allow_html[$db_field]));
						} elseif($custom[$db_field] !== FALSE) { // run normal validation, unless set to false.
							$result[$db_field] = form_validate($db_field, "trim", isset($allow_html[$db_field]));
						} else {
							// Set to false -- don't run validation and don't make the field.
						}
					}
				}
			}
			return $result;
		}
	}

	/* Legacy */
	if(settings('form', 'errormsg_valid_int') === NULL) settings('form', 'errormsg_valid_int', 'The value enter is not an integer.');
	if(settings('form', 'errormsg_required') === NULL) settings('form', 'errormsg_required', 'This field is required.');
	if(settings('form', 'errormsg_maxlength') === NULL) settings('form', 'errormsg_maxlength', 'This field did not meet the maximum length requirement.');
	if(settings('form', 'errormsg_minlength') === NULL) settings('form', 'errormsg_minlength', 'This field did not meet the minimum length requirement.');
	if(settings('form', 'errormsg_int_range') === NULL) settings('form', 'errormsg_int_range', 'The integer value is not in valid range.');
	if(settings('form', 'errormsg_valid_email') === NULL) settings('form', 'errormsg_valid_email', 'Invalid email address.');




	/**
	 * Fancy javascript textarea length limit
	 * 
	 * @return NULL
	 */
	if(!function_exists("textarea_limit_js")) {
		function textarea_limit_js() {?>
			<script type="text/javascript">
				$("#<?=globals('form', 'current_form')?> textarea").each(function(){
					var $textarea = $(this);
					var maxlength = parseInt($textarea.attr("data-maxlength"));
					var $p;
	
					if (maxlength > 0) {
						$p = $(document.createElement("div"));
						$p.addClass("char-limit");
						$textarea.after($p);
	
						$textarea.blur(function(){
							onchange();
						}).keypress(function(e){
							if ($textarea.val().length >= maxlength) {
								if (e.keyCode != 8 && e.keyCode != 37 && e.keyCode != 38 && e.keyCode != 39 && e.keyCode != 40 && e.keyCode != 46) {
									e.preventDefault();
								}
							}
						}).keyup(function(){
							onchange();
						});
	
						onchange();
					}
	
					function onchange() {
						if ($textarea.val().length > maxlength) $textarea.val($textarea.val().substr(0, maxlength));
	
						var charsleft = maxlength - $textarea.val().length;
						if (charsleft == 1) {
							$p.text(charsleft + " character left");
						} else {
							$p.text(charsleft + " characters left");
						}
					}
				});
			</script>
		<?php }
	}
?>