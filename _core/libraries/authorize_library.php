<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Authorize Library
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *
 * @package phpGenesis
 */

	/**
	 * authorize_init() - loads the AuthnetAIM class and initializes the settings. You must add settings
	 * to config.php:
	 *
	 * settings("authorize", "login", "your-login-here");
	 * settings("authorize", "transkey", "your-key-here");
	 * settings("authorize", "test", false);
	 *
	 * @return NULL
	 *
	 */
	if(!function_exists("authorize_init")) {
		function authorize_init() {
			if(!thirdparty_plugin_is_loaded("AuthnetAIM/AuthnetAIM.class.phps")) {
				$test = settings("authorize", "test");
				if($test === true) { // If testing use testing account (IMPORTANT)
					$login = "45x6CQzY95K9";
					$transkey = "9h9breDw7B9N54Q7";
				} else { // Else, use the account setup in Config
					$login = settings("authorize", "login");
					$transkey = settings("authorize", "transkey");
				}
				if($login === NULL || $transkey === NULL) {
					authorize_error("Error initializing AuthnetAIM class. Need a valid login and transkey defined in config.php.", true, true);
				}
				load_thirdparty_plugin("AuthnetAIM/AuthnetAIM.class.phps");
				// if(!thirdparty_plugin_is_loaded("AuthnetAIM/AuthnetAIM.class.phps")) {
					// authorize_error("Couldn't load AuthnetAIM class. Verify that it is installed.");
				// }
				globals('authorize', 'AuthnetAIM', new AuthnetAIM($login, $transkey, $test));
			}
		}
	} // end authorize_init

	/**
	 * authorize_process_payment() - the main function in this library, this function
	 * actually takes and processes a payment with the Authorize.net API.
	 *
	 * Example usage:
	 *
	 * list($approved, $info) = authorize_process_payment($payment_info);
	 * 
	 * Pass in an array ($payment_info) with at least this information:
	 *
	 * array(
	 *	"creditcard" => "1111222233334444",
	 *	"expiration" => "04/12",
	 *	"cvv" => "142",
	 *	"total" => "50.23",
	 * );
	 *
	 * Full example:
	 * array(
	 *	"cust_id" => $customer['id'],
	 *	"customer_ip" => $_SERVER['REMOTE_ADDR'],
	 *	"email" => "jamon@clearsightdesign.com",
	 *	"email_customer" => TRUE,
	 *	"first_name" => "Jamon",
	 *	"last_name" => "Holmgren",
	 *	"address" => "123 4th street",
	 *	"city" => "Longview",
	 *	"state" => "WA",
	 *	"zip" => "98682",
	 *	"phone" => "360-609-4328",
	 *	"ship_to_first_name" => "Bill",
	 *	"ship_to_last_name" => "Smith",
	 *	"ship_to_address" => "321 9th Avenue",
	 *	"ship_to_city" => "Kelso",
	 *	"ship_to_state" => "WA",
	 *	"ship_to_zip" => "95482",
	 *	"description" => "Size 11.5 X-Press Black-on-black Shoes",
	 * };
	 *
	 * If approved, the transaction returns true and an array containing
	 * array($approval_code, $avs_result, $transaction_id) for inclusion
	 * in a database table of transactions. It does NOT store the information
	 * for you (right now). However, we may add that functionality sometime.
	 *
	 * @return array - (bool)$approved and (mixed)additional_info
	 *
	 */
	if(!function_exists("authorize_process_payment")) {
		function authorize_process_payment($p) {
			authorize_init();
			$payment = globals("authorize", "AuthnetAIM");
			if(is_object($payment)) {
				// Required fields
				$creditcard = $p['creditcard'];
				$expiration = $p['expiration'];
				$cvv = $p['cvv'];
				$total = $p['total'];
				
				// Unset those fields so they don't show up in the array when we loop through it.
				unset($p['creditcard'], $p['expiration'], $p['cvv'], $p['total']);
				
				// Set up the transaction
				$payment->setTransaction($creditcard, $expiration, $total, $cvv);

				// Prevents double-clicks - 60 seconds between duplicate transactions
				$payment->setParameter("x_duplicate_window", 60);

				// Find all other fields that are set and set the parameters
				foreach($p as $field => $value) {
					$payment->setParameter("x_" . $field, $value);
				}
				
				// Actually process the payment now
				$payment->process();
				
				// Now check the result
				if($payment->isApproved()) {
					$result = array(
						"approval_code" => $payment->getAuthCode(),
						"avs_result" => $payment->getAVSResponse(),
						"transaction_id" => $payment->getTransactionID(),
					);
					return array(true, $result);
				} elseif($payment->isDeclined()) {
					$reason = $payment->getResponseText();					// Get the reason it was declined
					return array(false, $reason);
				}	elseif ($payment->isError()) {
					return array(false, $payment->getResultResponse());
				}
			} else {
				authorize_error("Couldn't load AuthnetAIM object.", true, true);
			}
		}
	} // end authorize_process_payment


	/**
	 * Debugging function. Prints out the contents of the object in a table.
	 */
	if(!function_exists("authorize_print_pre")) {
		function authorize_print_pre($die = false) {
			authorize_init();
			$payment = globals("authorize", "AuthnetAIM");
			echo "Authorize Output:<br />" . $payment;
			if($die) die();
		}
	}

	/**
	 * authorize_error($message, $display = false, $die = false) - sets and optionally
	 * displays an error message. Also can hald execution.
	 *
	 * @param string $message
	 * @param bool $display
	 * @param bool $die
	 * @return string Returns the current error message
	 */
	if(!function_exists("authorize_error")) {
		function authorize_error($message = NULL, $display = false, $die = false) {
			if($message !== NULL) globals("authorize", "error_message", $message);
			if($display) echo "<p class='authorize-error'>{$message}</p>";
			if($die) die();
			return $message;
		}
	} // end authorize_error

?>