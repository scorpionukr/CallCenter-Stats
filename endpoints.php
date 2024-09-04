<?php
include "config.php";
?>
<!DOCTYPE html>

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Asterisk Call Center Stats</title>
    <style type="text/css" media="screen">@import "css/basic.css";</style>
    <style type="text/css" media="screen">@import "css/tab.css";</style>
    <style type="text/css" media="screen">@import "css/table.css";</style>
    <style type="text/css" media="screen">@import "css/fixed-all.css";</style>
    <style>
      pre {
  display: block;
  font-family: monospace;
  white-space: pre;
  margin: 1em 0;
  background: black;
  color: limegreen;
}
    </style>
    <script type="text/javascript" src="js/1.10.2/jquery.min.js"></script>
    <script src="js/handlebars.js"></script>
    <script src="js/locale.js"></script>
    <script>


    function guid() {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }
        return s4() + s4();
    }



    $(function getInboundRegs() {
        $(function() {
            $.ajax({
                type: 'POST',
                url: 'ajam.php',
                data: 'Action=PJSIPShowRegistrationInboundContactStatuses&ActionId=' + guid(),
                success: function(data) {
                    var inbounds = JSON.parse(data);
                    //$('#endpoints').html(data);
                    var theTemplateScript = $("#inbound-template").html();
                    var theTemplate = Handlebars.compile(theTemplateScript);
                    var context = { Inbound: inbounds };
                    var theCompiledHtml = theTemplate(context);
                    $('.inbound-placeholder').html(theCompiledHtml);
                }
            });
        });
        setTimeout(getInboundRegs, 59999);
    });


    Handlebars.registerHelper("endStatus", function(status) {
        var s = status;
        switch (s) {
            case 'Reachable':
                var e = "<span style='color: green;'>Зарегистрирован</span>";
                break;
            case 'Unreachable':
                var e = "<span style='color: firebrick;'>Недоступен</span>";

        }
        return new Handlebars.SafeString(e);
    });

    function ifLen(v) {
        if (v < 10) {
            return '0' + v;
        } else {
            return v;
        }
    }


        $(function getOutboundRegs() {
        $(function() {
            $.ajax({
                type: 'POST',
                url: 'ajam.php',
                data: 'CliCom=pjsip show registrations',
                success: function(data) {
                    $('#outbound').html(data);
                    // var outbounds = JSON.parse(data);
                    // var theTemplateScript = $("#outbound-template").html();
                    // var theTemplate = Handlebars.compile(theTemplateScript);
                    // var context = { Outbound: outbounds };
                    // var theCompiledHtml = theTemplate(context);
                    // $('.inbound-placeholder').html(theCompiledHtml);
                }
            });
        });
        setTimeout(getOutboundRegs, 60003);
    });

Handlebars.registerHelper("prettyDate", function (timestamp) {
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
  var time = date + ' ' + month + ' ' + hour + ':' + min + ':' + sec ;
  return time;
});
    </script>

    <script id="inbound-template" type="text/x-handlebars-template">
        <h3>Входящие регистрации</h3>
        <br/>
        <div class="table">
            <table width='99%' cellpadding=3 cellspacing=3 border=0 class="table table-striped">
                <thead>
                    <tr>
                        <th>Номер</th>
                        <th>Статус</th>
                        <th>Устройство</th>
                        <th>IP Адрес</th>
                        <th>Рег. истекает</th>
                    </tr>
                </thead>
                <tbody>
                    {{#each Inbound}}
                    <tr>
                        <td>{{EndpointName}}</td>
                        <td>{{endStatus Status}}</td>
                        <td>{{UserAgent}}</td>
                        <td>{{ViaAddress}}</td>
                        <td>{{prettyDate RegExpire}}</td>
                    </tr>
                    {{/each}}
                </tbody>
            </table>
        </div>
        <br />
    </script>
</head>

<body>
<?php include "menu.php";?>
    <div id="main">
        <div id="contents">
        <br /><br />
        <h3>Исходящие регистрации</h3>
        <pre id="outbound"></pre>
        <br /><br />
        <div class="inbound-placeholder"></div>
        <br />
    </div>
    </div>
</body>
</html>
