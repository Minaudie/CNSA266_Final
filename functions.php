<?php
	//TODO: remove?
	function pf_script_with_get($script) {
		$page = $script;
		$page = $page . "?";

		foreach($_GET as $key => $val) {
			$page = $page . $key . "=" . $val . "&";
		}
		return substr($page, 0, strlen($page)-1);
	}

	//checks if a number is numeric
	function pf_validate_number($value, $function, $redirect) {
		$error = 0;
		if(isset($value)) {
			if(is_numeric($value) == FALSE) {
				$error = 1;
			}

			if($error == 1) {
				redirect($redirect);
			} else {
				$final = $value;
			}
		} else {
			if($function == "redirect") {
				redirect($redirect);
			}

			if($function == "value") {
				$final = 0;
			}
		}
		return $final;
	}

	//page redirect function
	function redirect($url) {
		ob_start();
		header("Location: " . $url);
		ob_end_flush();
		exit();
	}
?>
