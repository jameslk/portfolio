<?php

Libs('model/tg', 'model/user', 'database/tg');

class M_Session extends M_Tg {
    public $table_name = 'tg_sessions';

	public $primary_key = 'session_id';

	public $fields = array(
        'session_id',
        'user_id',
        'start_time',
        'cookie_expire'
	);

    public $data_validate = array(
	    'session_id' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^[a-z0-9]{32}$/')
		),

		'user_id' => FILTER_VALIDATE_INT
	);

	public $data_sanitize = array(
		'start_time' => FILTER_SANITIZE_NUMBER_INT,
		'cookie_expire' => FILTER_SANITIZE_NUMBER_INT
	);

    static public function GetUserSession($id, array $fields = array(), $__CLASS__ = __CLASS__) {
        $session = new $__CLASS__;
        $user = new M_User;

        if(empty($fields)) {
            $fields = 'u.*';
        }
	    else {
	        if(!in_array($user->primary_key, $fields))
                array_push($fields, $user->primary_key);

	        foreach($fields as &$field)
                $field = 'u.'.$field;

            $fields = implode(', ', $fields);
        }

        $result = $session->db->query("
                SELECT s.start_time, s.cookie_expire, $fields
                FROM $session->table_name AS s, $user->table_name AS u
                WHERE s.$session->primary_key = %s
                    AND u.$user->primary_key = s.$user->primary_key
                LIMIT 1
            ", $id);

        if(!$result || !$result->num_rows)
            return false;

        $data = $result->fetch_assoc();

        $user->data = array_slice($data, 2);

        $session->SetID($id);
        $session->data['user_id'] = $user->data['user_id'];
        $session->data['start_time'] = $data['start_time'];
        $session->data['cookie_expire'] = $data['cookie_expire'];

        return array('user' => $user, 'session' => $session);
    }

    protected function GenerateID() {
        $count = 5; //max number of tries

        while($count--) {
            $id = md5(uniqid(rand(), true));

            $result = $this->db->query("
                SELECT $this->primary_key
                FROM $this->table_name
                WHERE $this->primary_key = %s
            ", $id);

            if(!$result || !$result->num_rows) {
                $this->SetID($id);
                return;
            }
        }

        MakeError('Failed to generate a unique session id');
    }

    public function Create() {
        $this->GenerateID();

        $result = $this->Insert();

        if($result === false)
            MakeError('Failed to create session', $this->data);
    }

    public function Refresh() {
        $this->AssertID();

        $old_id = $this->Id();
        $this->GenerateID();
        $this->data['start_time'] = time();

		$this->db->query_array("
                UPDATE $this->table_name
                SET session_id = %s, start_time = %s
                WHERE $this->primary_key = %s
    		", array($this->Id(), $this['start_time'], $old_id))
                or MakeError('Failed to refresh session', $this->data);
    }

    static public function DeleteByUserID($user_id, $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
        return $model->db->query("
            DELETE FROM $model->table_name
            WHERE user_id = %s
        ", $user_id);
    }

    /* Hacks until late static binding is available */
    static public function GetByID($id, array $fields = array()) {
        return Model::GetByID($id, $fields, __CLASS__);
    }

    static public function GetBySearch(array $search_data, array $fields = array()) {
        return Model::GetBySearch($search_data, $fields, __CLASS__);
    }
}