<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Auth library config
 */

$config['auth_autologin_key_expire'] = 60*5; //5 minutes

$config['auth_autologin_cookie'] = 'ma_login';
$config['auth_autologin_cookie_expire'] = 60*60*24*30; //1 month