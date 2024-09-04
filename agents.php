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

    function guid() {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }
        return s4() + s4();
    }

    $(function getQueueStatus() {
        $(function() {
            $.ajax({
                type: 'POST',
                url: 'ajam.php',
                data: 'Action=QueueStatus&ActionId=' + guid() + '&Queue=',
                success: function(data) {
                    var queue = JSON.parse(data);
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
                var mStatus = '<span class="text-info">' + window.locale["ru"]["sts"]["1"] + '</span>'; //свобод
                break;
            case '2':
                var mStatus = '<span class="text-sexess">' + window.locale["ru"]["sts"]["2"] + '</span>'; //разг
                break;
            case '3':
                var mStatus = "<span class='text-danger'>Занят</span>";
                break;
            case '4':
                var mStatus = "<span class='text-danger'>Ошибка</span>";
                break;
            case '5':
                var mStatus = '<span class="text-dimgray">' + window.locale["ru"]["sts"]["5"] + '</span>'; //недост
                break;
            case '6':
                var mStatus = '<span class="text-indred">' + window.locale["ru"]["sts"]["6"] + '</span>'; //вызов
                break;
            case '7':
                var mStatus = "RINGINUSE";
                break;
            case '8':
                var mStatus = '<span class="text-peru"><b>Удержание</b></span>';
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


Handlebars.registerHelper("ifQue", function(q, options) {

    let qselect = localStorage.getItem("queues");
    //let qselect2 = localStorage.getItem("queues").split(',').map(word => `'${word.trim()}'`).join(', ');
    let qselect2 = qselect.replace(/'/g, "");
    localStorage.setItem("queues2", qselect2);

    qselect2 = qselect2.split(',');
    let que = {};
    qselect2.forEach(v => [que[v]] = new Object([v]));


    if (q in que) {
        return options.fn(this);
    } else {
        return options.inverse(this);
    }

});


Handlebars.registerHelper("ifReach", function(q, options) {

    if (q != 5) {
        return options.fn(this);
    } else {
        return options.inverse(this);
    }

});

Handlebars.registerHelper("ifUnReach", function(q, options) {

    if (q == 5) {
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
    <script id="queues-template" type="text/x-handlebars-template">
            <table class="table centered">
                <h5>Активные {{l10n "sts.Agents"}}</h5>
                <thead>
                    <tr>
                        <th scope="col">{{l10n "sts.Queue"}}</th>
                        <th scope="col">{{l10n "sts.Name"}}</th>
                        <th scope="col">{{l10n "sts.InCall"}}</th>
                        <th scope="col">{{l10n "sts.Status"}}</th>
                        <th scope="col">{{l10n "sts.LastCall"}}</th>
                        <th scope="col">{{l10n "sts.CallsTaken"}}</th>
                        <th scope="col">{{l10n "sts.Paused"}}</th>
                    </tr>
                </thead>
                <tbody>
                    {{#each Queues}}
                    {{#ifM Event equals="QueueMember"}}
                    {{#ifReach Status}}
                    {{#ifQue Queue}}
                    <tr>
                        <td>{{Queue}}</td>
                        <td>{{Name}}</td>
                        <td>{{InCall}}</td>
                        <td>{{qmStatus Status}}</td>
                        <td>{{normDate LastCall}}</td>
                        <td>{{CallsTaken}}</td>
                        <td>{{qmPaused Paused Location}}</td>
                    </tr>
                    {{/ifQue}}
                    {{/ifReach}}
                    {{/ifM}}
                    {{/each}}
                </tbody>
            </table>
            <br />
             <table class="table centered">
                <h5>Незарег. {{l10n "sts.Agents"}}</h5>
                <thead>
                    <tr>
                        <th scope="col">{{l10n "sts.Queue"}}</th>
                        <th scope="col">{{l10n "sts.Name"}}</th>
                        <th scope="col">{{l10n "sts.InCall"}}</th>
                        <th scope="col">{{l10n "sts.Status"}}</th>
                        <th scope="col">{{l10n "sts.LastCall"}}</th>
                        <th scope="col">{{l10n "sts.CallsTaken"}}</th>
                        <th scope="col">{{l10n "sts.Paused"}}</th>
                    </tr>
                </thead>
                <tbody>
                    {{#each Queues}}
                    {{#ifM Event equals="QueueMember"}}
                    {{#ifUnReach Status}}
                    {{#ifQue Queue}}
                    <tr>
                        <td>{{Queue}}</td>
                        <td>{{Name}}</td>
                        <td>{{InCall}}</td>
                        <td>{{qmStatus Status}}</td>
                        <td>{{normDate LastCall}}</td>
                        <td>{{CallsTaken}}</td>
                        <td>{{qmPaused Paused Location}}</td>
                    </tr>
                    {{/ifQue}}
                    {{/ifUnReach}}
                    {{/ifM}}
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
<!-- <label class="custom-control custom-checkbox">
    <input type="checkbox" id="reach" class="custom-control-input">
    <span class="custom-control-indicator"></span>
    <span class="custom-control-description">Только зарегистрированных</span>
</label> -->
            <div>
                <a href="queues.php">Очереди</a>
                <a href="calls.php">Вызовы</a>
            </div>
            <br/>
        <div class="queues-placeholder">null</div>
        <br/>

    </div>
    </div>
    <div id='footer'><a href='https://asterisk-pbx.ru'>Asterisk-pbx.ru</a> 2018</div>
</body>

</html>
