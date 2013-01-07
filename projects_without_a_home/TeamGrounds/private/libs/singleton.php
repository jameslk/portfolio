<?php

interface Singleton {    
    static public function Instance();
    
    public function __clone();
    public function __wakeup();
}