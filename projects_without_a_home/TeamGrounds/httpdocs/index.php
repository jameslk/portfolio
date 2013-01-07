<?php

define('ROOT_DIR', '../');
require_once(ROOT_DIR.'private/global/global.php');

Libs('controller/tg_interface');

/* ----- */

class C_Index extends C_TG_Interface {
    public $template_name = 'index.tpl';
    
    function Action_Default() {
    }
}

$interface = new C_Index;
$interface->Display();