<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Murmur_model extends DataMapper {
    public $model = 'murmur';
    public $table = 'murmurs';
    
    public $has_many = array(
        'server' => array('class' => 'Server_model')
    );
    
    public function Murmur_model() {
        parent::DataMapper();
    }
}