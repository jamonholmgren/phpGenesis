<?php
	load_library("legacy/form"); // Load Legacy Library
	
	if(!class_exists("Form")) {
		class Form {
			protected $_obj, $_form_name, $_field_class;
			
			/**
			 *	Returns a string of single-value attributes, e.g. form method="post"
			 */
			protected function _single_attr($options, $allowed = array()) {
				$attr = "";
				foreach($allowed as $f => $value) {
					if($value === FALSE) {
						if(isset($options[$f])) {
							$value = $options[$f]; // replaces the default
							$attr .= " {$f}='{$value}'";
						}
					} else {
						if(isset($options[$f])) $value = $options[$f]; // replaces the default
						$attr .= " {$f}='{$value}'";
					}
				}
				return $attr;
			}
			
			/**
			 *	Returns a string of multiple space-separated values, e.g. div class="field inline"
			 */
			protected function _multi_attr($options, $allowed = array("class" => "")) {
				$attr = "";
				foreach($allowed as $f => $values) {
					if($values === FALSE) {
						if(isset($options[$f]) && $options[$f] !== FALSE) {
							$values = $options[$f]; // adds to the default
							$attr .= " {$f}='{$values}'";
						}
					} else {
						if(isset($options[$f]) && $options[$f] !== FALSE) $values .= " " . $options[$f]; // adds to the default
						$attr .= " {$f}='{$values}'";
					}
				}
				
				if($options['attr']) $attr .= " " . $options['attr'];
				
				return $attr;
			}
			
			protected function _field_name($name) {
				return $this->form_name() . "[{$name}]";
			}
			
			/**
			 *	Returns a (formatted, if necessary) value for a field
			 */
			protected function _field_value($name, $options = array(), $escape_single_quotes = FALSE) {
				$value = "";
				if(isset($this->_obj->$name)) $value = $this->_obj->$name; // Is it set in the object?
				if(isset($options['value'])) $value = $options['value']; // Was it set in the options array?
				if($this->value($name) !== NULL) $value = $this->value($name); // Has it been posted?
				
				// Date object
				if(is_object($value) && method_exists($value, "format")) {
					$format = "n/j/Y";
					if($options['format']) $format = $options['format'];
					$value = $value->format($format);
				}
				
				if($escape_single_quotes) $value = str_replace("'", "&#39;", $value);
				
				return $value;
			}
			
			/**
			 *	Formats values into an array readable for radios or selects.
			 */
			protected function _values($values, $options = array()) {
				if(!is_array($values)) throw new Exception("Form::_values expects an array.");
				$first_value = array_first($values);
				$key = "id"; if($options['option_id']) $key = $options['option_id'];
				$value = "name"; if($options['option_label']) $value = $options['option_label'];
				$new_values = array();
				
				if(is_array($first_value)) {
					foreach($values as $v) {
						$nk = $v[$key];
						$nv = $v[$value];
						$new_values[$nk] = $nv;
					}
				} elseif(is_object($first_value)) {
					foreach($values as $v) {
						$nk = $v->$key;
						$nv = $v->$value;
						$new_values[$nk] = $nv;
					}
				} else {
					// It's a normal array with key => value. Return it.
					if($options['key_same_as_value']) {
						foreach($values as $k => $v) {
							$new_values[$v] = $v;
						}
					} else {
						$new_values = $values;
					}
				}
				
				return $new_values;
			}
			
			protected function _set_obj($db_object, $options = array()) {
				if(is_object($db_object)) {
					$this->_obj = $db_object;
				} elseif(is_string($db_object)) {
					$this->_obj = FALSE;
					$this->_form_name = $db_object;
				} else {
					throw new Exception("Form library expects string or object.");
				}
			}
			
			public function form_name($options = array()) {
				if(!$this->_form_name) {
					$this->_form_name = get_class($this->_obj);
					if($options['form_name']) $this->_form_name = $options['form_name'];
				}
				return $this->_form_name;
			}
			
			/**
			 *	Sets up the database object and outputs the form open tag and an identifying key
			 *	
			 *	@return string
			 */
			public function open($db_object, $options = array()) {
				$this->_set_obj($db_object, $options);
				
				$classes = "form"; if($this->_obj && $this->_obj->is_invalid()) $classes .= " form_error";
				$this->_field_class = "field"; if($options['field_class']) $this->_field_class = $options['field_class'];
				
				$attr = $this->_single_attr($options, array("method" => "post", "enctype" => "multipart/form-data", "id" => $this->form_name() . "_form", "name" => $this->form_name() . "_form"));
				$attr .= $this->_multi_attr($options, array("class" => $classes));
				
				$html = "\n<form {$attr}>";
				$html .= "<input type='hidden' name='" . $this->form_name() . "_key' id='" . $this->form_name() . "_key' value='" . session_id() . "' />\n";
				return $html;
			}
			
			/**
			 *	Removes the database object reference and outputs the form close tag
			 *	
			 *	@return string
			 */
			public function close() {
				$html = "</form> <!-- End {$this->form_name()} -->\n";
				return $html;
			}
			
			/*//////////////////////////////////// Input Tags //////////////////////////////////////////////*/
			
			/**
			 *	Outputs the HTML for a label.
			 *	
			 *	@return string
			 */
			public function label($name, $options = array()) {
				$attr = $this->_single_attr($options, array(
					"id" => FALSE,
					"for" => $this->_field_name($name),
				));
				// $attr .= $this->_multi_attr($options, array());
				
				if($options['label_class']) $attr .= " class='{$options['label_class']}'";
				load_library("seo");
				$label = deslugify($name);
				if($options['label']) $label = $options['label'];
				
				$html = "<label {$attr}>{$label}</label>\n";
				return $html;
			}
			
			/**
			 *	Outputs the HTML for a text input.
			 *	
			 *	@return string
			 */
			public function text($name, $options = array()) {
				$attr = $this->_single_attr($options, array(
					"id" => $this->_field_name($name), "name" => $this->_field_name($name),
					"placeholder" => deslugify($name),
					"disabled" => FALSE, "readonly" => FALSE,
				));
				$attr .= $this->_multi_attr($options, array("class" => ""));
				$value = $this->_field_value($name, $options, TRUE);
				
				$html = "<input type='text' value='{$value}' {$attr} />\n";
				return $html;
			}
			
			/**
			 *	Outputs the HTML for a password input.
			 *	
			 *	@return string
			 */
			public function password($name, $options = array()) {
				$attr = $this->_single_attr($options, array(
					"id" => $this->_field_name($name), "name" => $this->_field_name($name),
					"placeholder" => deslugify($name),
					"disabled" => FALSE, "readonly" => FALSE,
				));
				$attr .= $this->_multi_attr($options, array("class" => ""));
				
				$html = "<input type='password' value='' {$attr} />\n";
				return $html;
			}
			
			/**
			 *	Outputs the HTML for a hidden input.
			 *	
			 *	@return string
			 */
			public function hidden($name, $options = array()) {
				$attr = $this->_single_attr($options, array(
					"id" => $this->_field_name($name), "name" => $this->_field_name($name),
					"disabled" => FALSE, "readonly" => FALSE,
				));
				$attr .= $this->_multi_attr($options, array("class" => ""));
				$value = $this->_field_value($name, $options, TRUE);
				
				$html = "<input type='hidden' value='{$value}' {$attr} />\n";
				return $html;
			}
			
			/**
			 *	Outputs the HTML for a textarea.
			 *	
			 *	@return string
			 */
			public function textarea($name, $options = array()) {
				$attr = $this->_single_attr($options, array(
					"id" => $this->_field_name($name), "name" => $this->_field_name($name),
					"placeholder" => deslugify($name),
					"cols" => false, "rows" => false,
					"disabled" => FALSE, "readonly" => FALSE,
				));
				$attr .= $this->_multi_attr($options, array("class" => ""));
				$value = $this->_field_value($name, $options);
				
				$html = "<textarea {$attr}>" . htmlspecialchars($value) . "</textarea>\n";
				return $html;
			}
			
			/**
			 *	Outputs the HTML for a file input.
			 *	
			 *	@return string
			 */
			public function file($name, $options = array()) {
				$attr = $this->_single_attr($options, array(
					"id" => $this->form_name() . "_" . $name, "name" => $this->form_name() . "_" . $name,
					"disabled" => FALSE, "readonly" => FALSE,
				));
				$attr .= $this->_multi_attr($options, array("class" => ""));
				
				$html = "<input type='file' {$attr} />\n";
				return $html;
			}
			
			/**
			 *	Outputs the HTML for a checkbox.
			 *	
			 *	@return string
			 */
			public function checkbox($name, $options = array()) {
				$attr = $this->_single_attr($options, array(
					"id" => $this->_field_name($name), "name" => $this->_field_name($name),
					"disabled" => FALSE, "readonly" => FALSE,
				));
				$attr .= $this->_multi_attr($options, array("class" => ""));
				
				$checked = ""; if((bool)$this->_field_value($name, $options)) $checked = "checked='checked'";
				
				$html = "<input type='hidden' value='' name='" . $this->_single_attr($options, array("name" => $this->_field_name($name))) . "' />";
				$html .= "<input type='checkbox' value='on' {$attr} {$checked} />\n";
				return $html;
			}
			
			/**
			 *	Outputs the HTML for a select box. Required to include a values array in the 
			 *	$options array. Example: array("values" => $values_array)
			 *
			 *	If you want to use an object or array as the $values_array, include the following
			 *	arguments: array("values" => $values_array, "option_id" => "id", "option_label" => "name")
			 *
			 *	If you don't include the option_id and option_label it will automatically assume "id" and "name".
			 *	
			 *	
			 *	@return string
			 */
			public function select($name, $options = array()) {
				$attr = $this->_single_attr($options, array(
					"id" => $this->_field_name($name), "name" => $this->_field_name($name),
					"disabled" => FALSE, "readonly" => FALSE,
				));
				$attr .= $this->_multi_attr($options, array("class" => ""));
				
				$value = $this->_field_value($name, $options);
				
				$values_array = $this->_values((array)$options['values'], $options);
				
				$html = "<select {$attr}>\n";
				// NOTE: ?: is a "short ternary" operator. http://php.net/manual/en/language.operators.comparison.php
				if(is_array($options['default'] ?: $options['default_before'])) {
					foreach(($options['default'] ?: $options['default_before']) as $id => $label) {
						$id = str_replace("'", "&#39;", $id);
						$html .= "<option value='" . $id . "'>" . htmlspecialchars($label) . "</option>\n";
					}
				}
				foreach($values_array as $id => $label) {
					$sel = ""; if($id == $value) $sel = "selected='selected'";
					$id = str_replace("'", "&#39;", $id);
					$html .= "<option value='" . $id . "' {$sel}>" . htmlspecialchars($label) . "</option>\n";
				}
				if(is_array($options['default_after'])) {
					foreach($options['default_after'] as $id => $label) {
						$id = str_replace("'", "&#39;", $id);
						$html .= "<option value='" . $id . "'>" . htmlspecialchars($label) . "</option>\n";
					}
				}
				$html .= "</select>\n";
				
				return $html;
			}
			
			/**
			 *	Outputs the HTML for radio buttons. Required to include a values array in the 
			 *	$options array. Example: array("values" => $values_array)
			 *	
			 *	If you want to use an object or array as the $values_array, include the following
			 *	arguments: array("values" => $values_array, "option_id" => "id", "option_label" => "name")
			 *
			 *	If you don't include the option_id and option_label it will automatically assume "id" and "name".
			 *	
			 *	Option: "label_position" => "before|after" (default: after)
			 *	
			 *	
			 *	@return string
			 */
			public function radio($name, $options = array()) {
				$attr = $this->_single_attr($options, array(
					"name" => $this->_field_name($name),
					"disabled" => FALSE, "readonly" => FALSE,
				));
				$attr .= $this->_multi_attr($options, array("class" => ""));
				
				$value = $this->_field_value($name, $options);
				
				$values_array = $this->_values((array)$options['values']);
				
				// TODO: Needs work.
				$html = "";
				foreach($values_array as $id => $label) {
					$sel = ""; if($id == $value) $sel = "checked='checked'";
					if($options['label_position'] == "before") $html .= "<label for='{$id}'>{$label}</label>";
					$id = str_replace("'", "&#39;", $id);
					$html .= "<input type='radio' id='{$id}' value='{$id}' {$sel} />";
					if($options['label_position'] != "before") $html .= "<label for='{$id}'>{$label}</label>";
				}
				
				return $html;
			}
			
			/*/////////////////////////////////////// Field methods /////////////////////////////////////*/
			
			protected function _has_error($name = NULL) {
				if($this->_obj && method_exists($this->_obj, "is_invalid")) {
					if($this->_obj->is_invalid($name)) {
						return TRUE;
					}
				}
				return FALSE;
			}
			
			protected function _errors($name) {
				$html = "";
				if($this->_has_error($name)) {
					$html = "<span class='error'>" . $this->_obj->errors->on($name) . "</span>";
				}
				return $html;
			}
			
			protected function _field($name, $input_html, $options) {
				$attr = "";
				if($options['field_attr']) $attr = $options['field_attr'];
				$options['field_class'] = $this->_field_class . " " . (string)$options['field_class'];
				if($this->_has_error($name)) $options['field_class'] .= " field_error";
				
				$html = "<div class='" . $options['field_class'] . "' {$attr}>\n";
				if(!$options['suppress_label'] && $options['label_position'] != "after") $html .= $this->label($name, $options);
				$html .= $input_html;
				if(!$options['suppress_label'] && $options['label_position'] == "after") $html .= $this->label($name, $options);
				$html .= $this->_errors($name);
				$html .= "</div>\n";
				return $html;
			}
			
			/**
			 *	Option: "field_class" => "class1 class2"
			 *	Option: "field_attr" => "data-custom='test' data-custom2='test2'"
			 *	
			 *	@return string
			 */
			public function text_field($name, $options = array()) {
				$options['field_class'] = (string)$options['field_class'] . " text_field";
				return $this->_field($name, $this->text($name, $options), $options);
			}
			
			/**
			 *	Option: "field_class" => "class1 class2"
			 *	Option: "field_attr" => "data-custom='test' data-custom2='test2'"
			 *	
			 *	@return string
			 */
			public function file_field($name, $options = array()) {
				$options['field_class'] = (string)$options['field_class'] . " file_field";
				return $this->_field($name, $this->file($name, $options), $options);
			}
			
			/**
			 *	Option: "field_class" => "class1 class2"
			 *	Option: "field_attr" => "data-custom='test' data-custom2='test2'"
			 *	
			 *	@return string
			 */
			public function password_field($name, $options = array()) {
				$options['field_class'] = (string)$options['field_class'] . " password_field";
				return $this->_field($name, $this->password($name, $options), $options);
			}
			
			/**
			 *	Option: "field_class" => "class1 class2"
			 *	Option: "field_attr" => "data-custom='test' data-custom2='test2'"
			 *	
			 *	@return string
			 */
			public function textarea_field($name, $options = array()) {
				$options['field_class'] = (string)$options['field_class'] . " textarea_field";
				return $this->_field($name, $this->textarea($name, $options), $options);
			}
			
			/**
			 *	Option: "field_class" => "class1 class2"
			 *	Option: "field_attr" => "data-custom='test' data-custom2='test2'"
			 *	
			 *	@return string
			 */
			public function select_field($name, $options = array()) {
				$options['field_class'] = (string)$options['field_class'] . " select_field";
				return $this->_field($name, $this->select($name, $options), $options);
			}
			
			/**
			 *	Option: "field_class" => "class1 class2"
			 *	Option: "field_attr" => "data-custom='test' data-custom2='test2'"
			 *	
			 *	@return string
			 */
			public function checkbox_field($name, $options = array()) {
				$options['label_position'] = "after";
				$options['field_class'] = (string)$options['field_class'] . " checkbox_field";
				return $this->_field($name, $this->checkbox($name, $options), $options);
			}
			
			/**
			 *	Option: "field_class" => "class1 class2"
			 *	Option: "field_attr" => "data-custom='test' data-custom2='test2'"
			 *	
			 *	@return string
			 */
			public function radio_field($name, $options = array()) {
				$options['suppress_label'] = TRUE;
				$options['field_class'] = (string)$options['field_class'] . " radio_field";
				return $this->_field($name, $this->radio($name, $options), $options);
			}
			
			/*///////////////////////////////////// Buttons //////////////////////////////////////////*/
			/**
			 *	Outputs HTML for a button.
			 *	
			 *	@return string
			 */
			public function button($label, $options = array()) {
				$attr = $this->_single_attr($options, array(
					"type" => "button",
				));
				$attr .= $this->_multi_attr($options, array("class" => ""));
				
				$html = "<button {$attr}>{$label}</button>\n";
				return $html;
			}
			
			/**
			 *	Outputs HTML for a button type="submit"
			 *	
			 *	@return string
			 */
			public function submit($label, $options = array()) {
				$options['type'] = "submit";
				return $this->button($label, $options);
			}
			
			/**
			 *	Outputs HTML for a link
			 *	
			 *	@return string
			 */
			public function link($label, $options = array()) {
				$attr = $this->_single_attr($options, array(
					"target" => FALSE,
					"href" => FALSE,
				));
				$attr .= $this->_multi_attr($options, array("class" => ""));
				
				$html = "<a {$attr}>{$label}</a>\n";
				return $html;
			}
			
			/**
			 *	Outputs HTML for a submit input. Not really used these days, but it's here if you need it.
			 *	
			 *	@return string
			 */
			public function input_submit($label, $options) {
				$attr = $this->_single_attr($options, array(
					"type" => "submit",
					"value" => $label,
				));
				$attr .= $this->_multi_attr($options, array("class" => ""));
				
				$html = "<input type='submit' {$attr} />\n";
				return $html;
			}
			
			/*///////////////////////////////////////// Form posted stuff ////////////////////////////////////*/
			public function posted($obj, $csrf = TRUE) {
				$this->_set_obj($obj);
				
				$key = input_post($this->form_name() . "_key");
				if($key && ($csrf == FALSE || $key = session_id())) {
					return TRUE;
				}
				return FALSE;
			}
			
			public function values($valid_fields = FALSE, $invalid_fields = FALSE) {
				$temp_values = input_post($this->form_name());
				if($valid_fields) {
					foreach($valid_fields as $f) {
						if(isset($temp_values[$f])) $values[$f] = $temp_values[$f];
					}
				} else {
					$values = $temp_values;
				}
				if($invalid_fields) {
					foreach($invalid_fields as $f) {
						unset($values[$f]);
					}
				}
				return $values;
			}
			
			public function value($field) {
				$values = $this->values();
				if(isset($values[$field])) return $values[$field];
				return NULL;
			}
			
			public function checkbox_value($field, $on_value = 1, $off_value = 0) {
				$value = $this->value($field);
				if($value == "on" || $value === TRUE || $value > 0) return $on_value;
				return $off_value;
			}
			
			public function uploaded_file($field, $options = array()) {
				return input_file_array($this->form_name() . "_" . $field);
			}
			
			public function save_uploaded_file($field, $path, $options = array()) {
				$file = $this->uploaded_file($field);
				
				$filename = "%filename%";
				if(isset($options['filename'])) $filename = $options['filename'];
				
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
					if($options['width'] && $options['height'] && $options['type']) {
						load_library("image");
						image_resize($new_path_and_file, $new_path_and_file, $options['width'], $options['height'], $options['type']);
					}
				} else {
					return FALSE;
				}
				return $filename . "." . $fileinfo['extension'];
			}
			
			/**
			 *	Returns a valid array of values OR FALSE if one of the values is not valid.
			 *	This will NOT tell you what went wrong. This is for simple (contact form, etc)
			 *	validations. So plan on just saying "One or more fields is incorrect - please
			 *	check your answers" or something.
			 *	
			 *	@return array
			 */
			public function validate($validations = array()) {
				load_library("validation");
				// Loop through all the validations, separated by |
				$final = array();
				foreach($validations as $field => $rulestring) {
					$value = $this->value($field); // First, get the posted value.
					$rules = explode("|", $rulestring); // Explode all the rules by | into an array
					
					// Loop through the rules, extracting the rule arguments and running the function
					foreach($rules as $rule_with_arguments) {
						$rule_arguments = array(); // Initialize a variable that we will use to hold arguments for each rule
						preg_match('/(.*)\[(.*?)\]/i', $rule_with_arguments, $matches);
						if(count($matches) > 2) {
							$rule = $matches[1];
							$args = explode(",", $matches[2]);
							switch(count($args)) {
								case 1: $result = $rule($value, $args[0]); break;
								case 2: $result = $rule($value, $args[0], $args[1]); break;
								case 3: $result = $rule($value, $args[0], $args[1], $args[2]); break;
							}
						} else {
							$rule = $rule_with_arguments; // No arguments here, so just assign it directly
							$result = $rule($value);
						}
						
						// Check the result of this rule
						if($result === FALSE) {
							return FALSE; // Everything stops here. No returned array of valid entries. FALSE instead.
						} else {
							// No problem -- keep looping with a modified version of the value
							$value = $result;
						}
					}
					
					$final[$field] = $value;
				}
				
				return $final;
			}
			
			/**
			 *	Returns true or false based on the validation array passed in.
			 *	
			 *	
			 *	@return bool
			 */
			public function valid($validations = array()) {
				return is_array($this->validate($validations));
			}
		}
	}
?>