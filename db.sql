CREATE TABLE IF NOT EXISTS `channel` (
  `cid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='頻道列表';

INSERT INTO `channel` (`cid`, `name`) VALUES(1, '大廳');

CREATE TABLE IF NOT EXISTS `chatlog` (
  `messageid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '訊息編號',
  `time` int(10) unsigned NOT NULL COMMENT '發言時間',
  `fromuid` int(10) unsigned NOT NULL COMMENT '發言者uid',
  `fromusername` char(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '發言者名稱',
  `touid` int(10) unsigned DEFAULT NULL COMMENT '密頻目標uid',
  `tousername` char(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '接收者名稱',
  `channel` smallint(5) unsigned DEFAULT NULL COMMENT '頻道ID',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '類型',
  `message` char(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`messageid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='聊天室訊息';

CREATE TABLE IF NOT EXISTS `ipban` (
  `ip` char(50) NOT NULL,
  `count` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `lastfailtime` int(10) NOT NULL,
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mailuser` (
  `hash` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `username` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` char(50) COLLATE utf8_unicode_ci NOT NULL,
  `regtime` int(10) NOT NULL,
  PRIMARY KEY (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `online_user` (
  `sid` char(8) COLLATE utf8_unicode_ci NOT NULL COMMENT 'HASH值',
  `uid` int(10) unsigned NOT NULL COMMENT '登入者UID',
  `channel` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '所在頻道',
  `ip` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '登入者IP',
  `logintime` int(10) unsigned NOT NULL COMMENT '登入時間',
  `lastmsgid` int(10) unsigned NOT NULL COMMENT '最後閱讀訊息編號',
  `lastactiontime` int(10) unsigned NOT NULL COMMENT '最後操作時間',
  `lastaction` char(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='在線表';

CREATE TABLE IF NOT EXISTS `user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` char(50) COLLATE utf8_unicode_ci NOT NULL,
  `action` tinyint(1) NOT NULL DEFAULT '0',
  `regtime` int(10) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
