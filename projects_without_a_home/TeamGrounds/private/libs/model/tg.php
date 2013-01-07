<?php

Libs('model', 'database/tg', 'social_parser', 'bb_parser');

class M_Tg extends Model {
    public function __construct($id = false) {
	    parent::__construct($id);
        $this->db = DB_Tg::Instance()
            or $this->db = new DB_Tg();
    }
    
    static public function SanitizeText($text) {
        return trim($text);
    }
    
    public function GetSafe($field) {
        return htmlentities($this[$field], ENT_COMPAT, 'UTF-8');
    }
    
    public function GetParsed($field) {
        $this->AssertID();

        $data = $this->GetSafe($field);
        
        /* Convert newlines to HTML */
        $data = nl2br($data);

        /* Parse Social Text */
        $social_parser = new SocialParser;
        $data = $social_parser->Parse($data);
        
        /* Parse BB Code */
        $bb_parser = new BBParser;
        $data = $bb_parser->Parse($data);

        return $data;
    }
}