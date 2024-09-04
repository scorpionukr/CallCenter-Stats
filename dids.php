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
$event_query = "select callid, event, data1 from queuelog where time >= '$start' and time <= '$end' and event in ('COMPLETEAGENT', 'COMPLETECALLER', 'DID', 'ABANDON') order by callid DESC;";

$event_res = $connection->query($event_query);

$did_query = "select distinct(data1) from queuelog where event = 'DID';";

$did_res = $connection->query($did_query);

// $dids = array();
$events = array();
// $did = array();
// $eve = array();

$i = 0;
while ($d = $did_res->fetch_array(MYSQLI_ASSOC)) {
	if ($d['data1'] != "") {
		$dids[] = $d;
	}
}

while ($e = $event_res->fetch_array(MYSQLI_ASSOC)) {
	$ev = $e['event'];
	$cd = $e['callid'];
	$events["$cd"]["$ev"][] = $e;
}

foreach ($dids as $val) {
	$dd = $val['data1'];
	$p = "/$dd/";
	foreach ($events as $k => $v) {
		$c = $events["$k"]['DID'][0]['data1'];
		//$e[] = $events["$k"]["$v"];
		if (preg_match($p, $c)) {
			if ($events["$k"]['ABANDON']) {
				$cnt["$dd"]['ABN'] += 1;
				$cnt["Всего"]['ABN'] += 1;
			} else if (($events["$k"]['COMPLETECALLER']) || ($events["$k"]['COMPLETEAGENT'])) {
				$cnt["$dd"]['ANS'] += 1;
				$cnt["Всего"]['ANS'] += 1;
			}
			$cnt["$dd"]['ALL'] += 1;
			$cnt["Всего"]['ALL'] += 1;
		}
	}
}
asort($cnt);
$cnt = json_encode($cnt);
// echo "<pre>";
// print_r($cnt);
// echo "</pre>";
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

       var cnts = <?php echo $cnt; ?>;
       cnts = JSON.stringify(cnts);
       cnts = JSON.parse(cnts);
       console.log(cnts);
       $(function() {
           var theTemplateScript = $("#trunks-template").html();
           var theTemplate = Handlebars.compile(theTemplateScript);
           var context = { over: cnts };
           var theCompiledHtml = theTemplate(context);
           $(".trunks-placeholder").html(theCompiledHtml);
       });
        Handlebars.registerHelper("ifEmpty", function(c) {
           if (c == undefined) {
             return "-";
           } else {
             return c;
           }
        });
          Handlebars.registerHelper("ifAll", function(b) {
             if (b == "Всего") {
               return "text-danger";
             } else {
               return "";
             }
          });
    </script>

<script id="trunks-template" type="text/x-handlebars-template">
    <div class="table table-list-search">
        <table class="table table-sm table-striped centered">
            <thead>
                <tr class="text-center" >
                    <th class="text-left">Номер</th>
                    <th>Пропущено</th>
                    <th>Отвечено</th>
                    <th>Всего</th>
                </tr>
            </thead>
            <tbody>
                    {{#each over}}
                <tr class="text-center {{ifAll @key}}">
                    <td class="text-left {{ifAll @key}}"><b>{{@key}}</b></td>
                    <td class="{{ifAll @key}}">{{ifEmpty this.ABN}}</td>
                    <td class="{{ifAll @key}}">{{ifEmpty this.ANS}}</td>
                    <td class="{{ifAll @key}}"><b>{{ifEmpty this.ALL}}</b></td>
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
      <h1>Входящие вызовы: <?php echo $start . " - " . $end ?></h1>
      <br/>
       <div><a href="trunks.php">По транкам</a></div>
      <br/>
      <h2>По номерам (queuelog)</h2>
      <br/>
        <div style="float: right;">
        <form action="#" method="get" class="">
            <input class="form-control" style="width: 240px; height: 20px;" id="system-search" name="q" placeholder="Фильтр" required>
        </form>
        </div>
        <br/><br/>
          <div id="tr" class="trunks-placeholder"></div>
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
