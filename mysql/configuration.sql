-- SQL Table Structure
--
-- Import this configure into your database
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL,
  `email` varchar(256) NOT NULL,
  `first_name` varchar(256) NOT NULL DEFAULT '',
  `last_name` varchar(256) NOT NULL DEFAULT '',
  `password` varchar(256) NOT NULL,
  `creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime NOT NULL
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(64) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `key` varchar(128) NOT NULL,
  `value` varchar(512) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `USER_PREFERENCE` (`user_id`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- Boot up the event scheduler
-- You can check to see if the event scheduler is running using:
--     SHOW PROCESSLIST;
SET GLOBAL event_scheduler = ON;


-- Set up a schedule to clear sessions older than 30 days
-- Show all events using:
--     SELECT * FROM INFORMATION_SCHEMA.EVENTS
CREATE EVENT `delete_old_sessions`
ON SCHEDULE EVERY 1 MINUTE STARTS CURRENT_TIMESTAMP DO
    DELETE FROM sessions WHERE create_time < DATE_SUB(NOW(), INTERVAL 30 DAY);
