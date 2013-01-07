<?php

Libs('database', 'singleton');

class DB_Singleton extends Database implements Singleton {
    static protected $instance = false;
    
    public function __construct($dbname, $username, $password, $server = 'localhost') {
    	if(self::$instance)
    		MakeError('Second instance of singleton class "'.self.'" not allowed');
    	else
    	   self::$instance = $this;
    	
        parent::__construct($dbname, $username, $password, $server);
    }
    
    static public function Instance() {
        if(self::$instance)
            return self::$instance;
        else
            return false;
    }
    
    public function __clone() {
        MakeError('Clone of singleton class "'.self.'" not allowed');
    }
    
    public function __wakeup() {
        MakeError('Deserializing of singleton class "'.self.'" not allowed');
    }
}