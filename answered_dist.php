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
</head>
<?php

$sql = "SELECT queuename, agent, event, data1, data2 FROM $DBTable WHERE time >= '$start' AND time <= '$end' AND event IN ('COMPLETECALLER', 'COMPLETEAGENT') AND queuename IN ($queue) AND agent IN ($agent)";
$res = mysqli_query($connection, $sql);
mysqli_close($connection);
$start_parts = explode(" ,:", $start);
$end_parts   = explode(" ,:", $end);   
?>
<body>
<?php include("menu.php"); ?>
<div id="main">
    <div id="contents">
	        <table width='100%' border=0 cellpadding=0 cellspacing=0>
                <caption><?php echo $lang["$language"]['report_info']?></caption>
                <tbody>
                <tr>
                    <td><?php echo $lang["$language"]['queue']?>:</td>
                    <td><?php echo $queue?></td>
                </tr>
                </tr>
                    <td><?php echo $lang["$language"]['start']?>:</td>
                    <td><?php echo $start_parts[0]?></td>
                </tr>
                </tr>
                <tr>
                    <td><?php echo $lang["$language"]['end']?>:</td>
                    <td><?php echo $end_parts[0]?></td>
                </tr>
                <tr>
                    <td><?php echo $lang["$language"]['period']?>:</td>
                    <td><?php echo $period?> <?php echo $lang["$language"]['days']?></td>
                </tr>
                </tbody>
                </table>
            <a name='resp'></a>
            <table width='99%' cellpadding=3 cellspacing=3 border=0 >
            <caption>
            <a href='#0'><img src='images/go-up.png' border=0 class='icon' width=16 height=16 
            <?php 
            tooltip($lang["$language"]['gotop'],200);
            ?>
            ></a>&nbsp;&nbsp;
            <?php echo $lang["$language"]['call_response']?></caption>
            <thead>
            <tr>

                <td valign=top width="50%" align=center  bgcolor='#ffffff'>
				 <table width='99%' cellpadding=1 cellspacing=1 border=0 class='sortable' id='tableresp'>
				<thead>
                <tr> 
				   	<TH><?php echo $lang["$language"]['queue']?></th>
					<TH><?php echo $lang["$language"]['15sec']?></th>
					<TH><?php echo $lang["$language"]['_30sec']?></th>
					<TH><?php echo $lang["$language"]['45sec']?></th>
					<TH><?php echo $lang["$language"]['_60sec']?></th>
					<TH><?php echo $lang["$language"]['75sec']?></th>
					<TH><?php echo $lang["$language"]['90sec']?></th>
					<TH><?php echo $lang["$language"]['91sec']?></th>
					<TH><?php echo $lang["$language"]['ALLS']?></th>
                </tr>
				</thead>
				<tbody>
<?php
while($row=mysqli_fetch_row($res)) {
$hold["$row[0]"][]=$row[3];
if ($row[3]<=15) {
 $hold15["$row[0]"] += count($row[3]);
 } elseif (($row[3] >= 16)&&($row[3] <= 30)) {
 $hold30["$row[0]"] += count($row[3]);
 } elseif (($row[3] >= 31)&&($row[3] <= 45)) {
 $hold45["$row[0]"] += count($row[3]);
 } elseif (($row[3] >= 46)&&($row[3] <= 60)) {
 $hold60["$row[0]"] += count($row[3]);
 } elseif (($row[3] >= 61)&&($row[3] <= 75)) {
 $hold75["$row[0]"] += count($row[3]);
 } elseif (($row[3] >= 76)&&($row[3] <= 90)) {
 $hold90["$row[0]"] += count($row[3]);
 } elseif ($row[3] >= 91) {
 $hold91["$row[0]"] += count($row[3]);
 }
$dur["$row[0]"][]=$row[4];
if ($row[4]<=5) {
 $dur5["$row[0]"] += count($row[4]);
 } elseif (($row[4] >= 6)&&($row[4] <= 10)) {
 $dur10["$row[0]"] += count($row[4]);
 } elseif (($row[4] >= 11)&&($row[4] <= 15)) {
 $dur15["$row[0]"] += count($row[4]);
 } elseif (($row[4] >= 16)&&($row[4] <= 20)) {
 $dur20["$row[0]"] += count($row[4]);
 } elseif (($row[4] >= 21)&&($row[4] <= 25)) {
 $dur25["$row[0]"] += count($row[4]);
 } 
} 
foreach ($hold as $key=>$row) {
     $total = count($hold["$key"]);	 
  	echo "<tr><td>".$key."</td>
	<td>".$hold15["$key"]."</td>
	<td>".$hold30["$key"]."</td>
	<td>".$hold45["$key"]."</td>
	<td>".$hold60["$key"]."</td>
	<td>".$hold75["$key"]."</td>
	<td>".$hold90["$key"]."</td>
	<td>".$hold91["$key"]."</td>
	<td>".$total."</td></tr>\n";
}

$perans15 = 0;$perans30 = 0;$perans45 = 0;$perans60 = 0;$perans75 = 0;$perans90 = 0;$perans91 = 0;
foreach($res as $row){
 if ( ($row['data1'] >= 0) && ($row['data1'] <= 15) ){
  $perans15 += count($row['data1']);
   } elseif (($row['data1'] >= 16) && ($row['data1'] <= 30)) {
  $perans30 += count($row['data1']); 
   } elseif (($row['data1'] >= 31) && ($row['data1'] <= 45)) {
  $perans45 += count($row['data1']); 
   } elseif (($row['data1'] >= 46) && ($row['data1'] <= 60)) {
  $perans60 += count($row['data1']);
   } elseif (($row['data1'] >= 61) && ($row['data1'] <= 75)) {
  $perans75 += count($row['data1']);
   } elseif (($row['data1'] >= 76) && ($row['data1'] <= 90)) {
  $perans90 += count($row['data1']); 
   } elseif ($row['data1'] >= 91) {
  $perans91 += count($row['data1']); 
  }
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

echo "[0, 0],[15, ".$perans15."],[30, ".$perans30."],[45, ".$perans45."],[60, ".$perans60."],[75, ".$perans75."],[90, ".$perans90."],[91, ".$perans91."],\n";
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
            tooltip($lang["$language"]['gotop'],200);
            ?>
            ></a>&nbsp;&nbsp;
            <?php echo $lang["$language"]['dur_by_period']?></caption>
            <thead>
            <tr>

                <td valign=top width="50%" align=center  bgcolor='#ffffff'>
				<table width='99%' cellpadding=1 cellspacing=1 border=0 class='sortable' id='tabledurper'>
				 <thead>
                  <tr> 
				   	<TH><?php echo $lang["$language"]['queue']?></th>
					<TH><?php echo $lang["$language"]['0-5sec']?></th>
					<TH><?php echo $lang["$language"]['6-10sec']?></th>
					<TH><?php echo $lang["$language"]['11-15sec']?></th>
					<TH><?php echo $lang["$language"]['16-20sec']?></th>
					<TH><?php echo $lang["$language"]['21-25sec']?></th>
					<TH><?php echo $lang["$language"]['26sec']?></th>
                 </tr>
				</thead>
				<tbody>
                <?php
$perdur5 = 0;$perdur10 = 0;$perdur15 = 0;$perdur20 = 0;$perdur25 = 0;
foreach($res as $row){
 if ( ($row['data2'] >= 0) && ($row['data2'] <= 5) ){
  $perdur5 += count($row['data2']);
   } elseif (($row['data2'] >= 6) && ($row['data2'] <= 10)) {
  $perdur10 += count($row['data2']); 
   } elseif (($row['data2'] >= 11) && ($row['data2'] <= 15)) {
  $perdur15 += count($row['data2']); 
   } elseif (($row['data2'] >= 16) && ($row['data2'] <= 20)) {
  $perdur20 += count($row['data2']);
   } elseif (($row['data2'] >= 11) && ($row['data2'] <= 25)) { 
  $perdur25 += count($row['data2']);
  } 
} 
foreach ($dur as $key=>$row) {
     $total2 = count($dur["$key"]);	
     $total25 = $dur5["$key"] + $dur10["$key"] + $dur15["$key"] + $dur20["$key"] + $dur25["$key"];
	 $total26 = $total2 - $total25;
//   $percent = number_format($total25 * 100 / $totall,2);
	echo "<tr><td>".$key."</td>
	<td>".$dur5["$key"]."</td>
	<td>".$dur10["$key"]."</td>
	<td>".$dur15["$key"]."</td>
	<td>".$dur20["$key"]."</td>
	<td>".$dur25["$key"]."</td>
	<td>".$total26."</td>
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
   echo "[0, 0],[5, ".$perdur5."],[10, ".$perdur10."],[15, ".$perdur15."],[20, ".$perdur20."],[25, ".$perdur25."],\n";
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
            tooltip($lang["$language"]['gotop'],200);
            ?>
            ></a>&nbsp;&nbsp;
            <?php echo $lang["$language"]['disconnect_cause']?></caption>
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
foreach($res as $row){
    if	($row['event'] == "COMPLETEAGENT") {
         $action = $lang["$language"]['agent_hungup'] ;
		 $num += count($row['event']);
    } elseif ($row['event'] == "COMPLETECALLER") {
         $action2=$lang["$language"]['caller_hungup'];
		 $num2 += count($row['event']);
     }	 
    }
echo "['".$action."', ".$num."],['".$action2."', ".$num2."]\n";	
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
