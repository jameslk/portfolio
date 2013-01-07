<?php

Libs('controller');

class C_Sub extends Controller {
    public $action_var = 'sub_';
    public $ajax_var = 'ajax_';
    
    public $parent;
    public $root_parent;
    
    /**
     * Get all controller vars that affect this controller's output. These are
     * all properties of this class that end with "_var".
     */
    public function GetControllerVars() {
        return $this->root_parent->GetControllerVars();
    }

    function __construct(Controller $parent) {
        parent::__construct();
        
        $this->parent = $parent;
        
        if(isset($parent->root_parent))
            $this->root_parent = $parent->root_parent;
        else
            $this->root_parent = $parent;
        
        $parent->children[] = $this;
        
        $this->action_var .= $this->id;
        $this->ajax_var .= $this->id;
    }
}