<?php

Libs('controller/sub/pages', 'common');

class SC_Steps extends SC_Pages {
    public $template_subpath = 'steps/';
    public $template_name = 'steps.tpl';
    
    public $page_var = 'step';
    
    public $page_func_prefix = 'Step_';
    
    public $next_step = NULL;
    
    public function Step() {
        return $this->Page();
    }
    
    public function NextStep() {
        if($this->next_step !== NULL)
            return $this->next_step;
        
        $current_step = $this->Step();
        
        reset($this->pages);
        while(list($step) = each($this->pages)) {
            if($step == $current_step) {
                $this->next_step = key($this->pages);
                reset($this->pages);
                return $this->next_step;
            }
        }
    }
    
    public function GoTo($step, $vars = false, $remove_vars = array()) {
        if(!$vars)
            $vars = array();
        
        if(isset($this->pages[$step]))
            $this->Redirect(array_merge(array($this->page_var => $step),
                $vars), $remove_vars);
        else
            MakeWarning('Invalid step', $step);
    }
    
    public function GoToFirst($vars = false, $remove_vars = array()) {
        if(!$vars)
            $vars = array();
        
        reset($this->pages);
        
        $this->Redirect(array_merge(array($this->page_var => key($this->pages)),
            $vars), $remove_vars);
    }
    
    public function GoToNext($ignore_if_last_step = false, $vars = false, $remove_vars = array()) {
        if(!$vars)
            $vars = array();
        
        if($this->NextStep())
            $this->Redirect(array_merge(array($this->page_var => $this->NextStep()),
                $vars), $remove_vars);
        else if(!$ignore_if_last_step)
            MakeWarning('Unable to go to next step: no more steps', $step);
    }
    
    public function Content() {
        $this->parent->template->assign('step', $this->Step());
        $this->parent->template->assign('step_title', $this->pages[$this->Step()]);
        
        $this->template->assign('steps', $this->pages);
        $this->template->assign('selected_step', $this->Page());
        $this->template->assign('step_var', $this->page_var);
        
        return C_Sub::Content();
    }
}