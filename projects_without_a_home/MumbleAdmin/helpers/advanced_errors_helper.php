<?php

if(!function_exists('make_error')) {
    function make_error($report, $additional_info = NULL) {
        log_message('error', $report);
        
        $CI =& get_instance();
        
        $CI->config->load('advanced_errors', FALSE, TRUE);
        
        $public_error = $CI->config->item('adverrors_public_error');
        if($public_error === FALSE)
            $public_error = 'An error has occured.';
        
        if(!$CI->config->item('adverrors_enable')) {
            show_error($public_error);
            return; //advanced error reporting is disabled
        }
        
        if($CI->config->item('adverrors_email_enable')) {
            $CI->load->library('email');
            
            $CI->email->from($CI->config->item('adverrors_email_from'),
                $CI->config->item('adverrors_email_from_name'));
            
            $CI->email->to($CI->config->item('adverrors_email_to'));
            
            $CI->email->subject($CI->config->item('adverrors_email_subject'));
            
            $message = "A server error was encountered!"
                      ."\n\nWebsite: ".$CI->config->site_url()
                      ."\nDate: ".date($CI->config->item('adverrors_email_date_format'))
                      ."\nIP Address: ".$_SERVER['REMOTE_ADDR'].' ('.(!empty($_SERVER['REMOTE_ADDR']) ? gethostbyaddr($_SERVER['REMOTE_ADDR']) : '').')'
                      ."\nURI: ".$_SERVER['REQUEST_URI']
                      ."\n\nReport:\n".$report;
            
            if($additional_info !== NULL) {
                if(!is_string($additional_info))
                    $additional_info = var_export($additional_info, true);
                
                $message .= "\n\nAdditional Information:\n"
                           .$additional_info;
            }
            
            if($CI->config->item('adverrors_include_last_error')
                && ($last_error = error_get_last())) {
                    $message .= "\n\nLast PHP Error:"
                               ."\nError Type: ".$last_error['type']
                               ."\nError Message: ".$last_error['message']
                               ."\nFile: ".$last_error['file']
                               ."\Line Number: ".$last_error['line'];
            }
            
            if($CI->config->item('adverrors_include_backtrace')) {
                ob_start();
                debug_print_backtrace();
                $backtrace = nl2br(ob_get_contents());
                ob_end_clean();
                
                $message .= "\n\nBacktrace:\n"
                           .$backtrace;
            }
            
            $CI->email->message($message);
            $CI->email->send();
        }
        
        show_error($public_error);
    }
}


if(!function_exists('make_warning')) {
    function make_warning($report, $additional_info = NULL) {
        log_message('debug', $report);
        
        $CI =& get_instance();
        
        $CI->config->load('advanced_errors', FALSE, TRUE);
        
        if(!$CI->config->item('adverrors_enable'))
            return; //advanced error reporting is disabled
        
        if($CI->config->item('adverrors_email_enable') && $CI->config->item('adverrors_email_warnings')) {
            $CI->load->library('email');
            
            $CI->email->from($CI->config->item('adverrors_email_from'),
                $CI->config->item('adverrors_email_from_name'));
            
            $CI->email->to($CI->config->item('adverrors_email_to'));
            
            $CI->email->subject($CI->config->item('adverrors_email_warning_subject'));
            
            $message = "A server warning was encountered!"
                      ."\n\nWebsite: ".$CI->config->site_url()
                      ."\nDate: ".date($CI->config->item('adverrors_email_date_format'))
                      ."\nIP Address: ".$_SERVER['REMOTE_ADDR'].' ('.(!empty($_SERVER['REMOTE_ADDR']) ? gethostbyaddr($_SERVER['REMOTE_ADDR']) : '').')'
                      ."\nURI: ".$_SERVER['REQUEST_URI']
                      ."\n\nReport:\n".$report;
            
            if($additional_info !== NULL) {
                if(!is_string($additional_info))
                    $additional_info = var_export($additional_info, true);
                
                $message .= "\n\nAdditional Information:\n"
                           .$additional_info;
            }
            
            if($CI->config->item('adverrors_include_last_error')
                && ($last_error = error_get_last())) {
                    $message .= "\n\nLast PHP Error:"
                               ."\nError Type: ".$last_error['type']
                               ."\nError Message: ".$last_error['message']
                               ."\nFile: ".$last_error['file']
                               ."\Line Number: ".$last_error['line'];
            }
            
            if($CI->config->item('adverrors_include_backtrace')) {
                ob_start();
                debug_print_backtrace();
                $backtrace = nl2br(ob_get_contents());
                ob_end_clean();
                
                $message .= "\n\nBacktrace:\n"
                           .$backtrace;
            }
            
            $CI->email->message($message);
            $CI->email->send();
        }
    }
}