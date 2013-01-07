<?php

Libs('controller/sub');

class SC_Tg extends C_Sub {
    public $template_subpath = 'tg_interface/sub/';
    
    public $session;
    public $messages;
    
    function __construct(C_TG_Interface $parent) {
        parent::__construct($parent);
        
        $this->session =& $parent->session;
        $this->messages =& $parent->messages;
    }
    
    public function Content() {
        $this->template->assign('session', $this->session);
        
        if($this->session->IsActive())
            $this->template->assign('session_user', $this->session->user);
        else
            $this->template->assign('session_user', false);
        
        return parent::Content();
    }
}