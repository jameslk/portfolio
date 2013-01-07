<?php

define('ROOT_DIR', '../../');
require_once(ROOT_DIR.'private/global/global.php');

Libs('controller/tg_interface');

/* ----- */

class C_TeamProfile extends C_TG_Interface {
    public $title = 'Team Profile';
    public $template_name = 'team/profile.tpl';

    public $team = NULL;

    public function AddTeamData($key, $data) {
        $this->template->append('team', array($key => $data), true);
    }

    public function AddTeamDataArray(array $data) {
        $this->template->append('team', $data, true);
    }

    protected function Controller_Pre(&$params) {
        parent::Controller_Pre($params);

        if(isset($_REQUEST['tid']))
            $uid = $_REQUEST['tid'];
        else
            return;
        
        $fields = array('*', 'avatar.*', 'profile.*');
        $user = false;
        if($uid !== '') {
            if(is_numeric($uid))
                $user = M_User::GetByID($uid, $fields);
            else
                $user = M_User::GetBySearch(array('profile_key' => $uid), $fields);
        }

        if($user) {
            $this->user = $user;

            if($this->session->IsActive() && $this->session->user->Id() == $this->user->Id()) {
                $this->is_self = true;
                $this->template->assign('is_self', true);
            }

            $this->AddContentSub('comments', new SC_Comments($this, $this->user));
            $this->ExecContentSub('comments');
        }
    }

    protected function Controller_Unknown_User($uid) {
        AppLog::Report('User not found', compact('uid'));
        $this->ExecTemplate('errors/404.tpl');
    }

    protected function Action_Default() {
        if(!$this->user) {
            $this->Controller_Unknown_User();
            return; //invalid user
        }

        AppLog::Report('User profile request', $this->user->data);

        $this->AddPlayerDataArray($this->user->data);

        $profile = $this->user->profile;
        if($profile)
            $this->AddPlayerDataArray($profile->data);
        else
            MakeWarning('User does not have a profile!', $this->user->data);

        /* Avatar URL */
        $this->AddPlayerData('avatar_url', $this->user->GetAvatarPath());

        /* Last Seen */
        if($this->user->IsOnline())
            $this->AddPlayerData('is_online', true);
        else
            $this->AddPlayerData('lastseen_duration',
                Duration(time()-$this->user['lastseen'], true));

        /* Add Friend */
        if(!$this->session->IsActive()
            || (($this->session->user->Id() != $this->user->Id())
                && !$this->user->IsFriend($this->session->user))) {
            //todo: Check if user settings allow friend requests
            $this->template->assign('can_addfriend', true);
        }

        /* Summary */
        if($profile)
            $this->AddPlayerData('summary', $profile->GetParsed('summary'));

        /* Games & Game Experience */
        $user_games = M_UserGame::GetArrayBySearch(
            array('user_id' => $this->user->Id()), false, array('title'));

        $games = array();
        if($user_games) {
            foreach($user_games as $user_game) {
                $gamexp_array = M_UserGamexp::GetArrayBySearch(array(
                    $user_game->primary_key => $user_game->Id()
                ));

                //todo: Check if game is in database
                $games[] = array(
                    'id' => $user_game->Id(),
                    'title' => $user_game['title'],
                    'external' => true,
                    'gamexp_array' => $gamexp_array
                );
            }
        }

        $this->AddPlayerData('games', $games);

        if($profile) {
            /* Country */
            if($profile['country']) {
                $countrycodes = GetCountryCodes();
                $this->AddPlayerData('country', $countrycodes[$profile['country']]);
            }

            /* Age */
            if($profile['show_age'] && $this->user['birthday']) {
                $duration = DurationArray(time()-$this->user['birthday']);
                if($duration['years'])
                    $this->AddPlayerData('age', $duration['years']);
            }

            /* Gender */
            if($profile['gender'])
                $this->AddPlayerData('gender', ucwords($profile['gender']));

            /* IRC */
            if($profile['irc_channel'] && $profile['irc_network']) {
                $channel = substr($profile['irc_channel'], 1);
                $this->AddPlayerData('irc', "irc://$profile[irc_network]/$channel");
            }
        }

        if($profile && !$this->is_self) {
            /* Personal Info */
            if($profile->IsInSet('show_personal', 'anyone')) {
                $this->template->assign('show_personal', true);
            }
            else if($profile['show_personal'] && $this->session->IsActive()) {
                if($profile->IsInSet('show_personal', 'friends')
                    && $this->user->IsFriend($this->session->user))
                    $this->template->assign('show_personal', true);

                //todo: Check teams, groups
            }

            /* Contact Info */
            if($profile->IsInSet('allow_contact', 'anyone')) {
                $this->template->assign('can_contact', true);
            }
            else if($profile['allow_contact'] && $this->session->IsActive()) {
                if($profile->IsInSet('allow_contact', 'friends')
                    && $this->user->IsFriend($this->session->user))
                    $this->template->assign('can_contact', true);

                //todo: Check teams, groups
            }
        }

        /* Friends */
        $friends = $this->user->GetFriends(array('displayname', 'profile_key'), 20);

        foreach($friends as &$friend) {
            $friend = array(
                'avatar_uri' => $friend->GetAvatarPath('small'),
                'displayname' => $friend->GetSafe('displayname'),
                'profile_uri' => $friend->GetProfileURI()
            );
        }

        $this->AddPlayerData('friends', $friends);
    }

    protected function Action_AddFriend() {
        if(!$this->user) {
            $this->Controller_Unknown_User();
            return; //invalid user
        }

        $this->RequireSession();

        $friendship = M_Friendship::GetByUserIDs(
            $this->session->user->Id(),
            $this->user->Id()
        );

        //todo: Warn user if friendship is still pending?

        if(!$friendship) {
            $friendship = new M_Friendship;
            $friendship->SetUserIDs(
                $this->session->user->Id(),
                $this->user->Id()
            );

            //todo: Send friendship request

            $friendship->Create();
        }

        $this->Redirect();
    }
}

$interface = new C_UserProfile;
$interface->Display();