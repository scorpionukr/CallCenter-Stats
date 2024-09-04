<?php
include "config.php";

if ((isset($_POST['agentAdd'])) && (isset($_POST['agentCode']))) {
	$agentadd = $_POST['agentAdd'];
	$agentcode = $_POST['agentCode'];
	$query = "INSERT INTO queue_users (`code`, `agent`) values ('$agentcode', '$agentadd')";
	$add = $confpbx->query($query);
}

if (isset($_POST['agentDel'])) {
	$agentdel = $_POST['agentDel'];
	$query = "DELETE FROM queue_users where `id` = '$agentdel'";
	$add = $confpbx->query($query);
}