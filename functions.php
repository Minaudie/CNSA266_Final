<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>PHP Code Blocks</title>
<meta http-equiv="content-type"
	content="text/html; charset=iso-8859-1"/>
</head>
<body>
<p>
<?php
return substr($page, 0, strlen($page)-1);
}
function pf_validate_number($value, $function, $redirect) {
if(isset($value) == TRUE) {
if(is_numeric($value) == FALSE) {
$error = 1;
}
if($error == 1) {
header(“Location: “ . $redirect);
}
else {
$final = $value;
}
}
else {
if($function == ‘redirect’) {
header(“Location: “ . $redirect);
}
if($function == “value”) {
$final = 0;
}
}
return $final;
}

return substr($page, 0, strlen($page)-1);
}
function pf_validate_number($value, $function, $redirect) {
if(isset($value) == TRUE) {
if(is_numeric($value) == FALSE) {
$error = 1;
}
if($error == 1) {
header(“Location: “ . $redirect);
}
else {
$final = $value;
}
}
else {
if($function == ‘redirect’) {
header(“Location: “ . $redirect);
}
if($function == “value”) {
$final = 0;
}
}
return $final;
}
?>
</p>
</body>
</html>