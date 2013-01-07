<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Step4 extends MY_Controller {
    public $install_key;
    
	public function Step4() {
		parent::MY_Controller();
		
		$this->load->library('form_validation');
	}
	
	public function index() {
	    $this->layout_data['step'] = 4;
	    $this->layout_data['step_title'] = 'Configuration Settings';
	    $this->layout_data['can_continue'] = TRUE;
	    
	    if(!isset($this->content_data['site_url']))
	       $this->content_data['site_url'] = $this->config->item('root_url');
	    
	    if($this->input->post('continue')) {
            if($this->input->post('success')) {
                $this->_clear_cache_dir();
                $this->output->set_header('Location: '.$this->_get_data('site_url').'global_admin');
                return;
            }
            else {
                $this->_process_form();
            }
        }
	    
	    $this->view_layout('step4.tpl', 'layout.tpl');
    }
    
    public function _process_form() {
	    $validation_rules = array(
            array(
                'field' => 'site_url',
                'label' => 'MumbleAdmin URL',
                'rules' => 'required'
            ),
            
            array(
                'field' => 'murmur_ice_hostname',
                'label' => 'Murmur Ice Hostname',
                'rules' => 'required'
            ),
            
            array(
                'field' => 'murmur_ice_port',
                'label' => 'Murmur Ice Port',
                'rules' => 'required|integer'
            ),
        );
        
        $this->form_validation->set_rules($validation_rules);
        
        if(!$this->form_validation->run()) {
            $this->layout_data['form_errors'] = validation_errors();
            return;
        }
        
        $site_url = $this->input->post('site_url');
        if(substr($site_url, -1) != '/')
            $site_url .= '/';
        
        $this->_set_data('site_url', $site_url);
        
        $this->_set_data('murmur_ice_hostname', $this->input->post('murmur_ice_hostname'));
        $this->_set_data('murmur_ice_port', $this->input->post('murmur_ice_port'));
        
        $this->_create_configs();
        
        $this->content_data['subpage'] = 'success';
    }
    
    public function _create_configs() {
        $this->_write_config('config', array(
            'site_url' => $this->_get_data('site_url'),
            'db_prefix' => $this->_get_data('db_prefix'),
        ));
        
        $this->_write_config('database', array(
            'db_hostname' => $this->_get_data('db_hostname'),
            'db_username' => $this->_get_data('db_username'),
            'db_password' => $this->_get_data('db_password'),
            'db_name' => $this->_get_data('db_name'),
            'db_driver' => $this->_get_data('db_driver'),
        ));
        
        $this->_write_config('datamapper', array(
            'db_prefix' => $this->_get_data('db_prefix'),
        ));
        
        $this->_write_config('Murmur', array(
            'murmur_ice_hostname' => $this->_get_data('murmur_ice_hostname'),
            'murmur_ice_port' => $this->_get_data('murmur_ice_port'),
        ));
    }
    
    public function _write_config($name, $data) {
        $config = $this->smarty->fetch('config_templates/'.$name.'.tpl', $data);
        
        file_put_contents(APPPATH.'../config/'.$name.'.php', $config);
    }
    
    public function _clear_cache_dir() {
        $files = scandir(BASEPATH.'cache');
        
        foreach($files as $file) {
            $file = BASEPATH.'cache/'.$file;
            if(is_file($file))
                unlink($file);
        }
    }
}
