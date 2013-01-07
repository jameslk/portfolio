CREATE TABLE IF NOT EXISTS tg_users (
    user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    email CHAR(255) NOT NULL DEFAULT '',
    profile_key CHAR(100) NOT NULL DEFAULT '',
    
	password CHAR(32) NOT NULL DEFAULT '',
	
	displayname VARCHAR(255) NOT NULL DEFAULT '',
	
	realname CHAR(255) NOT NULL DEFAULT '',
	
	timezone SMALLINT NOT NULL DEFAULT '0',
	birthday CHAR(10) NOT NULL DEFAULT '',
	birthday_search date NOT NULL DEFAULT '0000-00-00',
	languageid SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	
	joindate INT UNSIGNED NOT NULL DEFAULT '0',
	lastseen INT UNSIGNED NOT NULL DEFAULT '0',
	ipaddress CHAR(15) NOT NULL DEFAULT '',
    
    -- Table Settings
    PRIMARY KEY (user_id),
    UNIQUE KEY email (email),
    KEY profile_key (profile_key),
    KEY birthday (birthday),
    KEY birthday_search (birthday_search)
);

CREATE TABLE IF NOT EXISTS tg_user_profiles (
    uprofile_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL DEFAULT '0',
    
    summary TEXT NOT NULL DEFAULT '',
    
    country CHAR(2) NOT NULL DEFAULT '',
    location VARCHAR(100) NOT NULL DEFAULT '',
    gender ENUM('', 'male', 'female') NOT NULL DEFAULT '',
    interests VARCHAR(255) NOT NULL DEFAULT '',
	website VARCHAR(100) NOT NULL DEFAULT '',
	
	msn VARCHAR(100) NOT NULL DEFAULT '',
	aim VARCHAR(20) NOT NULL DEFAULT '',
	yahoo VARCHAR(32) NOT NULL DEFAULT '',
	icq VARCHAR(20) NOT NULL DEFAULT '',
	skype VARCHAR(32) NOT NULL DEFAULT '',
	irc_channel VARCHAR(100) NOT NULL DEFAULT '',
	irc_network VARCHAR(255) NOT NULL DEFAULT '',
	
	show_age BOOL NOT NULL DEFAULT true,
	
	show_personal SET('', 'friends', 'teams', 'groups', 'anyone') NOT NULL DEFAULT 'anyone',
	allow_contact SET('', 'friends', 'teams', 'groups', 'anyone') NOT NULL DEFAULT 'friends,teams',
    
    -- Table Settings
    PRIMARY KEY (uprofile_id),
    UNIQUE user_id (user_id)
);

CREATE TABLE IF NOT EXISTS tg_user_games (
    ugame_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL DEFAULT '0',
    
    title CHAR(64) NOT NULL DEFAULT '',
    
    list_order INT UNSIGNED NOT NULL DEFAULT '0',
    
    -- Table Settings
    PRIMARY KEY (ugame_id),
    UNIQUE KEY unique_ugame (user_id, title)
);

CREATE TABLE IF NOT EXISTS tg_user_gamexp (
    gamexp_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ugame_id INT UNSIGNED NOT NULL DEFAULT '0',
    
    title VARCHAR(255) NOT NULL DEFAULT '0',
    content TEXT NOT NULL DEFAULT '',
    
    list_order INT UNSIGNED NOT NULL DEFAULT '0',
    
    -- Table Settings
    PRIMARY KEY (gamexp_id),
    UNIQUE unique_gamexp(gamexp_id, ugame_id)
);

CREATE TABLE IF NOT EXISTS tg_sessions (
    session_id CHAR(32) NOT NULL DEFAULT '',
    
    user_id INT UNSIGNED NOT NULL DEFAULT '0',
    start_time INT UNSIGNED NOT NULL DEFAULT '0',
    cookie_expire INT UNSIGNED NOT NULL DEFAULT '0',
    
    -- Table Settings
    PRIMARY KEY (session_id),
    UNIQUE KEY user_id (user_id)
);

CREATE TABLE IF NOT EXISTS tg_avatars (
    avatar_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    parent_id INT UNSIGNED NOT NULL DEFAULT '0',
    parent_type ENUM('user', 'team', 'group') NOT NULL DEFAULT 'user',
    
    -- Table Settings
    PRIMARY KEY (avatar_id),
    UNIQUE KEY unique_avatar (parent_id, parent_type)
);

CREATE TABLE IF NOT EXISTS tg_friendships (
    friendship_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    user1_id INT UNSIGNED NOT NULL DEFAULT '0',
    user2_id INT UNSIGNED NOT NULL DEFAULT '0',
    
    is_request BOOL NOT NULL DEFAULT false,
    
    -- Table Settings
    PRIMARY KEY (friendship_id),
    UNIQUE KEY unique_friendship (user1_id, user2_id)
);