<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends MY_Controller {
    public $server_model;
    public $murmur_server;
    
    public $server_name;
    
	public function Dashboard() {
		parent::MY_Controller();
		
		//$this->output->enable_profiler(TRUE);
		
		$this->load->library('murmur');
		$this->load->helper('date');
		
		$this->layout_data['title'] = 'mymumble - dashboard';
	}
	
	public function pre_call($method) {
        if(!$this->auth->is_authed())
            $this->redirect('/login');
        
        if(!$this->get_server_name())
            $this->redirect('/select_server');
        
        $this->server_model = new Server_model;
	    $this->server_model->get_by_name($this->get_server_name());
	    if(!$this->server_model->exists()) {
	        show_error('Invalid server name specified');
	        return FALSE;
        }
        
        $this->murmur_server = $this->murmur->get_server($this->server_model->virtual_id);
        if(!$this->murmur_server) {
            make_error('No murmur server was found', $this->server_model->virtual_id);
            return FALSE;
        }
        
        $this->content_data['server_name'] = $this->input->get('s');
    }
	
	public function index() {
	    $server = $this->server_model->get_data();
	    
        $server['status'] = $this->murmur_server->is_running();
        $server['location'] = $this->server_model->murmur->get()->location;
        $server['slots'] = $this->murmur_server->get_config_item('users');
        
        if($this->murmur_server->is_running()) {
            $server['users'] = count($this->murmur_server->get_users());
            $server['uptime'] = timespan(0, $this->murmur_server->get_uptime());
        }
        else {
            $server['users'] = '-';
            $server['uptime'] = '-';
        }
	    
	    $this->view_layout('dashboard.tpl', 'layout.tpl');
	}
	
	public function select_server() {
	    $this->view_layout('select_server.tpl', 'layout.tpl');
	}
	
	public function start_server() {
	    $this->murmur_server->start();
	    
	    $this->redirect('/');
    }
    
    public function stop_server() {
        $this->murmur_server->stop();
        
        $this->redirect('/');
    }
}
