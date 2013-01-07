<?php

Libs('controller/sub', 'common');

class SC_Pages extends C_Sub {
    public $pages = array();
    
    public $page_var = 'page';
    
    public $page_func_prefix = 'Page_';
    
    function __construct(Controller $parent, array $pages) {
        parent::__construct($parent);
        
        $this->pages = $pages;
    }
    
    public function Page() {
        $page = $_REQUEST[$this->page_var];
        if(isset($this->pages[$page]))
            return $page;
        else
            return key($this->pages);
    }
    
    public function Action_Default() {
        $page = $this->Page();
        if($page === false)
            return;
        
        $page_func = $this->page_func_prefix.ConvertNameStyle($page);
        if(method_exists($this->parent, $page_func))
            call_user_func(array($this->parent, $page_func), $page);
    }
    
    public function Content() {
        $this->parent->template->assign('page', $this->Page());
        $this->parent->template->assign('page_title', $this->pages[$this->Page()]);
        
        $this->template->assign('pages', $this->pages);
        $this->template->assign('selected_page', $this->Page());
        $this->template->assign('page_var', $this->page_var);
        
        return parent::Content();
    }
}