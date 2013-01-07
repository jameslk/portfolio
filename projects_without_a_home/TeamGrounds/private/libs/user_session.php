<?php

Libs('singleton', 'model/session', 'model/user');

class UserSession implements Singleton {
    static protected $instance = false;

    public $user = NULL;
    public $session = NULL;

    public $id_cookie = 'usession';

    public $persistent = false;

    public function __construct() {
        if(self::$instance)
    		MakeError('Second instance of singleton class "'.self.'" not allowed');
    	else
    	   self::$instance = $this;

        $this->LoadSession();
    }
    
    public function __destruct() {
        if($this->IsActive()) {
            $this->user->SetFields(array(
                    'lastseen' => time(),
                    'ipaddress' => $_SERVER['REMOTE_ADDR']
                ), $fail_data)
                    or MakeError('Failed to update user session data', $fail_data);
            
            $this->user->Update(true, array('lastseen', 'ipaddress'));
        }
    }

    static public function Instance() {
        if(self::$instance)
            return self::$instance;
        else
            return false;
    }

    public function __clone() {
        MakeError('Clone of singleton class "'.self.'" not allowed');
    }

    public function __wakeup() {
        MakeError('Deserializing of singleton class "'.self.'" not allowed');
    }

    public function Id() {
        if($this->IsActive())
            return $this->session->Id();

        MakeWarning('Session id requested on a non-active session');
        return false;
    }

    protected function DataExpireTime() {
        if($this->persistent)
            return time()+CFG_COOKIE_EXPIRE;
        else
            return time()+session_cache_expire();
    }

    public function SetSessionData($name, $value) {
        if($this->persistent)
            return setcookie($name, $value, $this->session['cookie_expire'], '/');
        else
            $_SESSION[$name] = $value;
    }

    public function DeleteSessionData($name) {
        if($this->persistent)
            return setcookie($name, '', time()-3600, '/');
        else
            unset($_SESSION[$name]);
    }

    public function IsActive() {
        if($this->session)
            return true;
        else
            return false;
    }

    protected function IsExpired() {
        $limit = time()-CFG_SESSION_EXPIRE;
        if($this->session['start_time'] < $limit)
            return true;
        else
            return false;
    }

    public function LoadSession() {
        if($this->IsActive()) {
            MakeWarning('A user session is already active', var_export($user, true));
            return;
        }

        $persistent = false;
        if(isset($_SESSION[$this->id_cookie])) {
            $id = $_SESSION[$this->id_cookie];
        }
        else if(isset($_COOKIE[$this->id_cookie])) {
            $id = $_COOKIE[$this->id_cookie];
            $persistent = true;
        }
        else {
            return; //no session found
        }

        $session = new M_Session;

        if(!$session->FilterField($session->primary_key, $id)) {
            AppLog::Report('Invalid session id supplied', compact('id'));
            return;
        }

        $models = M_Session::GetUserSession($id);
        if(!$models) {
            MakeWarning('User session not found', compact('id'));
            return;
        }

        $this->persistent = $persistent;

        $this->user = $models['user'];
        $this->session = $models['session'];

        if($this->IsExpired()) {
            $this->session->Refresh();
            $this->SetSessionData($this->id_cookie, $this->Id());
        }

        AppLog::Report('User session loaded',
            array('session_id' => $this->Id(), 'user_id' => $this->user->Id()));
    }

    public function CreateSession(M_User $user, $persistent = false) {
        if($this->IsActive()) {
            MakeWarning('A user session is already active', var_export($user, true));
            return;
        }

        $this->persistent = $persistent;

        $this->user = $user;

        $session = M_Session::GetBySearch(array($user->primary_key => $user->Id()));
        if($session) {
            $this->session = $session;

            $this->session->SetFields(array('cookie_expire' => $this->DataExpireTime()), $fail_data)
                or MakeError('Failed to set data fields', $fail_data);

            $this->session->Update();

            if($this->IsExpired())
                $this->session->Refresh();

            AppLog::Report('User session re-loaded',
            array('session_id' => $this->Id(), 'user_id' => $this->user->Id()));
        }
        else {
            $this->session = new M_Session;

            $this->session->SetFields(array(
                    'user_id' => $user->Id(),
                    'start_time' => time(),
                    'cookie_expire' => $this->DataExpireTime()
                ), $fail_data)
                    or MakeError('Failed to set data fields', $fail_data);

            $this->session->Create();

            AppLog::Report('User session created',
                array('session_id' => $this->Id(), 'user_id' => $this->user->Id()));
        }

        $this->SetSessionData($this->id_cookie, $this->Id());
    }

    public function DeleteSession() {
        if(!$this->IsActive()) {
            MakeWarning('A user session is not active', var_export($user, true));
            return;
        }

        $this->DeleteSessionData($this->id_cookie);

        AppLog::Report('User session deleted',
            array('session_id' => $this->Id(), 'user_id' => $this->user->Id()));

        $this->user = NULL;
        $this->session = NULL;
        $this->persistent = false;
    }
    
    public function FormatDate($timestamp) {
        //todo: Format date from timestamp according to user's timezone
        return date('j M Y', $timestamp);
    }
    
    public function FormatTime($timestamp) {
        //todo: Format time from timestamp according to user's timezone
        return date('h:ia', $timestamp);
    }
}