<?php
/*
Copyright 2018, https://asterisk-pbx.ru

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
//query mixed from queuelog and cdr (queuelog table must be in cdr databases)
$sql = "select queuelog.time, queuelog.callid, queuelog.agent, queuelog.data1, queuelog.event, cdr.billsec, cdr.disposition, cdr.src, cdr.dst from queuelog_bk, cdr where queuelog.time >= '$start' AND queuelog.time <= '$end' AND queuelog.callid = cdr.uniqueid and queuelog.event = 'REC' and queuelog.data1 like 'out%' and queuelog.agent in ($agent);";

$res = $connection->query($sql);

$out = array();
$rec = array();
while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
	$row['data1'] = getRec($row['data1'], $row['time']);
	$out[] = $row;

}

$header_pdf = array("Дата", "Агент", "Номер", "Назнач.", "Продолж.");
$width_pdf = array(25, 25, 25, 25, 25);
$title_pdf = "Исходящие вызовы";
$data_pdf = array();
foreach ($out as $k => $r) {
	$time = strtotime($r['time']);
	$time = date('Y-m-d H:i:s', $time);
	$min = seconds2minutes($r['billsec']);
	$linea_pdf = array($time, $r['agent'], $r['src'], $r['dst'], $min);
	$data_pdf[] = $linea_pdf;
}

$out = json_encode($out);

$connection->close();

function getRec($recfile, $time) {
	$time = strtotime($time);
	$tmpRec = '<audio controls preload="none">
             <source src="dl.php?f=[_file]">
         </audio>
         <a href="dl.php?f=[_file]"></a>';

	$rec['filename'] = $recfile . '.wav';
	$rec['path'] = '/var/spool/asterisk/monitor/' . date('Y/m/d/', $time) . $rec['filename'];
	if (file_exists($rec['path']) && preg_match('/(.*)\.wav$/i', $rec['filename'])) {
		$tmpRes = str_replace('[_file]', base64_encode($rec['path']), $tmpRec);
	} else {
		$tmpRes = $_REQUEST['recfile'];
	}

	return $tmpRes;
}

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
      let outs = <?php echo $out; ?>;
 function outOverData(arr) {
     let eve = {};
     let res = {};

     arr.map(v => [v.agent, v.event]).map(v => eve[v] = (eve[v] || 0) + 1);
     arr.map(v => [v.agent, v.disposition]).map(v => eve[v] = (eve[v] || 0) + 1);

     Object.keys(eve).map(v => v.split(",")).map((v, i) => {
         return [v[0], v[1], Object.values(eve)[i]];
     }).map(v => {
         if (v[0] in res) {
             let agent = res[v[0]];
             let event = {
                 [v[1]]: v[2] };
             res[v[0]] = { ...agent, ...event };
         } else {
             let agent = { "agent": v[0] };
             let event = {
                 [v[1]]: v[2] || 0 };
             res[v[0]] = { ...agent, ...event };
         }
     });

     return res;
 }

   var over_out = outOverData(outs);
   over_out = JSON.stringify(over_out);
   over_out = over_out.replace(/NO\sANSWER/g, "NO_ANSWER");
   over_out = JSON.parse(over_out);

$(function() {
    var theTemplateScript = $("#overs-template").html();
    var theTemplate = Handlebars.compile(theTemplateScript);
    var context = { over: over_out };
    var theCompiledHtml = theTemplate(context);
    $(".overs-placeholder").html(theCompiledHtml);
});

$(function() {
    var theTemplateScript = $("#out-template").html();
    var theTemplate = Handlebars.compile(theTemplateScript);
    var context = { out: outs };
    var theCompiledHtml = theTemplate(context);
    $('.out-placeholder').html(theCompiledHtml);
});

Handlebars.registerHelper("prettyDate", function (timestamp) {
  //var a = Date.parse(timestamp);
  var a = new Date(timestamp * 1000);
  if (navigator.language == 'ru') {
     var months = ['Янв','Фев','Мар','Апр','Май','Июня','Июля','Авг','Сен','Окт','Ноя','Дек'];
   } else {
       var months = ['Jan','Feb','Mar','Apr','May', 'Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
   }
  var year = a.getFullYear();
  var month = months[a.getMonth()];
  var date = a.getDate();
  //var hour = a.getHours();
  var hour = (a.getHours() < 10 ? '0' : '') + a.getHours();
  //var min = a.getMinutes();
  var min = (a.getMinutes() < 10 ? '0' : '') + a.getMinutes();
  //var sec = a.getSeconds();
  var sec = (a.getSeconds() < 10 ? '0' : '') + a.getSeconds();
  if( a < 3600000 )
  var time = min + ':' + sec ;
  else
  var time = date + ' ' + month + ' ' + hour + ':' + min + ':' + sec ;

  return time;
});

Handlebars.registerHelper("dataNorm", function (d) {
      if (d == undefined)
        return "0";
      else
        return d;

  });

Handlebars.registerHelper("getStatus", function (s) {
      switch (s) {
  case 'ANSWERED':
    status = '<span style="color: green">Ответ</span>';
    break;
  case 'NO ANSWER':
    status = '<span style="color: grey">Нет ответа</span>';
    break;
  case 'BUSY':
    status = '<span style="color: firebrick">Занято</span>';
    break;
  }
   return status;

  });
    </script>

<script id="overs-template" type="text/x-handlebars-template">
    <h2>Обзор</h2>
        <div class="table">
            <table class="table table-sm table-striped">
                <thead>
                    <tr class="text-center">
                        <th class="text-left">Event</th>
                        <th>Отв.</th>
                        <th>Не отв.</th>
                        <th>Занято</th>
                        <th>Всего</th>
                    </tr>
                </thead>
                <tbody>
                        {{#each over}}
                    <tr class="text-center">
                        <td class="text-left">{{@key}}</td>
                        <td>{{dataNorm this.ANSWERED}}</td>
                        <td>{{dataNorm this.NO_ANSWER}}</td>
                        <td>{{dataNorm this.BUSY}}</td>
                        <td>{{dataNorm this.REC}}</td>
                    </tr>
                        {{/each}}
                </tbody>
            </table>
        </div>
    </script>

<script id="over-template" type="text/x-handlebars-template">
<h3>Agent Events</h3>
    <div class="table">
        <table class="table table-sm table-striped">
            <thead>
                <tr class="text-center" >
                    <th class="text-left">Агент</th>
                    <th>Отвечено</th>
                    <th>Не Отвечено</th>
                    <th>Занято</th>
                    <th>Всего</th>
                </tr>
            </thead>
            <tbody>
                    {{#each over}}
                <tr class="text-center">
                    <td class="text-left"><b>{{this.agent}}</b></td>
                    <td>{{this.ANSWERED}}</td>
                    <td>{{this.NO ANSWER}}</td>
                    <td>{{this.BUSY}}</td>
                    <td>{{this.REC}}</td>
                </tr>
                    {{/each}}
            </tbody>
        </table>
    </div>
</script>
<script id="out-template" type="text/x-handlebars-template">
    <div class="table table-list-search">
        <table id="cdr" class="table table-striped">
            <thead>
                <tr>
                    <th>дата</th>
                    <th>агент</th>
                    <th>номер</th>
                    <th>набр.</th>
                    <th>прод.</th>
                    <th>статус</th>
                    <th>зап.</th>
                </tr>
            </thead>
            <tbody>
                {{#each out}}
                <tr>
                    <td>{{prettyDate callid}}</td>
                    <td>{{agent}}</td>
                    <td>{{src}}</td>
                    <td>{{dst}}</td>
                    <td>{{prettyDate billsec}}</td>
                    <td>{{{getStatus disposition}}}</td>
                    <td>{{{data1}}}</td>
                </tr>
                {{/each}}
            </tbody>
        </table>
</script>
</head>
<html>
<body>
<?php include "menu.php";?>
<div id="main">
    <div id="contents">
      <h1>Исходящие вызовы: <?php echo $start . " - " . $end ?></h1>
      <br/>
      <div class="overs-placeholder"></div>
      <br/>
      <h2>Детализация</h2>
      <br/>
        <div style="float: right;">
        <form action="#" method="get" class="">
            <input class="form-control" style="width: 240px; height: 20px;" id="system-search" name="q" placeholder="Фильтр" required>
        </form>
        </div>
<?php
print_exports($header_pdf, $data_pdf, $width_pdf, $title_pdf, $cover_pdf);
?>
        <br/>
        <hr/>
      <div class="out-placeholder"></div>

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
<div id='footer'><a href='https://asterisk-pbx.ru'>Asterisk-pbx.ru</a> 2018</div>
</body>
</html>
