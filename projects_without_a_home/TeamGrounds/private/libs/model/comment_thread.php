<?php

Libs('model/entity_parent', 'model/comment_post');

class M_CommentThread extends M_EntityParent {
	public $table_name = 'tg_comment_threads';

	public $primary_key = 'thread_id';
	
	public $fields = array(
        'parent_id',
        'parent_type',
        
        'lastpost_date'
	);

	public $data_validate = array(
	    'thread_id' => FILTER_VALIDATE_INT,
	    
	    'parent_id' => FILTER_VALIDATE_INT,
	    'parent_type' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('regexp' => '/^.+$/')
		)
	);
	
	public $data_sanitize = array(
	    'lastpost_date' => FILTER_SANITIZE_NUMBER_INT
	);
	
	public $posts;
    
    public function NewThread(M_CommentPost $post) {
        $id = $this->Create();
        
        $post->SetFields(array(
            $this->primary_key => $id,
            'is_firstpost' => true
        ), $fail_data)
            or MakeError('Failed to set new thread post data', $fail_data);
        
        $post->Create();
    }
    
    public function GetPosts($limit = false, array $fields = array()) {
        $this->AssertID();
        
        $posts = M_CommentPost::GetArrayBySearch(
            array('thread_id' => $this->Id()), $limit, $fields);
        
        usort($posts, array($this, 'SortCmpPosts'));
        
        return $this->posts = $posts;
    }
    
    public function SortCmpPosts($a, $b) {
        if($a['is_firstpost'] == true)
            return -1;
        else if($b['is_firstpost'] == true)
            return 1;
        else if($a['post_date'] == $b['post_date'])
            return 0;
        else
            return ($a['post_date'] > $b['post_date']) ? 1 : -1;
    }
    
    public function Delete() {
        $this->AssertID();
        
        $post_model = new M_CommentPost;
        
        $post_model->db->query("
            DELETE FROM $post_model->table_name
            WHERE $this->primary_key = %s
        ", $this->Id());
        
        return parent::Delete();
    }
    
    static public function GetArrayBySearch(array $search_data, $limit = false, array $fields = array(), $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
        
        if(empty($fields)) {
            $fields = '*';
	    }
	    else {
            if(!in_array($model->primary_key, $fields))
                array_push($fields, $model->primary_key);
            
            $fields = implode(', ', $fields);
	    }
	    
	    $search_keys = array_keys($search_data);
        foreach($search_keys as &$key)
            $key = $key.' = %s';
        
        $where = implode(' AND ', $search_keys);
        
        if($limit)
            $limit = 'LIMIT '.$limit;
        else
            $limit = '';
	    
	    $result = $model->db->query_array("
                SELECT $fields
                FROM $model->table_name
                WHERE $where
                ORDER BY lastpost_date DESC
                $limit
            ",
            $search_data);
        
        if(!$result || !$result->num_rows)
            return false;
        
        $models = array();
        while($data = $result->fetch_assoc()) {
            $model = new $__CLASS__;
            $model->data = $data;
            $models[] = $model;
        }
        
        $result->close();
        
        return $models;
    }
    
    /* Hacks until late static binding is available */
    static public function GetByID($id, array $fields = array()) {
        return Model::GetByID($id, $fields, __CLASS__);
    }

    static public function GetBySearch(array $search_data, array $fields = array()) {
        return Model::GetBySearch($search_data, $fields, __CLASS__);
    }
    
    static public function GetByParent(Model $parent, array $fields = array()) {
        return M_EntityParent::GetByParent($parent, $fields, __CLASS__);
    }
    
    static public function GetCountByParent(Model $parent) {
        return M_EntityParent::GetCountByParent($parent, __CLASS__);
    }
    
    static public function GetArrayByParent(M_Entity $parent, $limit = false, array $fields = array(), $__CLASS__ = __CLASS__) {
        $parent->AssertID();
        
        return self::GetArrayBySearch(array(
            'parent_id' => $parent->Id(),
            'parent_type' => $parent->entity_type
        ), $limit, $fields, $__CLASS__);
    }
}