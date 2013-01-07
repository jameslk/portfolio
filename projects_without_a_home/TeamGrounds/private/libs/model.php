<?php

Libs('database/tg');

class Model implements ArrayAccess {
    public $db;
    
	public $table_name = '';
	public $primary_key = '';
	
	public $fields = array();
	
	public $data = array();
	public $data_validate = array();
	public $data_sanitize = array();
	
	const HAS_ONE = 1;
	const HAS_MANY = 2;
	const BELONGS_TO = 3;
	
	public $related_models = array();
	
	public $model_cache = array();
	public $update_fields = array();
	public $func_cache = array();
	
	function __construct($id = false) {
	    if($id !== false)
	       $this->SetID($id);
    }
    
    public function Id() {
        if(isset($this->data[$this->primary_key]))
            return $this->data[$this->primary_key];
        else
            MakeError('Failed to get model ID', array('class' => get_class($this)));
    }
    
    public function SetID($id) {
        if(!$this->FilterField($this->primary_key, $id))
            MakeError('Validation of id field failed', compact('id'));
        
        $this->data[$this->primary_key] = $id;
    }
    
    public function HasID() {
        return isset($this->data[$this->primary_key]);
    }
    
    public function AssertID() {
        if(!$this->HasID())
            MakeError('Failed to assert that this model is identified', array('class' => get_class($this)));
    }
    
    public function CacheRelatedModel($alias, $model) {
        if(isset($this->related_models[$alias])) {
            $relationship = $this->related_models[$alias]['relationship'];
            
            if($relationship == self::HAS_MANY)
                $this->model_cache[$alias][$model->Id()] = $model;
            else
                $this->model_cache[$alias] = $model;
        }
    }
    
    protected function GetModelSearchData($alias, $model) {
        /* This function needs to be defined for related model lookups */
        MakeError('GetModelSearchData has not been defined for related model lookups', get_class($this));
    }
    
    protected function HandleModelJoin($alias) {
        $related_info = $this->related_models[$alias];

        Libs($related_info['lib']);
        
        $model = new $related_info['model'];
        
        if(isset($related_info['search_data']))
            $search_data = $related_info['search_data'];
        else
            $search_data = $this->GetModelSearchData($alias, $model);
        
        $where = '';
        if(isset($search_data['where'])) {
            /* Append additional where SQL for this lookup */
            $where = preg_replace_callback(
                '/(\'(?:\\.|[^\'])*\')|([a-z0-9_-]+)/m',
                //todo: Replace with lambda function
                create_function('$matches', '
                if(trim($matches[1]))
                    return $matches[1];
                else
                    return \''.$alias.'\'.\'.\'.$matches[2];
                '),
                $search_data['where']);
        }
        
        $join_sql = '';
        
        if(isset($search_data['on'])) {
            /* External join on SQL provided */
            if($where) {
                $join_sql = "
                    LEFT JOIN $model->table_name AS $alias
                    ON (
                        $search_data[join_on]
                        AND ($where)
                    )
                ";
            }
            else {
                $join_sql = "
                    LEFT JOIN $model->table_name AS $alias
                    ON $search_data[join_on]
                ";
            }
        }
        else if(isset($search_data['foreign_keys'])) {
            /* Add join on foreign keys */
            if(is_array($search_data['foreign_keys'])) {
                $on = array();
                foreach($search_data['foreign_keys'] as $this_field => $foreign_field)
                    $on[] = "$this->table_name.$this_field = $alias.$foreign_field";
                
                if($where) {
                    $on = implode(' AND ', $on);
                    
                    $join_sql = "
                        LEFT JOIN $model->table_name AS $alias
                        ON (
                            $on
                            AND ($where)
                        )
                    ";
                }
                else {
                    $on = implode(', ', $on);
                    
                    $join_sql = "
                        LEFT JOIN $model->table_name AS $alias
                        ON $on
                    ";
                }
            }
            else if(!$where) {
                $join_sql = "
                    LEFT JOIN $model->table_name AS $alias
                    USING($search_data[foreign_keys])
                ";
            }
            else {
                $join_sql = "
                    LEFT JOIN $model->table_name AS $alias
                    ON (
                        $this->table_name.$search_data[foreign_keys] = $alias.$search_data[foreign_keys]
                        AND ($where)
                    )
                ";
            }
        }
        else {
            MakeError('Nothing to join on for related model', $alias);
        }
        
        return array('model' => $model, 'join_sql' => $join_sql);
    }
    
    protected function HandleModelLookup($alias, $fields, array $models = NULL) {
        if(!$models)
            $models = array($this);
        
        $first_model = current($models);
        $primary_key_field = $first_model->table_name.'.'.$first_model->primary_key;
        
        $related_info = $this->related_models[$alias];
        $join_data = $this->HandleModelJoin($alias);
        $related_model = $join_data['model'];
        $join_sql = $join_data['join_sql'];
        
        $select_conditions = array(
            'from' => $first_model->table_name,
            'join' => $join_sql
        );
        
        $where_data = array();
        if(($data_count = count($models)) > 1) {
            /* WHERE primary_key IN (data1, ...) */
            $where_logic = $primary_key_field.' IN (%s';
            
            while(--$data_count)
                $where_logic .= ', %s';
            
            $where_logic .= ')';
            
            foreach($models as $model)
                $where_data[] = $model->Id();
        }
        else {
            /* WHERE primary_key = data */
            $where_logic = $primary_key_field.' = %s';
            $where_data[] = $first_model->Id();
        }
        
        if(!$fields)
            $fields = array('*');
        
        foreach($fields as &$field)
            $field = $alias.'.'.$field;
        
        /* Append primary_key to lookup fields so we can determine which result
           row correspondes to which model */
        $fields[] = $primary_key_field;
        
        $result = $related_model->Select($where_logic, $where_data,
            $select_conditions, $fields);
        
        if(!$result)
            return;
        
        $field_info = $result->fetch_fields();
        $fields = array();
        foreach($field_info as $field) {
            if($field->table == $alias)
                $fields[] = $field->name;
        }
        
        $data = array();
        while($row = $result->fetch_row()) {
            $id = array_pop($row); //this should be the primary_key
            $row = array_combine($fields, $row);
            
            if(!array_key_exists($related_model->primary_key, $row))
                MakeWarning('No related primary key field in row',
                    array_merge(array('Primary Key' => $related_model->primary_key), $row));
            else if($row[$related_model->primary_key] !== NULL)
                $data[$id][] = $row;
        }
        
        $result->close();
        
        if(empty($data))
            return;
        
        /* Create new related models from result data and cache for each models */
        foreach($models as $model) {
            if(isset($data[$model->Id()])) {
                if($related_info['relationship'] == self::HAS_MANY) {
                    foreach($data[$model->Id()] as $row) {
                        $related_model = new $related_info['model'];
                        $related_model->data = $row;
                        $model->model_cache[$alias][$related_model->Id()] = $related_model;
                    }
                }
                else {
                    $related_model = new $related_info['model'];
                    $related_model->data = $data[$model->Id()][0];
                    $model->model_cache[$alias] = $related_model;
                }
            }
            else {
                $model->model_cache[$alias] = NULL; //no related models found
            }
        }
    }
    
    protected function HandleRelated(&$fields) {
        if(!is_array($fields) || empty($fields))
            return;
        
        $related_data = array('join_aliases' => array(), 'join_sql' => '',
            'join_models' => array(), 'lookup_fields' => array());
        
        $handled_joins = array();
        
        /* Search for related model fields in the form "alias.field" */
        $new_fields = array();
        foreach($fields as $field) {
            if(strpos($field, '.')) {
                list($alias, $related_field) = explode('.', $field, 2);
                
                if(!isset($this->related_models[$alias]))
                    MakeError('No related model found for alias', $alias);
                
                $related_info = $this->related_models[$alias];
                
                if($related_info['relationship'] == self::HAS_MANY) {
                    $related_data['lookup_fields'][$alias][] = $related_field;
                }
                else {
                    $related_data['join_aliases'][] = $alias;
                    $new_fields[] = $field;
                }
            }
            else {
                /* Add this table before every field */
                $new_fields[] = $this->table_name.'.'.$field;
            }
        }
        
        if(!empty($related_data['join_aliases']))
            $fields = $new_fields;
        
        foreach($related_data['join_aliases'] as $alias) {
            $join_data = $this->HandleModelJoin($alias);
            
            $related_data['join_models'][$alias] = get_class($join_data['model']);
            
            /* Append join model SQL */
            $related_data['join_sql'] .= $join_data['join_sql'];
            
            if(!in_array($alias.'.*', $fields)
                && !in_array($join_data['model']->primary_key, $fields)) {
                /* Append join model's primary key to select fields */
                $fields[] = $alias.'.'.$join_data['model']->primary_key;
            }
        }
        
        return $related_data;
    }
    
    public function Select($where_logic, array $where_data, $select_conditions = false, $fields = false) {       
        if(is_array($fields) && !empty($fields))
            $fields = implode(', ', $fields);
        else
            $fields = '*';
        
        $from = $this->table_name;
        $join = '';
        $group_by = '';
        $order_by = '';
        $limit = '';
        
        if(!empty($select_conditions)) {
            /* Define SELECT query conditions */
            if(isset($select_conditions['from']))
                $from = $select_conditions['from'];
            
            if(isset($select_conditions['join']))
                $join = $select_conditions['join'];
            
            if(isset($select_conditions['group_by'])) {
                if(is_array($select_conditions['group_by']))
                    $group_by = 'GROUP BY ('
                        .implode(', ', $select_conditions['group_by']).')';
                else
                    $group_by = 'GROUP BY '.$select_conditions['group_by'];
            }
            
            if(isset($select_conditions['order_by'])) {
                if(is_array($select_conditions['order_by']))
                    $group_by = 'ORDER BY ('
                        .implode(', ', $select_conditions['order_by']).')';
                else
                    $group_by = 'ORDER BY '.$select_conditions['order_by'];
            }
            
    	    if(isset($select_conditions['limit']))
                $limit = 'LIMIT '.$select_conditions['limit'];
        }
	    
	    /* Perform lookup */
	    $result = $this->db->query_array("
            SELECT $fields
            FROM $from $join
            WHERE $where_logic
            $group_by
            $order_by
            $limit
        ", $where_data);
        
        if($result) {
            if($result->num_rows)
                return $result;
            else
                $result->close();
        }
        
        return false;
    }
    
    public function FindByWhere($where_logic, array $where_data, $select_conditions = false, $fields = false) {
        if(is_array($fields) && !empty($fields)) {
            if(!in_array($this->primary_key, $fields) && !in_array('*', $fields))
                $fields[] = $this->primary_key;
	    }
	    
        /* Get related models for joining and to be looked-up externally */
        $related_data = $this->HandleRelated($fields);
        $join_sql = $related_data['join_sql'];
        
        if(!empty($join_sql)) {
            if(is_array($select_conditions))
                $select_conditions['join'] = $join_sql;
            else
                $select_conditions = array('join' => $join_sql);
        }
        
        $result = $this->Select($where_logic, $where_data, $select_conditions, $fields);
        
        if(!$result)
            return false;
        
        $models = array();
        $this_class = get_class($this);
        if(empty($related_data['join_models'])) { //no joins
            while($data = $result->fetch_assoc()) {
                $model = new $this_class;
                $model->data = $data;
                $models[$model->Id()] = $model;
            }
        }
        else {
            $field_info_array = $result->fetch_fields();
            
            $skip_table = array();
            
            /* Add join data to related models and cache them for this model */
            while($data = $result->fetch_row()) {
                $model = new $this_class;
                
                foreach($field_info_array as $i => $field_info) {
                    $table = $field_info->table;
                    $field = $field_info->name;
                    
                    if($table == $this->table_name) {
                        /* Add field data to this model */
                        $model->data[$field] = $data[$i];
                    }
                    else if(!isset($skip_table[$table])) {
                        /* Add field data to related model (from join) */
                        
                        /* Check if related model has been created and cached yet */
                        if(!isset($model->model_cache[$table])) {
                            /* Check if we need to handle this table */
                            if(isset($related_data['join_models'][$table])) {
                                $related_model = new $related_data['join_models'][$table];
                                
                                $primary_key_value = '';
                                if($field != $related_model->primary_key) {
                                    /* Find primary key value for this related model */
                                    $primary_key_found = false;
                                    foreach($field_info as $j => $info) {
                                        if($info->table == $table && $info->name == $related_model->primary_key) {
                                            $primary_key_found = true;
                                            $primary_key_value = $data[$j];
                                            break;
                                        }
                                    }
                                    
                                    if(!$primary_key_found) {
                                        MakeWarning('Primary key for joined model not found', get_class($related_model));
                                        $skip_table[$table] = true;
                                        continue;
                                    }
                                }
                                else {
                                    $primary_key_value = $data[$i];
                                }
                                
                                /* Skip empty joined table */
                                if($primary_key_value === NULL) {
                                    $skip_table[$table] = true;
                                    continue;
                                }
                                
                                /* Cache newly created related model */
                                $model->model_cache[$table] = $related_model;
                            }
                            else {
                                MakeWarning('Unknown joined model', $table);
                                $skip_table[$table] = true;
                                continue;
                            }
                        }
                        
                        $model->model_cache[$table]->data[$field] = $data[$i];
                    }
                }
                
                $models[$model->Id()] = $model;
            }
        }
        
        $result->close();
        
        if(!empty($related_data['lookup_fields'])) {
            /* Perform related model external lookups */
            foreach($related_data['lookup_fields'] as $alias => $fields)
                $this->HandleModelLookup($alias, $fields, $models);
        }
        
        return $models;
    }
    
    public function Find(array $search_data, $select_conditions = false, $fields = false) {
        $where_fields = array_keys($search_data);
        $where_data = array();
        foreach($where_fields as &$field) {
            if(is_array($search_data[$field]) && (($data_count = count($search_data[$field])) > 1)) {
                /* Flatten out search data for sprintf */
                foreach($search_data[$field] as $data)
                    $where_data[] = $data;
                
                /* WHERE $field IN (data1, ...) */
                $field .= ' IN (%s';
                
                while(--$data_count)
                    $field .= ', %s';
                
                $field .= ')';
            }
            else {
                /* WHERE $key = data */
                $where_data[] = $search_data[$field];
                $field .= ' = %s';
            }
        }
        
        $where_logic = implode(' AND ', $where_fields);
        
        if($select_conditions) {
            if(isset($select_conditions['where'])) {
                $where_logic .= " AND ($select_conditions[where])";
            }
            else if(isset($select_conditions['where_logic'])
                && is_array($select_conditions['where_data'])) {
                $where_logic .= " AND ($select_conditions[where_logic])";
                $where_data = array_merge($where_data, $select_conditions['where_data']);
            }
        }
        
        return $this->FindByWhere($where_logic, $where_data,
            $select_conditions, $fields);
    }
    
    static public function Exists($id, $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
        
        $count = $model->db->query_first_array("
            SELECT COUNT(*)
            FROM $model->table_name
            WHERE $model->primary_key = %s
            LIMIT 1
        ", array($id));
        
        if($count === false)
            MakeWarning('Failed to get node count for search data', $search_data);
        
        return $count > 0;
    }
    
    static public function GetByID($id, array $fields = array(), $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
        
        $models = $model->FindByWhere("$model->primary_key = %s", array($id),
            array('limit' => 1), $fields);
        
        if(is_array($models))
            return current($models);
        else
            return false;
    }
    
    static public function GetBySearch(array $search_data, array $fields = array(), $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
        
        $models = $model->Find($search_data, array('limit' => 2), $fields);
        
        if(count($models) > 1)
            MakeError('Multiple nodes found for search data', $search_data);
        
        if(is_array($models))
            return current($models);
        else
            return false;
    }
    
    static public function GetCountBySearch(array $search_data, $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
	    
	    $search_keys = array_keys($search_data);
        foreach($search_keys as &$key)
            $key = $key.' = %s';
        
        $where = implode(' AND ', $search_keys);
        
        $count = $model->db->query_first_array("
            SELECT COUNT(*)
            FROM $model->table_name
            WHERE $where
        ",
        $search_data);
        
        if($count === false)
            MakeWarning('Failed to get node count for search data', $search_data);
        
        return $count;
    }
    
    static public function GetArrayBySearch(array $search_data, $limit = false, array $fields = array(), $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
        
        if($limit)
            $limit = array('limit' => $limit);
        
        return $model->Find($search_data, $limit, $fields);
    }
    
    static public function GetCountByWhere($where_logic, array $where_data, $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
        
        $count = $model->db->query_first_array("
            SELECT COUNT(*)
            FROM $model->table_name
            WHERE $where_logic
        ", $where_data);
        
        if($count === false)
            MakeWarning('Failed to get node count for search data', $search_data);
        
        return $count;
    }
    
    static public function GetArrayByWhere($where_logic, array $where_data, $limit = false, array $fields = array(), $__CLASS__ = __CLASS__) {
        $model = new $__CLASS__;
        
        if($limit)
            $limit = array('limit' => $limit);
        
        return $model->FindByWhere($where_logic, $where_data, $limit, $fields);
    }
	
	public function FilterField($field_name, &$field_value) {
	    if($field_name != $this->primary_key && !in_array($field_name, $this->fields))
            return false;
        
	    if(isset($this->data_validate[$field_name])) {
	        $filter_data = $this->data_validate[$field_name];
	        
	        if(is_array($filter_data)) {
                $filter = $filter_data['filter'];
                unset($filter_data['filter']);
                
                $result = filter_var($field_value, $filter, $filter_data);
            }
            else {
                $result = filter_var($field_value, $filter_data);
            }
            
            if($result === false)
                return false;
            
            $field_value = $result;
        }
        
        if(isset($this->data_sanitize[$field_name])) {
            $filter_data = $this->data_sanitize[$field_name];
	        
	        if(is_array($filter_data)) {
                $filter = $filter_data['filter'];
                unset($filter_data['filter']);
                
                $result = filter_var($field_value, $filter, $filter_data);
            }
            else {
                $result = filter_var($field_value, $filter_data);
            }
            
            if($result === false)
                return false;
            
            $field_value = $result;
        }
        
        return true;
    }
	
	public function FilterFields(array &$data, &$fail_data, array $fields = array()) {
	    $fail_data = false;
	    
	    if(empty($fields))
	       $fields = array_intersect(array_keys($data), $this->fields);
	    
	    $fields = array_flip($fields);
	    
	    $raw_data = array_intersect_key($data, $fields);
	    if(!$raw_data)
            return false;
        
        $filters = array_intersect_key($this->data_validate, $fields);
        if($filters) {
            $valid_data = filter_var_array($raw_data, $filters);
    		if($valid_data === false) {
    		    //This shouldn't happen a lot
    		    MakeWarning('Validation failed', compact('raw_data', 'filters'));
    			return false;
    		}
    		
    		$valid_data = array_merge($raw_data, $valid_data);
    		
    		foreach($valid_data as $field) {
    			if($field === false) {
    				$fail_data = $valid_data;
    				
    				return false;
    			}
    		}
    		
    		$raw_data = $valid_data;
    	}
        
        $filters = array_intersect_key($this->data_sanitize, $fields);
        if($filters) {
            $clean_data = filter_var_array($raw_data, $filters);
    		if($clean_data === false) {
    		    //This shouldn't happen a lot
    		    MakeWarning('Sanitization failed', compact('raw_data', 'filters'));
                return false;
            }
            
            $raw_data = array_merge($raw_data, $clean_data);;
        }
        
        $data = $raw_data;
        return true;
    }
    
    public function SetField($field_name, $field_value, $validate = true) {
        if($validate && !$this->FilterField($field_name, $field_value))
            return false;
        
        if(($this->data[$field_name] !== $field_value)
            && ($field_name != $this->primary_key)) {
            $this->data[$field_name] = $field_value;
            $this->update_fields[$field_name] = true;
        }
        
        return true;
    }
    
    public function SetFields(array $data, &$fail_data, array $fields = array()) {
        if(!$this->FilterFields($data, $fail_data, $fields))
            return false;
        
        if(empty($fields))
	       $fields = array_keys($data);
        
        foreach($fields as $field) {
            if(($this->data[$field] !== $data[$field]) && ($field != $this->primary_key)) {
                $this->data[$field] = $data[$field];
                $this->update_fields[$field] = true;
            }
        }
        
        return true;
    }
	
	public function SetData(array $data, &$fail_data) {
	    $fail_data = false;
	    
	    $data = array_intersect_key($data, $this->fields);
	    if(!$data)
            return false;
	    
		$valid_data = filter_var_array($data, $this->data_validate);
		if($valid_data === false) {
		    //This shouldn't happen a lot
		    MakeWarning('Validation failed', array('data' => $data, 'validate' => $this->data_validate));
			return false;
		}
		
		$valid_data = array_merge($data, $valid_data);
		
		foreach($valid_data as $field) {
			if($field === false) {
				$fail_data = $valid_data;
				
				return false;
			}
		}
		
		$clean_data = filter_var_array($valid_data, $this->data_sanitize);
		if($clean_data === false) {
		    //This shouldn't happen a lot
		    MakeWarning('Sanitization failed', array('data' => $valid_data, 'validate' => $this->data_sanitize));
            return false;
        }
		
		$this->data = array_merge($valid_data, $clean_data);
		
		return true;
	}
	
	public function IsUpdated($field) {
        if(isset($this->update_fields[$field]))
            return true;
        else
            return false;
    }
	
	public function IsInSet($field, $value) {
	    $this->AssertID();
	    
	    if(!isset($this->func_cache['IsInSet'][$field]) || $this->IsUpdated($field))
            $this->func_cache['IsInSet'][$field] = array_flip($this->GetSetArray($field));
        
        return isset($this->func_cache['IsInSet'][$field][$value]);
    }
    
    public function GetSetArray($field) {
        $this->AssertID();
        
        if(isset($this->func_cache['GetSetArray'][$field]) && !$this->IsUpdated($field))
            return $this->func_cache['GetSetArray'][$field];
        else
            return $this->func_cache['GetSetArray'][$field] = explode(',', $this[$field]);
    }
    
    static public function ConvertToSet($data) {
        if(is_array($data))
            return implode(',', $data);
        else
            return $data;
    }
    
    public function NeedFields() {
        $fields = func_get_args();
        if(empty($fields))
            return;
        
        $this->NeedFieldsArray($fields);
    }
    
    public function NeedFieldsArray(array $fields) {
        $lookup_fields = array();
        foreach($fields as $field) {
            if(!isset($this[$field])) {
                if(in_array($field, $this->fields))
                    $lookup_fields[] = $field;
                else
                    MakeWarning('Invalid model field', $field);
            }
        }
        
        if(!empty($lookup_fields)) {
            $this->AssertID();
            
            $select_fields = implode(', ', $lookup_fields);
            
            $result = $this->db->query("
                SELECT $select_fields
                FROM $this->table_name
                WHERE $this->primary_key = %s
            ", $this->Id());
            
            if(!$result || !$result->num_rows)
                MakeError('Failed to lookup needed model fields', $lookup_fields);
        }
    }
	
	public function Lookup($field) {
	    $this->AssertID();
	    
	    $field_data = $this->db->query_first("
            SELECT $field
            FROM $this->table_name
            WHERE $this->primary_key = %s
        ", $this->Id());
        
        if(!$field_data)
            MakeWarning('Failed to lookup field for id',
                array('field' => $field, $this->primary_key => $this->Id()));
        
        return $this->data[$field] = $field_data;
    }
	
	public function Insert(array $fields = array()) {
	    if(empty($fields))
	       $fields = $this->fields;
	    
		$query_data = array();
		foreach($fields as $key => $field) {
		    if(isset($this->data[$field]))
                $query_data[] = $this->data[$field];
            else
                unset($fields[$key]);
        }
        
        $values = implode(', ', array_fill(0, count($fields), '%s'));
        
        $fields = implode(', ', $fields);
		
		$result = $this->db->query_array("
                INSERT INTO $this->table_name
                ($fields)
    			VALUES ($values)
    		", $query_data);
		
		if(!$result)
            return $result;
        else
            return $this->db->insert_id;
	}
	
	public function Create() {
        $insert_id = $this->Insert();

		if($insert_id === false)
            MakeError('Failed to create data node', $this->data);

		$this->SetID($insert_id);

		return $insert_id;
    }
	
	public function Update($ignore_no_update = false, array $fields = array(), $force = false) {
	    $this->AssertID();
	    
	    if(empty($this->update_fields) && (!$force || empty($fields))) {
	        if($ignore_no_update)
	           AppLog::Report('No fields to update (ignored)');
	        else
	           MakeWarning('No fields to update');
	        
	        return;
        }
        
	    $update_fields = array_keys($this->update_fields);
	    if(!empty($fields))
	       $update_fields = array_intersect($update_fields, $fields);
	    
	    $data = array_intersect_key($this->data, array_flip($update_fields));
	    
        ksort($data);
	    sort($update_fields);
	    
        foreach($update_fields as &$field)
            $field = $field.' = %s';
        
        $query_set = implode(', ', $update_fields);
        
        $result = $this->db->query_array("
                UPDATE $this->table_name
                SET $query_set
                WHERE $this->primary_key = %s
            ", array_merge($data, array($this->Id())));
        
        $this->update_fields = array();
        
        if($result)
            return $this->db->affected_rows;
        else
            return false;
    }
    
    public function Delete() {
        $this->AssertID();
        
        return $this->db->query("
                DELETE FROM $this->table_name
                WHERE $this->primary_key = %s
            ", $this->Id());
    }
    
    public function __get($alias) {
        if(isset($this->model_cache[$alias])) {
            return $this->model_cache[$alias];
        }
        else {
            if(isset($this->related_models[$alias])) {
                $this->HandleModelLookup($alias, false);
                return $this->model_cache[$alias];
            }
            else {
                MakeWarning('Undefined related model alias', $alias);
                return NULL;
            }
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return NULL;
    }
    
    public function offsetGet($item) {
        if(!isset($this[$item]) && ($item != $this->primary_key) && $this->HasID()) {
            if(in_array($item, $this->fields))
                $this->Lookup($item);
            else
                MakeWarning('Invalid model field', $item);
        }
        
        return $this->data[$item];
    }
    
    public function offsetSet($item, $value) {
        MakeError('Cannot set data directly, use Set functions', array($item => $value));
    }
    
    public function offsetExists($item) {
        return isset($this->data[$item]);
    }
    
    public function offsetUnset($item) {
        MakeError('Cannot change data directly, use Set functions', array($item => $value));
    }
    
    public function getIterator() {
        return new ArrayIterator($this->data);
    }
}