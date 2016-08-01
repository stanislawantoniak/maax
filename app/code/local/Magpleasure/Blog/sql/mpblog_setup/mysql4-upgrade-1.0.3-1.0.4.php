<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

$installer = $this;
$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('mp_blog_comments_subscriptions')}`(
  `subscription_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` INT(10) UNSIGNED NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `customer_id` INT(10) UNSIGNED,
  `subscription_type` SMALLINT(1) UNSIGNED NOT NULL,
  `status` SMALLINT(1) UNSIGNED NOT NULL,
  `hash` VARCHAR(255) NOT NULL,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP NOT NULL,
  PRIMARY KEY (`subscription_id`),
  INDEX (`hash`),
  INDEX (`email`),
  CONSTRAINT `FK_MPBLOG_SUBSCRIPTION_POST` FOREIGN KEY (`post_id`) REFERENCES `{$this->getTable('mp_blog_posts')}`(`post_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=INNODB CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('mp_blog_comments_notifications')}`(
  `notification_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` INT(10) UNSIGNED NOT NULL,
  `subscription_id` INT(10) UNSIGNED NOT NULL,
  `comment_id` BIGINT UNSIGNED NOT NULL,
  `status` SMALLINT(1) UNSIGNED NOT NULL,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL,
  PRIMARY KEY (`notification_id`),
  CONSTRAINT `FK_MPBLOG_NOTIFICATION_POST` FOREIGN KEY (`post_id`) REFERENCES `{$this->getTable('mp_blog_posts')}`(`post_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_MPBLOG_NOTIFICATION_SUBSCRIPTION` FOREIGN KEY (`subscription_id`) REFERENCES `{$this->getTable('mp_blog_comments_subscriptions')}`(`subscription_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=INNODB CHARSET=utf8;


ALTER TABLE `{$this->getTable('mp_blog_comments')}`
  ADD COLUMN `notified` SMALLINT(1) DEFAULT 1  NOT NULL AFTER `updated_at`;

    ");

$installer->endSetup();