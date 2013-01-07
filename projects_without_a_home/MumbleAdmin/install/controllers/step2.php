<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Step2 extends MY_Controller {
    public $install_key;
    
	public function Step2() {
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
	    $this->layout_data['step'] = 2;
	    $this->layout_data['step_title'] = 'Database Information';
	    $this->layout_data['can_continue'] = TRUE;
	    
	    if($this->input->post('continue')) {
	        if($this->input->post('success'))
	           $this->_proceed_to('/step3/');
	        else
	           $this->_process_form();
        }
        else if($this->input->post('overwrite_tables') || $this->input->post('try_drop')) {
            $this->_overwrite_tables();
        }
        else if($this->input->post('skip_tables') || $this->input->post('try_create')) {
            $this->_create_tables();
        }
	    
	    $this->view_layout('step2.tpl', 'layout.tpl');
    }
    
    public function _process_form() {
	    $validation_rules = array(
           array(
               'field' => 'db_hostname',
               'label' => 'Database Host Address',
               'rules' => 'required'
           ),
           
           array(
               'field' => 'db_port',
               'label' => 'Database Port',
               'rules' => 'integer'
           ),
           
           array(
               'field' => 'db_name',
               'label' => 'Database Name',
               'rules' => 'required|alpha_dash'
           ),
           
           array(
               'field' => 'db_username',
               'label' => 'Database Username',
               'rules' => 'required|alpha_dash'
           ),
           
           array(
               'field' => 'db_password',
               'label' => 'Database Password',
               'rules' => 'required'
           ),
           
           array(
               'field' => 'db_prefix',
               'label' => 'Database Table Prefix',
               'rules' => 'alpha_dash'
           ),
        );
        
        $this->form_validation->set_rules($validation_rules);
        
        if(!$this->form_validation->run()) {
            $this->layout_data['form_errors'] = validation_errors();
            return;
        }
        
        $success = $this->schema_mysql->connect(
            $this->input->post('db_hostname'),
            $this->input->post('db_port'),
            $this->input->post('db_name'),
            $this->input->post('db_username'),
            $this->input->post('db_password'),
            $this->input->post('db_prefix')
        );
        
        if(!$success) {
            $this->layout_data['form_errors'] = 'Failed to connect to database.
                <br />Database Error: '.$this->schema_mysql->error_message();
            
            return;
        }
        
        $this->_set_data('db_isset', TRUE);
        $this->_set_data('db_hostname', $this->input->post('db_hostname'));
        $this->_set_data('db_port', $this->input->post('db_port'));
        $this->_set_data('db_name', $this->input->post('db_name'));
        $this->_set_data('db_username', $this->input->post('db_username'));
        $this->_set_data('db_password', $this->input->post('db_password'));
        $this->_set_data('db_prefix', $this->input->post('db_prefix'));
        
        $this->_set_data('db_driver', 'mysql');
        
        if($this->schema_mysql->tables_exist()) {
            $this->content_data['subpage'] = 'overwrite_tables';
            $this->layout_data['can_continue'] = FALSE;
            return;
        }
        
        $this->_create_tables();
    }
    
    public function _create_tables() {
        if(!$this->schema_mysql->create_tables()) {
            $this->content_data['subpage'] = 'create_error';
            $this->content_data['db_error'] = $this->schema_mysql->error_message();
            $this->layout_data['can_continue'] = FALSE;
            return;
        }
        
        $this->content_data['subpage'] = 'success';
    }
    
    public function _overwrite_tables() {
        if(!$this->schema_mysql->drop_tables()) {
            $this->content_data['subpage'] = 'drop_error';
            $this->content_data['db_error'] = $this->schema_mysql->error_message();
            $this->layout_data['can_continue'] = FALSE;
            return;
        }
        
        $this->_create_tables();
    }
}
