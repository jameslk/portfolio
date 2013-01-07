<?php

Libs('model/tg', 'model/avatar', 'model/friendship', 'model/user_profile');

class M_Entity extends M_Tg {
    public $entity_type;
    
	public $fields = array(
		'profile_key'
	);

	public $data_validate = array(
		'profile_key' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/(?!^[0-9]*$|-|_)^[a-zA-Z0-9-_]{4,100}$|^$/')
		)
	);

	public $data_sanitize = array(
	);
	
	public $related_models = array(
        'avatar' => array(
            'lib' => 'model/avatar',
            'model' => 'M_Avatar',
            
            'relationship' => self::HAS_ONE
        )
	);
	
	protected function GetModelSearchData($alias, $model) {
	    if($alias == 'avatar') {
            return array(
                'foreign_keys' => array($this->primary_key => 'parent_id'),
                'where' => "parent_type = '$this->entity_type'"
            );
        }
	}

    public function CheckProfileKeyExists($profile_key) {
        if($profile_key === '')
            return false;
        
        $id_check = '';
        if($this->HasID())
            $id_check = $this->db->sqlprintf("AND $this->primary_key != %s",
                $this->Id());
        
        $result = $this->db->query("
            SELECT profile_key
            FROM $this->table_name
            WHERE profile_key = %s $id_check
            LIMIT 1
        ", $profile_key);
        
        if($result && $result->num_rows) {
            $result->close();
            return true;
        }
        
        return false;
    }
    
    public function GetProfileURI() {
        $this->AssertID();
        
        $uri = "/$this->entity_type/";
        
        if($this['profile_key'])
            return $uri.$this['profile_key'];
        else
            return $uri.$this->Id();
    }
    
    public function GetAvatarPath($size = '') {
        if($this->avatar)
            return $this->avatar->GetPath($size);
        else
            return M_Avatar::GetDefaultPath($this->entity_type, $size);
    }

    /* Hacks until late static binding is available */
    static public function GetByID($id, array $fields = array()) {
        return Model::GetByID($id, $fields, __CLASS__);
    }

    static public function GetBySearch(array $search_data, array $fields = array()) {
        return Model::GetBySearch($search_data, $fields, __CLASS__);
    }
}