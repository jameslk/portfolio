<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Step3 extends MY_Controller {
    public $install_key;
    
	public function Step3() {
		parent::MY_Controller();
		
		$this->load->library('form_validation');
		
		$this->load->model('schema_mysql');
		if($this->_get_data('db_isset')) {
            $this->schema_mysql->connect(
                $this->_get_data('db_hostname'),
                $this->_get_data('db_port'),
                $this->_get_data('db_name'),
                $this->_get_data('db_username'),
                $this->_get_data('db_password'),
                $this->_get_data('db_prefix')
            );
        }
	}
	
	public function index() {
	    $this->layout_data['step'] = 3;
	    $this->layout_data['step_title'] = 'Root Admin Information';
	    $this->layout_data['can_continue'] = TRUE;
	    
	    if($this->input->post('continue')) {
            if($this->input->post('success'))
                $this->_proceed_to('/step4/');
            else
                $this->_process_form();
        }
	    
	    $this->view_layout('step3.tpl', 'layout.tpl');
    }
    
    public function _process_form() {
	    $validation_rules = array(
            array(
                'field' => 'admin_email',
                'label' => 'Admin E-mail',
                'rules' => 'required|xss_clean|valid_email'
            ),
            
            array(
                'field' => 'admin_password',
                'label' => 'Admin Password',
                'rules' => 'required|min_length[3]|matches[password_confirm]'
            ),
            
            array(
                'field' => 'password_confirm',
                'label' => 'Password Confirmation',
                'rules' => 'required'
            ),
        );
        
        $this->form_validation->set_rules($validation_rules);
        
        if(!$this->form_validation->run()) {
            $this->layout_data['form_errors'] = validation_errors();
            return;
        }
        
        //todo: Encrypt password
        
        $success = $this->schema_mysql->create_root_admin(
            $this->input->post('admin_email'),
            md5($this->input->post('admin_password'))
        );
        
        if(!$success) {
            $this->layout_data['form_errors'] = 'Failed to create root admin user.
                <br />Database Error: '.$this->schema_mysql->error_message();
            
            return;
        }
        
        $this->content_data['subpage'] = 'success';
    }
}
