<?php

$dbhost = "localhost";
$dbuser = "sysalert";
$dbpass = "sysalert";
$db	= "sysalert";

$cli = 0;


# DB schema (for reference)
# sysalert
# +-------------+--------------+------+-----+-------------------+-------+
# | Field       | Type         | Null | Key | Default           | Extra |
# +-------------+--------------+------+-----+-------------------+-------+
# | hostname    | varchar(255) | NO   |     | NULL              |       | 
# | id          | int(11)      | NO   | PRI | NULL              |       | 
# | status      | varchar(255) | YES  |     | NULL              |       | 
# | value       | text         | YES  |     | NULL              |       | 
# | alertstatus | tinyint(4)   | YES  |     | NULL              |       | 
# | lastalert   | timestamp    | NO   |     | CURRENT_TIMESTAMP |       | 
# +-------------+--------------+------+-----+-------------------+-------+
#
# machines
#+-------+--------------+------+-----+---------+----------------+
#| Field | Type         | Null | Key | Default | Extra          |
#+-------+--------------+------+-----+---------+----------------+
#| id    | bigint(11)   | NO   | PRI | NULL    | auto_increment | 
#| name  | varchar(255) | NO   |     |         |                | 
#| key   | varchar(255) | NO   |     |         |                | 
#| value | text         | NO   |     |         |                | 
#+-------+--------------+------+-----+---------+----------------+


ini_set('output_buffering', 0);
header("Content-Type: text/plain");

class Utils
{
	function init()
	{
		global $cli;
		global $argv;
		global $ipnum;
		global $port;
		global $hostname;

		if (isset($argv)) {
			$cli = 1;
			$ipnum = $argv[1];
			$port = $argv[2];
			$hostname = $argv[3];
		} else {
			$cli = 0;
			$ipnum = $_SERVER["REMOTE_ADDR"];
			$hostname = $_REQUEST["hostname"];
			$port = $_REQUEST["port"];
		}
		$cli && print "CLI mode enabled\n";

		if (!$ipnum || !$hostname || !$port) {
			print "Insufficient data\n";
			die;
		}
	}

	function connect($host)
	{
		global $cli;
		global $port;

		$fp = @fsockopen($host, $port, $err, $errmsg, 10);
		if (!$fp) {
			print "Error connecting to $host: $errmsg\n";
			die;
		}

		$cli && print "Connection to $host established\n";

		# get past the banner
		$junk = fgets($fp, 1024);
		$cli && print $junk;

		return $fp;
	}

	function updatekeyval($hostname, $key, $value)
	{
		$query = "select `name`,`key`,`value` from machines where name='$hostname' and `key`='$key'";
		$cli && print $query."\n";

		$ret = MySQL::query($query);
		if (mysql_num_rows($ret) == 0) {
			error_log("inserting");
			MySQL::query("insert into machines (`name`, `key`, `value`) values ('$hostname', '$key', '$value');");
		} else {
			$dbval = mysql_fetch_array($ret, MYSQL_ASSOC);
			$oldval = $dbval["value"];
			if ($oldval != $value) {
				print "$hostname::$key('$oldval') -> '$value')\n";
				MySQL::query("update machines set `value`='$value' where `name` = '$hostname' and `key`='$key'");
			}
		}
		
	}

	function fetchdata($fp)
	{
		global $hostname;
		global $cli;
		global $ipnum;
		global $port;
		global $interval;

		fputs($fp, "cleanup\n");
		fgets($fp, 1024); # a notification, eat it.
		fputs($fp, "numcpus\n");
		$numcpus = fgets($fp, 1024);
		fputs($fp, "getinterval\n");
		$interval = fgets($fp, 1024);
		fputs($fp, "lastlogin\n");
		$lastlogin = fgets($fp, 1024);
		fputs($fp, "listusers\n");
		$users = fgets($fp, 1024);
		fputs($fp, "os\n");
		$ostype = fgets($fp, 1024);


		fputs($fp, "dump all\n");
		while(1) {
			$line = fgets($fp, 1024);
			if (preg_match("/^# Fetching/", $line)) {
				continue;
			}
			if (preg_match("/^no results/", $line)) {
				$cli && print "No data\n";
				break;
			}
			if (preg_match("/^# end of data/", $line)) {
				$cli && print "End of data\n";
				break;
			}

			list($id, $status, $value, $alertstatus, $lastalert) = split(";", $line);

			MySQL::query("insert into status (hostname, status, value, alertstatus, lastalert) values(
				'$hostname','$status', '$value', '$alertstatus', FROM_UNIXTIME($lastalert));");
	
			Utils::updatekeyval($hostname, "address", $ipnum);
			Utils::updatekeyval($hostname, "ncpu", $numcpus);
			Utils::updatekeyval($hostname, "port", $port);
			Utils::updatekeyval($hostname, "interval", $interval);
			Utils::updatekeyval($hostname, "alert_$status", $alertstatus);
			Utils::updatekeyval($hostname, "users", $users);
			Utils::updatekeyval($hostname, "lastlog", $lastlogin);
			Utils::updatekeyval($hostname, "ostype", $ostype);
			
			if ($status == "disk") {
				preg_match("/^(.+):.*/", $value, $match);
				$disk = $match[1];

				$ret = MySQL::query("select `name`,`key` from machines where name=\"$hostname\" and `key`=\"disk\" and `value`='$disk'");
				if (mysql_num_rows($ret) == 0) {
					MySQL::query("insert into machines (`name`, `key`, `value`) values ('$hostname', 'disk', '$disk');");
					$cli && print $hostname." port inserted (".mysql_num_rows($ret).")\n";
				}
			}

			$item++;
		}
		print "Fetched $item items\n";
		fputs($fp, "clear all\n");
		fputs($fp, "exit\n");
		fclose($fp);
		return;
	}
}

class MySQL
{

	function init()
	{
		global $dbhost;
		global $dbuser;
		global $dbpass;
		global $db;
		global $dbh;
		global $cli;

		if (!$dbh) {
			$dbh = mysql_connect($dbhost, $dbuser, $dbpass);
			if (!$dbh) {
				print "Cannot connect to db: ".mysql_error()."\n";
				die;
			}
			$cli && print "MySQL connection with $dbh\n";
			$ret = mysql_select_db($db, $dbh);
			if (!$ret) {
				print "Could not select $db: ".mysql_error()."\n";
				die;
			}
		}
	}
	function query($query)
	{
		global $dbh;

		$res = @mysql_query($query, $dbh);
		if (!$res) {
			print $query."\n";
			print "Problem with SQL query: ".mysql_error()."\n";
			die;
		}
		return $res;
	}
}


Utils::init();
MySQL::init();

@$fp = Utils::connect($ipnum);
@Utils::fetchdata($fp);
