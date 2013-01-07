<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends Controller {
    public $layout_data = array();
    public $content_data = array();
    
    public $install_key;
    
    public function MY_Controller() {
		parent::Controller();
		
		$this->load->library('session');
		
		$this->load->helper('url');
		
		$this->config->load('install_defaults');
	}
    
    public function _remap($method) {
        // is_callable() returns TRUE on some versions of PHP 5 for private and protected
		// methods, so we'll use this workaround for consistent behavior
		$class = get_class($this);
		if ( ! in_array(strtolower($method), array_map('strtolower', get_class_methods($this))))
		{
			show_404("{$class}/{$method}");
		}

		// Call the requested method.
		// Any URI segments present (besides the class/function) will be passed to the method for convenience
		$args = array_slice($this->uri->rsegment_array(), 2);
		
		/* Call the pre_call method first */
		$continue = call_user_func_array(array(&$this, 'pre_call'), array_merge((array)$method, $args));
		
		/* Then call the requested method if pre_call returned true */
		if($continue !== FALSE)
		  call_user_func_array(array(&$this, $method), $args);
    }
    
    public function view_layout($content_tpl, $layout_tpl, $content_var = 'content') {
        $content = $this->smarty->fetch($content_tpl, $this->content_data);
        
        $this->layout_data[$content_var] = $content;
        $this->smarty->view($layout_tpl, $this->layout_data);
    }
    
    public function _get_data($key) {
        $data = $this->session->userdata('install_data');
        if(is_array($data) && isset($data[$key]))
            return $data[$key];
        else
            return FALSE;
    }
    
    public function _set_data($key, $value) {
        $data = $this->session->userdata('install_data');
        if(!is_array($data))
            $data = array();
        
        $data[$key] = $value;
        $this->session->set_userdata('install_data', $data);
    }
    
    public function _new_install() {
        $this->session->set_userdata('install_data', array());
        
	    $this->install_key = uniqid();
	    $this->_set_data('install_key', $this->install_key);
    }
	
	public function pre_call() {
	    $this->layout_data['total_steps'] = 4;
	    
	    $this->layout_data['form_action'] = $_SERVER['REQUEST_URI'];
	    
	    $this->content_data = array_merge(
            $this->content_data,
            (array)$this->config->item('install_defaults'),
            $_POST
        );
	    
	    if($this->input->post('install_key'))
	       $this->install_key = $this->input->post('install_key');
	    else
	       $this->install_key = $this->session->flashdata('install_key');
	    
	    if(!$this->install_key || $this->install_key != $this->_get_data('install_key')) {
    	    if(!$this->uri->rsegment(1) || $this->uri->rsegment(1) == 'step1')
    	        $this->_new_install();
            else
                redirect('/step1/');
        }
        
        $this->layout_data['install_key'] = $this->install_key;
    }
    
    public function _proceed_to($step) {
        $this->session->set_flashdata('install_key', $this->install_key);
        redirect($step);
    }
}