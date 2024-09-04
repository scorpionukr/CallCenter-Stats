<?php
require_once 'sendfile.class.php';
if (isset($_REQUEST['f'])) {
	$fname = base64_decode($_REQUEST['f']);
	$file =  $fname;
	$send = new Sendfile;
	$send->Path = $file;
	$send->send();
	exit;
}

header('HTTP/1.1 404 Not Found');