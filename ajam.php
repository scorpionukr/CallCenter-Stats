<?php
require_once 'ajam.class.php';
include 'config.php';

$params = array();

if (isset($_POST['Action'])) {
	$command = $_POST['Action'];
	foreach ($_POST as $k => $v) {
		$params[$k] = $v;
	}
	$ajam = new Ajam($config);
	$ajam->doCommand($command, $params);
	$result = $ajam->getResult();
	$res2 = $result;

	$result = explode("\r\n", $result);
	end($result);
	$count1 = key($result);
	$result = array_slice($result, 3, $count1);
	$result = array_slice($result, 0, $count1 - 8);
	array_shift($result);

	$result = implode(';', $result);
	$result = explode(';;', $result);

	end($result);
	$count2 = key($result);

	for ($i = 0; $i <= $count2; ++$i) {
		$item = explode(';', $result[$i]);
		foreach ($item as &$val) {
			list($k, $v) = array_pad(explode(': ', $val, 2), 2, null);
			$parse[$i][$k] = $v;
		}
	}
	print_r(json_encode($parse));
}

if (isset($_POST['CliCom'])) {
	$params['Command'] = $_POST['CliCom'];
	$ajam = new Ajam($config);
	$ajam->doCommand('Command', $params);
	$result = $ajam->getResult();

	$result = explode("\r\n", $result);
	$result = array_slice($result, 5);
	foreach ($result as $key => $value) {
		if ($value == preg_match('/^Output:\s$/', subject)) {
			print(preg_filter('/Output:/', '', $value));
			print '<br/>';
		}

	}
}