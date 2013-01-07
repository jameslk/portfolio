<?php

Libs('template');

class Controller {
    static public $counter = 1;

    public $id;
    
    public $children = array();

    public $action_var = 'do';
    public $action = '';
    
    public $ajax_var = 'ajax';
    public $is_ajax = false;

    public $template = NULL;
    public $template_name = '';
    public $template_subpath = '';

    public $content = '';

    function __construct() {
        $this->id = self::$counter++;

        $this->template = new Template;
    }

    protected function Action_Default() {
    }

    protected function Controller_Pre(&$params) {
    }

    protected function Controller_Post($params) {
    }
    
    protected function Controller_Ajax_Pre(&$params) {
    }
    
    protected function Controller_Ajax_Post($params) {
    }

    protected function Contoller_Unknown() {
        $this->ExecTemplate('errors/404.tpl');
    }

    public function InFocus() {
        if(isset($_REQUEST['cfocus'.$this->id]))
            return true;
        else
            return false;
    }
    
    /**
     * Get all controller vars that affect this controller's output. These are
     * all properties of this class that end with "_var".
     */
    public function GetControllerVars(Controller $controller = NULL) {
        if(!$controller)
            $controller = $this;
        
        $obj_vars = get_object_vars($controller);
        
        $controller_vars = array();
        foreach($obj_vars as $var => $val) {
            if(substr($var, -4) == '_var')
                $controller_vars[] = $val;
        }
        
        foreach($controller->children as $child_controller)
            $controller_vars = array_merge($controller_vars,
                $this->GetControllerVars($child_controller));
        
        return $controller_vars;
    }

    protected function ExecTemplate($path, $use_root = false) {
        $this->template->assign('controller', $this);
        $this->template->assign('id', $this->id);
        $this->template->assign('action', $this->action);
        $this->template->assign('action_var', $this->action_var);
        $this->template->assign('ajax_var', $this->ajax_var);

        if($use_root)
            $this->content = $this->template->fetch($use_root.$path);
        else
            $this->content = $this->template->fetch($this->template_subpath.$path);
    }

    public function Content() {
        if(!$this->is_ajax)
            $this->ExecTemplate($this->template_name);
        
        return $this->content;
    }

    public function Execute() {
        if($this->action === '') {
            if(isset($_REQUEST[$this->action_var])) {
                $this->action = $_REQUEST[$this->action_var];
            }
            else if(isset($_REQUEST[$this->ajax_var])) {
                $this->action = $_REQUEST[$this->ajax_var];
                $this->is_ajax = true;
            }
            else {
                $this->action = 'Default';
            }
        }

        if(!$this->is_ajax)
            $action_method = 'Action_'.$this->action;
        else
            $action_method = 'Ajax_'.$this->action;

        AppLog::Report('Controller method requested ', get_class($this).'::'.$action_method);

        $unknown = true;

        if(method_exists($this, $action_method)) {
            $reflect = new ReflectionMethod(get_class($this), $action_method);
            $method_params = $reflect->getParameters();

            $unknown = false;
            $ordered_params = array();
            foreach($method_params as $i => $param) {
                $pname = $param->getName();

                if(isset($_REQUEST[$pname])) {
                    $ordered_params[$pname] = $_REQUEST[$pname];
                }
                else if($param->isDefaultValueAvailable()) {
                    $ordered_params[$pname] = $param->getDefaultValue();
                }
                else {
                    /* Missing required parameter */
                    AppLog::Report('Failed to perform action due to missing required argument',
                        compact('action_method', 'pname'));

                    $unknown = true;
                    break;
                }
            }

            if(!$unknown) {
                if(!$this->is_ajax) {
                    $this->Controller_Pre($ordered_params);
                    call_user_func_array(array($this, $action_method), $ordered_params);
                    $this->Controller_Post($ordered_params);
                }
                else {
                    $this->Controller_Ajax_Pre($ordered_params);
                    $this->content = call_user_func_array(array($this, $action_method), $ordered_params);
                    $this->Controller_Ajax_Post($ordered_params);
                }
            }
        }

        if($unknown) {
            AppLog::Report('Controller method not found', compact('action_method'));
            $this->Contoller_Unknown();
        }
    }

    public function Fetch() {
        $this->Execute();
        return $this->Content();
    }

    public function Display() {
        $this->Execute();
        echo $this->Content();
    }

    public function Redirect($uri_or_vars = false, array $remove_vars = array()) {
        if(!$uri_or_vars || is_array($uri_or_vars)) {
            if(!$uri && empty($remove_vars))
                $remove_vars = array($this->action_var); //remove this action

            $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $uri_vars = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

            /* Parse query string into array */
            $new_vars = array();
            if($uri_vars)
                parse_str($uri_vars, $new_vars);

            /* Remove all matching vars from the new query string */
            foreach($remove_vars as $var) {
                if(isset($new_vars[$var]))
                    unset($new_vars[$var]);
            }
            
            if(is_array($uri_or_vars))
                /* Add new query vars */
                $new_vars = array_merge($new_vars, $uri_or_vars);

            /* Rebuild URI and redirect this session */
            $uri = $uri_path.'?'.http_build_query($new_vars);
        }
        else {
            $uri = $uri_or_vars;
        }

        AppLog::Report('Redirecting session', compact('uri'));

        header('Location: '.$uri);
        exit();
    }
}