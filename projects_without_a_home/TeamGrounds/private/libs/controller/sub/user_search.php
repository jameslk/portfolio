<?php

Libs('controller/sub/tg', 'pagination');

class SC_UserSearch extends SC_Tg {
    public $template_name = 'user_search.tpl';
    
    public $default_max_results = 10;
    public $max_results_threshold = 30;
    
    public $include_this_user = false;
    
    function __construct($parent, $search_actions) {
        parent::__construct($parent);
        
        $this->template->assign('search_actions', $search_actions);
    }
    
    public function Search_Pre() {
        $this->template->assign('has_searched', true);
    }
    
    public function ActiveSearchFields() {
        $fields = func_get_args();
        
        $active_fields = array();
        foreach($fields as $field) {
            if(isset($_REQUEST[$field]) && $_REQUEST[$field])
                $active_fields[$field] = $_REQUEST[$field];
        }
        
        if(!empty($active_fields))
            return $active_fields;
        
        $this->messages->Error('Please enter something to search for.');
        return false;
    }
    
    public function Action_Search_Personal() {
        $this->Search_Pre();
        
        $search_data = $this->ActiveSearchFields('displayname', 'email', 'realname');
        
        if(!$search_data)
            return;
        
        $where = array();
        
        if(isset($search_data['displayname']))
            $where[] = "displayname LIKE '%%%s%%'";
        
        if(isset($search_data['email']))
            $where[] = 'email = %s';
        
        if(isset($search_data['realname']))
            $where[] = "realname LIKE '%%%s%%'";
        
        $where = implode(' OR ', $where);
        
        if($this->session->IsActive() && !$this->include_this_user) {
            $where = "($where) AND user_id != %s";
            $search_data[] = $this->session->user->Id();
        }
        
        $count = M_User::GetCountByWhere($where, $search_data);
        
        if(isset($_REQUEST['max_results']))
            $max_results = min($_REQUEST['max_results'], $this->max_results_threshold);
        else
            $max_results = $this->default_max_results;
        
        $pagination = new Pagination($count, $max_results, 'user_search_page');
        
        $users = M_User::GetArrayByWhere($where, $search_data,
            $pagination->SQL_Limit(), array('displayname', 'profile_key',
                'avatar.*', 'games.*'));
        
        $this->template->assign('users', $users);
    }
}