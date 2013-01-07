--CREATE TABLE IF NOT EXISTS tg_forum_threads (
--    thread_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
--    
--    parent_id INT UNSIGNED NOT NULL DEFAULT '0',
--    parent_type ENUM('user', 'team', 'group') NOT NULL DEFAULT 'user',
--    
--    forum_id INT UNSIGNED NOT NULL DEFAULT '0',
--    
--    firstpost_id INT UNSIGNED NOT NULL DEFAULT '0',
--    lastpost_id INT UNSIGNED NOT NULL DEFAULT '0',
--    
--    title varchar(250) NOT NULL default '',
--    
--    firstposter_id INT UNSIGNED NOT NULL DEFAULT '0',
--    lastposter_id INT UNSIGNED NOT NULL DEFAULT '0',
--    
--    reply_count INT UNSIGNED NOT NULL default '0',
--    view_count INT UNSIGNED NOT NULL default '0',
--    
--    is_open BOOL NOT NULL DEFAULT 'true',
--    
--    -- Table Settings
--    PRIMARY KEY (friendship_id),
--    UNIQUE KEY unique_friendship (user1_id, user2_id)
--);

CREATE TABLE IF NOT EXISTS tg_comment_threads (
    thread_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    parent_id INT UNSIGNED NOT NULL DEFAULT '0',
    parent_type ENUM('user', 'team', 'group') NOT NULL DEFAULT 'user',
    
    lastpost_date INT UNSIGNED NOT NULL DEFAULT '0',
    
    -- Table Settings
    PRIMARY KEY (thread_id),
    KEY parent (parent_id, parent_type)
);

CREATE TABLE IF NOT EXISTS tg_comment_posts (
    post_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    thread_id INT UNSIGNED NOT NULL DEFAULT '0',
    user_id INT UNSIGNED NOT NULL DEFAULT '0',
    
    content TEXT NOT NULL DEFAULT '',
    
    post_date INT UNSIGNED NOT NULL DEFAULT '0',
    edit_date INT UNSIGNED NOT NULL DEFAULT '0',
    
    is_firstpost BOOL NOT NULL DEFAULT false,
    reported INT UNSIGNED NOT NULL DEFAULT '0',
    
    -- Table Settings
    PRIMARY KEY (post_id),
    KEY thread_id (thread_id),
    KEY user_id (user_id),
    KEY reported (reported)
);

CREATE TABLE IF NOT EXISTS tg_tags (
    tag_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    field CHAR(64) NOT NULL DEFAULT '',
    title CHAR(64) NOT NULL DEFAULT '',
    popularity INT UNSIGNED NOT NULL DEFAULT '1',
    
    -- Table Settings
    PRIMARY KEY (tag_id),
    UNIQUE unique_tag (field, title)
);

CREATE TABLE IF NOT EXISTS tg_games (
    game_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    title CHAR(64) NOT NULL DEFAULT '',
    game_type CHAR(16) NOT NULL DEFAULT '',
    
    -- Table Settings
    PRIMARY KEY (game_id),
    UNIQUE title (title)
);