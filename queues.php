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
        <?php
echo "<script>
localStorage.setItem('queues',\"" . $_SESSION['QSTATS']['queue'] . "\");
</script>";
?>
    <script>

    var i = sessionStorage.length;
    while (i--) {
        var key = sessionStorage.key(i);
        if (/startcall-/.test(key)) {
            sessionStorage.removeItem(key);
        }
    }

    function guid() {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }
        return s4() + s4();
    }


    function queuePause(id, state) {
        $.ajax({
            url: 'ajam.php',
            method: 'POST',
            data: 'Action=QueuePause&ActionId=' + guid() + '&Interface=' + id + '&Paused=' + state,
            success: function(data) {
                $('#test').html(data);
            }
        });
    }


    function channelHangup(ch) {
        $.ajax({
            url: 'ajam.php',
            method: 'POST',
            async: true,
            data: 'Action=Hangup&ActionId=' + guid() + '&Channel=' + ch,
            success: function(data) {
                $('#test').html(data);
            }
        });
    }


    $(function getQueueSummary() {
        $(function() {
            $.ajax({
                type: 'POST',
                url: 'ajam.php',
                data: 'Action=QueueSummary&ActionId=' + guid() + '&Queue=',
                success: function(data) {
                    var queuesum = JSON.parse(data);
                    //$('#queuesum').html(data);
                    var theTemplateScript = $("#queuesum-template").html();
                    var theTemplate = Handlebars.compile(theTemplateScript);
                    var context = { Queuesum: queuesum };
                    var theCompiledHtml = theTemplate(context);
                    $('.queuesum-placeholder').html(theCompiledHtml);
                }
            });
        });
        setTimeout(getQueueSummary, 1382);
    });

    $(function getQueueStatus() {
        $(function() {
            $.ajax({
                type: 'POST',
                url: 'ajam.php',
                data: 'Action=QueueStatus&ActionId=' + guid() + '&Queue=',
                success: function(data) {
                    var queue = JSON.parse(data);
                    //$('#rqueues').html(data);
                    var theTemplateScript = $("#queues-template").html();
                    var theTemplate = Handlebars.compile(theTemplateScript);
                    var context = { Queues: queue };
                    var theCompiledHtml = theTemplate(context);
                    $('.queues-placeholder').html(theCompiledHtml);
                }
            });
        });
        setTimeout(getQueueStatus, 999);
    });

    Handlebars.registerHelper("ifQue", function(q, options) {

    let qselect = localStorage.getItem("queues");
    let qselect2 = qselect.replace(/'/g, "");

    qselect2 = qselect2.split(',');
    let que = {};
    qselect2.forEach(v => [que[v]] = new Object([v]));


    if (q in que) {
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


    Handlebars.registerHelper("ifQ", function(conditional, options) {
        if (conditional == options.hash.equals) {
            return options.fn(this);
        } else {
            return options.inverse(this);
        }
    });

    Handlebars.registerHelper("ifM", function(conditional, options) {
        if (conditional == options.hash.equals) {
            return options.fn(this);
        } else {
            return options.inverse(this);
        }
    });

    Handlebars.registerHelper("qmStatus", function(status) {
        var s = status;
        switch (s) {
            case '0':
                var mStatus = "Неизвестно";
                break;
            case '1':
                var mStatus = '<span class="text-info">' + window.locale["ru"]["sts"]["1"] + '</span>';
                break;
            case '2':
                var mStatus = '<span class="text-success">' + window.locale["ru"]["sts"]["2"] + '</span>';
                break;
            case '3':
                var mStatus = "<span class='text-danger'>Занят</span>";
                break;
            case '4':
                var mStatus = "<span class='text-danger'>Ошибка</span>";
                break;
            case '5':
                var mStatus = '<span class="text-muted">' + window.locale["ru"]["sts"]["5"] + '</span>';
                break;
            case '6':
                var mStatus = '<span class="text-warning">' + window.locale["ru"]["sts"]["6"] + '</span>';
                break;
            case '7':
                var mStatus = "RINGINUSE";
                break;
            case '8':
                var mStatus = "HOLD";
                break;
        }
        return new Handlebars.SafeString(mStatus);
    });

    Handlebars.registerHelper("qmPaused", function(paused) {
        var p = paused;
        switch (p) {
            case '0':
                var mPaused = "<span class='text-success'>В очереди</span>";
                break;
            case '1':
                var mPaused = "<span class='text-warning'>На паузе</span>";

        }
        return new Handlebars.SafeString(mPaused);
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

    Handlebars.registerHelper("lastCall", function(lastcall, status, paused, agent) {

        if (status == '1' && paused !== '1') {
            var dateFromAPI = lastcall;

            var now = new Date();
            var nowTimeStamp = now.getTime() / 1000;
            localStorage.removeItem('startcall-' + agent);
            localStorage.setItem('startcall-' + agent, nowTimeStamp);

            var microSecondsDiff = nowTimeStamp - dateFromAPI;
            var date = new Date(microSecondsDiff * 1000);
            var year = date.getFullYear();
            var month = date.getMonth();
            var day = date.getDay();
            var hour = date.getHours();
            var minute = date.getMinutes();
            var seconds = date.getSeconds();

            return new Handlebars.SafeString("<span class='text-info'>" + ifLen(minute) + ":" + ifLen(seconds) + "</span>");
        } else if (status == '2' || status == '6') {
            var dateFromAPI = lastcall;

            var now = new Date();
            var nowTimeStamp = now.getTime() / 1000;
            if (localStorage.getItem('startcall-' + agent)) {
                var startCall = localStorage.getItem('startcall-' + agent);
            } else {
                var startCall = lastcall
            }
            var microSecondsDiff = nowTimeStamp - startCall;
            var date = new Date(microSecondsDiff * 1000);
            var year = date.getFullYear();
            var month = date.getMonth();
            var day = date.getDay();
            var hour = date.getHours();
            var minute = date.getMinutes();
            var seconds = date.getSeconds();

            // Number of milliseconds per day =
            //   24 hrs/day * 60 minutes/hour * 60 seconds/minute * 1000 msecs/second
            //var daysDiff = Math.floor(microSecondsDiff / (1000 * 60 * 60 * 24));
            if (status == '2') {
                if (minute < 1) {
                    return new Handlebars.SafeString("<span class='text-success'>" + ifLen(minute) + ":" + ifLen(seconds) + "</span>");
                } else if (minute >= 1) {
                    return new Handlebars.SafeString("<span class='text-success'><b>" + ifLen(minute) + ":" + ifLen(seconds) + "</b></span>");
                } else if (minute >= 3) {
                    return new Handlebars.SafeString("<span class='text-warning'><b>" + ifLen(minute) + ":" + ifLen(seconds) + "</b></span>");
                } else {
                    return new Handlebars.SafeString("<span class='text-success'>" + ifLen(minute) + ":" + ifLen(seconds) + "</span>");
                }
            } else if (status == '6') {
                return new Handlebars.SafeString("<span class='text-warning'>" + ifLen(minute) + ":" + ifLen(seconds) + "</span>");
            }
        } else {
            return new Handlebars.SafeString('<span class="text-muted">&nbsp;--:--</span>');
        }
    });



        Handlebars.registerHelper("ifDial", function(conditional, options) {
        if (conditional == options.hash.compare) {
            return options.fn(this);
        } else {
            return options.inverse(this);
        }
    });

    Handlebars.registerHelper("ifAppDial", function(conditional, options) {
        if (conditional == options.hash.compare) {
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

    <script id="queuesum-template" type="text/x-handlebars-template">
        <h2>{{l10n "panel"}}</h2>
        <br />
        <h3>{{l10n "sts.Queues"}}</h3>
        <div class="table">
            <table class="table centered">
                <thead>
                    <tr>
                        <th>{{l10n "sts.Queue"}}</th>
                        <th>{{l10n "sts.LoggedIn"}}</th>
                        <th>{{l10n "sts.Available"}}</th>
                        <th>{{l10n "sts.Callers"}}</th>
                        <th>{{l10n "sts.HoldTime"}}</th>
                        <th>{{l10n "sts.TalkTime"}}</th>
                        <th>{{l10n "sts.LongestHoldTime"}}</th>
                    </tr>
                </thead>
                <tbody>
                    {{#each Queuesum}}
                    {{#ifQue Queue}}
                    <tr>
                        <td>{{Queue}}</td>
                        <td>{{LoggedIn}}</td>
                        <td>{{Available}}</td>
                        <td>{{Callers}}</td>
                        <td>{{HoldTime}}</td>
                        <td>{{TalkTime}}</td>
                        <td>{{LongestHoldTime}}</td>
                    </tr>
                    {{/ifQue}}
                    {{/each}}
                </tbody>
            </table>
        </div>
        <br />
    </script>
    <script id="queues-template" type="text/x-handlebars-template">
        <div class="table">
            <table class="table centered">
                <thead>
                    <tr>
                        <th scope="col">{{l10n "sts.Queue"}}</th>
                        <th>{{l10n "sts.Max"}}</th>
                        <th>{{l10n "sts.Strategy"}}</th>
                        <!-- <th>{{l10n "sts.Calls"}}</th> -->
                        <th>{{l10n "sts.Completed"}}</th>
                        <th>{{l10n "sts.Abandoned"}}</th>
                        <!-- <th>{{l10n "sts.Weight"}}</th> -->
                        <th>{{l10n "sts.ServiceLevel"}}</th>
                        <th>{{l10n "sts.ServicelevelPerf"}}</th>
                    </tr>
                </thead>
                <tbody>
                    {{#each Queues}} {{#ifQ Event equals="QueueParams"}} {{#ifQue Queue}}
                    <tr>
                        <td>{{Queue}}</td>
                        <td>{{Max}}</td>
                        <td>{{Strategy}}</td>
                        <!-- <td>{{Calls}}</td> -->
                        <td>{{Completed}}</td>
                        <td>{{Abandoned}}</td>
                        <td>{{ServiceLevel}}</td>
                        <td>{{ServicelevelPerf}}%</td>
                    </tr>
                    {{/ifQue}} {{/ifQ}} {{/each}}
                </tbody>
            </table>
            <br />
        </div>
    </script>
</head>
<html>
<body>
<?php include "menu.php";?>
        <div id="main">
        <div id="contents">
            <div>
                <a href="agents.php">Агенты</a>
                <a href="calls.php">Вызовы</a>
            </div>
            <br/>
        <div class="queuesum-placeholder">null</div>
        <div class="queues-placeholder">null</div>
        <br/>
    </div>
    </div>
    <div id='footer'><a href='https://asterisk-pbx.ru'>Asterisk-pbx.ru</a> 2018</div>
</body>

</html>
