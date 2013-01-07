<?php

Libs('thirdparty/jsmin/jsmin', 'thirdparty/csstidy/class.csstidy');

class ClientPacker {
    static public $used_paths = array();
    
    static public function Pack($type, $code) {
        if(!CFG_CLIENT_PACK)
            return $code;
        
        switch($type) {
            case 'js':
                return trim(JSMin::minify($code));
            
            case 'css':
                $tidy = new csstidy();
                $tidy->load_template('highest_compression');
                $tidy->parse($code);
                return $tidy->print->plain();
        }
    }
    
    static public function PackFile($type, $path, $html_attributes = '') {
        if(isset(self::$used_paths[$path]))
            return false; //file has already been added
        
        if(!preg_match('/^(?:[a-z0-9_-]\/?|\.(?!\.))*\.(?:js|css)$/iD', $path)) {
            MakeWarning('Invalid path specified', compact('path'));
            return false;
        }
        
        $full_path = CLIENT_DIR.'/'.$type.'/'.$path;
        
        if(!file_exists($full_path) || !is_file($full_path)) {
            MakeWarning('File does not exists', compact('full_path'));
            return false;
        }
        
        self::$used_paths[$path] = true;
        
        if(!CFG_CLIENT_PACK)
            return $type.'/'.$path;
        
        $cache_path = 'cache/'.$type.'/'.md5($path).'.'.$type;
        $full_cache_path = CLIENT_DIR.'/'.$cache_path;
        
        if(!file_exists($full_cache_path) || (filemtime($full_path) > filemtime($full_cache_path))) {
            $code = self::Pack($type, file_get_contents($full_path));
            if($code)
                file_put_contents($full_cache_path, $code);
            else
                return false;
        }
        
        return $cache_path;
    }
}