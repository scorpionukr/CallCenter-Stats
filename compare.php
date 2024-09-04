<?php
/*
Copyright 2017, https://asterisk-pbx.ru

This file is part of Asterisk Call Center Stats.
Asterisk Call Center Stats is free software: you can redistribute it
and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

Asterisk Call Center Stats is distributed in the hope that it will be
useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Asterisk Call Center Stats.  If not, see
<http://www.gnu.org/licenses/>.
 */
require_once "config.php";
include "sesvars.php";
//ini_set('display_errors',1);
//error_reporting(E_WARNING);
?>
<?php
//query answered
$q_ans = "SELECT queuename as col FROM $DBTable
WHERE time >= '$start' AND time <= '$end' AND event IN ('COMPLETECALLER', 'COMPLETEAGENT')
AND queuename IN ($queue)";
//query unanswered
$q_uns = "SELECT queuename as col FROM $DBTable
WHERE time >= '$start' AND time <= '$end' AND event IN ('ABANDON', 'EXITWITHTIMEOUT') AND queuename IN ($queue)";
//query did all
//$q_did = "SELECT callid, data1 as col FROM $DBTable
//WHERE time >= '$start' AND time <= '$end' AND event = 'DID'";
//query abandon
//$q_temp = "CREATE TEMPORARY TABLE abandon SELECT callid FROM $DBTable WHERE time >= '$start' AND time <= '$end' AND event = 'ABANDON';";
//$q_aban = "SELECT data1 as col FROM $DBTable as queuelog, abandon WHERE time >= '$start' AND time <= '$end' AND event = 'DID' AND queuelog.callid = abandon.callid;";
//$q_drop = "DROP TABLE abandon;";

function array_mesh() {
	$numargs = func_num_args();
	$arg_list = func_get_args();
	$out = array();
	for ($i = 0; $i < $numargs; $i++) {
		$in = $arg_list[$i];
		foreach ($in as $key => $value) {
			if (array_key_exists($key, $out)) {
				$sum = $in[$key] + $out[$key];
				$out[$key] = $sum;
			} else {
				$out[$key] = $in[$key];
			}
		}
	}
	return $out;
}

function array_min() {
	$numargs = func_num_args();
	$arg_list = func_get_args();
	$out = array();
	for ($i = 0; $i < $numargs; $i++) {
		$in = $arg_list[$i];
		foreach ($in as $key => $value) {
			if (array_key_exists($key, $out)) {
				$sum = $in[$key] - $out[$key];
				$out[$key] = $sum;
			} else {
				$out[$key] = $in[$key];
			}
		}
	}
	return $out;
}

function arr_cnt($res) {
	$out = array();
	$i = 0;
	foreach ($res as $key => $row) {
		$i = $i + 1;
		$count[$i] = $row['col'];
	}
	$out = array_count_values($count);
	return $out;
}

//queue
$ques = explode(',', $queue);
sort($ques, SORT_STRING);
foreach ($ques as $r => $v) {
	$ques2[] = trim($v, "'");
}
$qpattern = array_flip($ques2);
$qpattern2 = array_min($qpattern, $qpattern);

$resans = $connection->query($q_ans);
$queueans = arr_cnt($resans);
ksort($queueans, SORT_STRING);
$queueans = array_mesh($queueans, $qpattern2);
ksort($queueans, SORT_STRING);

$resuns = $connection->query($q_uns);
$queueuns = arr_cnt($resuns);
ksort($queueuns, SORT_STRING);
$queueuns = array_mesh($queueuns, $qpattern2);
ksort($queueuns, SORT_STRING);

$queueall = array_mesh($queueans, $queueuns);
ksort($queueall, SORT_STRING);

$head_q = array('Очереди', 'Всего', 'Отвеч.', 'Пропущ.');

$compare_col = array();
$compare_tab = array();
$compare_col = array_map(null, $ques, $queueall, $queueans, $queueuns);
$compare_tab = array_map(null, $ques, $queueall, $queueans, $queueuns);
$percent = array();
foreach ($compare_tab as $key => $val) {
	$que = $val[0];
	//$percent[$val[0]] = round(100 / ($val[1] / $val[3]),2);
	$percent[$que] = round($val[3] * 100 / ($val[1]), 2);
	$compare_per[] = array($val[0], $val[1], $val[2], $val[3], $percent[$val[0]] . "%");
}

array_unshift($compare_col, $head_q);
//$resdrop = $connection->query($q_drop);
$resans->free();
$resuns->free();

//print_r($compare_per);
// print_r($queueans);
// echo "<br/>";
// print_r($queueuns);
// echo "<br/>";
// print_r($queueall);
// echo "<br/>";
// print_r($ques);
// echo "<br/>";

//did
// $resdid = $connection->query($q_did);
// $didall = arr_cnt($resdid);
// ksort($didall, SORT_STRING);

// if ($connection->query($q_temp)) {
// 	$resaban = $connection->query($q_aban);
// 	$diduns = arr_cnt($resaban);
// 	ksort($diduns, SORT_STRING);
// } else {
// 	$diduns = array();
// }

// $didname = array_keys($didall);
// foreach ($didname as $k => $v) {
// 	$didnamestr[] = "'" . $v . "'";
// }
// sort($didnamestr, SORT_STRING);

// $didiff = array_diff_key($didall, $diduns);
// $didiff = array_min($didiff, $didiff);
// $diduns2 = $didiff + $diduns;
// ksort($diduns2, SORT_STRING);

// $didans = array_min($diduns2, $didall);

// $head_d = array('Номера', 'Всего', 'Отвеч.', 'Пропущ.');

// $compare_d = array();
// $compare_d2 = array();
// $compare_d = array_map(null, $didnamestr, $didall, $didans, $diduns2);
// $compare_d2 = array_map(null, $didnamestr, $didall, $didans, $diduns2);

// array_unshift($compare_d, $head_d);

// $resdrop = $connection->query($q_drop);

// $resdid->free();
//$resaban->free();

$connection->close();
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Asterisk Call Center Stats</title>
      <style type="text/css" media="screen">@import "css/basic.css";</style>
      <style type="text/css" media="screen">@import "css/tab.css";</style>
      <style type="text/css" media="screen">@import "css/table.css";</style>
      <style type="text/css" media="screen">@import "css/fixed-all.css";</style>
    <script type="text/javascript" src="js/1.10.2/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <style>
    tr.google-visualization-table-tr-head{
   background-color: #607D8B;
  }

</style>
    <script type="text/javascript">

      google.charts.load('current', {'packages':['bar']});
      google.charts.setOnLoadCallback(drawChartQueue);

      function drawChartQueue() {
        var data = google.visualization.arrayToDataTable(
         <?php print_r(json_encode($compare_col));?>
          );

        var options = {
          chart: {
            title: 'По очередям',
            subtitle: '<?php echo $start . " - " . $end ?>'
          },
            colors: ['#82E0AA', '#85C1E9', '#FA8072']
        };

        var chart = new google.charts.Bar(document.getElementById('columnchart_queue'));

        chart.draw(data, google.charts.Bar.convertOptions(options));
      }



      google.charts.load('current', {'packages':['table']});
      google.charts.setOnLoadCallback(drawTableQueue);

      function drawTableQueue() {

        var options = {
          data: {
            allowHtml: true
          }
        };

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Очередь');
        data.addColumn('number', 'Всего');
        data.addColumn('number', 'Отвеч.');
        data.addColumn('number', 'Проп.');
        data.addColumn('string', 'Проц. проп.');
        data.addRows(
            <?php print_r(json_encode($compare_per));?>
          );
        var table = new google.visualization.Table(document.getElementById('tableQueue'));

        table.draw(data, {showRowNumber: true, width: '800px', height: '400px'});
      }



    </script>

</head>

<body>
<?php include "menu.php";?>
<div id="main">
    <div id="contents">
      <h1>Сравнение принятых / пропущенных вызовов за выбранный период</h1>
      <div id="columnchart_queue" style="width: 800px; height: 400px;"></div>
       <br/>
      <div id="tableQueue"></div>
      <br/>
      <tr/>
    </div>
</div>

<div id='footer'><a href='https://asterisk-pbx.ru'>Asterisk-pbx.ru</a> 2018</div>
</body>
</html>
