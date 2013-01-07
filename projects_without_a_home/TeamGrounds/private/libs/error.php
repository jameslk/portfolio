<?php

/**
 * Server error handler.
 */

Libs('sendmail', 'applog');
/* ----- */

$GLOBALS['php_errors'] = array(
    E_ERROR              => 'Error',
    E_WARNING            => 'Warning',
    E_PARSE              => 'Parsing Error',
    E_NOTICE             => 'Notice',
    E_CORE_ERROR         => 'Core Error',
    E_CORE_WARNING       => 'Core Warning',
    E_USER_ERROR         => 'User Error',
    E_USER_WARNING       => 'User Warning',
    E_USER_NOTICE        => 'User Notice',
    E_STRICT             => 'Runtime Notice',
    E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
);

set_error_handler('ErrorHandler');
assert_options(ASSERT_CALLBACK, 'AssertHandler');

function MakeError($report, $additional = false) {
    global $error_title;
    
    AppLog::Report('Error:<br />'.$report, $additional);
    AppLog::Write();
    
    $mail = new SendMail;
    
    $mail->AddTo(CFG_ADMIN_EMAIL);
    $mail->From('no-reply@definiteblue.com', CFG_NAME);
    
    $mail->Subject('Website Error');
    $mail->IsHTML(true);
    
    if(is_array($additional)) {
        $additional_str = '';
        foreach($additional as $var => $val)
            $additional_str .= "$var: $val<br /><br />";
        
        $additional = $additional_str;
    }
    
    if($additional)
        $addinfo = '<h2>Additional Information</h2><p>'.$additional.'</p>';
    else
        $addinfo = '';
    
    if(($error = error_get_last()) != NULL) {
        $lasterror = '<h2>Last Error</h2>
            <p>Error Type: '.$error['type'].'
            <br />Error Message: '.$error['message'].'
            <br />File: '.$error['file'].'
            <br />Line Number: '.$error['line'].'</p>';
    }
    else {
        $lasterror = '';
    }
    
    ob_start();
    debug_print_backtrace();
    $backtrace = nl2br(ob_get_contents());
    ob_end_clean();
    
    $mail->Body('
        <html>
        <head>
        <title>Website Error</title>
        </head>
        <body>
        <h1>A server error was encountered!</h1>
        
        <p>Website: '.CFG_NAME.'
        <br />Date: '.date('F n, Y g:i:s a T').'
        <br />IP address: '.$_SERVER['REMOTE_ADDR'].' ('.(!empty($_SERVER['REMOTE_ADDR']) ? gethostbyaddr($_SERVER['REMOTE_ADDR']) : '').')
        <br />URI: '.$_SERVER['REQUEST_URI'].'
        </p>
        
        <h2>Report</h2>
        <p>'.$report.'</p>
        '.$addinfo.$lasterror.'
        <h2>Backtrace</h2>
        <p>'.$backtrace.'</p>
        </body>
        </html>'
    );
    
    $mail->Send();
}

function MakeWarning($report, $additional = false) {
    global $error_title;
    
    AppLog::Report('Warning:<br />'.$report, $additional);
    
    $mail = new SendMail;
    
    $mail->AddTo(CFG_ADMIN_EMAIL);
    $mail->From('no-reply@definiteblue.com', CFG_NAME);
    
    $mail->Subject('Website Warning');
    $mail->IsHTML(true);
    
    if(is_array($additional)) {
        $additional_str = '';
        foreach($additional as $var => $val)
            $additional_str .= "$var: $val<br /><br />";
        
        $additional = $additional_str;
    }
    
    if($additional)
        $addinfo = '<h2>Additional Information</h2><p>'.$additional.'</p>';
    else
        $addinfo = '';
    
    ob_start();
    debug_print_backtrace();
    $backtrace = nl2br(ob_get_contents());
    ob_end_clean();
    
    $mail->Body('
        <html>
        <head>
        <title>Website Warning</title>
        </head>
        <body>
        <h1>A server warning was encountered!</h1>
        
        <p>Website: '.CFG_NAME.'
        <br />Date: '.date('F n, Y g:i:s a T').'
        <br />IP address: '.$_SERVER['REMOTE_ADDR'].' ('.(!empty($_SERVER['REMOTE_ADDR']) ? gethostbyaddr($_SERVER['REMOTE_ADDR']) : '').')
        <br />URI: '.$_SERVER['REQUEST_URI'].'
        </p>
        
        <h2>Report</h2>
        <p>'.$report.'</p>
        '.$addinfo.'
        <h2>Backtrace</h2>
        <p>'.$backtrace.'</p>
        </body>
        </html>'
    );
    
    $mail->Send();
}

function ErrorHandler($errno, $errmsg, $filename, $linenum, $vars) {
    global $php_errors;
    
    $err = 'Error Number: '.$errno.'
        <br />Error Type: '.$php_errors[$errno].'
        <br />Error Message: '.$errmsg.'
        <br />File: '.$filename.'
        <br />Line Number: '.$linenum;
    
    switch($errno) {
        case E_ERROR:
        case E_PARSE:
        case E_CORE_ERROR:
        case E_USER_ERROR:
        case E_RECOVERABLE_ERROR:
            MakeError($err);
            break;
        
        case E_WARNING:
        case E_CORE_WARNING:
        case E_USER_WARNING:
            MakeWarning($err);
            break;
        
        default:
            return false;
    }
}

function AssertHandler($file, $line, $code) {
    $report = "<p>
            Assertion Failed:
            File: $file<br />
            Line: $line<br />
            Code: $code
        <p />";
    
    MakeError('An internal website error occured.', $report);
}