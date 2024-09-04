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
</head>
<?php
//if(isset($_POST['limit'])) {
//   $limit = $_POST['limit'];
//   $_SESSION['QSTATS']['limit']=$limit;
//} else {
//   $_SESSION['QSTATS']['limit'] = 100;
//}

if ( (isset($_POST['callerid_search'])) && (strlen($_POST['callerid_search']) > 0) ) {
    $callerid_search = $_POST['callerid_search'];
    $sql = "select distinct(callid) from $DBTable where time >= '$start' AND time <= '$end' and data2 like '%$callerid_search%'";
    $restemp = mysqli_query($connection, $sql);
        foreach($restemp as $temp){
		$callid_search .= ",'".$temp['callid']."'";
		}	
    $callid_search = substr($callid_search, 1);	
    $sql = "select time, callid, queuename, agent, event, data1, data2, data3 from $DBTable where time >= '$start' AND time <= '$end'
            and event in ('ABANDON','EXITWITHTIMEOUT','ENTERQUEUE') and callid in ($callid_search) order by callid";
    $resabandon = mysqli_query($connection, $sql);
} else {
    $sql = "select time, callid, queuename, agent, event, data1, data2, data3 from $DBTable
           where time >= '$start' AND time <= '$end' and queuename in ($queue)
           and event in ('ABANDON','EXITWITHTIMEOUT','ENTERQUEUE') order by callid, time limit 50000";
    $resabandon = mysqli_query($connection, $sql);
}
mysqli_close($connection);
$start_parts = explode(" ,:", $start);
$end_parts   = explode(" ,:", $end);     
?>

<body>
<?php include("menu.php"); ?>
<div id="main">
    <div id="contents">
		<TABLE width='90%' border=0 cellpadding=0 cellspacing=0>
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
<br />
<?php
$cover_pdf .= $lang["$language"]['queue'] . ": " . $queue . "\n";
$cover_pdf .= $lang["$language"]['start'] . ": " . $start_parts[0] . "\n";
$cover_pdf .= $lang["$language"]['end'] . ": " . $end_parts[0] . "\n";
$cover_pdf .= $lang["$language"]['period'] . ": " . $period . " " . $lang["$language"]['days'] . "\n";
?>
<div id="search" align="left">
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="frm1" name="frm1" method="post">
&nbsp;&nbsp;&nbsp;<b>CallerID:</b>&nbsp;<input type="text" id="callerid_search" name="callerid_search" />
&nbsp;&nbsp;&nbsp;<button type="submit" name="submit">Найти</button>
</form>
</div>			
<div id="rows" align="right">			
<!--form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="frm2" name="frm2" method="post">
	&nbsp;&nbsp;&nbsp;<b><?php echo $lang["$language"]['page_rows'];?></b>&nbsp;
<select onchange="this.form.submit()" id="pagerows" name="limit">
  <option selected="<?php echo $_SESSION['QSTATS']['limit'];?>"><?php echo $_SESSION['QSTATS']['limit'] / 2;?></option>
  <option value="100">50</option>
  <option value="200">100</option>
  <option value="1000">500</option>
  <option value="2000">1000</option>
</select>
</form-->
</div>
<br />				
<a name='user_abandon_calls'></a>
           <h3> <?php echo $lang["$language"]['user_abandon_calls']?></h3>
<br />		   
		   <table width='90%' cellpadding=1 cellspacing=1 border=0 class='sortable' id='user_abandon_calls' >
        <TR> 
			<TH><?php echo $lang["$language"]['time']?></TH>
			<TH><?php echo $lang["$language"]['callerid']?></TH>
			<TH><?php echo $lang["$language"]['queue']?></TH>										
			<TH><?php echo $lang["$language"]['event']?></TH>
			<TH><?php echo $lang["$language"]['holdtime']?></TH>
			<TH><?php echo $lang["$language"]['enterposition']?></TH>
			<TH><?php echo $lang["$language"]['hangupposition']?></TH>
			<TH><?php echo $lang["$language"]['callid']?></TH>
       </TR>
<?php
$header_pdf=array($lang["$language"]['time'],$lang["$language"]['callerid'], $lang["$language"]['queue'],$lang["$language"]['event'],$lang["$language"]['holdtime'],$lang["$language"]['enterposition'],$lang["$language"]['hangupposition']);
$width_pdf=array(40,32,23,23,23,25,25);
$title_pdf=$lang["$language"]['user_abandon_calls'];
$data_pdf = array();

foreach($resabandon as $row) {
if($row['event'] == "ENTERQUEUE") {
$callerid = $row['data2'];
} else {
$page_rows2 += count($row['event']); 
}
	    $hangupposition = $row['data1'];
	    $enterposition = $row['data2'];
        $holdtime = gmdate('i:s', $row['data3']);

	 $time = date('Y-m-d H:i:s', strtotime($row['time']));
        
if  (($row['event'] !== "ENTERQUEUE") ) {
	echo "<TR><TD>" . date('Y-m-d H:i:s', strtotime($row['time'])) . "</TD>
	      <TD>" . $callerid . "</TD>
	      <TD>" . $row['queuename'] . "</TD>	      
	      <TD>" . $row['event'] . "</TD>
	      <TD>" . $holdtime . "</TD>
		    <TD>" . $enterposition . "</TD>
		    <TD>" . $hangupposition . "</TD>
	      <TD>" . $row['callid'] . "</TD>
	      </TR>\n";
		    $linea_pdf = array($time,$callerid,$row['queuename'],$row['event'],$holdtime,$enterposition,$hangupposition);
        $data_pdf[]=$linea_pdf;
	}
 }
 
mysqli_free_result($resabandon); 
 
echo "<br />Всего найдено:".$page_rows2."<br />";
print_exports($header_pdf,$data_pdf,$width_pdf,$title_pdf,$cover_pdf);
 
echo "</table>";

 ?>
<?php 
print_exports($header_pdf,$data_pdf,$width_pdf,$title_pdf,$cover_pdf);
?>
	  <br/>	
     </div>
    </div>
   <div id="footer"><a href='https://asterisk-pbx.ru'>Asterisk-pbx.ru</a> 2017</div>
  </body>
 </html>
