#!/usr/bin/php
<?php
#
# Upgrades clients (requires ssh key setup)

$dbhost = "localhost";
$dbuser = "sysalert";
$dbpass = "sysalert";
$db     = "sysalert";

$cli = 0;

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



class Utils
{
	function init()
	{
		global $cli;
		global $argv;
		global $cmd;

		if (isset($argv)) {
			$cli = 1;
		} else {
			print "This should be run from the commandline. Bye!\n";
			die;
		}
			
	}

	function gethosts()
	{
		$ret = array();
		$res = MySQL::query("SELECT DISTINCT `name` FROM machines");
		while($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($ret, $row["name"]);
		}
		asort($ret);
		return $ret;
	}

	function gethostip($host)
	{
		$res = MySQL::query("SELECT `value` AS `ip` FROM machines WHERE `name`='$host' and `key`='address'");
		if (mysql_num_rows($res) == 0) {
			return "N/A";
		}
		$row = mysql_fetch_array($res, MYSQL_ASSOC);
		return $row["ip"];
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
				error_log("Cannot connect to db: ".mysql_error());
				die;
			}
			#$cli && error_log("MySQL connection with $dbh");
			$ret = mysql_select_db($db, $dbh);
			if (!$ret) {
				error_log("Could not select $db: ".mysql_error());
				die;
			}
		}
	}
	function query($query)
	{
		global $dbh;

		$res = @mysql_query($query, $dbh);
		if (!$res) {
			error_log("Query: $query"); 
			error_log("Problem with SQL query: ".mysql_error());
			die;
		}
		return $res;
	}
}


Utils::init();
MySQL::init();

$hosts = Utils::gethosts();
foreach($hosts as $host) {
	$ip = Utils::gethostip($host);
	print "$ip:$host\n";
}
