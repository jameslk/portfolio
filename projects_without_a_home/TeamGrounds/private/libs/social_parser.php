<?php

class SocialParser {
    protected function Parse_YouTube(&$str) {
        $str = preg_replace(
            '/(http:\/\/www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_]+)[^<\s]*/m',
            '<object width="425" height="355">
            <param name="allowScriptAccess" value="never" />
            <param name="allowNetworking" value="internal" />
            <param name="movie" value="http://www.youtube.com/v/$2&hl=en&rel=0" />
            <param name="wmode" value="transparent" />
            <embed type="application/x-shockwave-flash"
                allowScriptAccess="never" allowNetworking="internal"
                src="http://www.youtube.com/v/$2&hl=en&rel=0"
                width="425" height="355" wmode="transparent" />
            </object>',
            $str, 3
        );
    }
    
    public function Parse($str) {
        $this->Parse_YouTube($str);
        
        return $str;
    }
}