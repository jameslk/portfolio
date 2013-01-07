<?php

/**
 * @file config.php
 * @brief Global configuration file.
 */

/* General Settings */
define('CFG_NAME', 'TeamGrounds');
define('CFG_URL', 'http://www.teamgrounds.com'); //No trailing slash!
define('CFG_ADMIN_EMAIL', 'excess@gmail.com'); //Website Developer
define('CFG_DEBUG_MODE', false);

define('CFG_CLIENT_PACK', false);

/* See http://www.php.net/manual/en/timezones.php for a list of timezones */
define('CFG_TIMEZONE', 'America/Denver');

define('CFG_APPLOG_TEMPLATE', TEMPLATE_DIR.'/applog.tpl');
define('CFG_APPLOG_FILE', PUBLIC_DIR.'/logs/applog.html');

define('CFG_SESSION_EXPIRE', 60*60*12); //in seconds
define('CFG_COOKIE_EXPIRE', 60*60*24*30*2); //in seconds

/* How long before a user is considered offline (in seconds) */
define('CFG_ONLINE_DURATION', 60*10);

/* Comments */
define('CFG_COMMENTS_MAX_THREADS_PER_PAGE', 20);

/* BlueAnalytics Database Information */
define('CFG_TGDB_SERVER', 'db.teamgrounds.com');
define('CFG_TGDB_USERNAME', 'teamgrounds');
define('CFG_TGDB_PASSWORD', 'teamgrounds');
define('CFG_TGDB_NAME', 'teamgrounds');