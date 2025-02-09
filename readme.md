# CallCenter-Stats  
## Install Guide  
### Enable Asterisk queue log in MySQL  
Connect to MySQL and create a new tables in `asteriskcdrdb`  
```
use asteriskcdrdb;
```
```
-- Create queuelog
CREATE TABLE IF NOT EXISTS `queuelog` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `time` char(32) DEFAULT NULL,
    `callid` char(64) DEFAULT NULL,
    `queuename` char(64) DEFAULT NULL,
    `agent` char(64) DEFAULT NULL,
    `event` char(32) DEFAULT NULL,
    `data` char(64) DEFAULT NULL,
    `data1` char(64) DEFAULT NULL,
    `data2` char(64) DEFAULT NULL,
    `data3` char(64) DEFAULT NULL,
    `data4` char(64) DEFAULT NULL,
    `data5` char(64) DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Create agents_new
CREATE TABLE IF NOT EXISTS `agents_new` (
    `id` MEDIUMINT NOT NULL AUTO_INCREMENT,
    `agent` char(64) DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Create queues_new
CREATE TABLE IF NOT EXISTS `queues_new` (
    `id` MEDIUMINT NOT NULL AUTO_INCREMENT,
    `queuename` char(64) DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```
**Disable Log to file**  
Edit /etc/asterisk/logger.conf
```
queue_log_to_file = no
```
Add `options` to asterisk.conf
Edit /etc/asterisk/asterisk.conf
```
[options]
queue_adaptive_realtime = no
```
**Setup Realtime**  
Edit /etc/asterisk/extconfig.conf
```
[settings]
queue_log => odbc,asteriskcdrdb,queuelog
```
Edit `/etc/asterisk/sip.conf`
```
[general]
callcounter => yes
```
### Install CallCenter-Stats  
Create new site in Apache2 or add new directory in `/var/www/html`  
Clone git repository to new site  
```
git clone https://github.com/scorpionukr/CallCenter-Stats.git
```
Change owner  
```
chown -R www-data: CallCenter-Stats
```
Edit `config.php`  
```
// Setup MySQL connection
$DBServer = 'your_mysql_server';
$DBUser = 'user';
$DBPass = '';
$DBName = 'asteriskcdrdb';
$DBTable = 'queuelog';
// Setup AJAM
$config['urlraw'] = 'http://IP_PBX:8088/rawman';
$config['admin'] = 'ajamuser';
$config['secret'] = '';
$config['authtype'] = 'plaintext';
$config['cookiefile'] = null;
$config['debug'] = false;
```
Change chmod
```
chmod 777 ajam_cookie
```
**Setup AJAM in Asterisk and create user**
Edit `/etc/asterisk/manager.conf`
```
[general]
enabled = yes
port = 5038
bindaddr = 0.0.0.0
webenabled = yes
httptimeout = 60
[ajamuser]
secret = PASSWORD
deny = 0.0.0.0/0.0.0.0
permit = 192.168.200.2/255.255.255.0
read = system,agent,reporting
write = system,agent,reporting
```
Go to FreePBX -> Settings -> Advanced Settings  
Enable mini-HTTP Server  
Test AJAM
```
asterisk -rx 'http show status'
```