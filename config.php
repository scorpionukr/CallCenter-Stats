<?php
require_once "misc.php";

$DBServer = 'localhost';
$DBUser = 'freepbxuser';
$DBPass = '';
$DBName = 'asteriskcdrdb';
$DBTable = 'queuelog';

define('RECPATH',"/var/spool/asterisk/monitor/");

$connection = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
$connection->set_charset('utf8');

// check connection
if ($connection->connect_error) {
	trigger_error('Database connection failed: ' . $connection->connect_error, E_USER_ERROR);
}

$confpbx = new mysqli('localhost', 'freepbxuser', '', 'asterisk');
$confpbx->set_charset('utf8');


//$user = $_SERVER['PHP_AUTH_USER'];
//$pass = $_SERVER['PHP_AUTH_PW'];

//$valid_passwords2 = $confpbx->query("SELECT password_sha1 FROM ampusers WHERE username = '$user'");
//$valid_passwords = $valid_passwords2->fetch_row();

//$validated = (sha1($pass) == $valid_passwords[0]);

//if (!$validated) {
//	header('WWW-Authenticate: Basic realm="fs-tst"');
//	header('HTTP/1.0 401 Unauthorized');
//	die("Not authorized");
//}

//$valid_passwords2->free();

//AJAM for realtime. For use: webenable=yes; mini-http enable; 

$config['urlraw'] = 'http://127.0.0.1:8088/asterisk/rawman';
$config['admin'] = 'admin';
$config['secret'] = '';
$config['authtype'] = 'plaintext';
$config['cookiefile'] = null;
$config['debug'] = false;


// Available languages "en", "ru"
$language = "ru";

require_once "lang/$language.php";

$page_rows = '100';
//$midb = conecta_db($dbhost,$dbname,$dbuser,$dbpass);
$self = $_SERVER['PHP_SELF'];

$DB_DEBUG = false;

session_start();
header('content-type: text/html; charset: utf-8');

?>
