<?php

Libs('model/list_order');

class M_TeamMember extends M_ListOrder {
    public $table_name = 'tg_team_members';

	public $primary_key = 'tmember_id';
	
	public $fields = array(
        'team_id',
        'user_id',
        
        'team_role',
        
        'access_flags',
        
        'request_status',
        
        'list_order'
	);

	public $data_validate = array(
	    'tmember_id' => FILTER_VALIDATE_INT,
	    
	    'team_id' => FILTER_VALIDATE_INT,
	    'user_id' => FILTER_VALIDATE_INT,
	    
	    'access_flags' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^$|^((owner|admin|edit_profile|edit_members|edit_media|edit_events|edit_news),?)+$/')
		),
        
        'request_status' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^$|^(join|recruit)$/')
		)
	);
	
	public $data_sanitize = array(
	    'team_role' => FILTER_SANITIZE_STRING,
	    
        'list_order' => FILTER_SANITIZE_NUMBER_INT
	);
	
	public function IsOwner() {
	    $this->AssertID();
	    
	    return $this->IsInSet('access_flags', 'owner');
    }
    
    public function IsAdmin() {
	    $this->AssertID();
	    
	    return $this->IsInSet('access_flags', 'admin');
    }
    
    public function CheckAccess($flag) {
        if($this->IsInSet('access_flags', $flag))
            return true;
        else if($this->IsOwner() || $this->IsAdmin())
            return true;
        else
            return false;
    }
    
    /* Hacks until late static binding is available */
    static public function GetByID($id, array $fields = array()) {
        return Model::GetByID($id, $fields, __CLASS__);
    }

    static public function GetBySearch(array $search_data, array $fields = array()) {
        return Model::GetBySearch($search_data, $fields, __CLASS__);
    }
    
    static public function GetCountBySearch(array $search_data) {
        return Model::GetCountBySearch($search_data, __CLASS__);
    }
}