<?php

Libs('model/tag', 'model/list_order');

class M_UserGame extends M_ListOrder {
	public $table_name = 'tg_user_games';

	public $primary_key = 'ugame_id';
	
	public $fields = array(
        'user_id',
        'title',
        
        'list_order'
	);

	public $data_validate = array(
	    'ugame_id' => FILTER_VALIDATE_INT,
	    
	    'user_id' => FILTER_VALIDATE_INT,
	    'title' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/(?!^\s*$)^.{3,64}$/')
		)
	);

	public $data_sanitize = array(
        'title' => FILTER_SANITIZE_STRING,
        
        'list_order' => FILTER_SANITIZE_NUMBER_INT
	);
	
	public $list_dependency_fields = array(
	   'user_id'
	);
	
	public $tag_field = 'game';
    
    protected function UpdateTag() {
        $tag = M_Tag::GetBySearch(array(
            'title' => $this['title'],
            'field' => $this->tag_field
        ), array('tag_id'));
        
        if($tag) {
            $tag->UpPopularity();
        }
        else {
            $data = array(
                'title' => $this['title'],
                'field' => $this->tag_field
            );
            
            $tag = new M_Tag;
            if($tag->SetFields($data, $fail_data))
                $tag->Create();
            else
                MakeWarning('Failed to set tag data', $data);
        }
    }
    
    public function Create() {
        parent::Create();
        
        $this->UpdateTag();
    }
    
    public function Delete() {
        parent::Delete();
        
        $tag = M_Tag::GetBySearch(array(
            'title' => $this['title'],
            'field' => $this->tag_field
        ), array('tag_id'));
        
        if($tag)
            $tag->DownPopularity();
    }
    
    static public function GetByUser(M_User $user, array $fields = array()) {
        return self::GetBySearch(array(
            $user->primary_key => $user->Id()
        ), $fields);
    }
    
    static public function GetArrayByUser(M_User $user, array $fields = array()) {
        return self::GetArrayBySearch(array(
            $user->primary_key => $user->Id()
        ), $fields);
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
    
    static public function GetArrayBySearch(array $search_data, $limit = false, array $fields = array()) {
        return M_ListOrder::GetArrayBySearch($search_data, $limit, $fields, __CLASS__);
    }
}