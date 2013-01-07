<?php

define('ROOT_DIR', '../');
require_once(ROOT_DIR.'private/global/global.php');

Libs('controller/mdst_interface', 'database/mdst');

require_once(PUBLIC_DIR.'/enter_sale.php');
/* ----- */

class C_Index extends C_EnterSale {
    public $template_name = 'enter_sale.tpl';
}

$interface = new C_Index;
$interface->Execute();