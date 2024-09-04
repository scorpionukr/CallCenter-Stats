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

$start_parts = split(" ,:", $start);
$end_parts   = split(" ,:", $end);

if(isset($_POST['pagerows'])) {
   $page_rows = $_POST['pagerows'];
   $_SESSION['QSTATS']['pagerows']=$page_rows;
} else {
   $_SESSION['QSTATS']['pagerows'] = 1;
}   

if(isset($_POST['callerid'])) {
   $callerid = $_POST['callerid'];
   $_SESSION['QSTATS']['callerid']=$callerid;
} 

if(isset($_POST['uniqueid'])) {
   $uniqueid = $_POST['uniqueid'];
   $_SESSION['QSTATS']['uniqueid']=$uniqueid;
} 

if(isset($_POST['event'])) {
   $event = $_POST['event'];
   $_SESSION['QSTATS']['event']=$event;
} 

if(isset($_POST['ringnoanswer'])) {
   $ringnoanswer = $_POST['ringnoanswer'];
   $_SESSION['QSTATS']['ringnoanswer']=$ringnoanswer;
} else {
$ringnoanswer = 'FALSE';
}

$sql = "SELECT *,
CASE WHEN event like 'COMPLETE%' OR event like '%TRANSFER' THEN callid else 0 end as record_file
FROM $DBTable 
WHERE time >= '$start' AND time <= '$end'
and callid like '%$uniqueid%' and data2 like '%$callerid%' and event like '%$event%'
AND queuename IN ($queue) AND agent IN ($agent, 'NONE') AND event NOT IN ('$ringnoanswer')
LIMIT $page_rows";

$result = mysqli_query($connection, $sql);

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

        </TR>
        </THEAD>
        </TABLE>
		<br/>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="frm1" name="frm1" method="post">
    &nbsp;&nbsp;&nbsp;<input type="checkbox" name="ringnoanswer" value="RINGNOANSWER">&nbsp;<?php echo $lang["$language"]['hidden_ringnoanswer'];?><br/><br/>
    &nbsp;&nbsp;&nbsp;<b>UniqueID</b>&nbsp;<input type="text" id="uniqueid" name="uniqueid" />
	&nbsp;&nbsp;&nbsp;<b>CallerID</b>&nbsp;<input type="text" id="callerid" name="callerid" />
	&nbsp;&nbsp;&nbsp;<b><?php echo $lang["$language"]['event'];?></b>&nbsp;<select id="event" name="event"/>
  <option selected="<?php echo $_POST['event'];?>"><?php echo $_POST['event'];?></option>	
  <option value="%">--</option>	
  <option value="DID">did</option>
  <option value="ENTERQUEUE">enter</option>
  <option value="RINGNOANSWER">ringnoanswer</option>
  <option value="ABANDON">abandon</option>
  <option value="EXITWITHTIMEOUT">timeout</option>
  <option value="CONNECT">connect</option>
  <option value="COMPLETE">complete</option>
  <option value="COMPLETEAGENT">agent</option>
  <option value="COMPLETECALLER">caller</option>
  <option value="TRANSFER">transfer</option>
</select>
	&nbsp;&nbsp;&nbsp;<b><?php echo $lang["$language"]['page_rows'];?></b>&nbsp;<select id="pagerows" name="pagerows"/>
  <option selected="<?php echo $_SESSION['QSTATS']['pagerows'];?>"><?php echo $_SESSION['QSTATS']['pagerows'];?></option>
  <option value="1">1</option>
  <option value="500">500</option>
  <option value="1000">1000</option>
  <option value="10000">10000</option>
</select>
    &nbsp;&nbsp;&nbsp;<button type="submit" name="submit"><?php echo $lang["$language"]['filter'] ?> </button>
</form>	
		<br/>		
		<a name='raw'></a>
			<TABLE width='99%' cellpadding=1 cellspacing=1 border=0 class='sortable' id='tableraw'>
				<THEAD>
				<CAPTION><?php echo "Данные лога очереди за выбранный период";?></CAPTION>			
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
	$tmpError =  $row['callid'] ;
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
	    $linea_pdf = array(date('Y-m-d H:i:s', strtotime($row['time'])),$row['callid'],$row['queuename'],$row['event'],$row['agent'],$row['data1'],$row['data2'],$row['data3'],$row['data4'],$row['data5']);
        $data_pdf[]=$linea_pdf;	  

	} 	
$result->free();
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
