CREATE TABLE IF NOT EXISTS `#__jf_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL DEFAULT '0',
  `reference_id` int(11) NOT NULL DEFAULT '0',
  `reference_table` varchar(100) NOT NULL DEFAULT '',
  `reference_field` varchar(100) NOT NULL DEFAULT '',
  `value` mediumtext NOT NULL,
  `original_value` varchar(255) DEFAULT NULL,
  `original_text` mediumtext NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `combo` (`reference_id`,`reference_field`,`reference_table`),
  KEY `jfContent` (`language_id`,`reference_id`,`reference_table`),
  KEY `jfContentLanguage` (`reference_id`,`reference_field`,`reference_table`,`language_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__jf_languages_ext` (
  `lang_id` int(11) NOT NULL AUTO_INCREMENT,
  `image_ext` varchar(100) DEFAULT NULL,
  `fallback_code` varchar(20) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lang_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__jf_tableinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `joomlatablename` varchar(100) NOT NULL DEFAULT '',
  `tablepkID` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__jf_translationmap` (
  `language` char(7) NOT NULL DEFAULT '',
  `reference_id` int(11) NOT NULL DEFAULT '0',
  `translation_id` int(11) NOT NULL DEFAULT '0',
  `reference_table` varchar(100) NOT NULL DEFAULT '',
        UNIQUE KEY (`language`, `reference_id`, `reference_table`),
    UNIQUE KEY (`language`, `translation_id`, `reference_table`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__languages` (
  `lang_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lang_code` char(7) NOT NULL,
  `title` varchar(50) NOT NULL,
  `title_native` varchar(50) NOT NULL,
  `sef` varchar(50) NOT NULL,
  `image` varchar(50) NOT NULL,
  `description` varchar(512) NOT NULL,
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `published` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lang_id`),
  UNIQUE KEY `idx_sef` (`sef`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;