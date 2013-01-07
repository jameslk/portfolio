<?php

/**
 * @file global.php
 * @brief Globally shared code and configs are loaded here.
 */

if(!defined('ROOT_DIR'))
    define('ROOT_DIR', '../../');
/* ----- */

require_once(ROOT_DIR.'private/global/paths.php');
require_once(GLOBAL_DIR.'/config.php');
/* ----- */

//putenv('TZ='.CFG_TIMEZONE); //set the timezone for all date/time functions

mb_language('uni');
mb_internal_encoding('UTF-8');

if(session_id() === '')
    session_start();

/* Global Functions */

/**
 * @brief Load necessary scripts from the lib directory.
 * @param string
 * @param string [...]
 */
function Libs() {
    $args = func_get_args();
    
	foreach($args as $arg) {
		require_once(LIB_DIR.'/'.$arg.'.php');
    }
}

Libs('error', 'applog');

register_shutdown_function(array('AppLog', 'Write'));

AppLog::Report('Global initialization finished');