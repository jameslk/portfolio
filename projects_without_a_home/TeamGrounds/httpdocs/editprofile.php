<?php

define('ROOT_DIR', '../');
require_once(ROOT_DIR.'private/global/global.php');

Libs('controller/tg_interface', 'controller/sub/tabs', 'model/user_profile',
    'common', 'model/user_game', 'model/user_gamexp');

/* ----- */

class C_UserEditProfile extends C_TG_Interface {
    public $title = 'Edit Player Profile';
    public $template_name = 'user/editprofile.tpl';

    public $tabs = array(
        'summary' => 'Summary',
        'gamexp' => 'Game Experience',
        //todo: 'screenshots' => 'Screenshots & Pictures',
        //todo: 'videos' => 'Videos',
        'personal' => 'Personal Info',
        'contact' => 'Contact Info'
    );

    public $tabs_sub;

    public $gamexp_titles = array(
        'General' => Array(
            'Game IDs',
            'Game Achievements',
            'Game Specialties/Skills',
            'Game Configurations/Equipment',
            'Other Info'
        ),

        'Pro Gaming Related' => Array(
            'League Experience',
            'Online/LAN Tournements and Events',
            'Past Teams',
            'Present and Past Sponsors',
        ),

        'MMORPG Related' => Array(
            'Player vs. Environment (PvE)',
            'Player vs. Player (PvP)'
        )
    );

    public $profile;

    protected function Controller_Pre(&$params) {
        parent::Controller_Pre($params);

        $this->RequireSession();

        $this->template->assign($this->session->user->data);
        $this->profile = M_UserProfile::GetByUser($this->session->user);
        if($this->profile)
            $this->template->assign($this->profile->data);
        else
            MakeWarning('User does not have a profile!', $this->user->data);

        $this->tabs_sub = new SC_Tabs($this, $this->tabs);
        $this->AddContentSub('tabs', $this->tabs_sub);
        $this->ExecContentSub('tabs');
    }

    public function Tab_Gamexp() {
        if(isset($_REQUEST['ugame_id'])
            && M_UserGame::GetCountBySearch(array('ugame_id' => $_REQUEST['ugame_id']))) {
            $this->template->assign('ugame_id', $_REQUEST['ugame_id']);

            $gamexp_array = M_UserGamexp::GetArrayBySearch(array('ugame_id' => $_REQUEST['ugame_id']));

            if($gamexp_array) {
                $gamexp_data = array();
                foreach($gamexp_array as $gamexp) {
                    $gamexp_data[$gamexp->Id()] = array(
                        'title' => $gamexp['title'],
                        'content' => $gamexp['content']
                    );
                }

                $this->template->assign('gamexp_data', $gamexp_data);
            }

            $predefined_titles = array();
            foreach($this->gamexp_titles as $label => $options) {
                foreach($options as $option)
                    $predefined_titles[$label][$option] = $option;
            }
            $this->template->assign('predefined_titles', $predefined_titles);
        }
        else {
            /* Show game list */
            $this->template->assign('user_games', M_UserGame::GetArrayByUser($this->session->user));
        }
    }

    public function Tab_Personal() {
        /* Country */
        $this->template->assign('country_options',
            array_merge(array('' => ''), GetCountryCodes()));

        /* Birthday */
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

        /* Show Age */
        $this->template->assign('show_age_options', array(
            '1' => 'Yes',
            '0' => 'No'
        ));

        /* Gender */
        $this->template->assign('gender_options', array(
            '' => '',
            'male' => 'Male',
            'female' => 'Female'
        ));

        /* Show Personal */
        $this->template->assign('show_personal_options', array(
            'friends' => 'Friends',
            'teams' => 'Teams',
            'groups' => 'Groups',
            'anyone' => 'Anyone'
        ));

        $this->template->assign('show_personal', $this->profile->GetSetArray('show_personal'));
    }

    public function Tab_Contact() {
        /* Can Contact */
        $this->template->assign('allow_contact_options', array(
            'friends' => 'Friends',
            'teams' => 'Teams',
            'groups' => 'Groups',
            'anyone' => 'Anyone'
        ));

        $this->template->assign('allow_contact', $this->profile->GetSetArray('allow_contact'));
    }

    protected function Pre_Save() {
        $this->RequireSession();

        $this->template->assign($_POST);

        return true;
    }

    protected function Post_Save($notice, $models) {
        if(!$this->messages->has_errors) {
            AppLog::Report('User profile saved', $this->session->user->data);

            if(is_array($models)) {
                foreach($models as $model)
                    $model->Update(true);
            }
            else {
                $models->Update(true);
            }

            $this->messages->Notice($notice); //todo: Queue this message

            $this->Redirect($this->session->user->GetProfileURI());
        }
    }

    protected function Action_Save_Summary() {
        if(!$this->Pre_Save())
            return;

        $data = $_POST;

        /* Set summary */
        if(!$this->profile->SetField('summary', $data['summary']))
            $this->messages->FieldError('summary', 'Invalid input for summary supplied.');

        $this->Post_Save('Your profile summary has been saved.', $this->profile);
    }

    protected function Action_Save_Gamexp($ugame_id) {
        if(!$this->Pre_Save())
            return;

        $data = $_POST;

        $gamexp_array = array();
        if(isset($data['gamexp_data']) && is_array($data['gamexp_data'])) {
            foreach($data['gamexp_data'] as $gamexp_id => $gamexp_data) {
                $gamexp = new M_UserGamexp($gamexp_id);

                if(!$gamexp_data['title'])
                    $this->messages->FieldError("gamexp_data[$gamexp_id][title]", 'Please enter a title for this game experience.');
                else if(!$gamexp->SetField('title', $gamexp_data['title']))
                    $this->messages->FieldError("gamexp_data[$gamexp_id][title]", 'Please enter a valid title for this game experience.');

                if(!$gamexp_data['content'])
                    $this->messages->FieldError("gamexp_data[$gamexp_id][content]", 'The game experience content you have entered is too short.');
                else if(!$gamexp->SetField('content', $gamexp_data['content']))
                    $this->messages->FieldError("gamexp_data[$gamexp_id][content]", 'The game experience content you have entered is invalid.');

                $gamexp_array[] = $gamexp;
            }
        }

        $new_gamexp_array = array();
        if(isset($data['new']) && is_array($data['new'])) {
            foreach($data['new'] as $key => $new) {
                $new_gamexp = new M_UserGamexp;

                if(!$new['title'] && !$new['title_predef']) {
                    if($new['content'])
                        $this->messages->FieldError("new[$key][title]", 'Please enter or select a title for this game experience.');
                    else
                        continue; //ignore empty new game experience
                }
                else if($new['title'] && !$new_gamexp->SetField('title', $new['title'])) {
                    $this->messages->FieldError("new[$key][title]", 'Please enter a valid title for this game experience.');
                }
                else if($new['title_predef'] && !$new_gamexp->SetField('title', $new['title_predef'])) {
                    $this->messages->FieldError("new[$key][title]", 'Please select or enter a valid title for this game experience.');
                }

                if(!$new['content'])
                    $this->messages->FieldError("new[$key][content]", 'The game experience content you have entered is too short.');
                else if(!$new_gamexp->SetField('content', $new['content']))
                    $this->messages->FieldError("new[$key][content]", 'The game experience content you have entered is invalid.');

                $new_gamexp->SetField('ugame_id', $ugame_id);

                $new_gamexp_array[] = $new_gamexp;
            }
        }

        if(!$this->messages->has_errors) {
            AppLog::Report('User profile saved', $this->session->user->data);

            foreach($gamexp_array as $gamexp)
                $gamexp->Update();

            foreach($new_gamexp_array as $new_gamexp)
                $new_gamexp->Create();

            $this->messages->Notice('Your game experience has been saved.'); //todo: Queue this message

            $this->Redirect($this->session->user->GetProfileURI());
        }
    }

    protected function Action_Delete_Gamexp($gamexp_id) {
        $this->RequireSession();

        $gamexp = M_UserGamexp::GetByID($gamexp_id);
        if($gamexp)
            $gamexp->Delete();
        else
            //todo: Queue this message for redirect
            $this->messages->Error('The game you specified doesn\'t exist in your games.');

        $this->Redirect(false, array($this->action_var, 'gamexp_id'));
    }

    protected function Action_MoveGamexpUp($gamexp_id) {
        $this->RequireSession();

        $gamexp = M_UserGamexp::GetByID($gamexp_id);
        if($gamexp) {
            $gamexp->MoveUp();
            //todo: Queue this message for redirect
            $this->messages->Notice($gamexp['title'].' has been moved up one.');
        }
        else {
            //todo: Queue this message for redirect
            $this->messages->Error('The field you specified doesn\'t exist in your games.');
        }

        $this->Redirect();
    }

    protected function Action_MoveGamexpDown($gamexp_id) {
        $this->RequireSession();

        $gamexp = M_UserGamexp::GetByID($gamexp_id);
        if($gamexp) {
            $gamexp->MoveDown();
            //todo: Queue this message for redirect
            $this->messages->Notice($gamexp['title'].' has been moved down one.');
        }
        else {
            //todo: Queue this message for redirect
            $this->messages->Error('The field you specified doesn\'t exist in your games.');
        }

        $this->Redirect();
    }

    protected function Action_Save_PersonalInfo() {
        if(!$this->Pre_Save())
            return;

        $data = $_POST;

        /* Set country */
        $countrycodes = GetCountryCodes();
        if($data['country'] && !isset($countrycodes[$data['country']]))
            $this->messages->FieldError('country', 'Invalid country specified.');
        else if(!$this->profile->SetField('country', $data['country']))
            $this->messages->FieldError('country', 'Invalid country specified.');

        /* Set location */
        if(!$this->profile->SetField('location', $data['location']))
            $this->messages->FieldError('location', 'Invalid input for location supplied.');

        /* Set birthday */
        if($data['birthday_day'] && $data['birthday_month'] && $data['birthday_year']) {
            $this->session->user->SetField('birthday',
                mktime(0, 0, 0, $data['birthday_month'],  $data['birthday_day'],
                    $data['birthday_year']));
        }

        /* Set show_age */
        if(!$this->profile->SetField('show_age', $data['show_age']))
            $this->messages->FieldError('show_age', 'Please select either Yes or No.');

        /* Set gender */
        if(!$this->profile->SetField('gender', $data['gender']))
            $this->messages->FieldError('gender', 'Please select a either nothing, Male, or Female.');

        /* Set interests */
        if(!$this->profile->SetField('interests', $data['interests']))
            $this->messages->FieldError('interests', 'Invalid input for interests supplied.');

        /* Set website */
        if(!$this->profile->SetField('website', $data['website']))
            $this->messages->FieldError('website', 'Please enter a valid URL starting with "http://".');

        /* Set show_personal */
        if(!$this->profile->SetField('show_personal', Model::ConvertToSet($data['show_personal'])))
            $this->messages->FieldError('show_personal', 'Invalid choice.');

        $this->Post_Save('Your personal info has been saved.', array($this->session->user, $this->profile));
    }

    protected function Action_Save_ContactInfo() {
        if(!$this->Pre_Save())
            return;

        $data = $_POST;

        if(!$this->profile->SetFields($data, $fail_data, array(
            'msn', 'aim', 'yahoo', 'icq', 'skype'))) {
            $this->messages->FailDataError($fail_data); //we shouldn't be here
        }

        /* Set IRC */
        if(!$this->profile->FilterField('irc_channel', $data['irc_channel']))
            $this->messages->FieldError('irc_channel', 'Please enter a valid IRC channel or leave this field blank');
        else if(!$this->profile->FilterField('irc_network', $data['irc_network']))
            $this->messages->FieldError('irc_network', 'Please enter a valid IRC network address or leave this field blank');
        else if($data['irc_channel'] && $data['irc_network'])
            $this->profile->SetFields($data, $fail_data, array('irc_channel', 'irc_network'));

        /* Set allow_contact */
        if(!$this->profile->SetField('allow_contact', Model::ConvertToSet($data['allow_contact'])))
            $this->messages->FieldError('allow_contact', 'Invalid choice.');

        $this->Post_Save('Your contact info has been saved.', $this->profile);
    }
}

$interface = new C_UserEditProfile;
$interface->Display();