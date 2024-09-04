<?php
/*
Copyright 2019, https://asterisk-pbx.ru

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
$esql = "SELECT * FROM queuelog WHERE queuename IN ($queue) AND agent IN ($agent)  AND time >= '$start' AND time <= '$end'"; //AND data1 != 'Auto-Pause'

$evs = $connection->query($esql);

$agents = array();
$events = array();
$tmp = array();
// $qc = substr_count($queue, ',') + 1;
$qc = 2;
$ags = explode(",", $agent);

while ($e = $evs->fetch_assoc()) {
	$events[] = $e;
}

$evs->free();
$connection->close();



for ($i=0; $i < count($ags) ; $i++) {
    $linea_pdf = array();
    $a = trim($ags[$i],"'");
    $j=0;$m=0;$h = 0;$y = 0;
 	foreach ($events as $k => $v) {
 		if ($a === $v['agent']) {
          $date_part = explode(" ", $v['time']);
          $d = $date_part[0];
          $agents["$d"]["$a"]['DATE'] = $d;
          $agents["$d"]["$a"]['AGENT'] = $a;
	  
	  if ( ($v['event'] === "COMPLETEAGENT") || ($v['event'] === "COMPLETECALLER") ) {
          $tmp["$d"]["$a"]['INCALL'] += $v['data2'];
          $agents["$d"]["$a"]['COMPLETE'] += 1;
  		}

      if ( ($v['event'] === "ADDMEMBER") && ($v['callid'] === "MANAGER")) {
          $tmp["$d"]["$a"]['ADD'][$j] = strtotime($date_part[1]);
          ++$j;
      }
      if ( ($v['event'] === "REMOVEMEMBER") && ($v['callid'] === "MANAGER")) {
          $tmp["$d"]["$a"]['REM'][$m] = strtotime($date_part[1]);
          ++$m;
      }


      if ($v['event'] === "PAUSE") {
          $tmp["$d"]["$a"]['PA'] = strtotime($v['time']);
          ++$h;
      }

      if ( ($v['event'] === "UNPAUSE") && (strtotime($v['time']) > $tmp["$d"]["$a"]['PA'] )) {
          $tmp["$d"]["$a"]['PAUSE'] += strtotime($v['time']) - $tmp["$d"]["$a"]['PA'];
          ++$y;
      }

      if (($v['event'] === "BLINDTRANSFER") || ( $v['event'] === "ATTENDEDTRANSFER")) {
          $agents["$d"]["$a"]['TRANSFER_HCT'] += ($v['data3']/60) + ($v['data4']/60);
          //$agents["$d"]["$a"]['TRANSFER'] += 1;
      }
         $agents["$d"]["$a"]['TRANSFER_HCT'] = number_format($agents["$d"]["$a"]['TRANSFER_HCT'],0,".","");
         $agents["$d"]["$a"]['AVRG'] = number_format(($tmp["$d"]["$a"]['INCALL']) / $agents["$d"]["$a"]['COMPLETE'],2,".","");
      if ( ($v['event'] === "RINGNOANSWER") && ($v['data1'] > "1500")) {
          $agents["$d"]["$a"]['RNA'] += 1;
      }
	  }
  }
}


$cover_pdf .= $lang["$language"]['queue'] . ": " . $queue . "\n";
$cover_pdf .= $lang["$language"]['agent'] . ": " . $agent . "\n";

$header_pdf = array('Дата',	'Оператор','Разговор','Пауза','Свободен','На удержании','Вызовы','Средн.время','Переход');
$header_csv = array('Дата', 'Оператор','Время в статусе Разговаривает (мин)','Время в статусе Пауза (мин)','Время в статусе Свободен (мин)','Время в статусе На удержании (мин)','Количество вызовов (шт)','Среднее время разговора (сек)','Количество вызовов перешедших на другого оператора в случае не ответа (шт)');
//$header_pdf = array("Date",	"Agent","In call","Out Work","Free","In Xfer","Calls","Average","No Answer");
$width_pdf = array(32, 64, 23, 23, 23, 23, 23, 23, 23);
$title_pdf = "Отчет по агентам за период $start - $end";
$data_pdf = array();

foreach ($tmp as $key => $val) {
  foreach ($val as $k => $v) {
  	// for ($i=0; $i < count($tmp["$key"]["$k"]['UN']); $i++) {
  	// if( $i & 1) {
  	// 	if($tmp["$key"]["$k"]['UN'][$i] > $tmp["$key"]["$k"]['PA'][$i]) {
   //     $tmp["$key"]["$k"]['PAUSE'] += $tmp["$key"]["$k"]['UN'][$i] - $tmp["$key"]["$k"]['PA'][$i];
   //         }
   //      }
  	//  }
  	if( (count($tmp["$key"]["$k"]['REM']) > 1 ) && (count($tmp["$key"]["$k"]['ADD']) > 1 )) {
     $tmp["$key"]["$k"]['WORKTIME'] = array_pop($tmp["$key"]["$k"]['REM']) - array_shift($tmp["$key"]["$k"]['ADD']);
 } else {
 	$tmp["$key"]["$k"]['WORKTIME'] = 9 * 60 * 60;
 }

   if( $tmp["$key"]["$k"]['PAUSE'] < 86400 ){
     $tmp["$key"]["$k"]['PAUSE'] = $tmp["$key"]["$k"]['PAUSE'] / $qc;
     $tmp["$key"]["$k"]['FREE'] = $tmp["$key"]["$k"]['WORKTIME'] - ($tmp["$key"]["$k"]['INCALL'] + $tmp["$key"]["$k"]['PAUSE']);
     $agents["$key"]["$k"]['PAUSE'] = number_format($tmp["$key"]["$k"]['PAUSE']/60,0,".","");
   } else {
   	 $tmp["$key"]["$k"]['FREE'] = $tmp["$key"]["$k"]['WORKTIME'] - $tmp["$key"]["$k"]['INCALL'];
   	 $agents["$key"]["$k"]['PAUSE'] = 0;
   }
     $agents["$key"]["$k"]['INCALL'] = number_format($tmp["$key"]["$k"]['INCALL']/60,0,".","");
     $agents["$key"]["$k"]['FREE'] = number_format($tmp["$key"]["$k"]['FREE']/60,0,".","");
  }
}


foreach ($agents as $key => $val) {
     $agents["$key"]['CA'] = count($val);
  foreach ($val as $k => $v) {
     $linea_pdf = array($v['DATE'],$v['AGENT'],$v['INCALL'],$v['PAUSE'],$v['FREE'],$v['TRANSFER_HCT'],$v['COMPLETE'],$v['AVRG'],$v['RNA']);
     $data_pdf[] = $linea_pdf;
  }
}


ksort($agents);
$agents = json_encode($agents);
$tmp = json_encode($tmp);
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Asterisk Call Center Stats</title>
      <style type="text/css" media="screen">@import "css/basic.css";</style>
      <style type="text/css" media="screen">@import "css/tab.css";</style>
      <style type="text/css" media="screen">@import "css/table.css";</style>
      <style type="text/css" media="screen">@import "css/fixed-all.css";</style>
    <script src="js/1.10.2/jquery.min.js"></script>
    <script src="js/handlebars.js"></script>
    <script src="js/locale.js"></script>
    <script>
      var tmp = <?php echo $tmp; ?>;
       var agents = <?php echo $agents; ?>;
       agents = JSON.stringify(agents);
       agents = JSON.parse(agents);
       console.log(tmp);
       console.log(agents);
       $(function() {
           var theTemplateScript = $("#agents-template").html();
           var theTemplate = Handlebars.compile(theTemplateScript);
           var context = { tbl: agents };
           var theCompiledHtml = theTemplate(context);
           $(".agents-placeholder").html(theCompiledHtml);
       });     
          Handlebars.registerHelper("ifEmpty", function(c) {
             if (c == undefined) {
               return " ";
             } else {
               return c;
             }
          });
    </script>
<script id="agents-template" type="text/x-handlebars-template">
<!-- {{#each tbl}}
        {{@key}}
            {{#each this}}
                {{@key}} {{ this.INCALL }}
            {{/each}}
      {{/each}} -->
      <div class="table table-list-search">
        <table class="table table-sm centered" border="1">
            <thead>
                <tr class="text-center" >
                    <th class="text-left">Дата</th>
                    <th>Оператор</th>
                    <th>Время в статусе "Разговаривает", мин</th>
                    <!-- <th>Вне обслуж.(мин.)*</th> -->
                    <th>Время в статусе "Пауза", мин</th>
                    <th>Время в статусе "Свободен", мин *</th>
                    <th>Время в статусе "На удержании", мин</th>
                    <th>Количество вызовов, шт</th>
                    <th>Среднее время разговора, сек</th>
                    <th>Количество вызовов, <br/>перешедших на другого оператора в случае не ответа, шт</th>
                </tr>
            </thead>
            <tbody>
                    {{#each tbl}}
                <tr class="text-center">
                    <td rowspan="{{CA}}" scope="rowgroup">{{@key}}</td>
                    {{#each this}}
                    <td>{{ifEmpty this.AGENT}}</td>
                    <td>{{ifEmpty this.INCALL}}</td>
                    <!-- <td>{{ifEmpty this.OUTWORK}}</td> -->
                    <td>{{ifEmpty this.PAUSE}}</td>
                    <td>{{ifEmpty this.FREE}}</td>
                    <td>{{ifEmpty this.TRANSFER_HCT}}</td>
                    <td>{{ifEmpty this.COMPLETE}}</td>
                    <td>{{ifEmpty this.AVRG}}</td>
                    <td>{{ifEmpty this.RNA}}</td>
                </tr>
                    {{/each}}
                    {{/each}}
            </tbody>
        </table>
    </div>
</script>
</head>
<html>
<body>
<?php include "menu.php";?>
<div id="main">
    <div id="contents">
      <h1>Статистика по операторам за период: <?php echo $start . " - " . $end ?></h1>
      <br/>
        <div style="float: left;">
              <?php
               print_exports($header_pdf, $data_pdf, $width_pdf, $title_pdf, $cover_pdf, $header_csv);
              ?>
        </div>
        <div style="float: right;">
        <form action="#" method="get" class="">
            <input class="form-control" style="width: 240px; height: 20px;" id="system-search" name="q" placeholder="Фильтр" required>
        </form>
        </div>
        <br/><br/><br/>
          <div id="tr" class="agents-placeholder"></div>
        <br/>
        <hr/>
        <div>
          <p>
            <!-- * - Если "-", то события "ADDMEMBER" и "REMOVEMEMBER" отсутствуют.<br/> -->
  <!--           Получить корректные данные на основании событий "PAUSE" и "UNPAUSE" не получается, т.к. данные события зависят от действий пользователей и сильно фрагментированы в представленной БД.  -->
        </p>
      </div>
    </div>
</div>
<script>
$(document).ready(function() {
    var activeSystemClass = $('.list-group-item.active');
    $('#system-search').keyup(function() {
        var that = this;
        var tableBody = $('.table-list-search tbody');
        var tableRowsClass = $('.table-list-search tbody tr');
        $('.search-sf').remove();
        tableRowsClass.each(function(i, val) {
            var rowText = $(val).text().toLowerCase();
            var inputText = $(that).val().toLowerCase();
            if (inputText != '') {
                $('.search-query-sf').remove();
                tableBody.prepend('<tr class="search-query-sf"><td colspan="6"><strong>поиск по: "' +
                    $(that).val() +
                    '"</strong></td></tr>');
            } else {
                $('.search-query-sf').remove();
            }

            if (rowText.indexOf(inputText) == -1) {
                tableRowsClass.eq(i).hide();

            } else {
                $('.search-sf').remove();
                tableRowsClass.eq(i).show();
            }
        });
        if (tableRowsClass.children(':visible').length == 0) {
            tableBody.append('<tr class="search-sf"><td class="text-muted" colspan="6">Не найдено.</td></tr>');
        }
    });
});
</script>
<div id='footer'><a href='https://asterisk-pbx.ru'>Asterisk-pbx.ru</a> 2019</div>
</body>
</html>
