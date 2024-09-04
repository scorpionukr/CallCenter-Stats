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
<html xmlns="http://www.w3.org/1999/xhtml">
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
<body>

<?php

$start_parts = explode(" ,:", $start);
$end_parts   = explode(" ,:", $end);

if ( isset($_POST['alltime']) ) {
$alltime = "";
} else {
$alltime = "time >= '$start' AND time <= '$end' and";
}

if ( (isset($_POST['callerid'])) && (strlen($_POST['callerid']) > 0) ) {
    $callerid_search = $_POST['callerid'];
    $sql = "select distinct(callid) from $DBTable where $alltime data2 like '%$callerid_search%'";
    $restemp = mysqli_query($connection, $sql);
        foreach($restemp as $temp){
		$callid_search .= ",'".$temp['callid']."'";
		}	
    $callid_search = substr($callid_search, 1);	
    $sql = "select *,
    CASE WHEN event like 'COMPLETE%' OR event like '%TRANSFER' THEN callid else 0 end as record_file
	from $DBTable where $alltime callid in ($callid_search) order by callid";
    $result = mysqli_query($connection, $sql);
}
 elseif ( (isset($_POST['uniqueid'])) && (strlen($_POST['uniqueid']) > 0) ) {
    $uniqueid = $_POST['uniqueid'];
    $sql = "select *,
    CASE WHEN event like 'COMPLETE%' OR event like '%TRANSFER' THEN callid else 0 end as record_file
	from $DBTable where $alltime callid = '$uniqueid' order by time";
    $result = mysqli_query($connection, $sql);
}

mysqli_close($connection);

?>

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

        </TR>
        </THEAD>
        </TABLE>
   		<br />
		<h4>Расшифровка значений событий очереди.</h4>
		<br />
	<table>
	<thead>
	<tr>
		<th class="col0">Событие</th><th class="col1">data1</th><th class="col2">data2</th><th class="col3">data3</th>
	</tr>
	</thead>
	<tr>
		<td class="col0">ENTERQUEUE</td><td class="col1"></td><td class="col2">callerid</td><td class="col3">Позиция входа</td>
	</tr>
	<tr>
		<td class="col0">CONNECT</td><td class="col1">Время ожидания</td><td class="col2">uniqueid</td><td class="col3"> </td>
	</tr>
	<tr>
		<td class="col0">COMPLETEAGENT, COMPLETECALLER</td><td class="col1">Время ожидания до ответа</td><td class="col2">Продолжительность вызова</td><td class="col3">Позиция при соединении</td>
	</tr>
	<tr>
		<td class="col0">RINGNOANSWER</td><td class="col1">Время вызова агента (Agent Timeout)</td><td class="col2"> </td><td class="col3"> </td>
	</tr>
    <tr>
		<td class="col0">ABANDON</td><td class="col1">Позиция выхода</td><td class="col2">Позиция входа</td><td class="col3">Время ожидания</td>
	</tr>
	<tr>
		<td class="col0">EXITWITHTIMEOUT</td><td class="col1">Позиция выхода</td><td class="col2">Позиция входа</td><td class="col3">Таймаут очереди</td>
	</tr>
</table>
		<br/>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="frm1" name="frm1" method="post">
    &nbsp;&nbsp;&nbsp;<input type="checkbox" name="ringnoanswer" value="RINGNOANSWER" checked>&nbsp;<?php echo $lang["$language"]['hidden_ringnoanswer'];?>
	&nbsp;&nbsp;&nbsp;<input type="checkbox" name="alltime" value="alltime">&nbsp;<?php echo "Искать за все время";?><br/><br/>
    &nbsp;&nbsp;&nbsp;<b>UniqueID</b>&nbsp;<input type="text" id="uniqueid" name="uniqueid" />
	&nbsp;&nbsp;&nbsp;<b>CallerID</b>&nbsp;<input type="text" id="callerid" name="callerid" />
    &nbsp;&nbsp;&nbsp;<button type="submit" name="submit"><?php echo $lang["$language"]['filter'] ?> </button>
</form>	
		<br/>		
		<a name='raw'></a>
			<TABLE width='99%' cellpadding=1 cellspacing=1 border=0 class='sortable' id='tableraw'>
				<THEAD>
				<br />		
                <TR> 
				   	<TH><?php echo $lang["$language"]['time']?></TH>
					<TH><?php echo $lang["$language"]['callid']?></TH>
					<TH><?php echo $lang["$language"]['queue']?></TH>
					<TH><?php echo $lang["$language"]['agent']?></TH>					
					<TH><?php echo $lang["$language"]['event']?></TH>
					<TH><?php echo "data1"?></TH>
					<TH><?php echo "data2"?></TH>
					<TH><?php echo "data3"?></TH>
					<TH><?php echo "data4"?></TH>
					<TH><?php echo "data5"?></TH>
					<TH><?php echo $lang["$language"]['recordfile']?></TH>
                </TR>
				</THEAD>
				<TBODY>
                <?php
$header_pdf=array($lang["$language"]['time'],$lang["$language"]['id'], $lang["$language"]['queue'],$lang["$language"]['agent'],$lang["$language"]['event'],"data1","data2","data3");
$width_pdf=array(25,23,23,23,23,25,25,20);
$title_pdf=$lang["$language"]['user_abandon_calls'];
$data_pdf = array();				
foreach($result as $row) {
if ($row['record_file'] == '0') {
    $row['record_file'] = $lang["$language"]['norecords'];
}
	$tmpError =  "";//$row['callid'];
	$tmpRec = '<audio controls preload="none">
	           <source src="dl.php?f=[_file]">
			   </audio>
			   ';
	
	$rec['filename'] = $row['record_file'] . '.mp3';	
	$rec['path'] = '/home/asterisk/monitor/mp3/'.$rec['filename'];
	
	if (file_exists($rec['path']) && preg_match('/(.*)\.mp3$/i', $rec['filename'])) {
		$tmpRes = str_replace('[_file]', base64_encode($rec['filename']), $tmpRec);
	}
	else { 
		$tmpRes = $tmpError; 
  }
if(isset($_POST['ringnoanswer'])) {
if( $row['event'] !== "RINGNOANSWER"){
	echo "<TR><TD>" . date('Y-m-d H:i:s', strtotime($row['time'])) . "</TD>
	      <TD>" . $row['callid'] . "</TD>
	      <TD>" . $row['queuename'] . "</TD>
	      <TD>" . $row['agent'] . "</TD>
	      <TD>" . $row['event'] . "</TD>
		  <TD>" . $row['data1'] . "</TD>
	      <TD>" . $row['data2'] . "</TD>
	      <TD>" . $row['data3'] . "</TD>
	      <TD>" . $row['data4'] . "</TD>
		  <TD>" . $row['data5'] . "</TD>
		  <TD>" . $tmpRes . "</TD>
	      </TR>\n";
	    $linea_pdf = array(date('Y-m-d H:i:s', strtotime($row['time'])),$row['callid'],$row['queuename'],$row['agent'],$row['event'],$row['data1'],$row['data2'],$row['data3'],$row['data4'],$row['data5']);
        $data_pdf[]=$linea_pdf;
}		
	} else {
		echo "<TR><TD>" . $row['time'] . "</TD>
	      <TD>" . $row['callid'] . "</TD>
	      <TD>" . $row['queuename'] . "</TD>
	      <TD>" . $row['agent'] . "</TD>
	      <TD>" . $row['event'] . "</TD>
		  <TD>" . $row['data1'] . "</TD>
	      <TD>" . $row['data2'] . "</TD>
	      <TD>" . $row['data3'] . "</TD>
	      <TD>" . $row['data4'] . "</TD>
		  <TD>" . $row['data5'] . "</TD>
		  <TD>" . $tmpRes . "</TD>
	      </TR>\n";
	    $linea_pdf = array(date('Y-m-d H:i:s', strtotime($row['time'])),$row['callid'],$row['queuename'],$row['agent'],$row['event'],$row['data1'],$row['data2'],$row['data3'],$row['data4'],$row['data5']);
        $data_pdf[]=$linea_pdf;	 
	} 
}	
mysqli_free_result($result);
print_exports($header_pdf,$data_pdf,$width_pdf,$title_pdf,$cover_pdf);
                ?>
			   </TBODY>
			  </TABLE>
<?php
print_exports($header_pdf,$data_pdf,$width_pdf,$title_pdf,$cover_pdf);
?>			  
			  </BR>
</div>
</div>
</div>
<div id='footer'><a href='https://asterisk-pbx.ru'>Asterisk-pbx.ru</a> 2017</div>
</body>
</html>
