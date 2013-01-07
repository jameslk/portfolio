<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Step1 extends MY_Controller {
	public function Step1() {
		parent::MY_Controller();
	}
	
	public function index() {
	    $this->layout_data['step'] = 1;
	    $this->layout_data['step_title'] = 'Permissions and Settings Check';
	    
	    $this->layout_data['can_continue'] = true;
	    
	    if($this->input->post('continue'))
	       $this->_proceed_to('/step2/');
	    
	    $this->view_layout('step1.tpl', 'layout.tpl');
    }
}
