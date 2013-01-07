<?php

define('ROOT_DIR', '../');
require_once(ROOT_DIR.'private/global/global.php');

Libs('controller/tg_interface', 'common', 'controller/sub/avatar_uploader',
    'controller/sub/tabs', 'model/user_game');

/* ----- */

class C_UserSettings extends C_TG_Interface {
    public $title = 'Player Settings';
    public $template_name = 'user/settings.tpl';

    public $tabs = array(
        'account' => 'Account Settings',
        'games' => 'Manage Games'
    );

    public $tabs_sub;

    protected function Controller_Pre(&$params) {
        parent::Controller_Pre($params);

        $this->tabs_sub = new SC_Tabs($this, $this->tabs);

        $this->AddContentSub('tabs', $this->tabs_sub);
        $this->ExecContentSub('tabs');
    }

    protected function Controller_Post($params) {
        parent::Controller_Post($params);

        $this->RequireSession();

        if($this->tabs_sub->Tab() == 'games') {
            $user_games = M_UserGame::GetArrayBySearch(
                array('user_id' => $this->session->user->Id()));

            $this->template->assign('user_games', $user_games);
        }
    }

    public function Tab_Account() {
        $this->RequireSession();

        $this->template->assign($this->session->user->data);

        $avatar_uploader = new SC_AvatarUploader($this, $this->session->user);
        $avatar_uploader->save_message = 'Your avatar has been updated.';
        $this->AddContentSub('avatar_uploader', $avatar_uploader);
        $this->ExecContentSub('avatar_uploader');

        $birthday = $this->session->user['birthday'];
        if($birthday) {
            $this->template->assign('birthday_day', date('j', $birthday));
            $this->template->assign('birthday_month', date('n', $birthday));
            $this->template->assign('birthday_year', date('Y', $birthday));
        }

        $days = array();
        foreach(range(1, 31) as $day)
            $days[$day] = $day;

        $this->template->assign('birthday_days', $days);

        for($month = 1; $month <= 12; ++$month)
            $this->template->append('birthday_months',
                array($month => date('F', mktime(0, 0, 0, $month))), true);

        $years = array();
        $year_now = date('Y');
        foreach(range($year_now, $year_now-100) as $year)
            $years[$year] = $year;

        $this->template->assign('birthday_years', $years);

        $this->template->assign('timezones', GetTimeZones());
    }

    protected function Action_Default() {
        $this->RequireSession();

        AppLog::Report('User settings request', $this->session->user->data);
    }

    protected function Pre_Save() {
        $this->RequireSession();

        $this->template->assign($_POST);

        return true;
    }

    protected function Post_Save($notice) {
        if(!$this->messages->has_errors) {
            AppLog::Report('User settings saved', $this->session->user->data);
            $this->session->user->Update(true);
            $this->messages->Notice($notice);
        }
    }

    protected function Action_Save_AccountSettings() {
        if(!$this->Pre_Save())
            return;

        $data = $_POST;

        /* Set email */
        if(!$this->session->user->FilterField('email', $data['email']))
            $this->messages->FieldError('email', 'Please enter a valid email address.');
        else if($this->session->user->CheckEmailExists($data['email']))
            $this->messages->FieldError('email', 'Sorry, this email address is already in use.');
        else
            $this->session->user->SetField('email', $data['email'], false);

        //todo: send confirmation email

        /* Set password */
        if($data['old_password'] && $data['password'] &&
            $this->session->user->FilterField('password', $data['old_password'])) {
            if($data['old_password'] != $this->session->user['password'])
                $this->messages->FieldError('old_password', 'The password you supplied does not match the current one.');
            else if($data['password'] != $data['password_confirm'])
                $this->messages->FieldError('password_confirm', 'The password you entered does not match the confirmation password.');
            else
                $this->session->user->SetField('password', $data['password']);
        }

        /* Set timezone */
        if(!$this->session->user->SetField('timezone', $data['timezone']))
            $this->messages->FieldError('timezone'); //we shouldn't be here

        $this->Post_Save('Your account settings have been saved.');
    }

    protected function Action_Save_ProfileSettings() {
        if(!$this->Pre_Save())
            return;

        $data = $_POST;

        /* Set displayname */
        if(!$this->session->user->SetField('displayname', $data['displayname']))
            $this->messages->FieldError('displayname', 'Please enter a valid player name.');

        /* Set profile_key */
        if(isset($data['profile_key']))
            $profile_key = $data['profile_key'];
        else
            $profile_key = '';

        if($profile_key != $this->session->user->data['profile_key']) {
            if(!$this->session->user->FilterField('profile_key', $profile_key))
                $this->messages->FieldError('profile_key', 'Please enter a valid profile URL.');
            else if($this->session->user->CheckProfileKeyExists($profile_key))
                $this->messages->FieldError('profile_key', 'Sorry, this profile URL is already taken.');
            else
                $this->session->user->SetField('profile_key', $profile_key, false);
        }

        $this->Post_Save('Your profile settings have been saved.');
    }

    protected function Action_Save_ProfileInformation() {
        if(!$this->Pre_Save())
            return;

        $data = $_POST;

        /* Set birthday */
        if($data['birthday_day'] && $data['birthday_month'] && $data['birthday_year']) {
            $this->session->user->SetField('birthday',
                mktime(0, 0, 0, $data['birthday_month'],  $data['birthday_day'],
                    $data['birthday_year']));
        }

        $this->Post_Save('Your profile information has been saved.');
    }

    protected function Action_AddGame($game) {
        $this->RequireSession();

        $user_game = new M_UserGame;

        if(M_UserGame::GetCountBySearch(array(
            'user_id' => $this->session->user->Id(),
            'title' => $game
        )))
            $this->messages->FieldError('game', 'This game has already been added to your list.');
        else if(!$user_game->SetField('title', $game))
            $this->messages->FieldError('game', 'Please enter a valid game name.');

        if(!$this->messages->has_errors) {
            $user_game->SetField('user_id', $this->session->user->Id());

            $user_game->Create();

            $this->messages->Notice('Your games have been updated.');
        }
        else {
            $this->template->assign('game', $game);
        }
    }

    protected function Action_DeleteGame($game_id) {
        $this->RequireSession();

        $user_game = M_UserGame::GetByID($game_id);
        if($user_game) {
            $user_game->Delete();
            //todo: Queue this message for redirect
            $this->messages->Notice($user_game['title'].' has been removed from your games.');
        }
        else {
            //todo: Queue this message for redirect
            $this->messages->Error('The game you specified doesn\'t exist in your games.');
        }

        $this->Redirect();
    }

    protected function Action_MoveGameUp($game_id) {
        $this->RequireSession();

        $user_game = M_UserGame::GetByID($game_id);
        if($user_game) {
            $user_game->MoveUp();
            //todo: Queue this message for redirect
            $this->messages->Notice($user_game['title'].' has been moved up one.');
        }
        else {
            //todo: Queue this message for redirect
            $this->messages->Error('The game you specified doesn\'t exist in your games.');
        }

        $this->Redirect();
    }

    protected function Action_MoveGameDown($game_id) {
        $this->RequireSession();

        $user_game = M_UserGame::GetByID($game_id);
        if($user_game) {
            $user_game->MoveDown();
            //todo: Queue this message for redirect
            $this->messages->Notice($user_game['title'].' has been moved down one.');
        }
        else {
            //todo: Queue this message for redirect
            $this->messages->Error('The game you specified doesn\'t exist in your games.');
        }

        $this->Redirect();
    }
}

$interface = new C_UserSettings;
$interface->Display();