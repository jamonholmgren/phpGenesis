<?
	
	/**
	 *	Loads the thirdparty plugin paypal.class.php and initializes the object.
	 *	
	 *	
	**/
	if(!function_exists("paypal_init")) {
		function paypal_init() {
			if(!isset($GLOBALS['paypal']) || !is_object($GLOBALS['paypal']['paypal_class'])) {
				load_thirdparty_plugin("paypal/paypal.class.php");
				$GLOBALS['paypal']['paypal_class'] = new paypal_class;
				if(!is_object($GLOBALS['paypal']['paypal_class'])) die("<p>Couldn't load Paypal functionality! Check to make sure paypal/paypal.class.php is loading.</p>");
				if(settings("paypal", "sandbox") == TRUE) {
					$GLOBALS['paypal']['paypal_class']->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';   // testing paypal url
				} else {
					$GLOBALS['paypal']['paypal_class']->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';     // paypal url
				}
			}
			return $GLOBALS['paypal']['paypal_class'];
		}
	}
	
	/**
	 *	Handles incoming Paypal ipn requests.
	 *	
	 *	
	**/
	if(!function_exists("paypal_ipn_valid")) {
		function paypal_ipn_valid() {
			$p = paypal_init();
			if(is_object($p)) return $p->validate_ipn();
			return false;
		}
	}
	
?>