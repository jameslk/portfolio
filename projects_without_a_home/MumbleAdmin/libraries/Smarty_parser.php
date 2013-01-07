<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('thirdparty/smarty/libs/Smarty.class.php');

/**
 * Adopted from http://codeigniter.com/forums/viewthread/60050/P0/
 */
class Smarty_parser extends Smarty {

    function Smarty_parser($config = array())
    {
        parent::Smarty();
        
        if (count($config) > 0)
        {
            $this->initialize($config);
        }
        
        $this->register_prefilter(array($this, '_smarty_prefilter_ci'));

        // register Smarty resource named "ci"
        $this->register_resource("ci", array($this,
                        "ci_get_template", "ci_get_timestamp", "ci_get_secure", "ci_get_trusted")
        );
                                       
        log_message('debug', "Smarty_parser Class Initialized");
    }

    /**
     * Initialize preferences
     */    
    function initialize($config = array())
    {
        foreach ($config as $key => $val)
        {
            if (isset($this->$key))
            {
                $method = 'set_'.$key;
                
                if (method_exists($this, $method))
                {
                    $this->$method($val);
                }
                else
                {
                    $this->$key = $val;
                }            
            }
        }
    }

    /**
     *  Set the left/right variable delimiters
     */
    function set_delimiters($l = '{', $r = '}')
    {
        $this->left_delimiter = $l;
        $this->right_delimiter = $r;
    }
    
    function clear() {
        $this->clear_all_assign();
    }
    
    /**
     *  Parse a template using Smarty engine
     *
     * Parses pseudo-variables contained in the specified template,
     * replacing them with the data in the second param.
     * Allows CI and Smarty code to be combined in the same template
     * by prefixing template name with "ci:".
     */
    function fetch($template, $data = NULL) {
        $CI =& get_instance();
        
        $CI->benchmark->mark('smarty_parse_start');
        
        if(is_array($data))
            $this->assign(&$data);
        
        // make CI object directly accessible from a template (optional)
        $this->assign_by_ref('CI', $CI);

        $result = parent::fetch($template);
        
        $CI->benchmark->mark('smarty_parse_end');
        
        return $result;
    }
    
    function view($template, $data = NULL, $return_obj = FALSE)
    {
        $CI =& get_instance();
        $CI->output->final_output .= $this->fetch($template, $data);
        
        if($return_obj) {
            $this_obj = clone $this;
            
            $this->clear();
            
            return $this_obj;
        }
        else {
            $this->clear();
        }
    }
    
    function display($template, $data = NULL) {
        $CI =& get_instance();
        $CI->output->final_output .= $this->fetch($template, $data);
    }
    
    /**
     * Smarty pre-filter to handle calls to CodeIgniter helper functions, etc.
     * 
     * Use it like this in your template: {ci site_url('news/local/123')}
     * This will get expanded to: {php}echo site_url('news/local/123');{/php}
     */
    function _smarty_prefilter_ci($tpl_source, &$smarty) {
        return preg_replace('/{ci\s(.*?\))}/sm', '{php}echo $1;{/php}', $tpl_source);
    }

    /**
     * Smarty resource accessor functions
     */     
    function ci_get_template ($tpl_name, &$tpl_source, &$smarty_obj)
    {
        $CI =& get_instance();
        
        // ask CI to fetch our template
        $tpl_source = $CI->load->view($tpl_name, $smarty_obj->get_template_vars(), true);
        return true;
    }
    
    function ci_get_timestamp($view, &$timestamp, &$smarty_obj)
    {
        $CI =& get_instance();
        
        // Taken verbatim from _ci_load (Loader.php, 580):
        $ext = pathinfo($view, PATHINFO_EXTENSION);
        $file = ($ext == '') ? $view.EXT : $view;
        $path = $CI->load->_ci_view_path.$file;
        
        // get file modification date
        $timestamp = filectime($path);
        return ($timestamp !== FALSE);
    }
    
    function ci_get_secure($tpl_name, &$smarty_obj)
    {
        // assume all templates are secure
        return true;
    }
    
    function ci_get_trusted($tpl_name, &$smarty_obj)
    {
        // not used for templates
    }
}