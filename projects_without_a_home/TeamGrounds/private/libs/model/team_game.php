<?php

Libs('model/tag', 'model/user_game');

class M_TeamGame extends M_UserGame {
	public $table_name = 'tg_team_games';

	public $primary_key = 'tgame_id';
	
	public $fields = array(
        'team_id',
        'title',
        
        'list_order'
	);

	public $data_validate = array(
	    'tgame_id' => FILTER_VALIDATE_INT,
	    
	    'team_id' => FILTER_VALIDATE_INT,
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
	   'team_id'
	);
    
    static public function GetByTeam(M_Team $team, array $fields = array()) {
        return self::GetBySearch(array(
            $team->primary_key => $team->Id()
        ), $fields);
    }
    
    static public function GetArrayByTeam(M_Team $team, array $fields = array()) {
        return self::GetArrayBySearch(array(
            $team->primary_key => $team->Id()
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