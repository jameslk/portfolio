<?php

Libs('common', 'client_packer');

/**
 * Smarty modifications
 */

class SmartyMods {
    public $filters = array(
        'pre' => 'handle_code_blocks'
    );
    
    public $functions = array(
        'add_js_file' => 'js_file',
        'add_css_file' => 'css_file',
        'action_url' => 'action_url',
        'custom_url' => 'custom_url',
        'form_action' => 'form_action',
        'form_focus' => 'form_focus'
    );
    
    public $compiler_functions = array(
        'head_extra' => 'head_extra'
    );
    
    public $blocks = array(
        'add_to_head' => 'head',
        'add_client_code' => 'client_code'
    );
    
    public $modifiers = array(
        'make_json' => 'json',
        'clean_data' => 'clean_data'
    );
    
    function __construct(&$smarty_obj) {
        /* Register all Smarty filters */
        foreach($this->filters as $type => $func) {
            if($type == 'pre')
                $smarty_obj->register_prefilter(array($this, $func));
            else
                $smarty_obj->register_postfilter(array($this, $func));
        }
        
        /* Register all Smarty functions */
        foreach($this->functions as $func => $alias)
            $smarty_obj->register_function($alias, array($this, $func));
        
        /* Register all Smarty compiler functions */
        foreach($this->compiler_functions as $func => $alias)
            $smarty_obj->register_compiler_function($alias, array($this, $func));
        
        /* Register all Smarty blocks */
        foreach($this->blocks as $func => $alias)
            $smarty_obj->register_block($alias, array($this, $func));
        
        /* Register all Smarty modifiers */
        foreach($this->modifiers as $func => $alias)
            $smarty_obj->register_modifier($alias, array($this, $func));
    }
    
    /**
     * Output extra head section HTML.
     * @type Smarty Block
     */
    public function head_extra($tag_arg, &$smarty) {
        return 'if(isset($GLOBALS[\'head_extra\'])) {'.
               '    foreach($GLOBALS[\'head_extra\'] as $head_extra)'.
               '        echo implode("\n\n", $head_extra)."\n\n";'.
               '}';
    }
    
    /**
     * Add HTML that should be put in the head section (if possible).
     * @type Smarty Block
     */
    public function add_to_head($params, $content, &$smarty, &$repeat) {
        if($repeat)
            return;
        
        if(!isset($content))
            return;
        
        HeadExtra($content, $params['priority']);
    }
    
    public function cache_code($matches) {
        $html_attributes = preg_replace('/([a-z0-9_-]+=)\'(.*?)\'/i', '$1"$2"', $matches[2]);
        
        $priority = '';
        if(preg_match('/priority="(.*?)"/i', $html_attributes, $priority_matches)) {
            $priority = " priority='$priority_matches[1]'";
            $html_attributes = preg_replace('/priority=".*?"/i', '', $html_attributes);
        }
        
        $code = ClientPacker::Pack($matches[1], $matches[3]);
        $code = str_replace(array('{{', '}}'), array('{/literal}{', '}{literal}'), $code);
        
        switch($matches[1]) {
            case 'js':
                $html = '<script type="text/javascript"'
                    .$html_attributes."><!--\n".$code."\n--></script>";
                break;
            
            case 'css':
                $html = '<style type="text/css"'
                    .$html_attributes.">\n".$code."\n</style>";
                break;
        }
        
        return "{client_code$priority}{literal}$html{/literal}{/client_code}";
    }
    
    /**
     * Add javascript or css code that should be put in the head section (if possible).
     * @type Smarty Pre-Filter
     */
    public function handle_code_blocks($source, &$smarty) {
        return preg_replace_callback('/{(js|css)_code(\s*.*?)}(.+?){\/\1_code}/sm', array($this, cache_code), $source);
    }
    
    /**
     * Add client code that should be put in the head section (if possible).
     * @type Smarty Block
     */
    public function add_client_code($params, $content, &$smarty, &$repeat) {
        HeadExtra($content, $params['priority']);
    }
    
    /**
     * Add js link that should be put in the head section (if possible).
     * @type Smarty Function
     */
    public function add_js_file($params, &$smarty) {
        if(!isset($params['path']) || $params['path'] === '')
            $smarty->trigger_error("js_file: missing 'path' parameter");
        
        $path = ClientPacker::PackFile('js', $params['path']);
        if(!$path)
            return;
        
        $priority = $params['priority'];
        
        unset($params['path'], $params['priority']);
        
        $html_attributes = '';
        foreach($params as $key => $value)
            $html_attributes .= ' '.$key.'="'.$value.'"';
        
        $html_attributes .= ' type="text/javascript"';
                
        HeadExtra('<script'.$html_attributes
            .' src="/client/'.$path.'"></script>', $priority);
    }
    
    /**
     * Add css link that should be put in the head section (if possible).
     * @type Smarty Function
     */
    public function add_css_file($params, &$smarty) {
        if(!isset($params['path']) || $params['path'] === '')
            $smarty->trigger_error("css_file: missing 'path' parameter");
        
        $path = ClientPacker::PackFile('css', $params['path']);
        if(!$path)
            return;
        
        $priority = $params['priority'];
        
        unset($params['path'], $params['priority']);
        
        $html_attributes = '';
        foreach($params as $key => $value)
            $html_attributes .= ' '.$key.'="'.$value.'"';
        
        $html_attributes .= ' rel="stylesheet" type="text/css"';
        
        HeadExtra('<link'.$html_attributes.' href="/client/'
            .$path.'" />', $priority);
    }
    
    /**
     * Convert a variable to JSON code (e.g. for JavaScript). Remember to add
     * a @ before this modifier for it to work on all variables. For example,
     * if $my_var was an array, you would do {$my_var|@json}.
     *
     * @type Smarty Modifier
     */
    public function make_json($var) {
        return json_encode($var);
    }
    
    /**
     * Add a controller action and any query data to the current REQUEST_URI.
     * @type Smarty Function
     */
    public function action_url($params, &$smarty) {
        $template_vars = $smarty->get_template_vars();
        
        /* Get the URL to use */
        if(isset($params['url']) && $params['url'] !== '')
            $url = $params['url'];
        else
            $url = $_SERVER['REQUEST_URI'];
        
        /* Get the URI path */
        $uri_path = parse_url($url, PHP_URL_PATH);
        
        /* Get the action */
        $is_ajax = false;
        if(isset($params['do']) && $params['do'] !== '') {
            $action = $params['do'];
        }
        else if(isset($params['ajax']) && $params['ajax'] !== '') {
            $action = $params['ajax'];
            $is_ajax = true;
        }
        else {
            $smarty->trigger_error("action_url: missing 'do' parameter");
        }
        
        /* Get the action_var */
        if(isset($params['var']) && $params['var'] !== '') {
            $action_var = $params['var'];
        }
        else if(isset($template_vars['action_var'])) {
            if(!$is_ajax)
                $action_var = $template_vars['action_var'];
            else
                $action_var = $template_vars['ajax_var'];
        }
        else {
            MakeWarning('No action_var available, defaulting to "do"', var_export($smarty, true));
            $action_var = 'do';
        }
        
        /* Get additional query vars */
        $add_vars = array();
        if(isset($params['query']) && $params['query'] !== '')
            parse_str($params['query'], $add_vars);
        
        /* Get vars to remove */
        if(isset($params['remove']) && $params['remove'] !== '')
            $remove_vars = explode(',', $params['remove']);
        else
            $remove_vars = array();
        
        /* Get the focus controller */
        if(isset($params['focus']) && $params['focus'] !== '')
            $add_vars['cfocus'.$params['focus']] = 'true';
        
        /* Hack up current URI and return it with appended vars */
        $uri_vars = parse_url($url, PHP_URL_QUERY);
        
        $new_vars = array();
        if($uri_vars)
            parse_str($uri_vars, $new_vars);
        
        /* Remove all matching vars from the new query string */
        foreach($remove_vars as $var) {
            if(isset($new_vars[$var]))
                unset($new_vars[$var]);
        }
        
        $new_vars = array_merge($new_vars, $add_vars);
        $new_vars = array_merge($new_vars, array($action_var => $action));
        
        return $uri_path.'?'.http_build_query($new_vars);
    }
    
    /**
     * Customize the current REQUEST_URI.
     * @type Smarty Function
     */
    public function custom_url($params, &$smarty) {
        $template_vars = $smarty->get_template_vars();
        
        /* Get the URL to use */
        if(isset($params['url']) && $params['url'] !== '')
            $url = $params['url'];
        else
            $url = $_SERVER['REQUEST_URI'];
        
        /* Get the URI path */
        $uri_path = parse_url($url, PHP_URL_PATH);
        
        /* Get additional query vars */
        $add_vars = array();
        if(isset($params['query']) && $params['query'] !== '')
            parse_str($params['query'], $add_vars);
        
        /* Get vars to remove */
        if(isset($params['remove']) && $params['remove'] !== '')
            $remove_vars = explode(',', $params['remove']);
        else
            $remove_vars = array();
        
        /* Hack up current URI and return it with appended vars */
        $uri_vars = parse_url($url, PHP_URL_QUERY);
        
        $new_vars = array();
        if($uri_vars)
            parse_str($uri_vars, $new_vars);
        
        foreach($new_vars as $param => $value) {
            if(!strncmp($param, 'cfocus', 6))
                unset($new_vars[$param]); //get rid of any old cfocus vars
        }
        
        /* Remove all matching vars from the new query string */
        foreach($remove_vars as $var) {
            if(isset($new_vars[$var]))
                unset($new_vars[$var]);
        }
        
        $new_vars = array_merge($new_vars, $add_vars);
        $new_vars = array_merge($new_vars, array($action_var => $action));
        
        return $uri_path.'?'.http_build_query($new_vars);
    }
    
    /**
     * Add a controller action to an HTML form.
     * @type Smarty Function
     */
    public function form_action($params, &$smarty) {
        $template_vars = $smarty->get_template_vars();
        
        /* Get the action */
        if(!isset($params['do']) || $params['do'] === '')
            $smarty->trigger_error("action_url: missing 'do' parameter");
        
        $action = $params['do'];
        
        /* Get the action_var */
        if(isset($params['var']) && $params['var'] !== '') {
            $action_var = $params['var'];
        }
        else if(isset($template_vars['action_var'])) {
            $action_var = $template_vars['action_var'];
        }
        else {
            MakeWarning('No action_var available, defaulting to "do"', var_export($smarty, true));
            $action_var = 'do';
        }
        
        $form = '<input type="hidden" name="'.$action_var.'" value="'.$action.'" />';
        
        if(isset($params['get'])) {
            $controller_vars = $template_vars['controller']->GetControllerVars();
            if(!empty($controller_vars)) {
                /* Add controller vars */
                parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $uri_vars);
                
                foreach($controller_vars as $var) {
                    if($var != $action_var && isset($uri_vars[$var]))
                        $form .= "\n".'<input type="hidden" name="'.$var.'" value="'.$uri_vars[$var].'" />';
                }
            }
        }
        
        return $form;
    }
    
    /**
     * Add a controller focus to an HTML form.
     * @type Smarty Function
     */
    public function form_focus($params, &$smarty) {
        $template_vars = $smarty->get_template_vars();
        
        if(isset($params['id']) && $params['id'] !== '')
            $id = $params['id'];
        else if(isset($template_vars['id']))
            $id = $template_vars['id'];
        else
            $smarty->trigger_error("form_focus: no controller id specified");
        
        return '<input type="hidden" name="cfocus'.$id.'" value="true" />';
    }
    
    /**
     * Clean unsafe database data for HTML output.
     * @type Smarty Modifier
     */
    public function clean_data($var) {
        return htmlentities($var, ENT_COMPAT, 'UTF-8');
    }
}