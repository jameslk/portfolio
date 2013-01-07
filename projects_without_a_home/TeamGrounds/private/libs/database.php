<?php

class Database extends mysqli {
	public $last_query;
	
    public function __construct($dbname, $username, $password, $server = 'p:localhost') {
        parent::__construct($server, $username, $password);
        
        if($this->connect_error)
            MakeError('Failed to connect to database.',
                array_merge(compact('server', 'username', 'password'), array(mysqli_connect_error())));
        
        $this->select_db($dbname)
            or MakeError('Failed to select database.', compact('dbname'));
        
        AppLog::Report('Connection to database successful', compact('server', 'username', 'password', 'dbname'));
    }
    
    public function escape($str) {
        return $this->escape_string($str);
    }
    
    public function vsqlprintf($query_logic, array $query_data) {
        foreach($query_data as &$data) {
            if(!is_numeric($data))
                $data = $this->escape($data);
        }
        
        /* Insert quotes around format vars unless they already are inside quotes */
        $len = strlen($query_logic);
        $inside_quotes = false;
        $inside_var = false;
        for($i = 0; $i < $len; ++$i) {
            if($query_logic[$i] == "'" && $query_logic[$i-1] != '\\') {
                $inside_quotes = !$inside_quotes;
            }
            else if(!$inside_quotes && $query_logic[$i] == '%') {
                if($query_logic[$i+1] == '%') {
                    /* Skip escaped % */
                    ++$i;
                }
                else {
                    $query_logic = substr_replace($query_logic, "'%", $i, 1);
                    
                    $inside_var = true;
                    ++$len;
                    ++$i;
                }
            }
            else if($inside_var && strpos('bcdeufFosxX', $query_logic[$i])) {
                $query_logic = substr_replace($query_logic, $query_logic[$i]."'", $i, 1);
                
                $inside_var = false;
                ++$len;
                ++$i;
            }
        }
        
        return vsprintf($query_logic, $query_data);
    }
    
    public function sqlprintf(/*$query_logic, ... */) {
        $query_data = func_get_args();
        $query_logic = array_shift($query_data);
        
        return $this->vsqlprintf($query_logic, $query_data);
    }
    
    public function query_array($query_logic, array $query_data) {
        $query = $this->vsqlprintf($query_logic, $query_data);
        
        $result = parent::query($query)
            or MakeError('Failed to perform database query.', $query);
        
        AppLog::Report('Query executed', array('class' => get_class($this), 'query' => $query));
        
        return $result;
    }
    
    public function query(/* $query_logic, ... */) {
        $args = func_get_args();
        $query_logic = array_shift($args);
        
        return $this->query_array($query_logic, $args);;
    }
    
    public function query_first_array($query_logic, array $query_data) {
        $result = $this->query_array($query_logic, $query_data);
        
        if(!$result || !$result->num_rows)
            return false;
        
        $row = $result->fetch_array(MYSQLI_NUM);
        
        $result->close();
        
        return $row[0];
    }
    
    public function query_first(/* $query_logic, ... */) {
        $args = func_get_args();
        $query_logic = array_shift($args);
        
        return $this->query_first_array($query_logic, $args);
    }
    
    public function prepare($query) {
    	$this->last_query = $query;
    	$stmt = parent::prepare($query)
    		or MakeError('Failed to prepare database query.', $query);
    	
    	AppLog::Report('Query prepared', array('class' => get_class($this), 'query' => $query));
    	
    	return $stmt;
    }
    
    public function stmt_execute(&$stmt) {
    	$stmt->execute()
            or MakeError('Failed to execute prepared database query.', $this->last_query);
    }
    
    public function execute(/* $query, $types, ... */) {
    	$params = func_get_args();
    	$query = array_shift($params);
    	
        $stmt = $this->prepare($query);
        
        call_user_func_array(array($stmt, 'bind_param'), $params);
        
        $this->stmt_execute($stmt);
        
        return $stmt;
    }
}