<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class DMZ_Validate_Nonrelated {
    public function validate_nonrelated($object) {
        /* Find all related fields from model */
        if(!empty($object->has_one))
            $related_fields = array_keys($object->has_one);
        else
            $related_fields = array();
        
        if(!empty($object->has_many))
            $related_fields = array_merge($related_fields, array_keys($object->has_many));
        
        $validation_nonrelated = $object->validation;
        
        /* Remove related fields from validation rules */
        foreach($validation_nonrelated as $key => $rule) {
            foreach($related_fields as $field) {
                if($rule['field'] == $field)
                    unset($validation_nonrelated[$key]);
            }
        }
        
        /* Validate with non-related fields only */
        $old_validation =& $object->validation;
        $object->validation = $validation_nonrelated;
        
        $object->validate();
        
        $object->validation =& $old_validation;
        
        return $object;
    }
}