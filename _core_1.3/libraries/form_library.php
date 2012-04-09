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

	/**
	 *
	 *
	 *
	 */
	if(!function_exists("form_open")) {
		function form_open($name, $action='', $method='post', $options = array()) {
			globals('form', 'current_form', $name);
			$form_key = _get_form_key();


			$form_classes = "";
			if(isset($options['class']) && $options['class'] != '') {
				$form_classes = $options['class'];
			}
			$form_classes_html = " class=\"$form_classes\"";

			return "<form id=\"{$name}\" method=\"{$method}\" enctype=\"multipart/form-data\" action=\"{$action}\"{$form_classes_html}>
				<input type=\"hidden\" name=\"{$name}_key\" value=\"{$form_key}\" />
			";
		}
	} // end form_open

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

	if(!function_exists("_get_form_key")) {
		function _get_form_key() {
			if (session_isset('FORM_KEY')) {
				return session('FORM_KEY');
			} else {
				$form_key = uuid();
				session('FORM_KEY', $form_key);
				return $form_key;
			}
		}
	} // end _get_form_key

	if(!function_exists("form_value")) {
		function form_value($name, $value = NULL) {
			$fname = form_field_name($name);
			if($value === NULL) {
				return input_post($fname);
			} else {
				$_POST[$fname] = $value;
			}
		}
	}

	if(!function_exists("form_array_value")) {
		function form_array_value($name, $key, $value = NULL) {
			$fname = form_field_name($name);
			if($value === NULL) {
				$arry = input_post($fname);
				return $arry[$key];
			} else {
				$_POST[$fname][$key] = $value;
			}
		}
	}

	if(!function_exists("form_escape_html")) {
		function form_escape_html($value, $charset = "UTF-8") {
			return htmlspecialchars($value, ENT_COMPAT, $charset, false);
		}
	}

	if(!function_exists("form_textbox")) {
		function form_textbox($name = '', $label = '', $value = NULL, $options = array()) {
			$classes = array('textbox');
			$fname = form_field_name($name);
			$value = form_escape_html($value);

			$max_length_html = '';
			$style_html = '';
			if (isset($options['length']) && $options['length'] > 0) { $max_length_html = "maxlength=\"" . $options['length'] . "\" "; }
			// Check for form validation
			if(!isset($options['error'])) $options['error'] = form_validate_msg($name);
			if ($options['error'] != '') $classes[] = 'error';

			if (isset($options['required']) && $options['required'] != '') { $classes[] = 'required'; }
			if (isset($options['style']) && $options['style'] != '') { $style_html = "style=\"" . $options['style'] . "\" "; }
			$autocomplete = "";
			if (isset($options['autocomplete']) && $options['autocomplete'] == "off") $autocomplete = " autocomplete='off' ";

			if(form_value($name) !== NULL) $value = form_escape_html(form_value($name));

			return _form_field("<input type=\"text\" id=\"{$fname}\" name=\"{$fname}\" class=\"" . implode(" ", $classes) . "\" {$style_html} value=\"{$value}\" {$autocomplete} {$max_length_html} />", $fname, $label, $options);
		}
	}

	if(!function_exists("form_password")) {
		function form_password($name='', $label='', $value=null, $options = array()) {
			$classes = array('password');
			$fname = form_field_name($name);
			$value = form_escape_html($value);

			$max_length_html = '';
			if (isset($options['length']) && $options['length'] > 0) { $max_length_html = "maxlength='".$options['length']."' "; }
			if(!isset($options['error'])) $options['error'] = form_validate_msg($name);
			if ($options['error'] != '') $classes[] = 'error';
			if (isset($options['required']) && $options['required'] != '') { $classes[] = 'required'; }
			$autocomplete = " autocomplete='off' ";
			if (isset($options['autocomplete']) && $options['autocomplete'] == "off") $autocomplete = " autocomplete='off' ";

			return _form_field("<input type=\"password\" id=\"{$fname}\" name=\"{$fname}\" class=\"" . implode(" ", $classes) . "\" value=\"{$value}\" {$autocomplete} {$max_length_html} />", $fname, $label, $options);
		}
	}

	if(!function_exists("form_textarea")) {
		function form_textarea($name='', $label='', $value=null, $options = array()) {
			$fname = form_field_name($name);
			$classes = array('textarea');
			$value = form_escape_html($value);

			$rows_html = ""; $cols_html = "";
			$max_length_html = "";
			if (isset($options['length']) && $options['length'] > 0) { $max_length_html = " data-maxlength=\"" . $options['length'] . "\" "; globals('form', 'textarea-limit', true); }
			if (isset($options['rows']) && $options['rows'] > 0) { $rows_html = " rows=\"" . $options['rows'] . "\""; }
			if (isset($options['cols']) && $options['cols'] > 0) { $cols_html = " cols=\"" . $options['cols'] . "\""; }
			if(!isset($options['error'])) $options['error'] = form_validate_msg($name);
			if ($options['error'] != '') $classes[] = 'error';
			if (isset($options['required']) && $options['required'] != '') { $classes[] = 'required'; }

			if(form_value($name) !== NULL) $value = form_value($name);

			return _form_field("<textarea id=\"{$fname}\" name=\"{$fname}\" class=\"" . implode(" ", $classes) . "\" {$rows_html} {$cols_html} {$max_length_html} >{$value}</textarea>", $fname, $label, $options);
		}
	}


	/**
	 * Creates a checkbox control. If the $value is non-zero, will be checked. The default input value is "yes"
	 * and you can override it with the option "checked_value"
	 *
	 * @return string
	 */
	if(!function_exists("form_checkbox")) {
		function form_checkbox($name = '', $label = '', $value = NULL, $options = array()) {
			$fname = form_field_name($name);
			$classes = array('checkbox');

			if(!isset($options['template'])) {
				$options['template'] = DEFAULT_CHECKBOX_FIELD_TEMPLATE;
			}

			$checked_value = "yes";
			if(isset($options['checked_value'])) $checked_value = $options['checked_value'];

			if(!isset($options['error'])) $options['error'] = form_validate_msg($name);
			if ($options['error'] != '') $classes[] = 'error';

			$checked = ($value == false || $value == 0 || $value === NULL || $value == '') ? '' : ' CHECKED="CHECKED" ';

			return _form_field("<input type=\"checkbox\" id=\"{$fname}\" name=\"{$fname}\" value=\"{$checked_value}\" class=\"" . implode(" ", $classes) . "\" {$checked} />", $fname, $label, $options);
		}
	}

	if(!function_exists("form_radio")) {
		function form_radio($name='', $values=array(), $checked_value=NULL) {
			$fname = form_field_name($name);
			$classes = array('radio');
			$html = "";

			if(!isset($options['template'])) {
				$options['template'] = DEFAULT_CHECKBOX_FIELD_TEMPLATE;
			}

			$keys = array_keys($values);
			$index = 0;
			for (; $index < count($values); $index++) {
				$value = $keys[$index];
				$label = $values[$value];

				if(form_value($name) !== NULL) $checked_value = form_escape_html(form_value($name));

				if(!isset($options['error'])) $options['error'] = form_validate_msg($name);
				if ($options['error'] != '') $classes[] = 'error';

				$options['ignore_error'] = ($index < count($values) - 1);

				$checked = ""; if($value == $checked_value) $checked = " CHECKED=\"CHECKED\" ";
				$html .= _form_field("<input type=\"radio\" id=\"{$fname}_{$value}\" name=\"{$fname}\" value=\"{$value}\" class=\"" . implode(" ", $classes) . "\" {$checked} />", "{$fname}_{$value}", $label, $options);
			}

//			foreach($values as $value => $label) {
//				if(form_value($name) !== NULL) $checked_value = form_escape_html(form_value($name));
//
//				if(!isset($options['error'])) $options['error'] = form_validate_msg($name);
//				if ($options['error'] != '') $classes[] = 'error';
//
//				$checked = ""; if($value == $checked_value) $checked = " CHECKED=\"CHECKED\" ";
//				$html .=   _form_field("<input type=\"radio\" id=\"{$fname}_{$value}\" name=\"{$fname}\" value=\"{$value}\" class=\"" . implode(" ", $classes) . "\" {$checked} />", "{$fname}_{$value}", $label, $options);
//			}
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
	 * Here's a way to add an option at the beginning of the select element:
	 * "option_default" => "None Selected"
	 * The option value will be an empty string
	 *
	 * @return string
	 */
	if(!function_exists("form_select")) {
		function form_select($name='', $label='', $value=NULL, $values=NULL, $options = array()) {
			$classes = array('select');
			$fname = form_field_name($name);

			$max_length_html = '';
			if (isset($options['error']) && $options['error'] != '') { $classes[] = 'error'; }
			if (isset($options['required']) && $options['required'] != '') { $classes[] = 'required'; }

			if(form_value($name) !== NULL) $value = form_value($name);

			$option_html = "";
			if(isset($options['option_default'])) $option_html .= "<option value=\"\">{$options['option_default']}</option>";
			if (is_array($values)) {
				foreach ($values as $val => $name) {
					$sel_name = $name;
					$sel_val = form_escape_html($val);

					if(is_array($name)) {
						if(isset($options['option_id'])) $sel_val = $name[$options['option_id']];
						if(isset($options['option_label'])) $sel_name = $name[$options['option_label']];
					}

					$selected = "";
					if ("$sel_val" == $value) { $selected = "selected=\"selected\""; } // Note: the cast to string is nessary in order to prevent 0 from equaling empty string.
					$option_html .= "<option value=\"{$sel_val}\" {$selected}>{$sel_name}</option>";
				}
			}

			return _form_field("<select id=\"{$fname}\" name=\"{$fname}\" class=\"" . implode(" ", $classes) . "\">{$option_html}</select>", $fname, $label, $options);
		}
	}

	if(!function_exists("form_submit")) {
		function form_submit($name='', $label='', $value=null, $options = array()) {
			$fname = form_field_name($name);
			$value = form_escape_html($value);
			return _form_field("<input type=\"submit\" id=\"{$fname}\" name=\"{$fname}\" class=\"submit\" value=\"{$value}\" />", $fname, $label, $options);
		}
	}

	if(!function_exists("form_button")) {
		function form_button($name='', $label='', $value=null, $options = array()) {
			$fname = form_field_name($name);
			$value = form_escape_html($value);
			return _form_field("<button type=\"submit\" id=\"{$fname}\" name=\"{$fname}\" class=\"submit\">{$value}</button>", $fname, $label, $options);
		}
	}

	if(!function_exists("form_hidden")) {
		function form_hidden($name='', $value='', $attributes = array()) {
			$fname = form_field_name($name);
			$value = form_escape_html($value);

			$attributes_html = "";
			foreach ($attributes as $key => $val) {
				$attributes_html .= " {$key} = \"{$val}\" ";
			}

			return "<input type=\"hidden\" name=\"{$fname}\" id=\"{$fname}\" value=\"{$value}\" {$attributes_html} />";
		}
	}

	if(!function_exists("form_file")) {
		function form_file($name='', $label='', $options = array()) {
			$fname = form_field_name($name);
			return  _form_field("<input name=\"{$fname}\" type=\"file\" />", $fname, $label, $options);
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
			$value = form_escape_html($value);
			return _form_field("<div id=\"{$fname}\" name=\"{$fname}\" class=\"custom\">{$value}</div>", $fname, $label, $options);
		}
	}

	if(!function_exists("form_get_file")) {
		function form_get_file($name) {
			$fname = form_field_name($name);
			return input_file_array($fname);
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

	if(!function_exists("form_field_name")) {
		function form_field_name($name) {
			return globals('form', 'current_form') . "_".$name;
		}
	}

	if(!function_exists("form_set_focus")) {
		function form_set_focus($name) {
			globals('form', globals('form', 'current_form') . '_focused_field', form_field_name($name));
		}
	}

	// Form Validation

	if(!function_exists("form_posted")) {
		function form_posted($name, $check_key_is_valid = true) {
			globals('form', 'current_form', $name);
			if (is_post() && input_post("{$name}_key") !== NULL) {
				if ($check_key_is_valid == false || _get_form_key() == input_post("{$name}_key")) {
					globals('form', $name.'_valid', true);
					return true;
				}
				die("<p class='error'>There was an error with the form submittal. The form keys don't match. Developer: check BASE_URL matches actual URL and that there is no trailing slash.</p>");
			}
			return false;
		}
	}

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

	if(!function_exists("form_end_validation")) {
		function form_end_validation() {
			unset_global('form', 'current_form');
		}
	}

	if(!function_exists("form_is_valid")) {
		function form_is_valid() {
			return globals('form', globals('form', 'current_form').'_valid');
		}
	}

	if(!function_exists("form_validate")) {
		function form_validate($name, $rulestring='trim') {
			load_library("validation");

			$valueobj = form_value($name);
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
					$matches = $ruleArgs[$rule];
					if (count($matches) > 2) {
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
					} elseif (is_string($result)) {
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
						} elseif (is_string($result)) {
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

	if(!function_exists("form_validate_msg")) {
		function form_validate_msg($name) {
			$fname = form_field_name($name);

			if (global_isset('form', 'val_'.$fname)) {
				$rule = globals('form', 'val_'.$fname);

				if (setting_isset('form', 'errormsg_'.$rule)) {
					return settings('form', 'errormsg_'.$rule);
				} else {
					return settings('form', 'errormsg_default');
				}
			}
		}
	}

	if(settings('form', 'errormsg_default') === NULL) settings('form', 'errormsg_default', 'This field is invalid.');
	if(settings('form', 'errormsg_required') === NULL) settings('form', 'errormsg_required', 'This field is required.');
	if(settings('form', 'errormsg_maxlength') === NULL) settings('form', 'errormsg_maxlength', 'This field did not meet the maximum length requirement.');
	if(settings('form', 'errormsg_minlength') === NULL) settings('form', 'errormsg_minlength', 'This field did not meet the minimum length requirement.');
	if(settings('form', 'errormsg_int_range') === NULL) settings('form', 'errormsg_int_range', 'The integer value is not in valid range.');
	if(settings('form', 'errormsg_valid_email') === NULL) settings('form', 'errormsg_valid_email', 'Invalid email address.');

	if(!function_exists("textarea_limit_js")) {
		function textarea_limit_js() {?>
			<script type="text/javascript">
				$("textarea").each(function(){
					$textarea = $(this);
					var maxlength = parseInt($textarea.attr("data-maxlength"));

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

						function onchange() {
							if ($textarea.val().length > maxlength) $textarea.val($textarea.val().substr(0, maxlength));

							var charsleft = maxlength - $textarea.val().length;
							if (charsleft == 1) {
								$p.text(charsleft + " character left");
							} else {
								$p.text(charsleft + " characters left");
							}
						} onchange();
					}
				});
			</script>
		<?php }
	}


	if(!function_exists("decimal")) {
		function decimal($value, $precision, $scale, $allow_null) {
			$float = (float)$value;

			if ($allow_null == 1 && $value == "") { return true; }

			if ($float == 0 && $value != "0") {
				return false;
			}

			$str = (string) $float;

			$pstr = preg_replace("/[^0-9]/", "", $str);
			if (strlen($pstr) > $precision) { return false; }

			$this_scale = 0;
			$decpos = strpos($str, ".");
			if ($decpos === false) {
				$this_scale = 0;
			} else {
				$this_scale = strlen($str)-$decpos-1;
			}
			if ($this_scale > $scale) { return false; }

			return true;
		}
	}

	if(!function_exists("enum")) {
		/**
		 * example: $enum_strings = cat:dog:mouse
		 * @param string $value
		 * @param string $enum_strings
		 * @return boolean
		 */
		function enum($value, $enum_strings) {
			return in_array($value, explode(":", $enum_strings));
		}
	}

	if(!function_exists("valid_email")) {
		function valid_email($data, $strict = false) {
			$regex = $strict ?
				'/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' :
				'/^([*+!.&#$�\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i'
			;
			if(preg_match($regex, trim($data), $matches)) {
				return array($matches[1], $matches[2]);
			} else {
				return false;
			}
		}
	}

	if(!function_exists("valid_int")) {
		function valid_int($var) {
			if(intval($var) == $var) return true;
			return false;
		}
	}

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


?>