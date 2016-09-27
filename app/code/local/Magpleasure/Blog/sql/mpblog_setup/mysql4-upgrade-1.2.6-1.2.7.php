<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

$installer = $this;
$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('mp_blog_drafts')}`(
  `draft_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `post_id` INT(10) UNSIGNED,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `full_content` TEXT NOT NULL,
  `short_content` TEXT,
  PRIMARY KEY (`draft_id`),
  INDEX (`user_id`, `post_id`),
  CONSTRAINT `FK_MPBLOG_DRAFT_TO_POST` FOREIGN KEY (`post_id`) REFERENCES `{$this->getTable('mp_blog_posts')}`(`post_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=INNODB CHARSET=utf8;

");


$installer->endSetup();