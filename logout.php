<?php
require_once("config.php");
require_once("sesvars.php");

function flushSession() {
    unset($_SESSION["QSTATS"]);

    session_destroy();

    return true;
}
flushSession();
?>
