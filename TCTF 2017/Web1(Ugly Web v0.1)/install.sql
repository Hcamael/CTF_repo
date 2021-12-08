CREATE TABLE `users` (
  `userID` mediumint(8) unsigned NOT NULL auto_increment,
  `username` varchar(50) NOT NULL default '',
  `password` varchar(100) NOT NULL default '',
  `email` varchar(150) NOT NULL default '',
  `activationHash` varchar(150) default '',
  `active` tinyint(1) default 0,
  PRIMARY KEY  (`userID`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `activationHash` (`activationHash`),
  KEY `active` (`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE `message` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `from` varchar(50) NOT NULL default '',
  `to` varchar(50) NOT NULL default '',
  `msg` TEXT,
  `read` tinyint(1) default 0,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE `reset` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `userID` mediumint(8) unsigned NOT NULL,
  `hash` varchar(150) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE `flag` (
  `flag` varchar(150) NOT NULL,
  PRIMARY KEY  (`flag`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `flag` VALUES ('flag{*****}');
INSERT INTO `users` VALUES (1, 'admin', '********', 'root@5alt.me', '', 1);
