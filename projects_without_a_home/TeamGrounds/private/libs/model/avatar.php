<?php

Libs('model/entity_parent');

class M_Avatar extends M_EntityParent {
    public $table_name = 'tg_avatars';
	
	public $primary_key = 'avatar_id';
	
	public $fields = array(
        'parent_id',
        'parent_type'
	);
	
    public $data_validate = array(
        'parent_id' => FILTER_VALIDATE_INT,
        
        'parent_type' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^.+$/')
		)
	);
    
    public function FileID() {
        return base_convert($this->Id(), 10, 35);
    }
    
    public function Create() {
    	$insert_id = $this->Insert();

		if($insert_id === false)
            MakeError('Failed to create avatar', $this->data);

		$this->SetID($insert_id);

		AppLog::Report('Created avatar', $this->data);

		return $insert_id;
    }
    
    static public function GetDefaultPath($type, $size = '') {
        if($size)
            return sprintf('/images/default_avatars/%s_%s.jpg', $type, $size);
        else
            return sprintf('/images/default_avatars/%s.jpg', $type);
    }
    
    public function GetPath($size = '') {
        if($this->HasID()) {
            if($size)
                return sprintf('/images/avatars/%s_%s.jpg', $this->FileID(), $size);
            else
                return sprintf('/images/avatars/%s.jpg', $this->FileID());
        }
        else {
            return self::GetDefaultPath($this['parent_type'], $size);
        }
    }
    
    /* Hacks until late static binding is available */
    static public function GetByID($id, array $fields = array()) {
        return Model::GetByID($id, $fields, __CLASS__);
    }
    
    static public function GetBySearch(array $search_data, array $fields = array()) {
        return Model::GetBySearch($search_data, $fields, __CLASS__);
    }
    
    static public function GetByParent(Model $parent, array $fields = array()) {
        return M_EntityParent::GetByParent($parent, $fields, __CLASS__);
    }
}