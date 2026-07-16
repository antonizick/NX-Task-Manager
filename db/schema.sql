-- NXTM database schema
-- Reverse-derived from a production schema dump (antonizi_nxtask, MySQL 8.4 / Percona).
-- Requires MySQL 8.0+ or Percona Server 8+ (uses utf8mb4_0900_ai_ci, not available on MariaDB).
--
-- Usage:
--   mysql -u root -p -e "CREATE DATABASE antonizi_nxtask CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"
--   mysql -u root -p antonizi_nxtask < db/schema.sql
--   mysql -u root -p antonizi_nxtask < db/seed.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- Lookup tables (no app-facing CRUD; power color-coding in the views below)
-- ---------------------------------------------------------------------------

DROP TABLE IF EXISTS `dataColorPalette`;
CREATE TABLE `dataColorPalette` (
  `number` int NOT NULL,
  `colorcode` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`number`),
  KEY `number` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `greenColor`;
CREATE TABLE `greenColor` (
  `ind` int NOT NULL,
  `color_code` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`ind`),
  KEY `ind` (`ind`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------------
-- Base tables
-- ---------------------------------------------------------------------------

DROP TABLE IF EXISTS `dataTaskCategories`;
CREATE TABLE `dataTaskCategories` (
  `indx` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `color` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `fcolor` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `rowstat` int NOT NULL COMMENT 'Background status, deleted?',
  PRIMARY KEY (`indx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `datastatus`;
CREATE TABLE `datastatus` (
  `pkindx` int NOT NULL AUTO_INCREMENT,
  `status` varchar(32) NOT NULL,
  `color` varchar(32) NOT NULL,
  `fcolor` varchar(32) NOT NULL,
  `rowstat` int NOT NULL COMMENT 'Background status, deleted?',
  PRIMARY KEY (`pkindx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `dataLists`;
CREATE TABLE `dataLists` (
  `indx` int NOT NULL AUTO_INCREMENT,
  `lcode` varchar(255) DEFAULT NULL,
  `in1` int DEFAULT NULL,
  `in2` int DEFAULT NULL,
  `in3` int DEFAULT NULL COMMENT '999 to suppress',
  `Name` varchar(255) DEFAULT NULL,
  `dat1` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `dat2` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `dat3` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `dat4` text NOT NULL,
  PRIMARY KEY (`indx`),
  KEY `in2` (`in2`,`in3`),
  KEY `in3` (`in3`),
  KEY `in2_2` (`in2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `dataMemo`;
CREATE TABLE `dataMemo` (
  `indx` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `statcode` int DEFAULT NULL,
  `memo` text,
  PRIMARY KEY (`indx`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `dataNxlinks`;
CREATE TABLE `dataNxlinks` (
  `indx` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(128) DEFAULT NULL,
  `Category` varchar(128) DEFAULT NULL,
  `SO` int DEFAULT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `contact` varchar(128) DEFAULT NULL,
  `Link` varchar(255) DEFAULT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tier` int DEFAULT NULL,
  `lclass` int DEFAULT NULL COMMENT 'set to 88 to suppress',
  PRIMARY KEY (`indx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `datatasks`;
CREATE TABLE `datatasks` (
  `pkind` int NOT NULL AUTO_INCREMENT,
  `priority2day` int DEFAULT NULL COMMENT 'set > 80000 to supress',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `urgency` int DEFAULT NULL,
  `status` int DEFAULT NULL,
  `elenav` int DEFAULT NULL,
  `project` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `narritive` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `contact` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `cryo date` date DEFAULT NULL,
  `deadline1` date DEFAULT NULL,
  `deadline2` date DEFAULT NULL,
  `deadlineh` date DEFAULT NULL,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `links` varchar(388) DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `other` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `dwe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'contains the substring "personal" to route a row to the Personal Tasks module instead of Work Tasks',
  `sort overide` int DEFAULT NULL,
  `timeStamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pkind`),
  KEY `statusLock` (`status`),
  CONSTRAINT `statusLock` FOREIGN KEY (`status`) REFERENCES `datastatus` (`pkindx`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------------
-- Views
-- Sentinel values used throughout: priority2day = 88888 means archived;
-- rowstat = 88888 means soft-deleted category/status; lclass = 88 suppresses
-- a link; in3 = 999 suppresses a list row; dwe LIKE '%personal%' routes a
-- datatasks row to the Personal Tasks module instead of Work Tasks.
-- ---------------------------------------------------------------------------

DROP VIEW IF EXISTS `viewTasks`;
CREATE VIEW `viewTasks` AS
SELECT
  `t`.`pkind` AS `pkind`, `t`.`priority2day` AS `priority2day`, `t`.`description` AS `description`,
  `t`.`urgency` AS `urgency`, `t`.`status` AS `status`, `t`.`elenav` AS `elenav`, `t`.`project` AS `project`,
  `t`.`narritive` AS `narritive`, `t`.`contact` AS `contact`, `t`.`cryo date` AS `cryo date`,
  `t`.`deadline1` AS `deadline1`, `t`.`deadline2` AS `deadline2`, `t`.`deadlineh` AS `deadlineh`,
  `t`.`notes` AS `notes`, `t`.`links` AS `links`, `t`.`subject` AS `subject`, `t`.`tags` AS `tags`,
  `t`.`other` AS `other`, `t`.`dwe` AS `dwe`, `t`.`sort overide` AS `sort overide`,
  `s`.`status` AS `status_text`, `s`.`color` AS `status_color`, `s`.`fcolor` AS `status_font_color`,
  `p1`.`colorcode` AS `cc1`, `p2`.`colorcode` AS `cc2`,
  `c`.`name` AS `catname`, `c`.`color` AS `catBGcolor`, `c`.`fcolor` AS `catFcolor`
FROM `datatasks` `t`
  LEFT JOIN `datastatus` `s` ON (`t`.`status` = `s`.`pkindx`)
  LEFT JOIN `dataColorPalette` `p1` ON (`t`.`priority2day` = `p1`.`number`)
  LEFT JOIN `dataColorPalette` `p2` ON (`t`.`urgency` = `p2`.`number`)
  LEFT JOIN `dataTaskCategories` `c` ON (`t`.`elenav` = `c`.`indx`)
WHERE (`t`.`priority2day` < 80000) AND ((`t`.`dwe` IS NULL) OR (NOT (`t`.`dwe` LIKE '%personal%')));

DROP VIEW IF EXISTS `viewArchivedTasks`;
CREATE VIEW `viewArchivedTasks` AS
SELECT
  `t`.`pkind` AS `pkind`, `t`.`priority2day` AS `priority2day`, `t`.`description` AS `description`,
  `t`.`urgency` AS `urgency`, `t`.`status` AS `status`, `t`.`elenav` AS `elenav`, `t`.`project` AS `project`,
  `t`.`narritive` AS `narritive`, `t`.`contact` AS `contact`, `t`.`cryo date` AS `cryo date`,
  `t`.`deadline1` AS `deadline1`, `t`.`deadline2` AS `deadline2`, `t`.`deadlineh` AS `deadlineh`,
  `t`.`notes` AS `notes`, `t`.`links` AS `links`, `t`.`subject` AS `subject`, `t`.`tags` AS `tags`,
  `t`.`other` AS `other`, `t`.`dwe` AS `dwe`, `t`.`sort overide` AS `sort overide`,
  `s`.`status` AS `status_text`, `s`.`color` AS `status_color`, `s`.`fcolor` AS `status_font_color`,
  `p1`.`colorcode` AS `cc1`, `p2`.`colorcode` AS `cc2`,
  `c`.`name` AS `catname`, `c`.`color` AS `catBGcolor`, `c`.`fcolor` AS `catFcolor`
FROM `datatasks` `t`
  LEFT JOIN `datastatus` `s` ON (`t`.`status` = `s`.`pkindx`)
  LEFT JOIN `dataColorPalette` `p1` ON (`t`.`priority2day` = `p1`.`number`)
  LEFT JOIN `dataColorPalette` `p2` ON (`t`.`urgency` = `p2`.`number`)
  LEFT JOIN `dataTaskCategories` `c` ON (`t`.`elenav` = `c`.`indx`)
WHERE (`t`.`priority2day` = 88888) AND ((`t`.`dwe` IS NULL) OR (NOT (`t`.`dwe` LIKE '%personal%')));

DROP VIEW IF EXISTS `viewPTasks`;
CREATE VIEW `viewPTasks` AS
SELECT
  `t`.`pkind` AS `pkind`, `t`.`priority2day` AS `priority2day`, `t`.`description` AS `description`,
  `t`.`urgency` AS `urgency`, `t`.`status` AS `status`, `t`.`elenav` AS `elenav`, `t`.`project` AS `project`,
  `t`.`narritive` AS `narritive`, `t`.`contact` AS `contact`, `t`.`cryo date` AS `cryo date`,
  `t`.`deadline1` AS `deadline1`, `t`.`deadline2` AS `deadline2`, `t`.`deadlineh` AS `deadlineh`,
  `t`.`notes` AS `notes`, `t`.`links` AS `links`, `t`.`subject` AS `subject`, `t`.`tags` AS `tags`,
  `t`.`other` AS `other`, `t`.`dwe` AS `dwe`, `t`.`sort overide` AS `sort overide`,
  `s`.`status` AS `status_text`, `s`.`color` AS `status_color`, `s`.`fcolor` AS `status_font_color`,
  `p1`.`colorcode` AS `cc1`, `p2`.`colorcode` AS `cc2`,
  `c`.`name` AS `catname`, `c`.`color` AS `catBGcolor`, `c`.`fcolor` AS `catFcolor`
FROM `datatasks` `t`
  LEFT JOIN `datastatus` `s` ON (`t`.`status` = `s`.`pkindx`)
  LEFT JOIN `dataColorPalette` `p1` ON (`t`.`priority2day` = `p1`.`number`)
  LEFT JOIN `dataColorPalette` `p2` ON (`t`.`urgency` = `p2`.`number`)
  LEFT JOIN `dataTaskCategories` `c` ON (`t`.`elenav` = `c`.`indx`)
WHERE (`t`.`priority2day` <> 88888) AND (`t`.`dwe` LIKE '%personal%');

DROP VIEW IF EXISTS `viewPArchivedTasks`;
CREATE VIEW `viewPArchivedTasks` AS
SELECT
  `t`.`pkind` AS `pkind`, `t`.`priority2day` AS `priority2day`, `t`.`description` AS `description`,
  `t`.`urgency` AS `urgency`, `t`.`status` AS `status`, `t`.`elenav` AS `elenav`, `t`.`project` AS `project`,
  `t`.`narritive` AS `narritive`, `t`.`contact` AS `contact`, `t`.`cryo date` AS `cryo date`,
  `t`.`deadline1` AS `deadline1`, `t`.`deadline2` AS `deadline2`, `t`.`deadlineh` AS `deadlineh`,
  `t`.`notes` AS `notes`, `t`.`links` AS `links`, `t`.`subject` AS `subject`, `t`.`tags` AS `tags`,
  `t`.`other` AS `other`, `t`.`dwe` AS `dwe`, `t`.`sort overide` AS `sort overide`,
  `s`.`status` AS `status_text`, `s`.`color` AS `status_color`, `s`.`fcolor` AS `status_font_color`,
  `p1`.`colorcode` AS `cc1`, `p2`.`colorcode` AS `cc2`,
  `c`.`name` AS `catname`, `c`.`color` AS `catBGcolor`, `c`.`fcolor` AS `catFcolor`
FROM `datatasks` `t`
  LEFT JOIN `datastatus` `s` ON (`t`.`status` = `s`.`pkindx`)
  LEFT JOIN `dataColorPalette` `p1` ON (`t`.`priority2day` = `p1`.`number`)
  LEFT JOIN `dataColorPalette` `p2` ON (`t`.`urgency` = `p2`.`number`)
  LEFT JOIN `dataTaskCategories` `c` ON (`t`.`elenav` = `c`.`indx`)
WHERE (`t`.`priority2day` = 88888) AND (`t`.`dwe` LIKE '%personal%');

DROP VIEW IF EXISTS `viewMemo`;
CREATE VIEW `viewMemo` AS
SELECT `indx`, `Name`, `statcode`, `memo`
FROM `dataMemo`
WHERE (`statcode` < 8000) OR (`statcode` IS NULL);

DROP VIEW IF EXISTS `viewNxlinks`;
CREATE VIEW `viewNxlinks` AS
SELECT `indx`, `Name`, `Category`, `SO`, `Description`, `contact`, `Link`, `date_created`, `last_updated`, `tier`, `lclass`
FROM `dataNxlinks`
WHERE `lclass` <> 88;

DROP VIEW IF EXISTS `viewListList`;
CREATE VIEW `viewListList` AS
SELECT MIN(`indx`) AS `indx`, `lcode`
FROM `dataLists`
WHERE `in3` <> 999
GROUP BY `lcode`
ORDER BY `lcode`;

DROP VIEW IF EXISTS `viewListData`;
CREATE VIEW `viewListData` AS
SELECT
  `dataLists`.`indx` AS `indx`, `dataLists`.`lcode` AS `lcode`, `dataLists`.`in1` AS `in1`,
  `dataLists`.`in2` AS `in2`, `dataLists`.`in3` AS `in3`, `dataLists`.`Name` AS `Name`,
  `dataLists`.`dat1` AS `dat1`, `dataLists`.`dat2` AS `dat2`, `dataLists`.`dat3` AS `dat3`, `dataLists`.`dat4` AS `dat4`,
  `gc1`.`ind` AS `in2_ind`, `gc1`.`color_code` AS `in2_color`,
  `gc2`.`ind` AS `in3_ind`, `gc2`.`color_code` AS `in3_color`
FROM `dataLists`
  LEFT JOIN `greenColor` `gc1` ON (`dataLists`.`in2` = `gc1`.`ind`)
  LEFT JOIN `greenColor` `gc2` ON (`dataLists`.`in3` = `gc2`.`ind`)
WHERE (`dataLists`.`in3` <> 999) OR (`dataLists`.`in3` IS NULL);

DROP VIEW IF EXISTS `viewTaskCategories`;
CREATE VIEW `viewTaskCategories` AS
SELECT `indx`, `name`, `color`, `fcolor`, `rowstat`
FROM `dataTaskCategories`
WHERE `rowstat` <> 88888;

DROP VIEW IF EXISTS `viewDataStatus`;
CREATE VIEW `viewDataStatus` AS
SELECT `pkindx` AS `indx`, `status` AS `name`, `color`, `fcolor`, `rowstat`
FROM `datastatus`
WHERE `rowstat` <> 88888;

SET FOREIGN_KEY_CHECKS = 1;
