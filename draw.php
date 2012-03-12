<?php

# Draw a graph according to time index, output a graphic

ini_set("memory_limit","256M");

require_once("graph/jpgraph.php");
require_once("graph/jpgraph_line.php");
require_once("graph/jpgraph_date.php");

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
		global $status;
		global $hostname;
		global $disk;
		global $period;

		if (isset($argv)) {
			$cli = 1;
			$hostname = $argv[1];
			$status = $argv[2];
			$period = $argv[3];
			if (count($argv) == 5) {
				$argv[4];
			}
		} else {
			$cli = 0;
			$hostname = $_REQUEST["hostname"];
			$status = $_REQUEST["status"];
			$period = $_REQUEST["period"];
			@$disk = $_REQUEST["disk"];
		}
		$cli && error_log("CLI mode enabled\n");

		if (!$hostname || !$status || !$period) {
			error_log("Insufficient data\n");
			die;
		}
		if ($status == "disk" && !$disk) {
			error_log("Which disk?");
			die;
		}
	}

	function calc_period($period, $interval) {

		$HOUR = 60 * 60;
		$DAY = 24 * $HOUR;
		$WEEK = 7 * $DAY;
		$MONTH = 4 * $WEEK;
		$YEAR = 12 * $MONTH;
		
		$found = 0;
		if ($period == "hour") {
			$amount = floor($HOUR / $interval);
			$adjustment = MINADJ_1;
			$format = "G:i";
			$found++;
		}
		if ($period == "day") {
			$amount = floor($DAY / $interval);
			$adjustment = HOURADJ_1;
			$format = "G:i";
			$found++;
		}
		if ($period == "week") {
			$amount = floor($WEEK / $interval);
			$adjustment = DAYADJ_1;
			$format = "l";
			$found++;
		}
		if ($period == "month") {
			$amount = floor($MONTH / $interval);
			$adjustment = DAYHADJ_WEEK;
			$format = "\w\e\e\k W";
			$found++;
		}
		if ($period == "year") {
			$amount = floor($YEAR / $interval);
			$adjustment = MONTHADJ_1;
			$format = "M";
			$found++;
		}
		if (!$found) {
			error_log("Unknown period");
			die;
		}

		$ret = array("records" => $amount, "adjust" => $adjustment, "format" => $format);
		return $ret;
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
			$cli && error_log("MySQL connection with $dbh");
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

$xdata = array();
$ydata = array();
$limit = array();


# Get the interval
$query = "SELECT `value` FROM machines WHERE `name`='$hostname' and `key`='interval'";
$res = MySQL::query($query);
$row = mysql_fetch_array($res, MYSQL_ASSOC);
$interval = $row['value'];

$param = Utils::calc_period($period, $interval);
$amount = "LIMIT ".$param['records'];

# Prepare query
if ($status == "load" || $status == "cpu") {
	$query = "SELECT UNIX_TIMESTAMP(lastalert) as lastalert, status, value FROM status WHERE hostname='$hostname' AND status='$status' ORDER BY lastalert DESC $amount";
}

if ($status == "disk") {
	$query = "SELECT UNIX_TIMESTAMP(lastalert) as lastalert, status, value FROM status WHERE hostname='$hostname' AND status='$status' and value like '$disk:%' ORDER BY lastalert DESC $amount";
}
error_log("Query: $query");

$res = MySQL::query($query);
$numres = mysql_num_rows($res);
#$cli && error_log("Got $numres results");

$i=0;
while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
	$xdata[$i] = $row["lastalert"];

	if ($status == "disk") { # example: /storage:  99.98 %
		preg_match("/:\s(.+) %$/", $row["value"], $match);
		$freespace = $match[1];
		$freespace = 100 - $freespace;
		$freespace = sprintf("%.02f", $freespace);
		array_push($ydata, $freespace);
	} else {
		$data = sprintf("%.02f", $row["value"]);
		array_push($ydata, $data);
	}

	#error_log("time: $xdata[$i] value: $ydata[$i]");
	
	$i++;
}

$graph = new Graph(500, 300, "auto");
$graph->SetMargin(60,60,30,130);

if ($status == "disk") {
	$graph->SetScale("datlin",0,100);
} else {
	$graph->SetScale("datlin");
}

$graph->xaxis->SetLabelAngle(90);


$graph->xaxis->scale->SetDateFormat($param['format']);

if ($period == "hour" || $period == "day") {
	$graph->xaxis->scale->SetTimeAlign($param['adjust']);
} else {
	$graph->xaxis->scale->SetDateAlign($param['adjust']);
}

$line = new LinePlot($ydata,$xdata);

if ($status == "disk") {
	$graph->title->set("($period) space used on $disk on $hostname ($amount)");
} else {
	$graph->title->set("($period) $status on $hostname ($amount)");
}

if ($status == "disk") {
	$line->SetFillColor('green@0.5');
} 
if ($status == "load") {
	$line->SetFillColor('red@0.5');
} 

$graph->Add($line);
$graph->Stroke();
?>
