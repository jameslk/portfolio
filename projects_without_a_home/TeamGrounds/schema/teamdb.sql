CREATE TABLE IF NOT EXISTS tg_teams (
    team_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    name VARCHAR(255) NOT NULL DEFAULT '',
    
    profile_key CHAR(100) NOT NULL DEFAULT '',
	
	created_by INT UNSIGNED NOT NULL DEFAULT '0',
	created_date INT UNSIGNED NOT NULL DEFAULT '0',
    
    -- Table Settings
    PRIMARY KEY (team_id),
    KEY profile_key (profile_key)
);

CREATE TABLE IF NOT EXISTS tg_team_profiles (
    tprofile_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    team_id INT UNSIGNED NOT NULL DEFAULT '0',
    
    game_tag VARCHAR(32) NOT NULL DEFAULT '',
	
	team_type ENUM('clan', 'guild') NOT NULL DEFAULT 'clan',
	
	location VARCHAR(100) NOT NULL DEFAULT '',
	website VARCHAR(100) NOT NULL DEFAULT '',
	
	summary TEXT NOT NULL DEFAULT '',
    
    -- Table Settings
    PRIMARY KEY (tprofile_id),
    UNIQUE team_id (team_id)
);

CREATE TABLE IF NOT EXISTS tg_team_members (
    tmember_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    team_id INT UNSIGNED NOT NULL DEFAULT '0',
    user_id INT UNSIGNED NOT NULL DEFAULT '0',
    
    team_role VARCHAR(64) NOT NULL DEFAULT '',
    
    access_flags SET(
        '',
        'owner',
        'admin',
        
        'edit_profile',
        'edit_members',
        
        'edit_media',
        'edit_events',
        'edit_news'
    ) NOT NULL DEFAULT '',
    
    request_status ENUM('', 'join', 'recruit') NOT NULL DEFAULT '',
    
    list_order INT UNSIGNED NOT NULL DEFAULT '0',
    
    -- Table Settings
    PRIMARY KEY (tmember_id),
    UNIQUE KEY unique_member (team_id, user_id),
    KEY access_flags (access_flags)
);

CREATE TABLE IF NOT EXISTS tg_team_games (
    tgame_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    team_id INT UNSIGNED NOT NULL DEFAULT '0',
    
    title CHAR(64) NOT NULL DEFAULT '',
    
    list_order INT UNSIGNED NOT NULL DEFAULT '0',
    
    -- Table Settings
    PRIMARY KEY (tgame_id),
    UNIQUE KEY unique_tgame (team_id, title)
);