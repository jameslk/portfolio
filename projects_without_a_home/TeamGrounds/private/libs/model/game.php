<?php

Libs('model/tg');

class M_Game extends M_Entity {
	public $table_name = 'tg_games';

	public $primary_key = 'game_id';
	
	public $fields = array(
	);

	public $data_validate = array(
	);

	public $data_sanitize = array(
	);
	
	public $tag_field = 'game';

    /* Hacks until late static binding is available */
    static public function GetByID($id, array $fields = array()) {
        return Model::GetByID($id, $fields, __CLASS__);
    }

    static public function GetBySearch(array $search_data, array $fields = array()) {
        return Model::GetBySearch($search_data, $fields, __CLASS__);
    }
}