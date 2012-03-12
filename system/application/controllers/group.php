<?php

class Group extends Controller {
	
	function __construct() {
		parent::Controller();
		$this->load->model("Status");
		$this->load->model("Groups");
	}
	
	function index() {
		
	}
}