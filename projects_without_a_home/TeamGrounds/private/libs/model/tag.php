<?php

Libs('model/tg');

class M_Tag extends M_Tg {
    public $table_name = 'tg_tags';

	public $primary_key = 'tag_id';
	
	public $fields = array(
        'field',
        'title',
        'popularity'
	);

	public $data_validate = array(
        'tag_id',
    
        'field',
        'title'
	);

	public $data_sanitize = array(
        'popularity'
	);
    
    public function UpPopularity() {
        $this->AssertID();
        
        $result = $this->db->query("
            UPDATE $this->table_name
            SET popularity = popularity+1
            WHERE $this->primary_key = %s
        ", $this->Id());
        
        if(!$result || !$this->db->affected_rows) {
            MakeWarning('Failed to increment tag popularity', $this->data);
            return false;
        }
        
        return true;
    }
    
    public function DownPopularity() {
        $this->AssertID();
        
        $result = $this->db->query("
            UPDATE $this->table_name
            SET popularity = popularity-1
            WHERE $this->primary_key = %s AND popularity != %d
        ", $this->Id(), 0);
        
        /* If last query had no affect, either tag doen't exist or is unpopular */
        if((!$result || !$this->db->affected_rows) && !$this->Delete()) {
            MakeWarning('Failed to decrement or delete unpopular tag', $this->data);
            return false;
        }
        
        return true;
    }
    
    static public function AjaxSearch($title_part, $field, $limit = 10, $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
        
        $title_part = $model->db->escape($title_part);
        
        $result = $model->db->query("
            SELECT title
            FROM $model->table_name
            WHERE title LIKE '$title_part%%' AND field = %s
            ORDER BY popularity DESC
            LIMIT $limit
        ", $field);
        
        if(!$result || !$result->num_rows)
            return false;
        
        $titles = array();
        while($title = $result->fetch_array())
            $titles[] = $title[0];
        
        return $titles;
    }

    /* Hacks until late static binding is available */
    static public function GetByID($id, array $fields = array()) {
        return Model::GetByID($id, $fields, __CLASS__);
    }

    static public function GetBySearch(array $search_data, array $fields = array()) {
        return Model::GetBySearch($search_data, $fields, __CLASS__);
    }
}