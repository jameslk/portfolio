<?php

class Schema_MySQL extends Model {
    public $tables = array(
        'ci_sessions' => "(
            session_id varchar(40) DEFAULT '0' NOT NULL,
            ip_address varchar(16) DEFAULT '0' NOT NULL,
            user_agent varchar(50) NOT NULL,
            last_activity int(10) unsigned DEFAULT 0 NOT NULL,
            user_data text NOT NULL,
            PRIMARY KEY (session_id)
        )",
        
        'servers' => "(
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            
            virtual_id INT UNSIGNED NOT NULL DEFAULT '0',
            
            name CHAR(255) NOT NULL DEFAULT '',
            title CHAR(255) NOT NULL DEFAULT '',
            
            is_suspended BOOL NOT NULL DEFAULT false,
            
            -- Table Settings
            PRIMARY KEY (id),
            UNIQUE KEY virtual_id (virtual_id),
            UNIQUE KEY name (name)
        )",
        
        'murmurs' => "(
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            
            name CHAR(255) NOT NULL DEFAULT '',
            location CHAR(255) NOT NULL DEFAULT '',
            
            ice_address CHAR(255) NOT NULL DEFAULT '',
            
            -- Table Settings
            PRIMARY KEY (id),
            UNIQUE KEY name (name)
        )",
        
        'join_murmurs_servers' => "(
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            
            murmur_id INT UNSIGNED NOT NULL DEFAULT '0',
            server_id INT UNSIGNED NOT NULL DEFAULT '0',
            
            -- Table Settings
            PRIMARY KEY (id)
        )",
        
        'users' => "(
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            
            email CHAR(255) NOT NULL DEFAULT '',
        	password CHAR(128) NOT NULL DEFAULT '',
        	
        	reg_id INT UNSIGNED NOT NULL DEFAULT '0',
        	
        	is_gadmin BOOL NOT NULL DEFAULT false,
        	
        	is_owner BOOL NOT NULL DEFAULT false,
            
            can_startstop BOOL NOT NULL DEFAULT false,
            can_editconf BOOL NOT NULL DEFAULT false,
            can_moderate BOOL NOT NULL DEFAULT false,
            can_setacl BOOL NOT NULL DEFAULT false,
        	
        	timezone SMALLINT NOT NULL DEFAULT '0',
        	languageid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
        	
        	lastseen INT UNSIGNED NOT NULL DEFAULT '0',
        	ipaddress CHAR(15) NOT NULL DEFAULT '',
            
            -- Table Settings
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        )",
        
        'join_servers_users' => "(
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            
            server_id INT UNSIGNED NOT NULL DEFAULT '0',
            user_id INT UNSIGNED NOT NULL DEFAULT '0',
            
            -- Table Settings
            PRIMARY KEY (id)
        )",
        
        'user_autologins' => "(
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            
            user_id INT UNSIGNED NOT NULL DEFAULT '0',
            
            login_key CHAR(32) NOT NULL DEFAULT '',
            start_time INT UNSIGNED NOT NULL DEFAULT '0',
            
            -- Table Settings
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id),
            UNIQUE KEY login_key (login_key)
        )",
    );
    
    public function has_error() {
        return $this->db->_error_number() ? TRUE : FALSE;
    }
    
    public function error_message() {
        return $this->db->_error_message();
    }
    
    public function connect($hostname, $port, $dbname, $username, $password, $dbprefix) {
        $config['hostname'] = $hostname;
        $config['username'] = $username;
        $config['password'] = $password;
        $config['database'] = $dbname;
        $config['dbdriver'] = 'mysql';
        $config['dbprefix'] = $dbprefix;
        $config['pconnect'] = FALSE;
        $config['char_set'] = 'utf8';
        $config['dbcollat'] = 'utf8_general_ci';
        
        if($port)
            $config['port'] = $port;
        
        $this->load->database($config);
        $this->load->dbforge();
        
        return !$this->has_error();
    }
    
    public function tables_exist() {
        $existing_tables = $this->db->list_tables();
        
        foreach($this->tables as $table => $sql) {
            if(in_array($this->db->dbprefix.$table, $existing_tables))
                return TRUE;
        }
        
        return FALSE;
    }
    
    public function create_tables() {
        foreach($this->tables as $table => $sql) {
            $this->db->simple_query('CREATE TABLE IF NOT EXISTS `'
                .$this->db->dbprefix.$table.'` '.$sql);
            
            if($this->has_error())
                return FALSE;
        }
        
        return TRUE;
    }
    
    public function drop_tables() {
        foreach($this->tables as $table => $sql) {
            $this->dbforge->drop_table($table);
            
            if($this->has_error())
                return FALSE;
        }
        
        return TRUE;
    }
    
    public function create_root_admin($email, $password) {
        $this->db->insert('users', array(
            'email' => $email,
            'password' => $password,
            'is_gadmin' => TRUE
        ));
        
        return !$this->has_error();
    }
}