<?php

define('ROOT_DIR', '../');
require_once(ROOT_DIR.'private/global/global.php');

Libs('controller/mdst_interface', 'database/mdst');
/* ----- */

class C_CreateSalesRep extends C_MDST_Interface {
    public $title = 'Create Sales Rep';
    
    protected function Action_Default() {
        parent::Action_Default();
    }
}

$interface = new C_CreateSalesRep;
$interface->Execute();