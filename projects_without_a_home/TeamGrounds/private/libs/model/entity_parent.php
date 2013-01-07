<?php

Libs('model/tg', 'model/entity');

class M_EntityParent extends M_Tg {
    public $fields = array(
        'parent_id',
        'parent_type'
	);
	
	public $data_validate = array(
	    'parent_id' => FILTER_VALIDATE_INT,
	    'parent_type' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^.{1,16}$/')
		)
	);
	
	public function SetParent(Model $parent) {
        $parent->AssertID();
        
        $this->SetFields(array(
            'parent_id' => $parent->Id(),
            'parent_type' => $parent->entity_type), $fail_data)
                or MakeError('Failed to set parent', $fail_data);
    }
	
    static public function GetByParent(M_Entity $parent, array $fields = array(), $__CLASS__ = __CLASS__) {
        $parent->AssertID();
        
        return self::GetBySearch(array(
            'parent_id' => $parent->Id(),
            'parent_type' => $parent->entity_type
        ), $fields, $__CLASS__);
    }
    
    static public function GetCountByParent(M_Entity $parent, $__CLASS__ = __CLASS__) {
        $parent->AssertID();
        
        return self::GetCountBySearch(array(
            'parent_id' => $parent->Id(),
            'parent_type' => $parent->entity_type
        ), $__CLASS__);
    }
    
    static public function GetArrayByParent(M_Entity $parent, $limit = false, array $fields = array(), $__CLASS__ = __CLASS__) {
        $parent->AssertID();
        
        return self::GetArrayBySearch(array(
            'parent_id' => $parent->Id(),
            'parent_type' => $parent->entity_type
        ), $limit, $fields, $__CLASS__);
    }
}