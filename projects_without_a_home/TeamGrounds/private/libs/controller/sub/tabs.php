<?php

Libs('controller/sub/pages', 'common');

class SC_Tabs extends SC_Pages {
    public $template_subpath = 'tabs/';
    public $template_name = 'tabs.tpl';
    
    public $page_var = 'tab';
    
    public $page_func_prefix = 'Tab_';
    
    public function Tab() {
        return $this->Page();
    }
    
    public function Content() {
        $this->parent->template->assign('tab', $this->Tab());
        $this->parent->template->assign('tab_title', $this->pages[$this->Tab()]);
        
        $this->template->assign('tabs', $this->pages);
        $this->template->assign('selected_tab', $this->Page());
        $this->template->assign('tab_var', $this->page_var);
        
        return C_Sub::Content();
    }
}