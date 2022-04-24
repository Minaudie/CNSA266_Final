<?php
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
