<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends MY_Controller {
    public $server_model;
    public $murmur_server;
    
	public function Dashboard() {
		parent::MY_Controller();
		
		//$this->output->enable_profiler(TRUE);
		
		$this->load->library('murmur');
		$this->load->helper('date');
		
		$this->layout_data['title'] = 'MumbleAdmin Global Admin - Dashboard';
	}
	
	public function pre_call($method) {
        if(!$this->auth->is_authed())
            redirect('/global_admin/login');
    }
	
	public function index() {
	    $this->view_layout('global_admin/dashboard.tpl', 'global_admin/layout.tpl');
	}
}
