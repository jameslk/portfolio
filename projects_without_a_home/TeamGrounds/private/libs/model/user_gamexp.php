<?php

Libs('model/tag', 'model/list_order');

class M_UserGamexp extends M_ListOrder {
	public $table_name = 'tg_user_gamexp';

	public $primary_key = 'gamexp_id';

	public $fields = array(
	    'gamexp_id',
        'ugame_id',

        'title',
        'content',

        'list_order'
	);

	public $data_validate = array(
	    'gamexp_id' => FILTER_VALIDATE_INT,
        'ugame_id' => FILTER_VALIDATE_INT,

        'title' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/(?!^\s*$)^.{2,255}$/')
		),
        'content' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/(?!^\s*$)^.{1,4096}$/m')
		)
	);

	public $data_sanitize = array(
        'list_order' => FILTER_SANITIZE_NUMBER_INT
	);

	public $list_dependency_fields = array(
	   'ugame_id'
	);

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