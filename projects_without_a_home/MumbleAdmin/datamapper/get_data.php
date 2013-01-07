<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class DMZ_Get_Data {
    public function get_data($object) {
        $data = array();
        foreach($object->fields as $field)
            $data[$field] = $object->{$field};
        
        return $data;
    }
}