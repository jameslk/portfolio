<?php

Libs('model/tg');

class M_CommentPost extends M_Tg {
	public $table_name = 'tg_comment_posts';

	public $primary_key = 'post_id';

	public $fields = array(
        'thread_id',
        'user_id',

        'content',

        'post_date',
        'edit_date',

        'is_firstpost',
        'reported'
	);

	public $data_validate = array(
	    'post_id' => FILTER_VALIDATE_INT,
	    'thread_id' => FILTER_VALIDATE_INT,
	    'user_id' => FILTER_VALIDATE_INT,

	    'content' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/(?!^\s*$)^.{2,4096}$/m')
		)
	);

	public $data_sanitize = array(
	    'content' => array(
			'filter' => FILTER_CALLBACK,
			'options' => 'trim'
		),

	    'post_date' => FILTER_SANITIZE_NUMBER_INT,
        'edit_date' => FILTER_SANITIZE_NUMBER_INT,

        'is_firstpost' => FILTER_SANITIZE_NUMBER_INT,
        'reported' => FILTER_SANITIZE_NUMBER_INT
	);

	public $user;

    protected function UpdateThread($date) {
        $this->AssertID();

        $thread = new M_CommentThread($this['thread_id']);
        $thread->SetField('lastpost_date', $date);
        $thread->Update();
    }

    public function Create() {
        $date = time();

        if(!isset($this['post_date']))
            $this->SetField('post_date', $date);

        parent::Create();

        $this->UpdateThread($date);
    }

    public function GetUser() {
        $this->AssertID();

        $user = M_User::GetByID($this['user_id'], array('profile_key', 'displayname'));
        if(!$user)
            //todo: Fall back to saved displayname from post instead?
            MakeWarning('Failed to get user model for comment post', $this->data);

        return $this->user = $user;
    }

    /* Hacks until late static binding is available */
    static public function GetByID($id, array $fields = array()) {
        return Model::GetByID($id, $fields, __CLASS__);
    }

    static public function GetBySearch(array $search_data, array $fields = array()) {
        return Model::GetBySearch($search_data, $fields, __CLASS__);
    }

    static public function GetArrayBySearch(array $search_data, $limit = false, array $fields = array()) {
        return Model::GetArrayBySearch($search_data, $limit, $fields, __CLASS__);
    }
}