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
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Asterisk Call Center Stats</title>
    <style type="text/css" media="screen">@import "css/basic.css";</style>
    <style type="text/css" media="screen">@import "css/tab.css";</style>
    <style type="text/css" media="screen">@import "css/table.css";</style>
    <style type="text/css" media="screen">@import "css/fixed-all.css";</style>
	<script type="text/javascript" src="js/1.10.2/jquery.min.js"></script>
	<script type="text/javascript" src="js/sorttable.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
</head>
<?php
// This query shows every call for agents, we collect into a named array the values of holdtime and calltime
$query = "SELECT time, queuename, agent, event, data1, data2, data3 FROM $DBTable
WHERE time >= '$start' AND time <= '$end' AND event IN ('COMPLETECALLER', 'COMPLETEAGENT')
AND queuename IN ($queue) AND agent in ($agent)";

$res = mysqli_query($connection, $query);
while ($row = mysqli_fetch_row($res)) {
	$total_calls2["$row[2]"]++;
	$record["$row[2]"][] = $row[0] . "|" . $row[1] . "|" . $row[3] . "|" . $row[4];
	$total_hold2["$row[2]"] += $row[4];
	$total_time2["$row[2]"] += $row[5];
	$grandtotal_hold += $row[4];
	$grandtotal_time += $row[5];
	$grandtotal_calls++;
	$hold["$row[1]"][] = $row[4];
	if ($row[4] <= 5) {
		$hold15["$row[1]"] += count($row[4]);
	} elseif (($row[4] >= 6) && ($row[4] <= 10)) {
		$hold30["$row[1]"] += count($row[4]);
	} elseif (($row[4] >= 11) && ($row[4] <= 15)) {
		$hold45["$row[1]"] += count($row[4]);
	} elseif (($row[4] >= 16) && ($row[4] <= 20)) {
		$hold60["$row[1]"] += count($row[4]);
	} elseif (($row[4] >= 21) && ($row[4] <= 25)) {
		$hold75["$row[1]"] += count($row[4]);
	} elseif (($row[4] >= 26) && ($row[4] <= 30)) {
		$hold90["$row[1]"] += count($row[4]);
	} elseif ($row[4] >= 31) {
		$hold91["$row[1]"] += count($row[4]);
	}
	$durall["$row[1]"][] = $row[5];
	if ($row[5] <= 5) {
		$dur5["$row[1]"] += count($row[5]);
	} elseif (($row[5] >= 6) && ($row[5] <= 10)) {
		$dur10["$row[1]"] += count($row[5]);
	} elseif (($row[5] >= 11) && ($row[5] <= 15)) {
		$dur15["$row[1]"] += count($row[5]);
	} elseif (($row[5] >= 16) && ($row[5] <= 20)) {
		$dur20["$row[1]"] += count($row[5]);
	} elseif (($row[5] >= 21) && ($row[5] <= 25)) {
		$dur25["$row[1]"] += count($row[5]);
	}
}
$total_calls_print = array_sum($total_calls2);
$total_duration_print = ceil(array_sum($total_time2) / 60);
$average_duration = ceil(array_sum($total_time2) / $total_calls_print);
$average_hold = ceil(array_sum($total_hold2) / $total_calls_print);

$start_parts = explode(" ,:", $start);
$end_parts = explode(" ,:", $end);

mysqli_close($connection);
?>

<body>
<?php include "menu.php";?>
<div id="main">
    <div id="contents">
        <TABLE width='99%' cellpadding=3 cellspacing=3 border=0>
        <THEAD>
        <TR>
            <TD valign=top width='50%'>
                <TABLE width='100%' border=0 cellpadding=0 cellspacing=0>
                <CAPTION><?php echo $lang["$language"]['report_info'] ?></CAPTION>
                <TBODY>
                <TR>
                    <TD><?php echo $lang["$language"]['queue'] ?>:</TD>
                    <TD><?php echo $queue ?></TD>
                </TR>
                </TR>
                       <TD><?php echo $lang["$language"]['start'] ?>:</TD>
                       <TD><?php echo $start_parts[0] ?></TD>
                </TR>
                </TR>
                <TR>
                       <TD><?php echo $lang["$language"]['end'] ?>:</TD>
                       <TD><?php echo $end_parts[0] ?></TD>
                </TR>
                <TR>
                       <TD><?php echo $lang["$language"]['period'] ?>:</TD>
                       <TD><?php echo $period ?> <?php echo $lang["$language"]['days'] ?></TD>
                </TR>
                </TBODY>
                </TABLE>

            </TD>
            <TD valign=top width='50%'>

                <TABLE width='100%' border=0 cellpadding=0 cellspacing=0>
                <CAPTION><?php echo $lang["$language"]['answered_calls'] ?></CAPTION>
                <TBODY>
                <TR>
                  <TD><?php echo $lang["$language"]['answered_calls'] ?></TD>
                  <TD><?php echo $total_calls_print ?> <?php echo $lang["$language"]['calls'] ?></TD>
                </TR>

                <TR>
                  <TD><?php echo $lang["$language"]['avg_calltime'] ?>:</TD>
                  <TD><?php echo $average_duration ?> <?php echo $lang["$language"]['secs'] ?></TD>
                </TR>
                <TR>
                  <TD><?php echo $lang["$language"]['total'] ?> <?php echo $lang["$language"]['calltime'] ?>:</TD>
                  <TD><?php echo $total_duration_print ?> <?php echo $lang["$language"]['minutes'] ?></TD>
                </TR>
                <TR>
                  <TD><?php echo $lang["$language"]['avg_holdtime'] ?>:</TD>
                  <TD><?php echo $average_hold ?> <?php echo $lang["$language"]['secs'] ?></TD>
                </TR>
                </TBODY>
              </TABLE>

            </TD>
        </TR>
        </THEAD>
        </TABLE>
        <?php
          $cover_pdf .= $lang["$language"]['queue'] . ": " . $queue . "\n";
          $cover_pdf .= $lang["$language"]['start'] . ": " . $start_parts[0] . "\n";
          $cover_pdf .= $lang["$language"]['end'] . ": " . $end_parts[0] . "\n";
          $cover_pdf .= $lang["$language"]['period'] . ": " . $period . " " . $lang["$language"]['days'] . "\n";
          $cover_pdf .= $lang["$language"]['answered_calls'] . ": " . $total_calls_print . " " . $lang["$language"]['calls'] ."\n";
          $cover_pdf .= $lang["$language"]['avg_calltime'] . ": " .  $average_duration . " " . $lang["$language"]['secs'] . "\n";
          $cover_pdf .= $lang["$language"]['total'] . " " . $lang["$language"]['calltime'] . ": " . $total_duration_print . " " . $lang["$language"]['minutes'] . "\n";
          $cover_pdf .= $lang["$language"]['avg_holdtime'] . ": " . $average_hold . " " . $lang["$language"]['secs'] . "\n";
        ?>
        <br/>
        <a name='summary'></a>
        <TABLE width='99%' cellpadding=3 cellspacing=3 border=0 class='sortable' id='tablesummary' >
        <CAPTION>
        <a href='#0'><img src='images/go-up.png' border=0 class='icon' width=16 height=16
        <?php
tooltip($lang["$language"]['gotop'], 200);
?>
        ></a>&nbsp;&nbsp;
        <?php echo $lang["$language"]['answered_calls_by_agent'] ?>
        </CAPTION>
		</br>
            <THEAD>
            <TR>
                  <TH><?php echo $lang["$language"]['agent'] ?></TH>
                  <TH><?php echo $lang["$language"]['Calls'] ?></TH>
                  <TH><?php echo $lang["$language"]['percent'] ?> <?php echo $lang["$language"]['Calls'] ?></TH>
                  <TH><?php echo $lang["$language"]['calltime'] ?></TH>
                  <TH><?php echo $lang["$language"]['percent'] ?> <?php echo $lang["$language"]['calltime'] ?></TH>
                  <TH><?php echo $lang["$language"]['avg'] ?> <?php echo $lang["$language"]['calltime'] ?></TH>
                  <TH><?php echo $lang["$language"]['holdtime'] ?></TH>
                  <TH><?php echo $lang["$language"]['avg'] ?> <?php echo $lang["$language"]['holdtime'] ?></TH>
            </TR>
            </THEAD>
            <TBODY>
                <?php
$header_pdf = array($lang["$language"]['agent'], $lang["$language"]['Calls'], $lang["$language"]['percent'], $lang["$language"]['calltime'], $lang["$language"]['percent'], $lang["$language"]['avg'], $lang["$language"]['holdtime'], $lang["$language"]['avg']);
$width_pdf = array(64, 32, 32, 32, 32,32, 32, 32);
$title_pdf = $lang["$language"]['answered_calls_by_agent'];

$contador = 0;
$query1 = "";
$query2 = "";
$data_pdf = array();
if ($total_calls2 > 0) {
	foreach ($total_calls2 as $agent => $val) {
		$contavar = $contador + 1;
		$cual = $contador % 2;
		if ($cual > 0) {$odd = " class='odd' ";} else { $odd = "";}
		$query1 .= "val$contavar=" . $total_time2["$agent"] . "&var$contavar=$agent&";
		$query2 .= "val$contavar=" . $val . "&var$contavar=$agent&";

		$time_print = seconds2minutes($total_time2["$agent"]);
		$avg_time = $total_time2["$agent"] / $val;
		$avg_time = round($avg_time, 2);

		$avg_print = seconds2minutes($avg_time);

		echo "<TR $odd>\n";
		echo "<TD>$agent</TD>\n";
		echo "<TD>$val</TD>\n";
		if ($grandtotal_calls > 0) {
			$percentage_calls = $val * 100 / $grandtotal_calls;
		} else {
			$percentage_calls = 0;
		}
		$percentage_calls = number_format($percentage_calls, 2);
		echo "<TD>$percentage_calls " . $lang["$language"]['percent'] . "</TD>\n";
		echo "<TD>$time_print " . $lang["$language"]['minutes'] . "</TD>\n";
		if ($grandtotal_time > 0) {
			$percentage_time = $total_time2["$agent"] * 100 / $grandtotal_time;
		} else {
			$percentage_time = 0;
		}
		$percentage_time = number_format($percentage_time, 2);
		echo "<TD>$percentage_time " . $lang["$language"]['percent'] . "</TD>\n";
		//echo "<TD>$avg_time ".$lang["$language"]['secs']."</TD>\n";
		echo "<TD>$avg_print " . $lang["$language"]['minutes'] . "</TD>\n";
		echo "<TD>" . $total_hold2["$agent"] . " " . $lang["$language"]['secs'] . "</TD>\n";
		$avg_hold = $total_hold2["$agent"] / $val;
		$avg_hold = number_format($avg_hold, 2);
		echo "<TD>$avg_hold " . $lang["$language"]['secs'] . "</TD>\n";
		echo "</TR>\n";

		$linea_pdf = array($agent, $val, "$percentage_calls " . $lang["$language"]['percent'], $total_time2["$agent"], "$percentage_time " . $lang["$language"]['percent'], "$avg_time " . $lang["$language"]['secs'], $total_hold2["$agent"] . " " . $lang["$language"]['secs'], "$avg_hold " . $lang["$language"]['secs']);
		$data_pdf[] = $linea_pdf;
		$contador++;
	}
}
?>
            </TBODY>
        </TABLE>
            <?php if ($total_calls2 > 0) {
	print_exports($header_pdf, $data_pdf, $width_pdf, $title_pdf, $cover_pdf);
}
?>

        <br/>
<TABLE width='99%' cellpadding=3 cellspacing=3 border=0>
<THEAD>
<TR>
<TD align=center bgcolor='#ffffff' width='50%'>
 <script type="text/javascript">
    google.charts.load("current", {packages:['corechart']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
      var data = google.visualization.arrayToDataTable([
	  <?php
echo "[\"Agent\", \"Dur\", {role: \"style\"}],\n";
foreach ($total_time2 as $key => $val) {
	$dur = ceil($val / 60);
	echo "[\"" . $key . "\", " . $dur . ", \"orangered\"],\n";
}
?>
      ]);
      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: "stringify",
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },
                       2]);

      var options = {
        title: <?php echo json_encode($lang["$language"]['agent_in_call_dur']); ?>,
        bar: {groupWidth: "75%"},
        legend: { position: "none" },
      };
      var chart = new google.visualization.ColumnChart(document.getElementById("dur_by_agent"));
      chart.draw(view, options);
  }

    </script>

    <div id="dur_by_agent"></div>
</TD>
<TD align=center bgcolor='#ffffff' width='50%'>
<script type="text/javascript">
    google.charts.load("current", {packages:['corechart']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
      var data = google.visualization.arrayToDataTable([
	  <?php
echo "[\"Agent\", \"Calls\", {role: \"style\"}],\n";
foreach ($total_calls2 as $key => $val) {
	echo "[\"" . $key . "\", " . $val . ", \"naviblue\"],\n";
}
?>
      ]);
      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: "stringify",
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },
                       2]);

      var options = {
        title: <?php echo json_encode($lang["$language"]['answered_calls_by_agent']); ?>,
        bar: {groupWidth: "75%"},
        legend: { position: "none" },
      };
      var chart = new google.visualization.ColumnChart(document.getElementById("ans_by_agent"));
      chart.draw(view, options);
  }
</script>
    <div id="ans_by_agent"></div>
	          </TD>
            </TR>
            </THEAD>
          </TABLE>
          <BR/>
            <a name='resp'></a>
            <table width='99%' cellpadding=3 cellspacing=3 border=0 >
            <caption>
            <a href='#0'><img src='images/go-up.png' border=0 class='icon' width=16 height=16
            <?php
tooltip($lang["$language"]['gotop'], 200);
?>
            ></a>&nbsp;&nbsp;
            <?php echo $lang["$language"]['call_response'] ?></caption>
            <thead>
            <tr>

                <td valign=top width="50%" align=center  bgcolor='#ffffff'>
				 <table width='99%' cellpadding=1 cellspacing=1 border=0 class='sortable' id='tableresp'>
				<thead>
                <tr>
				   	<TH><?php echo $lang["$language"]['queue'] ?></th>
					<TH><?php echo $lang["$language"]['5sec'] ?></th>
					<TH><?php echo $lang["$language"]['_10sec'] ?></th>
					<TH><?php echo $lang["$language"]['15sec'] ?></th>
					<TH><?php echo $lang["$language"]['_20sec'] ?></th>
					<TH><?php echo $lang["$language"]['25sec'] ?></th>
					<TH><?php echo $lang["$language"]['30sec'] ?></th>
					<TH><?php echo $lang["$language"]['31sec'] ?></th>
					<TH><?php echo $lang["$language"]['ALLS'] ?></th>
                </tr>
				</thead>
				<tbody>
<?php
foreach ($hold as $key => $row) {
	$total_ans15 += $hold15["$key"];
	$total_ans30 += $hold30["$key"];
	$total_ans45 += $hold45["$key"];
	$total_ans60 += $hold60["$key"];
	$total_ans75 += $hold75["$key"];
	$total_ans90 += $hold90["$key"];
	$total_ans91 += $hold91["$key"];
	$total = count($hold["$key"]);
	echo "<tr><td>" . $key . "</td>
	<td>" . $hold15["$key"] . "</td>
	<td>" . $hold30["$key"] . "</td>
	<td>" . $hold45["$key"] . "</td>
	<td>" . $hold60["$key"] . "</td>
	<td>" . $hold75["$key"] . "</td>
	<td>" . $hold90["$key"] . "</td>
	<td>" . $hold91["$key"] . "</td>
	<td>" . $total . "</td></tr>\n";
}
?>
			   </tbody>
			  </table>
			</td>
			 <td valign=top width='50%' bgcolor='#ffffff'>

<script type="text/javascript">
google.charts.load('current', {packages: ['corechart', 'line']});
    google.charts.setOnLoadCallback(drawBasic);
    function drawBasic() {
    var data = new google.visualization.DataTable();
      data.addColumn('number', 'Period');
      data.addColumn('number', '');
      data.addRows([
     <?php

echo "[0, 0],[5, " . $total_ans15 . "],[10, " . $total_ans30 . "],[15, " . $total_ans45 . "],[20, " . $total_ans60 . "],[25, " . $total_ans75 . "],[30, " . $total_ans90 . "],[31, " . $total_ans91 . "],\n";
?>
      ]);
  var options = {
        title: <?php echo json_encode($lang["$language"]['call_response']); ?>,
        hAxis: {
          title: '<?php echo json_encode($lang["$language"]['period']); ?>'
        },
        vAxis: {
          title: '<?php echo json_encode($lang["$language"]['count']); ?>'
        }
      };
      var chart = new google.visualization.LineChart(document.getElementById('ans_serv_level'));
      chart.draw(data, options);
    }
</script>
<div id="ans_serv_level"></div>
               </td>
              </tr>
             </thead>
            </table>
		  <br/>
		  <a name='durper'></a>
		  <table width='99%' cellpadding=3 cellspacing=3 border=0 >
            <caption>
            <a href='#0'><img src='images/go-up.png' border=0 class='icon' width=16 height=16
            <?php
tooltip($lang["$language"]['gotop'], 200);
?>
            ></a>&nbsp;&nbsp;
            <?php echo $lang["$language"]['dur_by_period'] ?></caption>
            <thead>
            <tr>

                <td valign=top width="50%" align=center  bgcolor='#ffffff'>
				<table width='99%' cellpadding=1 cellspacing=1 border=0 class='sortable' id='tabledurper'>
				 <thead>
                  <tr>
				   	<TH><?php echo $lang["$language"]['queue'] ?></th>
					<TH><?php echo $lang["$language"]['0-5sec'] ?></th>
					<TH><?php echo $lang["$language"]['6-10sec'] ?></th>
					<TH><?php echo $lang["$language"]['11-15sec'] ?></th>
					<TH><?php echo $lang["$language"]['16-20sec'] ?></th>
					<TH><?php echo $lang["$language"]['21-25sec'] ?></th>
					<TH><?php echo $lang["$language"]['26sec'] ?></th>
                 </tr>
				</thead>
				<tbody>
                <?php
foreach ($durall as $key => $row) {
	$total_dur5 += $dur5["$key"];
	$total_dur10 += $dur10["$key"];
	$total_dur15 += $dur15["$key"];
	$total_dur20 += $dur20["$key"];
	$total_dur25 += $dur25["$key"];
	$total2 = count($durall["$key"]);
	$total25 = $dur5["$key"] + $dur10["$key"] + $dur15["$key"] + $dur20["$key"] + $dur25["$key"];
	$total26 = $total2 - $total25;
	echo "<tr><td>" . $key . "</td>
	<td>" . $dur5["$key"] . "</td>
	<td>" . $dur10["$key"] . "</td>
	<td>" . $dur15["$key"] . "</td>
	<td>" . $dur20["$key"] . "</td>
	<td>" . $dur25["$key"] . "</td>
	<td>" . $total26 . "</td>
	</tr>\n";
}
?>
			  </tbody>
			 </table>
			</td>
			<td valign=top width='50%' bgcolor='#ffffff'>
<script type="text/javascript">
google.charts.load('current', {packages: ['corechart', 'line']});
    google.charts.setOnLoadCallback(drawBasic);
    function drawBasic() {
    var data = new google.visualization.DataTable();
      data.addColumn('number', 'Period');
      data.addColumn('number', '');

      data.addRows([
     <?php
echo "[0, 0],[5, " . $total_dur5 . "],[10, " . $total_dur10 . "],[15, " . $total_dur15 . "],[20, " . $total_dur20 . "],[25, " . $total_dur25 . "],\n";
?>
      ]);
      var options = {
	    title: <?php echo json_encode($lang["$language"]['dur_by_period']); ?>,
        hAxis: {
          title: '<?php echo json_encode($lang["$language"]['period']); ?>'
        },
        vAxis: {
          title: '<?php echo json_encode($lang["$language"]['count']); ?>'
        }
      };
      var chart = new google.visualization.LineChart(document.getElementById('ans_dur_period'));
      chart.draw(data, options);
    }
</script>
            <div id="ans_dur_period"></div>
            </td>
            </tr>
            </thead>
          </table>
          <br/>
           <a name='4'></a>
            <table width='99%' cellpadding=3 cellspacing=3 border=0>
            <caption>
            <a href='#0'><img src='images/go-up.png' border=0 class='icon' width=16 height=16

            <?php
tooltip($lang["$language"]['gotop'], 200);
?>
            ></a>&nbsp;&nbsp;
            <?php echo $lang["$language"]['disconnect_cause'] ?></caption>
              </table>
              <br/>
            <td align=center  bgcolor='#ffffff'>
	<script type="text/javascript">
	  var hangcause = <?php echo json_encode($lang["$language"]['disconnect_cause']); ?>;
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
<?php
echo "['Cause', 'Events'],\n";
foreach ($res as $row) {
	if ($row['event'] == "COMPLETEAGENT") {
		$action_agent = $lang["$language"]['agent_hungup'];
		$num += count($row['event']);
	} elseif ($row['event'] == "COMPLETECALLER") {
		$action_caller = $lang["$language"]['caller_hungup'];
		$num2 += count($row['event']);
	}
}
echo "['" . $action_agent . "', " . $num . "],['" . $action_caller . "', " . $num2 . "]\n";
mysqli_free_result($res);
?>
        ]);
        var options = {
          title: <?php echo json_encode($lang["$language"]['disconnect_cause']); ?>
        };
        var chart = new google.visualization.PieChart(document.getElementById('ans_disc_cause'));
        chart.draw(data, options);
      }
    </script>
    <div id="ans_disc_cause" style="width: 450px;"></div>

</td>
</tr>
</thead>
</table>

</div>
</div>

<div id='footer'><a href='https://asterisk-pbx.ru'>Asterisk-pbx.ru</a> 2017</div>
</body>
</html>
