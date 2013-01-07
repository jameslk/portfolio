<?php

Libs('template');

class AppLog {
    static public $applog = array();
    static public $start_time = 0;
    static public $write_log = true;
    
    static function Initialize() {
        self::$start_time = microtime(true);
    }

    static public function Report($report, $info = false) {
        $data = array(
            'time' => number_format(microtime(true) - self::$start_time, 4),
            'report' => $report
        );
        
        if($info) {
            if(is_array($info)) {
                $info_str = '';
                foreach($info as $var => $val)
                    $info_str .= "$var: $val<br /><br />";
                
                $info = $info_str;
            }
            
            $data['info'] = $info;
        }
        
        self::$applog[] = $data;
    }
    
    static public function Write() {
        self::Report('AppLog finalized');
        
        if(!self::$write_log)
            return;
        
        $template = new Template;
        $template->assign('applog', self::$applog);
        
        $file = fopen(CFG_APPLOG_FILE, 'w');
        fwrite($file, $template->fetch(CFG_APPLOG_TEMPLATE));
        fclose($file);
    }
}

AppLog::Initialize();