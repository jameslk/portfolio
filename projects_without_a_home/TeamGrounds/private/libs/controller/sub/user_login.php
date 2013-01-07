<?php

Libs('controller/sub/tg', 'model/user', 'user_session');

class SC_UserLogin extends SC_Tg {
    public $template_name = 'user_login.tpl';

    protected function Action_Default() {
    }

    protected function Action_Login($remember = false) {
        if($this->session->IsActive()) {
            //User is already logged in
            $this->Action_Default();
            return;
        }

        if($remember && ($remember == 'on' || $remember == 'checked'))
            $remember = true;
        else
            $remember = false;

        $data = $_POST;

        $model = new M_User;
        if(!$model->FilterFields($data, $fail_data, array('email', 'password'))) {
            AppLog::Report('Action_Login: Bad data');
            $this->messages->Error('Sorry, the username or password you have
                provided is incorrect. Please try again.');

            $this->Action_Default();
            return;
        }

        $user = M_User::GetBySearch($data);
        if(!$user) {
            //todo: Bad login, show captcha
            AppLog::Report('Action_Login: Bad login');
            $this->messages->Error('Sorry, the username or password you have
                provided is incorrect. Please try again.');

            $this->Action_Default();
            return;
        }

        AppLog::Report('User logged in', $data);

        $this->session->CreateSession($user, $remember);
    }

    protected function Action_Logout() {
        if(!$this->session->IsActive()) {
            //User is not logged in
            $this->Action_Default();
        }
        else {
            $this->session->DeleteSession();
        }

        $this->Redirect();
    }
}