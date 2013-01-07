<?php

Libs('model/entity', 'model/avatar', 'model/friendship', 'model/user_profile');

class M_User extends M_Entity {
    public $entity_type = 'user';
    
	public $table_name = 'tg_users';

	public $primary_key = 'user_id';
	
	public $fields = array(
        'email',
		'profile_key',

		'password',

		'displayname',
		
		'realname',

		'timezone',
		'birthday',
		'birthday_search',
		'languageid',

		'joindate',
		'lastseen',
		'ipaddress'
	);

	public $data_validate = array(
	    'user_id' => FILTER_VALIDATE_INT,

		'email' => FILTER_VALIDATE_EMAIL,
		'profile_key' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/(?!^[0-9]*$|-|_)^[a-zA-Z0-9-_]{4,100}$|^$/')
		),

		'password' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/(?!^\s*$)^.{4,32}$/')
		),

		'displayname' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/(?!^\s*$)^.{2,255}$/')
		)
	);

	public $data_sanitize = array(
	    'password' => array(
			'filter' => FILTER_CALLBACK,
			'options' => 'md5'
		),
	    
	    'realname' => FILTER_SANITIZE_STRING,

		'timezone' => FILTER_SANITIZE_NUMBER_INT,
		'birthday' => FILTER_SANITIZE_NUMBER_INT,
		'languageid' => FILTER_SANITIZE_NUMBER_INT,

		'joindate' => FILTER_SANITIZE_NUMBER_INT,
		'lastseen' => FILTER_SANITIZE_NUMBER_INT,
		'ipaddress' => FILTER_SANITIZE_STRING
	);
	
	public $related_models = array(
        'avatar' => array(
            'lib' => 'model/avatar',
            'model' => 'M_Avatar',
            
            'relationship' => self::HAS_ONE
        ),
        
        'profile' => array(
            'lib' => 'model/user_profile',
            'model' => 'M_UserProfile',
            
            'relationship' => self::HAS_ONE,
            
            'search_data' => array(
                'foreign_keys' => 'user_id'
            )
        ),
        
        'games' => array(
            'lib' => 'model/user_game',
            'model' => 'M_UserGame',
            
            'relationship' => self::HAS_MANY,
            
            'search_data' => array(
                'foreign_keys' => 'user_id'
            )
        )
	);
	
	public $friends = false;

    public function Create() {
        $insert_id = parent::Create();
        
        /* Create user profile */
        $user_profile = new M_UserProfile;
        $user_profile->SetField($this->primary_key, $this->Id());
        $user_profile->Create();
        
		return $insert_id;
    }
    
    public function CheckEmailExists($email) {
        $id_check = '';
        if($this->HasID())
            $id_check = $this->db->sqlprintf("AND $this->primary_key != %s",
                $this->Id());
        
        $result = $this->db->query("
            SELECT email
            FROM $this->table_name
            WHERE email = %s $id_check
            LIMIT 1
        ", $email, $this->Id());
        
        if($result && $result->num_rows) {
            $result->close();
            return true;
        }
        
        return false;
    }
    
    public function IsOnline() {
        $this->AssertID();
        
        if((time()-$this['lastseen']) < CFG_ONLINE_DURATION)
            return true;
        else
            return false;
    }
    
    public function IsFriend(M_User $user) {
        if(isset($this->func_cache['IsFriend'][$user->Id()]))
            return $this->func_cache['IsFriend'][$user->Id()];
        else
            return $this->func_cache['IsFriend'][$user->Id()] = M_Friendship::FriendshipExists($this->Id(), $user->Id());
    }
    
    public function GetFriends($fields = array(), $limit = false) {
        $this->AssertID();
        
        if(empty($fields))
            $fields = array($this->primary_key);
        
        $friendships = M_Friendship::GetArrayByUserID($this->Id(), $limit, true);
        if($friendships) {
            $friends = array();
            foreach($friendships as $friendship) {
                $friends[] = M_User::GetByID($friendship->GetFriendID($this->Id()), $fields)
                    or MakeWarning('Unable to find friend user', $friendship->data);
            }
            
            return $this->friends = $friends;
        }
        else {
            return array();
        }
    }
    
    public function GetProfileURI() {
        $this->AssertID();
        
        $uri = "/player/";
        
        if($this['profile_key'])
            return $uri.$this['profile_key'];
        else
            return $uri.$this->Id();
    }

    /* Hacks until late static binding is available */
    static public function GetByID($id, array $fields = array()) {
        return Model::GetByID($id, $fields, __CLASS__);
    }

    static public function GetBySearch(array $search_data, array $fields = array()) {
        return Model::GetBySearch($search_data, $fields, __CLASS__);
    }
    
    static public function GetCountByWhere($where_logic, array $where_data) {
        return Model::GetCountByWhere($where_logic, $where_data, __CLASS__);
    }
    
    static public function GetArrayByWhere($where_logic, array $where_data, $limit = false, array $fields = array(), $__CLASS__ = __CLASS__) {
        return Model::GetArrayByWhere($where_logic, $where_data, $limit, $fields, __CLASS__);
    }
    
    static public function Exists($id) {
        return Model::Exists($id, __CLASS__);
    }
}