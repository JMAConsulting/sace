-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC. All rights reserved.                        |
-- |                                                                    |
-- | This work is published under the GNU AGPLv3 license with some      |
-- | permitted exceptions and without any warranty. For full license    |
-- | and copyright information, see https://civicrm.org/licensing       |
-- +--------------------------------------------------------------------+
--
-- Generated from schema.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--

-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC. All rights reserved.                        |
-- |                                                                    |
-- | This work is published under the GNU AGPLv3 license with some      |
-- | permitted exceptions and without any warranty. For full license    |
-- | and copyright information, see https://civicrm.org/licensing       |
-- +--------------------------------------------------------------------+
--
-- Generated from drop.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--
-- /*******************************************************
-- *
-- * Clean up the existing tables
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civirule_rule_tag`;
DROP TABLE IF EXISTS `civirule_rule`;
DROP TABLE IF EXISTS `civirule_trigger`;
DROP TABLE IF EXISTS `civirule_rule_log`;
DROP TABLE IF EXISTS `civirule_rule_condition`;
DROP TABLE IF EXISTS `civirule_rule_action`;
DROP TABLE IF EXISTS `civirule_condition`;
DROP TABLE IF EXISTS `civirule_action`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civirule_action
-- *
-- * FIXME
-- *
-- *******************************************************/
CREATE TABLE `civirule_action` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Action ID',
     `name` varchar(80)   DEFAULT NULL ,
     `label` varchar(128)   DEFAULT NULL ,
     `class_name` varchar(128)   DEFAULT NULL ,
     `is_active` int NOT NULL  DEFAULT 1 ,
     `created_date` date   DEFAULT NULL ,
     `created_user_id` int   DEFAULT NULL ,
     `modified_date` date   DEFAULT NULL ,
     `modified_user_id` int   DEFAULT NULL
,
        PRIMARY KEY (`id`)



)  ENGINE=Innodb  ;

-- /*******************************************************
-- *
-- * civirule_condition
-- *
-- * FIXME
-- *
-- *******************************************************/
CREATE TABLE `civirule_condition` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Condition ID',
     `name` varchar(80)   DEFAULT NULL ,
     `label` varchar(128)   DEFAULT NULL ,
     `class_name` varchar(128)   DEFAULT NULL ,
     `is_active` int NOT NULL  DEFAULT 1 ,
     `created_date` date   DEFAULT NULL ,
     `created_user_id` int   DEFAULT NULL ,
     `modified_date` date   DEFAULT NULL ,
     `modified_user_id` int   DEFAULT NULL
,
        PRIMARY KEY (`id`)



)  ENGINE=Innodb  ;

-- /*******************************************************
-- *
-- * civirule_trigger
-- *
-- * FIXME
-- *
-- *******************************************************/
CREATE TABLE `civirule_trigger` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Trigger ID',
     `name` varchar(80)   DEFAULT NULL ,
     `label` varchar(128)   DEFAULT NULL ,
     `object_name` varchar(45)   DEFAULT NULL ,
     `op` varchar(45)   DEFAULT NULL ,
     `cron` int   DEFAULT 0 ,
     `class_name` varchar(128)   DEFAULT NULL ,
     `is_active` int NOT NULL  DEFAULT 1 ,
     `created_date` date   DEFAULT NULL ,
     `created_user_id` int   DEFAULT NULL ,
     `modified_date` date   DEFAULT NULL ,
     `modified_user_id` int   DEFAULT NULL
,
        PRIMARY KEY (`id`)



)  ENGINE=Innodb  ;

-- /*******************************************************
-- *
-- * civirule_rule
-- *
-- * FIXME
-- *
-- *******************************************************/
CREATE TABLE `civirule_rule` (
     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Rule ID',
     `name` varchar(80)   DEFAULT NULL ,
     `label` varchar(128)   DEFAULT NULL ,
     `trigger_id` int unsigned   DEFAULT NULL ,
     `trigger_params` text   DEFAULT NULL ,
     `is_active` int NOT NULL  DEFAULT 1 ,
     `description` varchar(255)   DEFAULT NULL ,
     `help_text` text   DEFAULT NULL ,
     `created_date` date   DEFAULT NULL ,
     `created_user_id` int   DEFAULT NULL ,
     `modified_date` date   DEFAULT NULL ,
     `modified_user_id` int   DEFAULT NULL,
     `is_debug` tinyint DEFAULT 0,
     PRIMARY KEY (`id`),
     CONSTRAINT FK_civirule_rule_trigger_id FOREIGN KEY (`trigger_id`) REFERENCES `civirule_trigger`(`id`) ON DELETE NO ACTION
) ENGINE=Innodb;

-- /*******************************************************
-- *
-- * civirule_rule_action
-- *
-- * FIXME
-- *
-- *******************************************************/
CREATE TABLE `civirule_rule_action` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique RuleAction ID',
     `rule_id` int unsigned   DEFAULT NULL ,
     `action_id` int unsigned   DEFAULT NULL ,
     `action_params` text   DEFAULT NULL ,
     `delay` text   DEFAULT NULL ,
     `ignore_condition_with_delay` int   DEFAULT 0 ,
     `is_active` int   DEFAULT 1
,
        PRIMARY KEY (`id`)


,          CONSTRAINT FK_civirule_rule_action_rule_id FOREIGN KEY (`rule_id`) REFERENCES `civirule_rule`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civirule_rule_action_action_id FOREIGN KEY (`action_id`) REFERENCES `civirule_action`(`id`) ON DELETE CASCADE
)  ENGINE=Innodb  ;

-- /*******************************************************
-- *
-- * civirule_rule_condition
-- *
-- * FIXME
-- *
-- *******************************************************/
CREATE TABLE `civirule_rule_condition` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique RuleCondition ID',
     `rule_id` int unsigned    ,
     `condition_link` varchar(3)   DEFAULT NULL ,
     `condition_id` int unsigned    ,
     `condition_params` text   DEFAULT NULL ,
     `is_active` int   DEFAULT 1
,
        PRIMARY KEY (`id`)


,          CONSTRAINT FK_civirule_rule_condition_rule_id FOREIGN KEY (`rule_id`) REFERENCES `civirule_rule`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civirule_rule_condition_condition_id FOREIGN KEY (`condition_id`) REFERENCES `civirule_condition`(`id`) ON DELETE CASCADE
)  ENGINE=Innodb  ;

-- /*******************************************************
-- *
-- * civirule_rule_log
-- *
-- * FIXME
-- *
-- *******************************************************/
CREATE TABLE `civirule_rule_log` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique RuleLog ID',
     `rule_id` int unsigned   DEFAULT NULL ,
     `contact_id` int unsigned   DEFAULT NULL ,
     `entity_table` varchar(255)   DEFAULT NULL ,
     `entity_id` int unsigned   DEFAULT NULL ,
     `log_date` datetime   DEFAULT NULL
,
        PRIMARY KEY (`id`)

    ,     INDEX `rule_id`(
        rule_id
  )
  ,     INDEX `contact_id`(
        contact_id
  )
  ,     INDEX `rule_contact_id`(
        rule_id
      , contact_id
  )


)  ENGINE=Innodb  ;

-- /*******************************************************
-- *
-- * civirule_rule_tag
-- *
-- * FIXME
-- *
-- *******************************************************/
CREATE TABLE `civirule_rule_tag` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique RuleTag ID',
     `rule_id` int unsigned    ,
     `rule_tag_id` int   DEFAULT NULL
,
        PRIMARY KEY (`id`)


,          CONSTRAINT FK_civirule_rule_tag_rule_id FOREIGN KEY (`rule_id`) REFERENCES `civirule_rule`(`id`) ON DELETE CASCADE
)  ENGINE=Innodb  ;

