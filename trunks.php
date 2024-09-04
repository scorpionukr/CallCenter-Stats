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
$sql_ans = "select calldate, dst, channel, uniqueid, disposition, lastapp from cdr where dst in ($queue) and calldate >= '$start' and calldate <= '$end' and disposition in ('ANSWERED');";

$clls = $connection->query($sql_ans);

$trk = "select channelid from trunks;";

$trks = $confpbx->query($trk);

$trunks = array();
$calls = array();
$trunk = array();

$i = 0;
while ($t = $trks->fetch_array(MYSQLI_ASSOC)) {
	$trunks[] = $t;
}

while ($c = $clls->fetch_array(MYSQLI_ASSOC)) {
	$disp = $c['disposition'];
	$app = $c['lastapp'];
	$calls[] = $c;
}

foreach ($trunks as $val) {
	$tr = $val['channelid'];
	$p = "/.+$tr.+/";
	foreach ($calls as $k => $v) {
		if (preg_match($p, $v['channel'])) {
			if ($v['lastapp'] === 'Queue') {
				$trunk["$tr"]['ANS'] += 1;
				$trunk["Всего"]['ANS'] += 1;
			} else {
				$trunk["$tr"]['ABN'] += 1;
				$trunk["Всего"]['ABN'] += 1;
			}

			//$trunk["$tr"] += 1;
			$trunk["$tr"]["ALL"] += 1;
			$trunk["Всего"]["ALL"] += 1;
		}
	}
}
asort($trunk);
$trunk = json_encode($trunk);
// echo "<pre>";
// print_r($trunk);
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

       var trunks = <?php echo $trunk; ?>;
       trunks = JSON.stringify(trunks);
       trunks = JSON.parse(trunks);
       console.log(trunks);
       $(function() {
           var theTemplateScript = $("#trunks-template").html();
           var theTemplate = Handlebars.compile(theTemplateScript);
           var context = { over: trunks };
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
      <div><a href="dids.php">По номерам</a></div>
      <br/>
      <h2>По транкам (cdr)</h2>
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
