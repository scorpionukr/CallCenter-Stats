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

function utftowin($str){
    //$str = mb_convert_encoding($str, 'windows-1251', 'UTF-8');
    $str = iconv('UTF-8', 'windows-1251', $str);
    return $str;
}

//$esql = "SELECT * FROM queuelog WHERE queuename IN ($queue) AND agent IN ($agent, 'NONE') AND time >= '$start' AND time <= '$end'";
$esql = "SELECT time, agent, queuename, event, data3 FROM queuelog WHERE queuename IN ($queue) AND time >= '$start' AND time <= '$end' AND event IN ('CONNECT', 'ENTERQUEUE', 'COMPLETECALLER', 'COMPLETEAGENT', 'RINGNOANSWER')";

$evs = $connection->query($esql);

$queues = array();
$events = array();
$tmp = array();

$ags = explode(",", $agent);

while ($e = $evs->fetch_assoc()) {
	$events[] = $e;
}

$evs->free();
$connection->close();
   $j = 0;
 	foreach ($events as $k => $v) {
          $date_part = explode(" ", $v['time']);
          $d = $date_part[0];
          $hour_part = explode(":", $date_part[1]);
          $h = preg_replace("/(0)(\d)/", "$2", $hour_part[0]);
          //$queues["$d"]["$h"]['DATE'] = $d;
          //$queues["$d"]["$h"]['HOUR'] = $h;
			if ( $v['event'] === "CONNECT")  {
          $queues["$d"]['MEMBERS']["$h"] += 1;
  		}

      if ($v['event'] === "ENTERQUEUE") {
        $queues["$d"]['DEP']["$h"] = $v['data3'];
        if ($v['data3'] > $queues["$d"]['DEP']["$h"]) {
          $queues["$d"]['DEP']["$h"] = $v['data3'];
        }
      }
      if (($v['event'] === "COMPLETECALLER") || ($v['event'] === "COMPLETEAGENT") || ($v['event'] === "RINGNOANSWER") ) {

          $tmp["$d"]["$h"]['TMP'][$v['agent']] += 1;
      }
          $queues["$d"]['AGENTS']["$h"] = count($tmp["$d"]["$h"]['TMP']);
	  }

$cover_pdf .= $lang["$language"]['queue'] . ": " . $queue . "\n";

//$header_pdf = array(utftowin("Дата"),utftowin("Часы"),utftowin("Польз. в очереди"),utftowin("Макс.глубина"),utftowin("Агентов в очереди"));
//$header_pdf = array("Дата","Часы","Вызовов","Максимальное кол-во абонентов в очеред","Количество свободных операторов");
$header_pdf = array("Дата","Час","0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23");
$width_pdf = array(22,64,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8);
$title_pdf = "Отчет по очереди за период $start - $end";
$data_pdf = array();

foreach ($queues as $key => $val) {
  for ($i=0; $i <= 23 ; $i++) { 

    if(!isset($queues["$key"]['DEP']["$i"])) {
      $queues["$key"]['DEP']["$i"] = 0;
    }
      if(!isset($queues["$key"]['AGENTS']["$i"])){
      $queues["$key"]['AGENTS']["$i"] = 0;
    }
  }
     $linea_pdf = array($key,"Макс. кол-во абонентов в очереди",$queues["$key"]['DEP']["0"],$queues["$key"]['DEP']["1"],$queues["$key"]['DEP']["2"],$queues["$key"]['DEP']["3"],$queues["$key"]['DEP']["4"],$queues["$key"]['DEP']["5"],$queues["$key"]['DEP']["6"],$queues["$key"]['DEP']["7"],$queues["$key"]['DEP']["8"],$queues["$key"]['DEP']["9"],$queues["$key"]['DEP']["10"],$queues["$key"]['DEP']["11"],$queues["$key"]['DEP']["12"],$queues["$key"]['DEP']["13"],$queues["$key"]['DEP']["14"],$queues["$key"]['DEP']["15"],$queues["$key"]['DEP']["16"],$queues["$key"]['DEP']["17"],$queues["$key"]['DEP']["18"],$queues["$key"]['DEP']["19"],$queues["$key"]['DEP']["20"],$queues["$key"]['DEP']["21"],$queues["$key"]['DEP']["22"],$queues["$key"]['DEP']["23"]);

     $data_pdf[] = $linea_pdf;

     $linea_pdf2 = array($key,"Кол-во своб. операторов",$queues["$key"]['AGENTS']["0"],$queues["$key"]['AGENTS']["1"],$queues["$key"]['AGENTS']["2"],$queues["$key"]['AGENTS']["3"],$queues["$key"]['AGENTS']["4"],$queues["$key"]['AGENTS']["5"],$queues["$key"]['AGENTS']["6"],$queues["$key"]['AGENTS']["7"],$queues["$key"]['AGENTS']["8"],$queues["$key"]['AGENTS']["9"],$queues["$key"]['AGENTS']["10"],$queues["$key"]['AGENTS']["11"],$queues["$key"]['AGENTS']["12"],$queues["$key"]['AGENTS']["13"],$queues["$key"]['AGENTS']["14"],$queues["$key"]['AGENTS']["15"],$queues["$key"]['AGENTS']["16"],$queues["$key"]['AGENTS']["17"],$queues["$key"]['AGENTS']["18"],$queues["$key"]['AGENTS']["19"],$queues["$key"]['AGENTS']["20"],$queues["$key"]['AGENTS']["21"],$queues["$key"]['AGENTS']["22"],$queues["$key"]['AGENTS']["23"]);
     $data_pdf[] = $linea_pdf2;
}


ksort($queues);
$queues = json_encode($queues);
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
       var q = <?php echo $queues; ?>;
       q = JSON.stringify(q);
       q = JSON.parse(q);
       //console.log(q);
       $(function() {
           var theTemplateScript = $("#que-template").html();
           var theTemplate = Handlebars.compile(theTemplateScript);
           var context = { tbl: q };
           var theCompiledHtml = theTemplate(context);
           $(".que-placeholder").html(theCompiledHtml);
       });
      Handlebars.registerHelper("ifEmpty", function(c) {
             if (c == undefined) {
               return "0";
             } else {
               return c;
             }
          });
    </script>
<script id="que-template" type="text/x-handlebars-template">
      <div class="table table-list-search">
        <table class="table table-sm centered">
            <thead>
                <tr class="text-center" >
                    <th class="text-left">Дата</th>
                    <th>Час</th>
                    <th>00</th>
                    <th>01</th>
                    <th>02</th>
                    <th>03</th>
                    <th>04</th>
                    <th>05</th>
                    <th>06</th>
                    <th>07</th>
                    <th>08</th>
                    <th>09</th>
                    <th>10</th>
                    <th>11</th>
                    <th>12</th>
                    <th>13</th>
                    <th>14</th>
                    <th>15</th>
                    <th>16</th>
                    <th>17</th>
                    <th>18</th>
                    <th>19</th>
                    <th>20</th>
                    <th>21</th>
                    <th>22</th>
                    <th>23</th>
                </tr>
            </thead>
            <tbody>
                    {{#each tbl}}
                      <tr class="text-center">
                          <td rowspan="2" scope="rowgroup">{{@key}}</td>
                          <td>Максимальное кол-во абонентов в очереди</td>
                          <td>{{ifEmpty DEP.[0]}}</td>
                          <td>{{ifEmpty DEP.[1]}}</td>
                          <td>{{ifEmpty DEP.[2]}}</td>
                          <td>{{ifEmpty DEP.[3]}}</td>
                          <td>{{ifEmpty DEP.[4]}}</td>
                          <td>{{ifEmpty DEP.[5]}}</td>
                          <td>{{ifEmpty DEP.[6]}}</td>
                          <td>{{ifEmpty DEP.[7]}}</td>
                          <td>{{ifEmpty DEP.[8]}}</td>
                          <td>{{ifEmpty DEP.[9]}}</td>
                          <td>{{ifEmpty DEP.[10]}}</td>
                          <td>{{ifEmpty DEP.[11]}}</td>
                          <td>{{ifEmpty DEP.[12]}}</td>
                          <td>{{ifEmpty DEP.[13]}}</td>
                          <td>{{ifEmpty DEP.[14]}}</td>
                          <td>{{ifEmpty DEP.[15]}}</td>
                          <td>{{ifEmpty DEP.[16]}}</td>
                          <td>{{ifEmpty DEP.[17]}}</td>
                          <td>{{ifEmpty DEP.[18]}}</td>
                          <td>{{ifEmpty DEP.[19]}}</td>
                          <td>{{ifEmpty DEP.[20]}}</td>
                          <td>{{ifEmpty DEP.[21]}}</td>
                          <td>{{ifEmpty DEP.[22]}}</td>
                          <td>{{ifEmpty DEP.[23]}}</td>
                        </tr>
                        <tr class="text-center">
                          <td>Количество свободных операторов</td>
                          <td>{{ifEmpty AGENTS.[0]}}</td>
                          <td>{{ifEmpty AGENTS.[1]}}</td>
                          <td>{{ifEmpty AGENTS.[2]}}</td>
                          <td>{{ifEmpty AGENTS.[3]}}</td>
                          <td>{{ifEmpty AGENTS.[4]}}</td>
                          <td>{{ifEmpty AGENTS.[5]}}</td>
                          <td>{{ifEmpty AGENTS.[6]}}</td>
                          <td>{{ifEmpty AGENTS.[7]}}</td>
                          <td>{{ifEmpty AGENTS.[8]}}</td>
                          <td>{{ifEmpty AGENTS.[9]}}</td>
                          <td>{{ifEmpty AGENTS.[10]}}</td>
                          <td>{{ifEmpty AGENTS.[11]}}</td>
                          <td>{{ifEmpty AGENTS.[12]}}</td>
                          <td>{{ifEmpty AGENTS.[13]}}</td>
                          <td>{{ifEmpty AGENTS.[14]}}</td>
                          <td>{{ifEmpty AGENTS.[15]}}</td>
                          <td>{{ifEmpty AGENTS.[16]}}</td>
                          <td>{{ifEmpty AGENTS.[17]}}</td>
                          <td>{{ifEmpty AGENTS.[18]}}</td>
                          <td>{{ifEmpty AGENTS.[19]}}</td>
                          <td>{{ifEmpty AGENTS.[20]}}</td>
                          <td>{{ifEmpty AGENTS.[21]}}</td>
                          <td>{{ifEmpty AGENTS.[22]}}</td>
                          <td>{{ifEmpty AGENTS.[23]}}</td>
                        </tr>
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
               print_exports($header_pdf, $data_pdf, $width_pdf, $title_pdf, $cover_pdf);
              ?>
        </div>
        <div style="float: right;">
        <form action="#" method="get" class="">
            <input class="form-control" style="width: 240px; height: 20px;" id="system-search" name="q" placeholder="Фильтр" required>
        </form>
        </div>
        <br/><br/><br/>
          <div id="tr" class="que-placeholder"></div>
        <br/>
        <hr/>
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
