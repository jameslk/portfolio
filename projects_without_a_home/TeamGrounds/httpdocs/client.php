<?php

define('ROOT_DIR', '../');
require_once(ROOT_DIR.'private/global/global.php');

Libs('common', 'applog');

AppLog::$write_log = false; //don't log this session

if(!isset($_GET['path']))
    Exit_404();

$path = $_GET['path'];

if(!preg_match('/^(?:[a-z0-9_-]\/?|\.(?!\.))*\.(?:js|css)$/iD', $path))
    Exit_404(); //possible hack attempt

$full_path = CLIENT_DIR.'/'.$path;

if(!file_exists($full_path) || !is_file($full_path))
    Exit_404();

if(pathinfo($path, PATHINFO_EXTENSION) == 'js')
    $mime_type = 'javascript/text';
else
    $mime_type = 'css/text';

header("Content-Type: $mime_type; charset: UTF-8");
header("Cache-Control: must-revalidate");

ob_start('ob_gzhandler');

readfile($full_path);