<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_autologin_model extends DataMapper {
    public $model = 'user_autologin';
    public $table = 'user_autologins';
    
    public $has_one = array(
        'user' => array('class' => 'User_model')
    );
    
    public $validation = array(
        array(
            'field' => 'login_key',
            'label' => 'Autologin Key',
            'rules' => array(
                'required',
                'unique',
                'min_length' => 32
            )
        ),
        
        array(
            'field' => 'user',
            'label' => 'User',
            'rules' => array('required')
        )
    );
    
    public function User_autologin_model() {
        parent::DataMapper();
    }
    
    public function generate_key() {
        $count = 5; //max number of tries

        while($count--) {
            $key = md5(uniqid(rand().get_cookie($this->config->item('sess_cookie_name')), true));

            $model_class = get_class($this);
            $model = new $model_class;
            $model->get_by_login_key($key);

            if(!$model->exists())
                return $key;
        }

        make_error('Failed to generate a unique autologin key');
    }
    
    public function refresh() {
        $this->login_key = $this->generate_key();
        $this->start_time = time();
        
        $this->save();
    }
}