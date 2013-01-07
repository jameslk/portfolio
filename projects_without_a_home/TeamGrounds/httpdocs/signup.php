<?php

define('ROOT_DIR', '../');
require_once(ROOT_DIR.'private/global/global.php');

Libs('controller/tg_interface', 'model/user');

/* ----- */

class C_SignUp extends C_TG_Interface {
    public $title = 'Sign Up';
    public $template_name = 'signup.tpl';
    
    protected function Action_SignUp() {
        $data = $_POST;
        
        $user = new M_User;
        
        /* Set displayname */
        if(!$user->SetField('displayname', $data['displayname']))
            $this->messages->FieldError('displayname', 'Please enter a valid player name.');
        
        /* Set email */
        if(!$user->FilterField('email', $data['email']))
            $this->messages->FieldError('email', 'Please enter a valid email address.');
        else if($user->CheckEmailExists($data['email']))
            $this->messages->FieldError('email', 'Sorry, this email address is already in use.');
        else
            $user->SetField('email', $data['email'], false);
        
        //todo: send confirmation email
        
        /* Set password */
        if(!$user->SetField('password', $data['password']))
            $this->messages->FieldError('password', 'Please enter a valid password.');
        
        $user->SetFields(array(
            'joindate' => time(),
            'lastvisit' => time(),
            'ipaddress' => $_SERVER['REMOTE_ADDR']
        ), $fail_data)
            or MakeWarning('Failed to set user static data', $fail_data);
        
        if(!$this->messages->has_errors) {
            AppLog::Report('User signed up', var_export($user->data, true));
            $user->Create();
            
            //todo: show them next steps
            
            $this->Redirect('/');
        }
        else {
            AppLog::Report('User signup failed', var_export($user->data, true));
        }
    }
}

$interface = new C_SignUp;
$interface->Display();