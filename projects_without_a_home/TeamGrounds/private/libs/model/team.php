<?php

Libs('model/entity', 'model/user', 'model/team_profile', 'model/team_member');

class M_Team extends M_Entity {
    public $entity_type = 'team';
    
	public $table_name = 'tg_teams';

	public $primary_key = 'team_id';
	
	public $fields = array(
        'team_id',
        
        'name',
    
        'profile_key',
    	
    	'created_by',
    	'created_date',
	);

	public $data_validate = array(
        'team_id' => FILTER_VALIDATE_INT,
        
        'name' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/(?!^\s*$)^.{2,255}$/')
		),
    
        'profile_key' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/(?!^[0-9]*$|-|_)^[a-zA-Z0-9-_]{4,100}$|^$/')
		),
		
		'created_by' => FILTER_VALIDATE_INT,
		'created_date' => FILTER_VALIDATE_INT
	);

	public $data_sanitize = array(
	);
	
	public $related_models = array(
        'avatar' => array(
            'lib' => 'model/avatar',
            'model' => 'M_Avatar',
            
            'relationship' => self::HAS_ONE
        ),
        
        'profile' => array(
            'lib' => 'model/team_profile',
            'model' => 'M_TeamProfile',
            
            'relationship' => self::HAS_ONE,
            
            'foreign_keys' => 'user_id'
        ),
        
        'members' => array(
            'lib' => 'model/team_member',
            'model' => 'M_TeamMember',
            
            'relationship' => self::HAS_MANY,
            
            'foreign_keys' => 'team_id'
        ),
        
        'games' => array(
            'lib' => 'model/team_member',
            'model' => 'M_TeamGame',
            
            'relationship' => self::HAS_MANY,
            
            'foreign_keys' => 'team_id'
        )
	);
	
	public function Create(M_User $user, M_TeamProfile $team_profile = NULL) {
	    $this->SetFields(array(
            'created_by' => $user->Id(),
            'created_date' => time()
        ), $fail_data)
            or MakeWarning('Failed to set team created data', $fail_data);
        
        $insert_id = parent::Create();
        
        /* Create team member */
        $team_member = new M_TeamMember;
        
        $team_member->SetFields(array(
            $this->primary_key => $this->Id(),
            $user->primary_key => $user->Id(),
            'access_flags' => 'owner'
        ), $fail_data)
            or MakeError('Failed to add team member', $fail_data);
        
        $team_member->Create();
        $this->CacheRelatedModel('members', $team_member);
        
        /* Create team profile */
        if($team_profile) {
            $team_profile->SetField($this->primary_key, $this->Id());
        }
        else {
            $team_profile = new M_TeamProfile;
            $team_profile->SetField($this->primary_key, $this->Id());
        }
        
        $team_profile->Create();
        $this->CacheRelatedModel('profile', $team_profile);
        
		return $insert_id;
    }
    
    public function GetOwnerID() {
        $this->AssertID();
        
        if(isset($this->func_cache['GetOwnerID']))
            return $this->func_cache['GetOwnerID'];
        
        $owner = M_TeamMember::GetBySearch(array(
            $this->primary_key => $this->Id(),
            'access_flags' => 'owner'
        ), array('user_id'));
        
        if($owner)
            return $this->func_cache['GetOwnerID'] = $owner['user_id'];
        else
            return $this->func_cache['GetOwnerID'] = $this['created_by'];
    }

    /* Hacks until late static binding is available */
    static public function Exists($id) {
        return Model::Exists($id, __CLASS__);
    }
    
    static public function GetByID($id, array $fields = array()) {
        return Model::GetByID($id, $fields, __CLASS__);
    }

    static public function GetBySearch(array $search_data, array $fields = array()) {
        return Model::GetBySearch($search_data, $fields, __CLASS__);
    }
}