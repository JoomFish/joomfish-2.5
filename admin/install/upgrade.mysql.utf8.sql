CREATE TABLE `#__languages_bak` SELECT * FROM `#__languages`;
CREATE TABLE `#__jf_content_bak` SELECT * FROM `#__jf_content`;

DROP TABLE `#__jf_languages_ext`;
RENAME TABLE `#__languages`  TO `#__jf_languages_ext`;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `#__languages` (`lang_id`, `lang_code`, `title`, `title_native`, `sef`, `image`, `description`, `published`) 
 SELECT `id`, `code`, `name`, `name`, `shortcode`, `image`, "", `active` from `#__jf_languages_ext`;


ALTER TABLE `#__jf_languages_ext`
  CHANGE `id` `lang_id` INT( 11 ) NOT NULL,
  DROP `iso`,
  DROP `code`,
  DROP `shortcode`,
  DROP `name`,
  DROP `active`,
  CHANGE `image` `image_ext` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL
;

CREATE TABLE IF NOT EXISTS `#__jf_translationmap` (
  `language` char(7) NOT NULL DEFAULT '',
  `reference_id` int(11) NOT NULL DEFAULT '0',
  `translation_id` int(11) NOT NULL DEFAULT '0',
  `reference_table` varchar(100) NOT NULL DEFAULT '',
    UNIQUE KEY (`language`, `reference_id`, `reference_table`),
    UNIQUE KEY (`language`, `translation_id`, `reference_table`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/** optional but does not work in all MySQL installations */
/*
CREATE VIEW `#__jf_languages` AS
SELECT `l`.`lang_id`, `l`.`lang_code`, `l`.`title`, `l`.`title_native`, `l`.`sef`, `l`.`description`, `l`.`published`, `l`.`image`, `lext`.`image_ext`, `lext`.`fallback_code`, `lext`.`params`, `lext`.`ordering`
 FROM `#__languages` as `l` left outer join `#__jf_languages_ext` as `lext`on `l`.`lang_id` = `lext`.`lang_id`
ORDER BY  `lext`.`ordering`
*/