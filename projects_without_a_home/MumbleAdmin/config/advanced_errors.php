<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Advanced error reporting config.
 */

/**
 * Enable/disable advanced error reporting.
 *
 * WARNING:
 * Anything built with advanced error reporting will NOT log any errors if this
 * is disabled.
 */
$config['adverrors_enable'] = TRUE;

/**
 * This is the message displayed to viewers of your website when they encounter
 * an error.
 */
$config['adverrors_public_error'] = '
    Woops! It looks like an internal error has occured. We apologize for any
    inconveniences this has caused and are looking into resolving this issue
    as soon as possible.
';

/**
 * Email settings to send the report.
 * 
 * If email is disabled, only a log entry without detailed information will be
 * created.
 */
$config['adverrors_email_enable'] = TRUE;
$config['adverrors_email_warnings'] = TRUE;
$config['adverrors_email_from'] = 'no-reply@definiteblue.com';
$config['adverrors_email_from_name'] = 'MyMumble';
$config['adverrors_email_to'] = array('james@jameskoshigoe.com');
$config['adverrors_email_subject'] = 'Error Report';
$config['adverrors_email_warning_subject'] = 'Warning Report';
$config['adverrors_email_date_format'] = 'F n, Y g:i:s a T';

/**
 * Include last php error (if available).
 */
$config['adverrors_include_last_error'] = TRUE;

/**
 * Include a function backtrace.
 */
$config['adverrors_include_backtrace'] = TRUE;