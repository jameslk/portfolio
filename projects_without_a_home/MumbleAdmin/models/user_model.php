<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends DataMapper {
    public $model = 'user';
    public $table = 'users';
    
    public $has_one = array(
        'user_autologin' => array('class' => 'User_autologin_model'),
    );
    
    public $has_many = array(
        'server' => array('class' => 'Server_model'),
    );
    
    public $validation = array(
        array(
            'field' => 'email',
            'label' => 'E-mail',
            'rules' => array(
                'required',
                'trim',
                'xss_clean',
                'valid_email',
                'unique',
            )
        ),
        
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => array(
                'required',
                'trim',
                'min_length' => 3,
                'md5'
            )
        ),
        
        array(
            'field' => 'server',
            'label' => 'Server',
            'rules' => array('required')
        )
    );
    
    public function User_model() {
        parent::DataMapper();
    }
    
    public function where_password($password) {
        return $this->where('password', md5(trim($password)));
    }
}