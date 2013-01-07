<?php

Libs('model/tg', 'model/tag');

class M_UserProfile extends M_Tg {
	public $table_name = 'tg_user_profiles';

	public $primary_key = 'uprofile_id';

	public $fields = array(
        'user_id',

        'summary',

        'country',
        'location',
        'gender',
        'interests',
    	'website',

    	'msn',
    	'aim',
    	'yahoo',
    	'icq',
    	'skype',
    	'irc_channel',
    	'irc_network',

    	'show_age',

    	'show_personal',
    	'allow_contact'
	);

	public $data_validate = array(
        'user_id' => FILTER_VALIDATE_INT,

        'summary' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^.{0,4096}$/m')
		),

		'country' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^$|^[A-Z]{2}$/')
		),
		'gender' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^$|^(male|female)$/')
		),

		'show_personal' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^$|^((friends|teams|groups|anyone),?)+$/')
		),
		'allow_contact' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^$|^((friends|teams|groups|anyone),?)+$/')
		),

		'irc_channel' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^$|^#[^\s]+$/')
		),
		'irc_network' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^$|^([a-zA-Z0-9][-a-zA-Z0-9]*[a-zA-Z0-9]\.)+([a-zA-Z0-9]{3,5})$/')
		)
	);

	public $data_sanitize = array(
		'summary' => array(
			'filter' => FILTER_CALLBACK,
			'options' => array(M_Tg, SanitizeText)
		),

        'location' => FILTER_SANITIZE_STRING,
        'interests' => FILTER_SANITIZE_STRING,

    	'website' => FILTER_SANITIZE_URL,

    	'msn' => FILTER_SANITIZE_EMAIL,
		'aim' => FILTER_SANITIZE_STRING,
		'yahoo' => FILTER_SANITIZE_EMAIL,
		'icq' => FILTER_SANITIZE_NUMBER_INT,
		'skype' => FILTER_SANITIZE_STRING,

		'show_age' => FILTER_SANITIZE_NUMBER_INT
	);

    static public function GetByUser(M_User $user, array $fields = array()) {
        return self::GetBySearch(array(
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
}