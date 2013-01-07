<?php

/**
 * @file paths.php
 * @brief Global configuration for all paths.
 */

if(!defined('ROOT_DIR'))
    define('ROOT_DIR', '../../');
/* ----- */

/* Public directory path */
define('PUBLIC_DIR', realpath(ROOT_DIR.'httpdocs'));

/* Private directory path */
define('PRIVATE_DIR', realpath(ROOT_DIR.'private'));

/* Global directory path */
define('GLOBAL_DIR', realpath(PRIVATE_DIR.'/global'));

/* Library directory path */
define('LIB_DIR', realpath(PRIVATE_DIR.'/libs'));

/* Template directory path */
define('TEMPLATE_DIR', realpath(PRIVATE_DIR.'/templates'));

/* Client directory path */
define('CLIENT_DIR', realpath(PRIVATE_DIR.'/client'));