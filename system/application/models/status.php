<?php

class Status extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function get_hosts()
	{
        $this->db->cache_off();
		$data = array();
		$query = $this->db->query("SELECT DISTINCT name AS hostname FROM machines");

		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($data, $row->hostname);
			}
			return $data;
		}
		return 0;
	}

	function get_heartbeat($host)
	{
		$now = time();

		$this->db->cache_off();
		$query = $this->db->query("SELECT UNIX_TIMESTAMP(lastalert) as lasttime FROM status WHERE hostname=? order by status.lastalert DESC LIMIT 1", array($host));
		$numrows = $query->num_rows();
		$delta = 0;
		if ($numrows == 1) {
			$row = $query->row_array();
			$delta = $now - $row['lasttime'];
			$query->free_result();
			
			# I love modulo-math :)
			$hours = (int)($delta / 3600);
			$minutes = (int) (($delta % 3600) / 60);
			$seconds = $delta % 60;

			$ago = "$hours:$minutes:$seconds ";
			$ago = sprintf("%02d:%02d:%02d ", $hours, $minutes, $seconds);
			return $ago;
		}
		return 0;
	}

	function get_monitor_items($host)
	{
		$data = array();

		$this->db->cache_off();
		$query = $this->db->query("SELECT DISTINCT status FROM status WHERE hostname=?", array($host));
		$numrows = $query->num_rows();
		if ($numrows) {
			foreach ($query->result() as $row) {
				array_push($data, $row->status);
			}
			return $data;
		}
	}

	function is_broken($host, $stat) 
	{
		$this->db->cache_off();

		# This query is slow :(
		# $query = $this->db->query("SELECT alertstatus FROM status WHERE hostname=? AND status=? ORDER BY lastalert DESC LIMIT 1", array($host, $stat));

		# This one isn't :) (80% performance increase)
		$query = $this->db->query("SELECT value as alertstatus FROM machines where `name`=? and `key`='alert_$stat'", $host); 
		$numrows = $query->num_rows();
		if ($numrows) {
			$row = $query->row_array();
			return $row["alertstatus"];
		}
	}

	function get_database_size()
	{	
		$query = $this->db->query("SELECT COUNT(*) AS items FROM status");
		$row = $query->row_array();
		return $row["items"];
	}

	function get_disks($host)
	{
		$data = array();
		$this->db->cache_off();
		$query = $this->db->query("SELECT DISTINCT value as disks FROM machines WHERE name=? AND `key`='disk'", $host);
		$numrows = $query->num_rows();
		if ($numrows) {
			foreach ($query->result() as $row) {
				array_push($data, $row->disks);
			}
			return $data;
		}	
	}
	
	function get_ip_num($host)
	{
		$ret = "";
		$this->db->cache_off();
		$res = $this->db->query("SELECT `value` AS address FROM machines WHERE `name`=? and `key`='address'", $host);
		$row = $res->row_array();
		$address = $row['address'];
			
		$res = $this->db->query("SELECT `value` AS port FROM machines WHERE `name`=? and `key`='port'", $host);
		$row = $res->row_array();
		$port = $row['port'];

		$ret = sprintf("%s:%d", $address, $port);
		
		return $ret;
	}
	
	function get_numcpus($host) {
		$this->db->cache_off();
		$res = $this->db->query("SELECT `value` AS `ncpu` FROM machines WHERE `name`=? and `key`='ncpu'", $host);
		$row = $res->row_array();
		return $row['ncpu'];
	}	
	
	function delete_host($host) {
		$this->db->cache_off();
		$res = $this->db->query("DELETE FROM machines WHERE name=?", $host);
		$res = $this->db->query("DELETE FROM status where hostname=?", $host);
	}
	
	function get_users($host) {
		$ret = array();
		$this->db->cache_off();
		$res = $this->db->query("SELECT `value` AS `users` FROM machines WHERE `name`=? and `key`='users'", $host);
		$row= $res->row_array();
		
		$users = split(";", $row["users"]);
		foreach ($users as $user) {
			array_push($ret, $user);
		}				
		array_pop($ret);
		return $ret;
	}
	
	function user_info($user) {
		list($username, $pty, $lastlogin) = split(":", $user);

		$ret = array(
			"username" => $username,
			"pty" => $pty,
			"lastlogin" => @date("r", $lastlogin),
			);
		
		return $ret;
	}

	function lastlogin($host) 
	{
		$ret = array();
		$users = array();
		$data = array();
		$tmp = array();

		$now = time();

		$this->db->cache_off();
		$res = $this->db->query("SELECT `value` AS lastlog FROM machines WHERE `name`=? and `key`='lastlog'", $host);
		$row = $res->row_array();
		$data = split(";", $row["lastlog"]);
		array_pop($data); /* pop empty record */
		foreach ($data as $user) {

			list($tmp["user"], $tmp["lastlog"]) = split(":", $user);

			$delta = $now - $tmp["lastlog"];
			if ($delta == $now) { /* adjust */
				$delta = 0;
			}

			# I love modulo-math :)
			$years = (int)($delta / (3600 * 24 * 7 * 4 * 12));
			$delta -= $years *  (3600 * 24 * 7 * 4 * 12);
			$months = (int)($delta / (3600 * 24 * 7 * 4));
			$delta -= $months * (3600 * 24 * 7 * 4);
			$days = (int)($delta / (3600 * 24));
			$delta -= $days * (3600 * 24);
			$hours = (int)($delta / 3600);
			$minutes = (int) (($delta % 3600) / 60);
			$seconds = $delta % 60;

			$ago = "$hours:$minutes:$seconds ";
			$ago = sprintf("%dY %dM %dD %02d:%02d:%02d ", $years, $months, $days, $hours, $minutes, $seconds);

			$tmp["lastlog"] = "$ago ago";
			array_push($ret, $tmp);
		}				
		return $ret;
	}

	function getos($host) 
	{

		$ret = "";
		$this->db->cache_off();
		$res = $this->db->query("SELECT `value` AS ostype FROM machines WHERE `name`=? and `key`='ostype'", $host);
		$row = $res->row_array();
		$ostype = $row['ostype'];
		return $ostype;
	}
}
