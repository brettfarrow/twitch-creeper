CREATE TABLE IF NOT EXISTS `your_table_name` (
  `twitch_name` varchar(32) NOT NULL,
  `display_name` varchar(64) NOT NULL,
  `viewers` int(10) unsigned NOT NULL,
  `time_gmt` datetime NOT NULL,
  `crawl_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`crawl_id`),
  UNIQUE KEY `crawl_id` (`crawl_id`),
  KEY `twitch_name` (`twitch_name`,`display_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;