<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

	if(!defined("SQL_DATE_FORMAT")) define("SQL_DATE_FORMAT", "Y-m-d H:i:s");
	
	if(!function_exists("db_connect")) {
		function db_connect($host, $username, $password, $database) {
			mysql_connect($host, $username, $password) or die('db_connect: could not connect: ' . mysql_error()); // connect to the database
			mysql_select_db($database); // select the correct database
			_db_log_query("Connected to {$database}");
			globals('db', 'connected', true);
		}
	} // end db_connect
	
	if(!function_exists("db_disconnect")) {
		function db_disconnect() {
			mysql_close();
			globals('db', 'connected', false);
		}
	} // end db_disconnect
	
	if(!function_exists("db_escape_string")) {
		function db_escape_string($str) {
			db_init();
			return mysql_real_escape_string($str);
		}
	}
	
	if(!function_exists("db_init")) {
		function db_init() {
			if(settings('db', 'enabled') !== false && !global_isset('db', 'connected')) {
				db_connect(
					settings('db', 'host'), 
					settings('db', 'username'), 
					settings('db', 'password'), 
					settings('db', 'database')
				);
				if(settings('db', 'log_queries')) globals("db", "query_log", array(array("time" => microtime(true), "query" => "Initializing database.")));
				// globals("db", "query_log", array("Initializing database."));
			}
		}
	} // end db_init
	
	if(!function_exists("db_table_exists")) {
		function db_table_exists($table) {
			db_init();
			$result = mysql_query(_db_log_query("SHOW TABLES LIKE `{$table}`"));
			if(!$result || (mysql_num_rows($result) < 1)) return false;
			if(mysql_num_rows($result) == 1) return true;
			return false;
		}
	} // end db_table_exists
	
	if(!function_exists("db_query")) {
		function db_query($q, $force_multiple_rows = false) {
			db_init();
			$result = mysql_query(_db_log_query($q));
			if(!$result) db_query_error_handler($q);
			if($result === true || $result === false) return $result;
			if(mysql_num_rows($result) < 1) return false;
			if(mysql_num_rows($result) == 1 && $force_multiple_rows === false) return mysql_fetch_assoc($result);
			if(mysql_num_rows($result) > 1 || $force_multiple_rows) {
				while($row = mysql_fetch_assoc($result)) $r[] = $row;
				return ($r);
			}
			return $result;
			return NULL;
		}
	} // end db_query
	
	/**
	 * Returns the results of a query as a multidimensional array.
	 *
	 */
	if(!function_exists("db_query_rows")) {
		function db_query_rows($q, $index_field = NULL) {
			db_init();
			
			$result = mysql_query(_db_log_query($q));
			
			if(!$result) db_query_error_handler($q);
			
			if(mysql_num_rows($result) < 1) return false;
			
			while($row = mysql_fetch_assoc($result)) {
				if($index_field !== NULL) {
					if(is_array($index_field)) {
						$index_fields = array_reverse($index_field);
						$index_count = count($index_fields);
						$row_single = $row;
						$count = 0;
						foreach($index_fields as $field_name) {
							$count += 1;
							if($count < $index_count) {
								$rtemp[$row[$field_name]] = $row_single;
								$row_single = $rtemp;
								unset($rtemp);
							}
						}
						$r[$row[$field_name]] = $row_single;
					} else {
						$r[$row[$index_field]] = $row;
					}
				} else {
					$r[] = $row;
				}
			}
			
			return ($r);
		}
	} // end db_query_rows

	if(!function_exists("db_query_row")) {
		function db_query_row($q) {
			$result = db_query_rows($q);
			if(is_array($result)) return $result[0];
			return false;
		}
	} // end db_query_row
	
	if(!function_exists("db_get_field")) {
		function db_get_field($table, $field, $where) {
			$row = db_query_row("SELECT {$field} FROM {$table} {$where}");
			if(is_array($row)) return $row[$field];
			return NULL;
		}
	} // end db_get_field
	
	if(!function_exists("db_get_row")) {
		function db_get_row($table, $field, $value) {
			return db_query_row("SELECT * FROM {$table} WHERE {$field} = '{$value}'");
		}
	} // end db_get_row

	if(!function_exists("db_get_rows")) {
		function db_get_rows($table, $where = "", $index_field = NULL) {
			return db_query_rows("SELECT * FROM {$table} {$where}", $index_field);
		}
	} // end db_get_rows
	
	if(!function_exists("db_insert")) {
		function db_insert($table, $new_row, $where = "") {
			db_init();
			foreach($new_row as $k => $v) {
				$insert_row[$k] = db_escape_string($v);
			}			
			$key_list = implode(", ", array_keys($new_row));
			$value_list = implode("', '", $insert_row);
			$q = _db_log_query("INSERT INTO {$table} ({$key_list}) VALUES ('{$value_list}') {$where}");
			$result = mysql_query($q);
			if(!$result) db_query_error_handler($q);
			if(mysql_insert_id() > 0) return mysql_insert_id();
			return false;
		}
	} // end db_insert
	
	/**
	 *	Allows inserting a row, or, if the row already exists, just updates it. Returns the ID of the inserted or updated row.
	 *
	 *	You should define a column or columns (that are contained in the $updated_row) in your MySQL database as UNIQUE INDEXes.
	 *	Otherwise, MySQL doesn't know what columns to try and match to see if it already exists.
	 *
	 *	@return int
	 */
	if(!function_exists("db_upsert")) {
		function db_upsert($table, $updated_row, $where = NULL) {
			db_init();
			
			// Legacy code detecter - swaps the values
			if(is_array($where)) list($where, $updated_row) = array($updated_row, $where);
			
			$update_list = "";
			foreach($updated_row as $k => $v) {
				$v = mysql_real_escape_string($v);
				$insert_row[$k] = $v;
				$update_list .= ", {$k} = '{$v}'";
			}			
			$key_list = implode(", ", array_keys($updated_row));
			$value_list = implode("', '", $insert_row);
			
			$q = _db_log_query("
				INSERT INTO {$table} 
					({$key_list}) VALUES ('{$value_list}')
				ON DUPLICATE KEY 
				UPDATE 
					id = LAST_INSERT_ID(id){$update_list}
				{$where}
			");
			
			$result = mysql_query($q);
			if(!$result) db_query_error_handler($q);
			if(mysql_insert_id() > 0) return mysql_insert_id();
			return false;
		}
	}
	

	
	if(!function_exists("db_delete_where")) {
		function db_delete_where($table, $where) {
			$where = str_ireplace("WHERE", "", $where); // remove any WHERE from the clause
			db_init();
			mysql_query(_db_log_query("DELETE FROM {$table} WHERE {$where}"));
			return mysql_affected_rows();
		}
	} // end db_delete_where

	if(!function_exists("db_delete")) {
		function db_delete($table, $field, $value) {
			db_init();
			if(!is_int($value)) {
				$value = "'" . db_escape_string(stripslashes($value)) . "'";
			}
			return db_delete_where($table, "{$field} = {$value}");
		}
	} // end db_delete
	
	if(!function_exists("db_update_field")) {
		function db_update_field($table, $where, $field, $value) {
			db_init();
			if(is_int($where)) $where = "WHERE id = {$where}";
			if($value != "NULL" && $value !== NULL) $value = "'" . db_escape_string(stripslashes($value)) . "'";
			$q = _db_log_query("UPDATE {$table} SET {$field} = {$value} {$where} ");
			$result = mysql_query($q);
			if(!$result) db_query_error_handler($q);
			return $result;
		}
	} // end db_update_field
	
	if(!function_exists("db_affected_rows")) {
		function db_affected_rows() {
			db_init();
			return mysql_affected_rows();
		}
	} // end db_affected_rows
	
	if(!function_exists("db_update")) {
		function db_update($table, $where, $updated_row) {
			if(is_array($updated_row)) {
				db_init();
				if(is_int($where)) {
					$where = "WHERE id = {$where}";
				}
				$key_list = "";
				foreach($updated_row as $k => $v) {
					if($v === NULL || $v == "NULL") {
						$value = "NULL";
					} else {
						$value = "'" . db_escape_string(stripslashes($v)) . "'";
					}
					$key_list .= " {$k} = {$value},";
				}
				$key_list = rtrim($key_list, ",");
				$q = _db_log_query("UPDATE {$table} SET {$key_list} {$where}"); 
				$result = mysql_query($q);
				if(!$result) db_query_error_handler($q);
				return $result;
			}
			return false;
		}
	} // end db_update
	
	
	/**
	 * Returns integer row count for specified WHERE clause
	 *
	 * Does not indicate failure but rather just returns zero on failure
	 * 
	 * @return int
	 */
	if(!function_exists("db_count_rows")) {
		function db_count_rows($table, $where = "") {
			db_init(); 
			$result = mysql_query(_db_log_query("SELECT COUNT(*) AS num_rows FROM {$table} {$where}"));
			if(!$result || mysql_num_rows($result) < 1) return 0;
			if(is_array($result = mysql_fetch_array($result))) return (int)$result['num_rows'];
			return 0; // None found
		}
	} // end db_count_rows

	/**
	 * Alias for db_count_rows
	 * @return int
	 */
	if(!function_exists("db_count")) {
		function db_count($table, $where = "") {
			return db_count_rows($table, $where);
		}
	} // end db_count
	

	if(!function_exists("db_enum_values")) {
		function db_enum_values($table, $field, $sorted = true, $set_keys = false) {
			db_init();
			$result = mysql_query(_db_log_query("SHOW COLUMNS FROM {$table}"));
			while($row = mysql_fetch_assoc($result)) {
				if($row['Field'] == $field) {
					$types = $row['Type'];
					$beginStr = strpos($types, "(") + 1;
					$endStr = strpos($types, ")");
					$types = substr($types, $beginStr, $endStr - $beginStr);
					$types = str_replace("'", "", $types);
					$types = split(',', $types);
					if($set_keys) {
						$t2 = $types;
						unset($types);
						foreach($t2 as $k => $v) {
							$types[$v] = $v;
						}
					}
					if($sorted) sort($types);
					break;
				}
			}
			return($types);
		} 
	} // end db_enum_values
	
	if(!function_exists("db_table_fields")) {
		function db_table_fields($table) {
			$q = _db_log_query("DESCRIBE {$table}");
			$result = mysql_query($q);
			if(!$result || (mysql_num_rows($result) < 1)) return false;
			while($row = mysql_fetch_assoc($result)) $r[] = $row['Field'];
			return $r;
		}
	} // end db_table_fields
	
	if(!function_exists("db_reorder")) {
		function db_reorder($table, $field = "sort", $spacing = 1) {
			$res = db_get_rows($table, "{$custom} ORDER BY {$field} ASC ");
			$ctr = 0;
			if(is_array($res)) {
				foreach($res as $r) {
					db_update_field($table, $r['id'], $field, $ctr);
					$ctr += $spacing;
				}
				return true;
			}
			return false;
		}
	} // end db_reorder
	
	if(!function_exists("db_query_error_handler")) {
		function db_query_error_handler($q) {
			switch (APP_STATUS) {
				case 'development':
				case 'testing':
					echo "<div class='db-error'>";
					echo "<p class='db-error-message'>Programming Error. Please copy and paste into an email and notify your website developer.</p>";
					echo "<p>" . mysql_error() . "</p>";
					echo "<p>" . $_SERVER['REQUEST_URI'] . "</p>";
					echo "<hr />";
					
					// remove left padding from $q
					$q_array = explode("\n", $q);
					$q_format = "";
					$q_ltrim = "";
					foreach((array)$q_array as $q_line) {
						$trimmed = ltrim($q_line, "\t");
						$cur_trim = strlen($q_line) - strlen($trimmed);
						if($trim_left == 0) {
							$trim_left = $cur_trim;
						}
						$q_format .= str_repeat("  ", max(0, $cur_trim - $trim_left)) . $trimmed . "\n";
					}
					
					echo "<pre>" . $q_format . "</pre>";
					echo "<hr />";
					echo "<pre>";
					$backtraces = debug_backtrace();
					echo "";
					foreach($backtraces as $f => $backtrace) {
						if($f > 0) {
							echo "File {$f}: " . $backtrace['file'] . "\n";
							echo "Line: " . $backtrace['line'] . "\n";
							echo "Called function: " . $backtrace['function'] . "\n\n";
						}
					}
					echo "</pre>";
					echo "</div>";
					die();
				break;
				default: break;
			}
			return false;
		} 
	} // end db_error_handler

	if(!function_exists("_db_log_query")) {
		function _db_log_query($q) {
			if(settings('db', 'log_queries') == true) {
				$log = globals("db", "query_log");
				$last_log = array_last($log);
				$timestamp = microtime(true); $duration = 0;
				if(is_array($last_log)) $duration = number_format(($timestamp - $last_log['time']), 4, ".", "");
				$log_entry = array("time" => $timestamp, "duration_since_last_query" => $duration, "query" => $q);
				$log[] = $log_entry;
				globals("db", "query_log", $log);
			}
			return $q;
		}
	} // _db_log_query
	
	if(!function_exists("db_analyze_performance")) {
		function db_performance_monitor($query_count_alert = 500, $query_time_alert = 10) {
			$log = globals("db", "query_log");
			$queries['very_long'] = 0;
			$queries['long'] = 0;
			$queries['medium'] = 0;
			$queries['short'] = 0;
			$queries['total'] = 0;
			$output = "";
			$output .= "<pre>";
			if(is_array($log)) {
				$last_q = "Init.";
				foreach($log as $c => $b) {
					$this_q = $b['query'];
					$d = $b['duration_since_last_query'];
					if($d > 2.00) {
						$output .= "\n";
						$output .= "! Very long query: {$d} seconds !\n";
						$output .= "    Query: {$last_q}\n";
						$queries['very_long'] += 1;
					} elseif($d > 1.0) {
						$output .= "\n";
						$output .= "- Long query: {$d} seconds\n";
						$output .= "    Query: {$last_q}\n";
						$queries['long'] += 1;
					} elseif($d > 0.1) {
						$output .= "\n";
						$output .= "- Medium query: {$d} seconds\n";
						$output .= "    Query: {$last_q}\n";
						$queries['medium'] += 1;
					} else {
						// Tiny query
						$output .= ".";
						$queries['short'] += 1;
					}
					$last_q = $this_q;
					$queries['total'] += 1;
				}
				
				$output .= "\n\nQuery Summary:\n";
				$output .= "Very Long Queries (larger than 2 seconds): " . $queries['very_long'] . "\n";
				$output .= "Long Queries (larger than 1.0 seconds): " . $queries['long'] . "\n";
				$output .= "Medium Queries (larger than 0.1 seconds): " . $queries['medium'] . "\n";
				$output .= "Short Queries: " . $queries['short'] . "\n";
				$output .= "Total Queries: " . $queries['total'] . "\n\n\n";

				$first_log = array_first($log);
				$last_log = array_last($log);
				if(is_array($first_log) && is_array($last_log)) {
					$total_duration = number_format(($last_log['time'] - $first_log['time']), 4, ".", "");
					$output .= "\n\nTotal Duration: " . $total_duration . " seconds.\n\n";
				} else {
					$output .= "\n\nNo total duration found.\n";
					$output .= "First log entry: \n\n" . json_encode($first_log) . "\n\n\n";
					$output .= "Last log entry: \n\n" . json_encode($last_log) . "\n\n\n";
				}
			}
			
			$output .= "\nDone.";
			$output .= "</pre>";
			if($queries['total'] > $query_count_alert || $total_duration > $query_time_alert) {
				$output = "Query performance was bad for this page: " . segments_full() . "\n\n\n" . $output;
				return $output;
			}
			return false;
		}
	}

	if(!function_exists("db_display_log")) {
		function db_display_log($display_pre = true) {
			$log = globals("db", "query_log");
			if($display_pre) echo "<pre>";
			if($show_all) print_r($log);
			$first_log = array_first($log); $last_log = array_last($log);
			if(is_array($first_log) && is_array($last_log)) {
				$total_duration = number_format(($last_log['time'] - $first_log['time']), 4, ".", "");
				echo "\n\nTotal Duration: " . $total_duration . " seconds.\n\n";
			} else {
				echo "\n\nNo total duration found.\n";
				var_dump($first_log);
				var_dump($last_log);
			}
			if($display_pre) echo "</pre>";
			return NULL;
		}
	} // db_display_log
	
	/**
	 *	db_display_last() shows the last query that was executed by the db library.
	 *	
	 *	It requires that query logging be turned on.
	 *
	 *	@return string
	 **/
	if(!function_exists("db_display_last")) {
		function db_display_last() {
			$log = globals("db", "query_log");
			$last_log = array_last($log);
			if(is_array($last_log)) {
				echo "<pre>" . print_r($last_log, true) . "</pre>";
			}
		}
	}
?>