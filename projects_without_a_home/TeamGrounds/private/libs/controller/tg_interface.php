<?php

Libs('controller', 'user_session', 'controller/sub/user_login',
    'controller/sub/tg_messages');

class C_TG_Interface extends Controller {
    public $template_subpath = 'tg_interface/';

    public $interface_template;

    public $title = 'TeamGrounds Interface';

    public $interface_subs = array();
    public $content_subs = array();

    public $session;

    public $messages;

    function __construct() {
        parent::__construct();

        $this->interface_template = new Template;

        $this->session = UserSession::Instance()
            or $this->session = new UserSession;

        $this->AddInterfaceSub('user_login', new SC_UserLogin($this));

        $this->messages = new SC_TG_Messages($this);
    }
    
    public function RequireSession() {
        if(!$this->session->IsActive()) {
            //todo: Queue this message
            $this->messages->Error('Sorry, the page you requested requires you
                to be signed in. Please sign in first or register a new account');
            
            $this->Redirect('/signup');
            exit();
        }
    }

    public function AddInterfaceSub($name, $sub) {
        $this->interface_subs[$name] = $sub;
    }

    public function AddContentSub($name, $sub) {
        $this->content_subs[$name] = $sub;
    }

    protected function ExecInterfaceSub($name) {
        $this->interface_template->append('subs', array(
            $name => $this->interface_subs[$name]->Execute()
        ), true);
    }

    protected function ExecContentSub($name) {
        $this->template->append('subs', array(
            $name => $this->content_subs[$name]->Execute()
        ), true);
    }

    protected function Controller_Pre(&$params) {
        $this->ExecInterfaceSub('user_login');
    }

    public function Content() {
        if(!$this->is_ajax) {
            $template_name = $this->template_name;
    
            /* If template_name was not set, default to script name for template */
            if($template_name === '') {
                $template_name = $_SERVER['SCRIPT_NAME'];
                $template_name = ltrim(pathinfo($template_name, PATHINFO_DIRNAME), '/')
                    .'/'.pathinfo($template_name, PATHINFO_FILENAME).'.tpl';
            }
    
            /* Generate interface sub content */
            $subs = $this->interface_subs;
            foreach($subs as &$controller)
                $controller = $controller->Content();
    
            $this->interface_template->assign('subs', $subs);
    
            /* Generate content sub content */
            $subs = $this->content_subs;
            foreach($subs as &$controller)
                $controller = $controller->Content();
    
            $this->template->assign('subs', $subs);
    
            /* Execute TG interface messages controller */
            $this->interface_template->assign('messages', $this->messages->Fetch());
    
            $this->ExecInterface($template_name);
        }

        return $this->content;
    }

    protected function ExecInterface($content_path = NULL) {
        if($content_path === NULL)
            $content_path = $this->template_name;

        if($this->session->IsActive())
            $session_user = $this->session->user;
        else
            $session_user = false;

        $this->template->assign('session', $this->session);
        $this->template->assign('session_user', $session_user);

        $this->interface_template->assign('session', $this->session);
        $this->interface_template->assign('session_user', $session_user);

        /* Fetch template from $path as the inner content */
        $this->ExecTemplate($content_path);
        $this->interface_template->assign('content', $this->content);
        
        $this->interface_template->assign('title', $this->title);

        /* Execute main interface */
        $this->content = $this->interface_template->fetch(
            $this->template_subpath.'interface.tpl');
    }
}