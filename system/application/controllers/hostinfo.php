<?php

class Hostinfo extends Controller {

	function __construct() {
		parent::Controller();

		#$this->load->scaffolding("status");
		$this->load->model("Status");
		#$this->output->enable_profiler(TRUE);
	}

	function machine($host) {

		$userinfo = array();
		$monitored = $this->Status->get_monitor_items($host);
		$users = $this->Status->get_users($host);
		foreach ($users as $user) {
			$userinfo[$user] = $this->Status->user_info($user);
		}
		if (count($userinfo) > 0) {
			$data["userinfo"] = $userinfo;
		}
		$period = $this->input->post('period');
		if (!$period) {
			$period = "hour";
		}
		$data["seen"] = $this->Status->get_heartbeat($host);
		$data["period"] = $period;
		$data["ncpu"] = $this->Status->get_numcpus($host);
		$data["host"] = $host;
		$data["ipnum"] = $this->Status->get_ip_num($host);
		$data["items"] = $monitored;
		$data["disks"] = $this->Status->get_disks($host);
		$this->load->view("hostinfo", $data);
	}

}
