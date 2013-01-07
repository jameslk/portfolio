<?php

Libs('thirdparty/smarty/libs/Smarty.class', 'smarty_mods');

class Template extends Smarty {
    function __construct() {
        parent::__construct();
        
        $this->template_dir = TEMPLATE_DIR;
        $this->compile_dir = TEMPLATE_DIR.'/compile';
        $this->cache_dir = TEMPLATE_DIR.'/cache';
        
        $constants = get_defined_constants(true);
        $constants = $constants['user'];
        
        $config_array = array();
        foreach($constants as $constant => $value) {
            if(!strncmp('CFG_', $constant, 4))
                $config_array[$constant] = $value;
            else if(!strcmp('_DIR', substr($constant, -4)))
                $config_array[$constant] = $value;
        }
        
        $this->assign($config_array);
        
        $this->assign('uri', $_SERVER['REQUEST_URI']);
        
        $smarty_mods = new SmartyMods($this);
    }
}