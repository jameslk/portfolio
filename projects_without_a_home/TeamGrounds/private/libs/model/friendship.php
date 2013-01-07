<?php

Libs('model/tg');

class M_Friendship extends M_Tg {
    public $table_name = 'tg_friendships';

	public $primary_key = 'friendship_id';
	
	public $fields = array(
        'user1_id',
        'user2_id',
        
        'is_request'
	);

	public $data_validate = array(
	    'friendship_id' => FILTER_VALIDATE_INT,
	    
	    'user1_id' => FILTER_VALIDATE_INT,
	    'user2_id' => FILTER_VALIDATE_INT,
	    
	    'is_request' => FILTER_VALIDATE_INT,
	);
    
    public function SetUserIDs($user1_id, $user2_id) {
        if($user1_id == $user2_id)
            return false; //cannot befriend yourself
        
        if(!$this->SetFields(array(
            'user1_id' => min($user1_id, $user2_id),
            'user2_id' => max($user1_id, $user2_id)
        ), $fail_data))
            return false;
    }
    
    public function Create() {
        $insert_id = $this->Insert();

		if($insert_id === false)
            MakeError('Failed to create friendship', $this->data);

		$this->SetID($insert_id);

		AppLog::Report('Created friendship', $this->data);

		return $insert_id;
    }
    
    public function GetFriendID($my_id) {
        $this->AssertID();
        
        if($this['user1_id'] == $my_id)
            return $this['user2_id'];
        else
            return $this['user1_id'];
    }
    
    static public function GetByUserIDs($user1_id, $user2_id, $no_requests = false, $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
	    
	    $match_request = '';
	    if($no_requests)
	       $match_request = "is_request = 'false' AND";
	    
	    $result = $model->db->query("
            SELECT *
            FROM $model->table_name
            WHERE
                $match_request
                (user1_id = %s AND user2_id = %s)
            LIMIT 2
            ", min($user1_id, $user2_id), max($user1_id, $user2_id));
        
        if(!$result || !$result->num_rows)
            return false;
        
        if($result->num_rows > 1)
            MakeWarning('Multiple friendships found for search data', $search_data);
        
        $model->data = $result->fetch_assoc();
        
        $result->close();
        
        return $model;
    }
    
    static public function GetArrayByUserID($user_id, $limit = false, $no_requests = false, $__CLASS__ = __CLASS__) {
	    $model = new $__CLASS__;
	    
	    if($limit)
	       $limit = 'LIMIT '.$limit;
	    else
	       $limit = '';
	    
	    $match_request = '';
	    if($no_requests)
	       $match_request = "is_request = 'false' AND";
	    
	    $result = $model->db->query("
            SELECT *
            FROM $model->table_name
            WHERE
                $match_request
                (user1_id = %s OR user2_id = %s)
            $limit
            ", $user_id, $user_id);
        
        if(!$result || !$result->num_rows)
            return false;
        
        $friendships = array();
        while($data = $result->fetch_assoc()) {
            $friendship = new $__CLASS__;
            $friendship->data = $data;
            $friendships[] = $friendship;
        }
        
        $result->close();
        
        return $friendships;
    }
    
    static public function FriendshipExists($user1_id, $user2_id, $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
	    
	    $result = $model->db->query("
            SELECT friendship_id
            FROM $model->table_name
            WHERE
                is_request = 'false' AND
                (user1_id = %s AND user2_id = %s)
            LIMIT 1
            ", min($user1_id, $user2_id), max($user1_id, $user2_id));
        
        if(!$result || !$result->num_rows)
            return false;
        
        $result->close();
        
        return true;
    }
    
    /* Hacks until late static binding is available */
    static public function GetByID($id, array $fields = array()) {
        return Model::GetByID($id, $fields, __CLASS__);
    }

    static public function GetBySearch(array $search_data, array $fields = array()) {
        return Model::GetBySearch($search_data, $fields, __CLASS__);
    }
}