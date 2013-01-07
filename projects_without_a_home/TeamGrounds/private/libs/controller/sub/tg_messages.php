<?php

Libs('controller/sub/tg');

class SC_TG_Messages extends SC_Tg {
    public $template_name = 'tg_messages.tpl';
    
    public $message_data = array();
    public $has_errors = false;
    
    public function Notice($notice) {
        $this->message_data[] = array(
            'type' => 'notice',
            'message' => $notice
        );
    }
    
    public function Success($message) {
        $this->message_data[] = array(
            'type' => 'success',
            'message' => $message
        );
    }
    
    public function Error($error) {
        $this->message_data[] = array(
            'type' => 'error',
            'message' => $error
        );
        
        $this->has_errors = true;
    }
    
    public function FieldError($field, $error = '') {
        if(!$error)
            $error = 'Invalid input supplied for "'.$field.'"';
        
        $this->message_data[] = array(
            'type' => 'fielderror',
            'message' => $error,
            'field' => $field
        );
        
        $this->has_errors = true;
    }
    
    public function FailDataError(array $fail_data) {
        foreach($fail_data as $field => $data) {
            if($data === false)
                $this->FieldError($field);
        }
        
        $this->has_errors = true;
    }

    protected function Action_Default() {
        $this->template->assign('message_data', $this->message_data);
        if(!empty($this->message_data))
            AppLog::Report('TG interface messages', var_export($this->message_data, true));
    }
}