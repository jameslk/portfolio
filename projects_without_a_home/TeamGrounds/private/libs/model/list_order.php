<?php

Libs('model/tg', 'model/tag');

class M_ListOrder extends M_Tg {
	public $fields = array(
        'list_order'
	);

	public $data_sanitize = array(
        'list_order' => FILTER_SANITIZE_NUMBER_INT
	);
	
	public $list_dependency_fields = array(
	);
    
    protected function Where_ListDependency() {
        if(!empty($this->list_dependency_fields)) {
            $this->NeedFieldsArray($this->list_dependency_fields);
            
            $where = $this->list_dependency_fields;
            foreach($where as &$field)
                $field = $field.' = %s';
            
            $where = implode(' AND ', $where);
            
            $data = array_intersect_key($this->data, array_flip($this->list_dependency_fields));
            
            return $this->db->vsqlprintf($where, $data);
        }
        else {
            return '1';
        }
    }
    
    public function Create() {
        if(!isset($this['list_order'])) {
            $list_order = $this->db->query_first("
                SELECT list_order
                FROM $this->table_name
                WHERE ".$this->Where_ListDependency()."
                ORDER BY list_order DESC
                LIMIT 1
            ");
            
            if($list_order === false)
                $list_order = 0;
            else
                ++$list_order;
            
            $this->SetField('list_order', $list_order);
        }
        
        parent::Create();
    }
    
    public function MoveUp() {
        $this->AssertID();
        
        $list_order = $this['list_order'];
        if($list_order < 1)
            return; //already at top
        
        /* Move down the item at list_order-1 */
        $this->db->query("
            UPDATE $this->table_name
            SET list_order = %s
            WHERE ".$this->Where_ListDependency()." AND list_order < %s
            ORDER BY list_order DESC
            LIMIT 1
        ", $list_order, $list_order);
        
        /* Move up this item to list_order-1 */
        $this->SetField('list_order', $list_order-1, false);
        $this->Update();
    }
    
    public function MoveDown() {
        $this->AssertID();
        
        $list_order = $this['list_order'];
        
        /* Move up the item at list_order+1 */
        $result = $this->db->query("
            UPDATE $this->table_name
            SET list_order = %s
            WHERE ".$this->Where_ListDependency()." AND list_order > %s
            ORDER BY list_order ASC
            LIMIT 1
        ", $list_order, $list_order);
        
        if(!$result || !$this->db->affected_rows)
            return; //already at bottom
        
        /* Move up this item to list_order-1 */
        $this->SetField('list_order', $list_order+1, false);
        $this->Update();
    }
    
    static public function GetArrayBySearch(array $search_data, $limit = false, array $fields = array(), $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
        
        $select_conditions = array('order' => 'list_order');
        
        if($limit)
            $select_conditions['limit'] = $limit;
        
        return $model->Find($search_data, $select_conditions, $fields);
    }
    
    static public function GetByUser(M_User $user, array $fields = array()) {
        return self::GetBySearch(array(
            $user->primary_key => $user->Id()
        ), $fields);
    }
    
    static public function GetArrayByUser(M_User $user, array $fields = array()) {
        return self::GetArrayBySearch(array(
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