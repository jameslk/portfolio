<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth_Interface extends MY_Controller {
    public $server_model;
    
	public function Auth_Interface() {
		parent::MY_Controller();
	}
	
	public function login() {
	    $this->layout_data['title'] = 'MumbleAdmin Global Admin - Log-in';
	    
	    if($this->auth->is_authed())
            redirect('/global_admin/');
        
        $this->content_data['form_action'] = site_url('global_admin/login/');
        
	    if(!$this->input->post('login')) {
	        $this->view_layout('login.tpl', 'layout.tpl');
	        return;
        }
        
	    $this->content_data = array_merge($this->content_data, $_POST);
	    $this->content_data['form_errors'] = array();
	    
	    $user_model = new User_model;
	    
	    $user_model->username = $this->input->post('email');
	    $user_model->password = $this->input->post('password');
	    
	    $user_model->validate()->get();
	    
	    if(!$user_model->exists()) {
	        $this->content_data['form_errors'][] = 'E-mail or password invalid';
	        $this->view_layout('login.tpl', 'layout.tpl');
	        return;
        }
        
        $this->auth->create_auth($user_model, $this->input->post('persistent'));
        
        redirect('/global_admin/');
	}
	
	public function logout() {
	    $this->auth->delete_auth();
	    redirect('/global_admin/');
    }
}
