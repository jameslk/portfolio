<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth {
    public $CI;
    
    public $user_model;
    public $autologin_model;
    
    public $autologin_key_expire;
    
    public $autologin_cookie;
    public $autologin_cookie_expire;
    
    public function Auth() {
        $this->CI =& get_instance();
        
        $this->CI->load->library('session');
        $this->CI->load->helper('cookie');
        
        $this->CI->config->load('Auth');
        
        $this->autologin_key_expire = $this->CI->config->item('auth_autologin_key_expire');
        
        $this->autologin_cookie = $this->CI->config->item('auth_autologin_cookie');
        $this->autologin_cookie_expire = $this->CI->config->item('auth_autologin_cookie_expire');
        
        $this->load_auth();
    }
    
    public function is_authed() {
        if($this->user_model && isset($this->user_model->id))
            return TRUE;
        else
            return FALSE;
    }
    
    public function is_persistent() {
        if($this->autologin_model && isset($this->user_model->id))
            return TRUE;
        else
            return FALSE;
    }

    public function set_autologin_cookie($login_key) {
        if($this->is_persistent())
            set_cookie($this->autologin_cookie, $login_key, $this->autologin_cookie_expire);
    }
    
    protected function is_autologin_expired() {
        if($this->is_persistent()) {
            $limit = $this->autologin_model->start_time+$this->autologin_key_expire;
            
            if(time() >= $limit)
                return TRUE;
            else
                return FALSE;
        }
    }
    
    public function load_auth() {
        if($this->is_authed())
            return;
        
        if(!$this->CI->session->userdata('user_persistent') && $this->CI->session->userdata('user_id')) {
            $user_id = $this->CI->session->userdata('user_id');
            
            $user_model = new User_model;
            $user_model->get_by_id($user_id);
            
            if(!$user_model->exists()) {
                make_warning('Unable to lookup user from session data', $user_id);
                return;
            }
            
            $this->user_model = $user_model;
        }
        else if($autologin_key = get_cookie($this->autologin_cookie)) {
            $autologin_model = new User_autologin_model;
            $autologin_model->get_by_login_key($autologin_key);
            
            if(!$autologin_model->exists()) {
                make_warning('Invalid autologin key supplied', $autologin_key);
                return;
            }
            
            $user_model = $autologin_model->user->get();
            
            if(!$user_model->exists()) {
                make_warning('Unable to lookup user from autologin', $autologin_model->get_data());
                return;
            }
            
            $this->user_model = $user_model;
            $this->autologin_model = $autologin_model;
            
            if($this->is_autologin_expired()) {
                $this->autologin_model->refresh();
                $this->set_autologin_cookie($this->autologin_model->login_key);
            }
        }
        else {
            return; //no session found
        }
    }
    
    public function create_auth(User_model $user_model, $persistent = false) {
        if($this->is_authed()) {
            make_warning('A user auth is already active', $user_model->get_data());
            return;
        }
        
        $this->user_model = $user_model;
        
        $this->CI->session->set_userdata('user_persistent', (bool)$persistent);
        
        if($persistent) {
            $autologin_model = new User_autologin_model;
            $autologin_model->get_by_user_id($user_model->id);
            
            $this->autologin_model = $autologin_model;
            
            if(!$this->autologin_model->exists()) {
                $this->autologin_model->user_id = $user_model->id;
                $this->autologin_model->login_key = $this->autologin_model->generate_key();
                $this->autologin_model->start_time = time();
    
                $this->autologin_model->save($user_model)
                    or make_warning('Validation failed for autologin_model', $autologin_model->errors->all);
            }
            else if($this->is_autologin_expired()) {
                $this->autologin_model->refresh();
            }
            
            $this->set_autologin_cookie($this->autologin_model->login_key);
        }
        else {
            $this->CI->session->set_userdata('user_id', $user_model->id);
        }
    }
    
    public function delete_auth() {
        if(!$this->is_authed())
            return;
        
        if($this->is_persistent())
            delete_cookie($this->autologin_cookie);
        
        $this->CI->session->unset_userdata('user_persistent');
        $this->CI->session->unset_userdata('user_id');
        
        $this->user_model = NULL;
        $this->autologin_model = NULL;
        $this->persistent = FALSE;
    }
}