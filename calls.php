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


    Handlebars.registerHelper("inState", function(status) {
        var s = status;
        switch (s) {
            case 'Up':
                var mStatus = '<span class="text-success">Разговаривает</span>';
                break;
            case 'Ringing':
                var mStatus = "<span class='text-danger'>Вызывается</span>";
                break;
            case 'Down':
                var mStatus = "<span class='text-info'>Отбой</span>";
                break;
        }
        return new Handlebars.SafeString(mStatus);
    });



    function ifLen(v) {
        if (v < 10) {
            return '0' + v;
        } else {
            return v;
        }
    }


    Handlebars.registerHelper("normDate", function(d) {
            var date = new Date(d * 1000);
            var hour = date.getHours();
            var minute = date.getMinutes();
            var seconds = date.getSeconds();
            return new Handlebars.SafeString( ifLen(hour) + ":" + ifLen(minute) + ":" + ifLen(seconds));

    });

    Handlebars.registerHelper("gNum", function(num) {
            var result = num.match( /Local\/(.+)@.*/i );
            return new Handlebars.SafeString( result[1]);

    });

    $(function getCalls() {
        $(function() {
            $.ajax({
                type: 'POST',
                url: 'ajam.php',
                data: 'Action=CoreShowChannels',
                success: function(data) {
                    var chan = JSON.parse(data);
                    //$('#channels').html(data);
                    var theTemplateScript = $("#channels-template").html();
                    var theTemplate = Handlebars.compile(theTemplateScript);
                    var context = {
                        Calls: chan
                    };
                    var theCompiledHtml = theTemplate(context);
                    $('.channels-placeholder').html(theCompiledHtml);
                }
            });
        });
        setTimeout(getCalls, 1000);
    });


    // $(function getQueueStatus() {
    //     $(function() {
    //         $.ajax({
    //             type: 'POST',
    //             url: 'ajam.php',
    //             data: 'Action=QueueStatus&ActionId=' + guid() + '&Queue=',
    //             success: function(data) {
    //                 var queue = JSON.parse(data);
    //                 //$('#rqueues').html(data);
    //                 var theTemplateScript = $("#queues-template").html();
    //                 var theTemplate = Handlebars.compile(theTemplateScript);
    //                 var context = { Queues: queue };
    //                 var theCompiledHtml = theTemplate(context);
    //                 $('.queues-placeholder').html(theCompiledHtml);
    //             }
    //         });
    //     });
    //     setTimeout(getQueueStatus, 999);
    // });


        Handlebars.registerHelper("ifDial", function(conditional, options) {
        if (conditional == options.hash.compare) {
            return options.fn(this);
        } else {
            return options.inverse(this);
        }
    });


        Handlebars.registerHelper("ifApp", function(conditional, options) {
        if (conditional == options.hash.compare) {
            return options.fn(this);
        } else {
            return options.inverse(this);
        }
    });

    Handlebars.registerHelper("ifE", function(conditional, options) {
        if (conditional == options.hash.equals) {
            return options.fn(this);
        } else {
            return options.inverse(this);
        }
    });

    Handlebars.registerHelper("constat", function(status) {
        var s = status;
        if (s == '<unknown>')
            var Stat = "RING";
        else
            var Stat = s;

        return new Handlebars.SafeString(Stat);
    });



Handlebars.registerHelper('l10n', function(keyword) {
		var lang = (navigator.language) ? navigator.language : navigator.userLanguage;

		//принудительно указать язык интерфеса, иначе язык браузера ('ru'/'en-US')
		var lang = 'ru';

		// pick the right dictionary
		var locale = window.locale[lang] || window.locale['en-US'];

		// loop through all the key hierarchy (if any)
		var target = locale;
		var key = keyword.split(".");
		for (i in key){
			target = target[key[i]];

		}

		//output
		return target;
});

    </script>
        <script id="channels-template" type="text/x-handlebars-template">
        <h5>Вызовы из очереди</h5>
        <br/>
        <div class="table">
            <table class="table centered">
                <thead>
                    <tr>
                        <th>DID</th>
                        <th>CallerID</th>
                        <th>Очередь</th>
                        <th>Агент</th>
                        <th>Продолж.</th>
                        <th>Состояние</th>
                    </tr>
                </thead>
                <tbody>
                    {{#each Calls}}
                    {{#ifDial ApplicationData compare='(Outgoing Line)'}}
                    {{#ifApp Application compare='AppQueue'}}
                    <tr>
                        <td>{{CallerIDNum}}</td>
                        <td>{{ConnectedLineNum}}</td>
                        <td>{{Exten}}</td>
                        <td>{{gNum Channel}}</td>
                        <td>{{Duration}}</td>
                        <td>{{inState ChannelStateDesc}}</td>
                    </tr>
                    {{/ifApp}}
                    {{/ifDial}}

                    {{/each}}
                </tbody>
            </table>
        </div>
<!--                 <h5>Все Вызовы</h5>
        <div class="table">
            <table class="table centered">
                <thead>
                    <tr>
                        <th>DID</th>
                        <th>CallerID</th>
                        <th>Очередь</th>
                        <th>Агент</th>
                        <th>Продолж.</th>
                        <th>Состояние</th>
                    </tr>
                </thead>
                <tbody>
                    {{#each Calls}}
                    {{#ifDial ApplicationData compare='(Outgoing Line)'}}
                    {{#ifApp Application compare='AppDial'}}
                    <tr>
                        <td>{{CallerIDNum}}</td>
                        <td>{{ConnectedLineNum}}</td>
                        <td>{{Exten}}</td>
                        <td>{{Channel}}</td>
                        <td>{{Duration}}</td>
                        <td>{{inState ChannelStateDesc}}</td>
                    </tr>
                    {{/ifApp}}
                    {{/ifDial}}
                    {{/each}}
                </tbody>
            </table> -->
        </div>
    </script>
        <script id="queues-template" type="text/x-handlebars-template">
            <h5>{{l10n "sts.INQueue"}}</h5>
            <br />
            <div class="table">
            <table class="table centered">
                <thead>
                    <tr>
                        <th scope="col">{{l10n "sts.Queue"}}</th>
                        <th>{{l10n "sts.Num"}}</th>
                        <!-- <th>{{l10n "sts.Name"}}</th> -->
                        <!-- <th>{{l10n "sts.CNum"}}</th> -->
                        <th>{{l10n "sts.Pos"}}</th>
                        <th>{{l10n "sts.Wait"}}</th>
                    </tr>
                </thead>
                <tbody>
                    {{#each Queues}}
                    {{#ifE Event equals="QueueEntry"}}
                    <tr>
                        <td>{{Queue}}</td>
                        <td>{{CallerIDNum}}</td>
                        <!-- <td>{{CallerIDName}}</td> -->
                        <!-- <td>{{ConnectedLineNum}}</td> -->
                        <td>{{Position}}</td>
                        <td>{{Wait}}</td>
                    </tr>
                    {{/ifE}}
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
            <div>
                <a href="queues.php">Очереди</a>
                <a href="agents.php">Агенты</a>
            </div>
            <br/>
        <div class="channels-placeholder">null</div>
        <br/>
        <!-- <div class="queues-placeholder">null</div> -->
    </div>
    </div>
</body>

</html>
