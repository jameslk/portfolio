<?php

Libs('database/singleton');

class DB_Tg extends DB_Singleton {
    public function __construct($db = CFG_TGDB_NAME) {
        parent::__construct($db, CFG_TGDB_USERNAME, CFG_TGDB_PASSWORD, CFG_TGDB_SERVER);
        
        $this->set_charset('utf8');
    }
}