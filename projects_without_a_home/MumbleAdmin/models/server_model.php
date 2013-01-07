<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Server_model extends DataMapper {
    public $model = 'server';
    public $table = 'servers';
    
    public $has_one = array(
        'murmur' => array('class' => 'Murmur_model')
    );
    
    public $has_many = array(
        'user' => array('class' => 'User_model')
    );
    
    public $validation = array(
        array(
            'field' => 'name',
            'label' => 'Server Name',
            'rules' => array(
                'required',
                'trim',
                'alpha_dash',
                'min_length' => 3
            )
        ),
        
        array(
            'field' => 'title',
            'label' => 'Server Title',
            'rules' => array(
                'trim',
                'xss_clean',
            )
        ),
        
        array(
            'field' => 'murmur',
            'label' => 'Murmur',
            'rules' => array('required')
        )
    );
    
    public function Server_model() {
        parent::DataMapper();
    }
    
    public function delete() {
        /* Delete user */
        $user = new User_model;
        
        $user->where_related_server('id', $this->id);
        $user->get();
        $user->delete_all();
        
        /* Delete server */
        parent::delete();
    }
}