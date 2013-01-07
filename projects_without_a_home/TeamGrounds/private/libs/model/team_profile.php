<?php

Libs('model/tg', 'model/tag');

class M_TeamProfile extends M_Tg {
	public $table_name = 'tg_team_profiles';

	public $primary_key = 'tprofile_id';

	public $fields = array(
        'team_id',
        
        'game_tag',
    	
    	'team_type',
    	
    	'location',
    	'website',
    	
    	'summary'
	);

	public $data_validate = array(
        'tprofile_id' => FILTER_VALIDATE_INT,
        'team_id' => FILTER_VALIDATE_INT,
        
        'team_type' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^(clan|guild)$/')
		),
    	
    	'summary' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^.{0,4096}$/m')
		)
	);

	public $data_sanitize = array(
        'location' => FILTER_SANITIZE_STRING,
    	'website' => FILTER_SANITIZE_URL,
    	
    	'summary' => array(
			'filter' => FILTER_CALLBACK,
			'options' => array(M_Tg, SanitizeText)
		)
	);

    static public function GetByTeam(M_Team $team, array $fields = array()) {
        return self::GetBySearch(array(
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
}