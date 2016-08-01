<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('mp_blog_views')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('mp_blog_views')}`(
  `view_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` INT(10) UNSIGNED NOT NULL,
  `customer_id` INT(10) UNSIGNED,
  `session_id` VARCHAR(255) NOT NULL,
  `remote_addr` BIGINT(20) NOT NULL,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `referer_url` TINYTEXT NULL,
  PRIMARY KEY (`view_id`),
  INDEX `MPBLOG_VIEWS_POST_ID` (`post_id`),
  INDEX `MPBLOG_VIEWS_CUSTOMER_ID` (`customer_id`),
  INDEX `MPBLOG_VIEWS_SESSION_ID` (`session_id`),
  CONSTRAINT `FK_MPBLOG_VIEWS_POST` FOREIGN KEY (`post_id`) REFERENCES `{$this->getTable('mp_blog_posts')}`(`post_id`) ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE `{$this->getTable('mp_blog_posts')}`
  ADD COLUMN `views` INT(10) DEFAULT 0  NOT NULL AFTER `comments_enabled`;

    ");

$installer->endSetup();