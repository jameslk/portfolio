<?php

define('ROOT_DIR', '../');
require_once(ROOT_DIR.'private/global/global.php');

Libs('controller/tg_interface', 'controller/sub/steps',
    'controller/sub/user_search', 'model/team', 'model/team_game');

/* ----- */

class C_CreateTeam extends C_TG_Interface {
    public $title = 'Create a New Team';
    public $template_name = 'team/create_team.tpl';

    public $steps = array(
        'team_info' => 'Team Information',
        'add_games' => 'Add Team Games',
        'recruit' => 'Recruit Team Members'
    );

    public $steps_sub;
    
    public $team_id_var = 'tid';
    
    public $team = NULL;

    protected function Controller_Pre(&$params) {
        parent::Controller_Pre($params);

        $this->RequireSession();

        $this->steps_sub = new SC_Steps($this, $this->steps);
        $this->AddContentSub('steps', $this->steps_sub);
        $this->ExecContentSub('steps');
        
        if(isset($_REQUEST[$this->team_id_var]) && M_Team::Exists($_REQUEST[$this->team_id_var])) {
            $this->team = new M_Team($_REQUEST[$this->team_id_var]);
            $this->template->assign('team', $this->team);
        }
    }
    
    protected function Controller_Post($params) {
        parent::Controller_Post($params);
        
        $this->template->assign($_POST);
    }
    
    public function Step_TeamInfo() {
        $this->template->assign('team_type', 'clan'); //default to clan
        $this->template->assign('team_type_options', array(
            'clan' => 'Clan',
            'guild' => 'Guild'
        ));
    }
    
    public function Step_Recruit() {
        $search_actions = '
            <a href="javascript:create_team.AddRecruit({$user->Id()}, \'{$user.displayname}\', \'{$user->GetAvatarPath(\'small\')}\');">Recruit Player</a>
        ';
        
        $this->AddContentSub('user_search', new SC_UserSearch($this, $search_actions));
        $this->ExecContentSub('user_search');
        
        if(isset($_SESSION['recruits_store']))
            $this->template->assign('add_recruits', $_SESSION['recruits_store']);
    }

    protected function Ajax_GamesList($q) {
        $games = M_Tag::AjaxSearch($q, 'game', 20);
        if($games)
            return implode("\n", $games);
        else
            return '';
    }
    
    protected function Ajax_StoreRecruits($recruits_store) {
        $_SESSION['recruits_store'] = $recruits_store;
    }
    
    protected function Action_CreateTeam () {
        //todo: Check if user has reached the maximum team registration limit
        
        $data = $_POST;
        
        $team = new M_Team;
        $profile = new M_TeamProfile;
        
        /* Set name */
        if(!$team->SetField('name', $data['name']))
            $this->messages->Error('Please enter a valid team name.');
        
        /* Set game_tag */
        if(!$profile->SetField('game_tag', $data['game_tag']))
            $this->messages->Error('Please enter a valid team game tag.');
        
        /* Set team_type */
        if(!$profile->SetField('team_type', $data['team_type']))
            $this->messages->Error('Please select a valid team type.');
        
        /* Set profile_key */
        if(!$team->FilterField('profile_key', $data['profile_key']))
            $this->messages->FieldError('profile_key', 'Please enter a valid team profile URL.');
        else if($team->CheckProfileKeyExists($data['profile_key']))
            $this->messages->FieldError('profile_key', 'Sorry, this team profile URL is already taken.');
        else
            $team->SetField('profile_key', $data['profile_key'], false);
        
        if(!$this->messages->has_errors) {
            AppLog::Report('Team was created', $team->data);
            $team->Create($this->session->user, $profile);
            
            $this->messages->Success('Your team has been created.'); //todo: Queue this
            
            $this->steps_sub->GoToNext(false, array('tid' => $team->Id()));
        }
        else {
            AppLog::Report('Team creation failed', var_export($team->data, true));
        }
    }
    
    public function RequireTeamOwner() {
        if(!$this->team)
            $this->messages->Error('Sorry, this team does not exist.');
        else if($this->team->GetOwnerID() != $this->session->user->Id())
            $this->messages->Error('You must be the owner of this team to edit it.');
        
        if($this->messages->has_errors) {
            //todo: Queue errors and redirect
            $this->steps_sub->GoToFirst();
        }
    }
    
    protected function Action_AddGames($tid) {
        $games = $_POST['games'];
        if(!is_array($games))
            $this->steps_sub->GoToNext(); //no games added
        
        $games = array_filter($games);
        if(empty($games))
            $this->steps_sub->GoToNext(); //no games added
        
        $this->RequireTeamOwner();
        
        if(M_TeamGame::GetCountBySearch(array($this->team->primary_key => $this->team->Id())) > 0)
            $this->steps_sub->GoToNext(); //team already has games
        
        $team_games = array();
        foreach($games as $game) {
            $team_game = new M_TeamGame;
            
            if(!$team_game->SetField('title', $game)) {
                $this->messages->Error('"'.$game.'" is not a valid game title.');
            }
            else {
                $team_game->SetField('team_id', $this->team->Id(), false);
                
                $team_games[] = $team_game;
            }
        }
        
        if(!$this->messages->has_errors) {
            foreach($team_games as $team_game)
                $team_game->Create();
            
            $this->steps_sub->GoToNext();
        }
        else {
            $this->template->assign('add_games', $games);
        }
    }
    
    protected function Action_AddRecruits($tid) {
        $new_recruits = $_POST['new_recruits'];
        if(!is_array($new_recruits))
            $this->Redirect($this->team->GetProfileURI()); //no members added
        
        $new_recruits = array_filter($new_recruits);
        if(empty($new_recruits))
            $this->Redirect($this->team->GetProfileURI()); //no members added
        
        $this->RequireTeamOwner();
        
        if(M_TeamMember::GetCountBySearch(array($this->team->primary_key => $this->team->Id())) > 1)
            $this->Redirect($this->team->GetProfileURI()); //team already has members
        
        foreach($new_recruits as $recruit) {
            if(($recruit != $this->session->user->Id()) && M_User::Exists($recruit)) {
                $team_member = new M_TeamMember;
                
                $team_member->SetField('team_id', $this->team->Id(), false);
                $team_member->SetField('user_id', $recruit);
                
                $team_member->Create();
            }
        }
        
        $this->Redirect($this->team->GetProfileURI());
    }
}

$interface = new C_CreateTeam;
$interface->Display();