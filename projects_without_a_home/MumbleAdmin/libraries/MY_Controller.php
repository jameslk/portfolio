<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends Controller {
    public $layout_data = array();
    public $content_data = array();
    
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
    
    public function pre_call($method) {}
    
    public function view_layout($content_tpl, $layout_tpl, $content_var = 'content') {
        $content = $this->smarty->fetch($content_tpl, $this->content_data);
        
        $this->layout_data[$content_var] = $content;
        $this->smarty->view($layout_tpl, $this->layout_data);
    }
    
    public function get_server_name() {
        return $this->input->get('s');
    }
    
    public function make_uri($path) {
        if(!$this->input->get('s'))
            return $path;
        
        if($path == '/')
            $path = '';
        
        return 'server/'.$this->get_server_name().'/'.$path;
    }
    
    public function redirect($path) {
        redirect($this->make_uri($path));
    }
}