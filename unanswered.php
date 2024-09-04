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
require_once("config.php");
include("sesvars.php");
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
	<script type="text/javascript" src="js/sorttable.js"></script>
    <script type="text/javascript" src="js/1.10.2/jquery.min.js"></script>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
</head>
<?php
$sql = "SELECT time, callid, queuename, event, data1, data2, data3 FROM $DBTable WHERE time >= '$start' AND time <= '$end' AND event IN ('ABANDON', 'EXITWITHTIMEOUT') AND queuename IN ($queue)";
$res1 = mysqli_query($connection, $sql);

while($row=mysqli_fetch_row($res1)) {		
	$queue_calls["$row[2]"] += count($row[1]);
	$abandon["$row[2]"][] = $row[6];
if ($row[3] == "ABANDON") {	
if ($row[6]<=10) {
 $abandon10["$row[2]"] += count($row[6]);
 } elseif (($row[6] >= 11)&&($row[6] <= 20)) {
 $abandon20["$row[2]"] += count($row[6]);
 } elseif (($row[6] >= 21)&&($row[6] <= 30)) {
 $abandon30["$row[2]"] += count($row[6]);
 } elseif (($row[6] >= 31)&&($row[6] <= 40)) {
 $abandon40["$row[2]"] += count($row[6]);
 } elseif (($row[6] >= 41)&&($row[6] <= 50)) {
 $abandon50["$row[2]"] += count($row[6]);
 } elseif (($row[6] >= 51)&&($row[6] <= 60)) {
 $abandon60["$row[2]"] += count($row[6]);
 } elseif ($row[6] >= 61) {
 $abandon61["$row[2]"] += count($row[6]);
 }
} 
}
	$total_calls = 0;
	$total_abandon_calls = 0;
	$total_timeout_calls = 0;
foreach($res1 as $row){
    $total_calls += count($row['callid']);
    $total_hold += $row['data3'];
	$abandon_end_pos +=$row['data1'];
	$abandon_start_pos +=$row['data2'];	
        if ( $row['event'] == "ABANDON" ) {
        $total_abandon_calls += count($row['callid']);
        $event_abandon = $lang["$language"]['user_abandon'];
        } elseif (	$row['event'] == "EXITWITHTIMEOUT") {
        $total_timeout_calls += count($row['callid']);
        $event_timeout=$lang["$language"]['timeout'];
        }
    $abandon_average_hold = number_format($total_hold / $total_calls,2);
    $abandon_average_start = round($abandon_start_pos / $total_calls);
    $abandon_average_end = floor($abandon_end_pos / $total_calls);
}

mysqli_free_result($res1);
mysqli_close($connection);	

$start_parts = explode(" ,:", $start);
$end_parts   = explode(" ,:", $end);

?>
<body>
<?php include("menu.php"); ?>
<div id="main">
	<div id="contents">
		<TABLE width='99%' cellpadding=3 cellspacing=3 border=0>
		<THEAD>
		<TR>
			<TD valign=top width='50%'>
				<TABLE width='100%' border=0 cellpadding=0 cellspacing=0>
				<CAPTION><?php echo $lang["$language"]['report_info']?></CAPTION>
				<TBODY>
				<TR>
					<TD><?php echo $lang["$language"]['queue']?>:</TD>
					<TD><?php echo $queue?></TD>
				</TR>
    	        </TR>
        	       	<TD><?php echo $lang["$language"]['start']?>:</TD>
            	   	<TD><?php echo $start_parts[0]?></TD>
				</TR>
    	        </TR>
        	    <TR>
            	   	<TD><?php echo $lang["$language"]['end']?>:</TD>
               		<TD><?php echo $end_parts[0]?></TD>
	            </TR>
    	        <TR>
        	       	<TD><?php echo $lang["$language"]['period']?>:</TD>
            	   	<TD><?php echo $period?> <?php echo $lang["$language"]['days']?></TD>
	            </TR>
				</TBODY>
				</TABLE>

			</TD>
			<TD valign=top width='50%'>

				<TABLE width='100%' border=0 cellpadding=0 cellspacing=0>
				<CAPTION><?php echo $lang["$language"]['unanswered_calls']?></CAPTION>
				<TBODY>
		        <TR> 
                  <TD><?php echo $lang["$language"]['number_unanswered']?>:</TD>
		          <TD><?php echo $total_calls?> <?php echo $lang["$language"]['calls']?></TD>
	            </TR>
                <TR>
                  <TD><?php echo $lang["$language"]['avg_wait_before_dis']?>:</TD>
                  <TD><?php echo $abandon_average_hold?> <?php echo $lang["$language"]['secs']?></TD>
                </TR>
		        <TR>
                  <TD><?php echo $lang["$language"]['avg_queue_pos_at_dis']?>:</TD>
		          <TD><?php echo $abandon_average_end?></TD>
	            </TR>
                <TR>
                  <TD><?php echo $lang["$language"]['avg_queue_start']?>:</TD>
                  <TD><?php echo $abandon_average_start?></TD>
                </TR>
				</TBODY>
	          </TABLE>

			</TD>
		</TR>
		</THEAD>
		</TABLE>
		<br/>	

		<a name='1'></a>
		<TABLE width='99%' cellpadding=3 cellspacing=3 border=0>
		<CAPTION>
		<a href='#0'><img src='images/go-up.png' border=0 width=16 height=16 class='icon' 
		<?php 
		tooltip($lang["$language"]['gotop'],200);
		?>
		></a>&nbsp;&nbsp;
		<?php echo $lang["$language"]['disconnect_cause']?>
		</CAPTION>
			<THEAD>
			<TR>
			<TD valign=top width='50%' bgcolor='#ffffff'>
				<TABLE width='99%' cellpadding=1 cellspacing=1 border=0>
				<THEAD>
				<TR>
					<TH><?php echo $lang["$language"]['cause']?></TH>
					<TH><?php echo $lang["$language"]['count']?></TH>
					<TH><?php echo $lang["$language"]['percent']?></TH>
				</TR>
				</THEAD>
				<TBODY>
                <TR> 
                  <TD><?php echo $lang["$language"]['user_abandon']?></TD>
			      <TD><?php echo $total_abandon_calls; ?> <?php echo $lang["$language"]['calls']; ?></TD>
			      <TD>
					  <?php
						if($total_calls > 0 ) {
							$percent=$total_abandon_calls*100/$total_calls;
						} else {
							$percent=0;
						}
						$percent=number_format($percent,2);
						echo $percent;
                      ?> 
                   <?php echo $lang["$language"]['percent']?></TD>
		        </TR>
			    <TR> 
                  <TD><?php echo $lang["$language"]['timeout']?></TD>
			      <TD><?php echo $total_timeout_calls; ?> <?php echo $lang["$language"]['calls']; ?></TD>
			      <TD>
					  <?php
						if($total_calls > 0 ) {
							$percent=$total_timeout_calls*100/$total_calls;
						} else {
							$percent=0;
						}
						$percent=number_format($percent,2);
						echo $percent;
                      ?> 
					<?php echo $lang["$language"]['percent']?></TD>
		        </TR>
				</TBODY>
			  </TABLE>
			</TD>
			<TD align=center bgcolor='#ffffff'>
	<script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {

        var data = google.visualization.arrayToDataTable([
<?php
	echo "['Cause', 'Events'],\n";
	echo "['".$event_abandon."', ".$total_abandon_calls."],\n";
	echo "['".$event_timeout."', ".$total_timeout_calls."],\n";
?>
        ]);
        var options = {
          title: <?php echo json_encode($lang["$language"]['disconnect_cause']); ?>
        };
        var chart = new google.visualization.PieChart(document.getElementById('unans_disc_cause'));
        chart.draw(data, options);
      }
    </script>
    <div id="unans_disc_cause"></div>
			</TD>
			</TR>
			</THEAD>
			</TABLE>

			<a name='2'></a>
			<TABLE width='99%' cellpadding=3 cellspacing=3 border=0>
			<CAPTION>
			<a href='#0'><img src='images/go-up.png' border=0 width=16 height=16 class='icon' 
			<?php 
			tooltip($lang["$language"]['gotop'],200);
			?>
			></a>&nbsp;&nbsp;
			<?php echo $lang["$language"]['unanswered_calls_qu']?>
			</CAPTION>
			<THEAD>
			<TR>
			<TD valign=top width='50%' bgcolor='#ffffff'>
				<TABLE width='99%' cellpadding=1 cellspacing=1 border=0>
				<THEAD>
                <TR> 
				   	<TH><?php echo $lang["$language"]['queue']?></TH>
					<TH><?php echo $lang["$language"]['count']?></TH>
					<TH><?php echo $lang["$language"]['percent']?></TH>
                </TR>
				</THEAD>
				<TBODY>
				<?php
				foreach($queue_calls as $key=>$row) {
				$percent_queue_calls = number_format($queue_calls["$key"]*100/$total_calls,2);
	               echo "<TR><TD>".$key."</TD>
	               <TD>".$queue_calls["$key"]."</TD>
				   <TD>".number_format($queue_calls["$key"]*100/$total_calls,2)."%</TD>
	               </TR>\n";
				}   
				
				?>
			  </TBODY>
			  </TABLE>
			</TD>
			<TD valign=top width="50%" align=center bgcolor='#ffffff'>
<script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
      var data = google.visualization.arrayToDataTable([
<?php
	     echo "['Queue', 'Calls'],\n";
    foreach($queue_calls as $key=>$row){
	     echo "['" . $key . "', " . $queue_calls["$key"] . "],\n";
    }
?>
        ]);
        var options = {
          title: <?php echo json_encode($lang["$language"]['unanswered_calls_qu']); ?>
        };
        var chart = new google.visualization.PieChart(document.getElementById('unans_q_calls'));
        chart.draw(data, options);
      }
    </script>
    <div id="unans_q_calls"></div>
			</TD>
			</TR>
			</THEAD>
			</TABLE>
			<br/>
		<a name='3'></a>
			<TABLE width='99%' cellpadding=3 cellspacing=3 border=0>
			<CAPTION>
			<a href='#0'><img src='images/go-up.png' border=0 width=16 height=16 class='icon' 
			<?php 
			tooltip($lang["$language"]['gotop'],200);
			?>
			></a>&nbsp;&nbsp;
			<?php echo $lang["$language"]['unanswered_by_period_queue']?>
			</CAPTION>			
			<THEAD>
			<TR>

	        <TD valign=top width="50%" align=center bgcolor='#ffffff'>
			    <TABLE width='99%' cellpadding=1 cellspacing=1 border=0>
				<THEAD>
                <TR> 
				   	<TH><?php echo $lang["$language"]['queue']?></TH>
					<TH><?php echo $lang["$language"]['10sec']?></TH>
					<TH><?php echo $lang["$language"]['20sec']?></TH>
					<TH><?php echo $lang["$language"]['30sec']?></TH>
					<TH><?php echo $lang["$language"]['40sec']?></TH>
					<TH><?php echo $lang["$language"]['50sec']?></TH>
					<TH><?php echo $lang["$language"]['60sec']?></TH>
					<TH><?php echo $lang["$language"]['61sec']?></TH>
                </TR>
				</THEAD>
				<TBODY>
<?php
foreach($abandon as $key=>$val){		  
$total_abandon10 += $abandon10["$key"];
$total_abandon20 += $abandon20["$key"];
$total_abandon30 += $abandon30["$key"];
$total_abandon40 += $abandon40["$key"];
$total_abandon50 += $abandon50["$key"];
$total_abandon60 += $abandon60["$key"];
$total_abandon61 += $abandon61["$key"];
echo "<TR><TD>".$key."</TD>
<TD>".$abandon10["$key"]."</TD>
<TD>".$abandon20["$key"]."</TD>
<TD>".$abandon30["$key"]."</TD>
<TD>".$abandon40["$key"]."</TD>
<TD>".$abandon50["$key"]."</TD>
<TD>".$abandon60["$key"]."</TD>
<TD>".$abandon61["$key"]."</TD>
</TR>\n";
}
echo "<TR><TD><b>".$lang["$language"]['ALLS']."<br/>".$total_abandon_calls."</b></TD>
<TD><b>".$total_abandon10."</b></TD>
<TD><b>".$total_abandon20."</b></TD>
<TD><b>".$total_abandon30."</b></TD>
<TD><b>".$total_abandon40."</b></TD>
<TD><b>".$total_abandon50."</b></TD>
<TD><b>".$total_abandon60."</b></TD>
<TD><b>".$total_abandon61."</b></TD>
</TR>";
?>
</TBODY>
</TABLE>
</TD>
<TD valign=top width="50%" align=center bgcolor='#ffffff'>
<script type="text/javascript">
google.charts.load('current', {packages: ['corechart', 'line']});
    google.charts.setOnLoadCallback(drawBasic);
    function drawBasic() {
    var data = new google.visualization.DataTable();
      data.addColumn('number', 'Period');
      data.addColumn('number', '');

      data.addRows([
     <?php
   echo "[0, 0],
         [10, ".$total_abandon10."],
         [20, ".$total_abandon20."],  
		 [30, ".$total_abandon30."],
		 [40, ".$total_abandon40."],
		 [50, ".$total_abandon50."],
		 [60, ".$total_abandon60."],
		 [61, ".$total_abandon61."]\n";
     ?>
      ]);
  var options = {
        title: <?php echo json_encode($lang["$language"]['unanswered_by_period_queue']); ?>,
        hAxis: {
          title: '<?php echo json_encode($lang["$language"]['period']); ?>'
        },
        vAxis: {
          title: '<?php echo json_encode($lang["$language"]['count']); ?>'
        }
      };
      var chart = new google.visualization.LineChart(document.getElementById('unanswered_by_period_queue'));
      chart.draw(data, options);
    }
</script>
            <div id="unanswered_by_period_queue"></div>
</TD></TR></THEAD></TABLE><br/>

</div>
</div>
<div id='footer'><a href='https://asterisk-pbx.ru'>Asterisk-pbx.ru</a> 2017</div>
</body>
</html>
