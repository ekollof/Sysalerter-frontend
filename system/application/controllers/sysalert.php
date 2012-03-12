<?php

class Sysalert extends Controller
{
	function __construct()
	{
		parent::Controller();

		$this->load->scaffolding("status");
		$this->load->model("Status");
		$this->load->model("Groups");
		#$this->output->enable_profiler(TRUE);
	}

	function index()
	{
		$seen = array();
		$watch = array();
		$broken = array();
		$whatbroken = array();
		$users = array();
		$lastlogin = array();
		$debugtxt = "";
		$alert = 0;
		$hosts = $this->Status->get_hosts();

        if ($hosts == 0) {
            header("Content-Type: text/plain");
            print "No data.";
            die();
        }
		# Gather data
		foreach($hosts as $host) {
			#$seen[$host] = $this->Status->get_heartbeat($host);
			$watch[$host] = $this->Status->get_monitor_items($host);
			$users[$host] = $this->Status->get_users($host);
			$lastlog[$host] = $this->Status->lastlogin($host);
			$ostype[$host] = $this->Status->getos($host);

			$whatbroken[$host] = array();
			$ncpu[$host] = $this->Status->get_numcpus($host);

			foreach($watch[$host] as $item) {
				$is_alerted = $this->Status->is_broken($host, $item);
				$alert += $is_alerted;
				if ($is_alerted) {
					$whatbroken[$host][$item] = $alert;
				}
			}
			$broken[$host] = $alert;
			$alert = 0;

		}

		$data["ostype"] = $ostype;
		$data["ncpu"] = $ncpu;
		$data["hosts"] = $hosts;
		$data["seen"] = $seen;
		$data["watches"] = $watch;
		$data["alerts"] = $broken;
		$data["breakage"] = $whatbroken;
		$data["debug"] = $debugtxt;
		$data["users"] = $users;
		$data["lastlog"] = $lastlog;

		$this->load->view("sys_index", $data);	

	}

	function info($pass)
	{
		if ($pass != "lalala") {
			return;
		}
		phpinfo();
	}

	function deletehost($host)
	{
		$data["host"] = $host;
		$this->load->view("delete_warning", $data);
	}
	
	function reallydeletehost($host)
	{
		$this->Status->delete_host($host);
		header("Location: /index.php");
	}
		
	
	
}

