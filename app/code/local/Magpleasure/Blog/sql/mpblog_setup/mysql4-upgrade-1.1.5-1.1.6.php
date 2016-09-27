<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

$installer = $this;
$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('mp_blog_posts')}`
  ADD COLUMN `google_profile` VARCHAR(255) NULL AFTER `posted_by`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('mp_blog_authors')}`(
  `author_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `google_profile` VARCHAR(255),
  `created_at` TIMESTAMP NOT NULL,
  PRIMARY KEY (`author_id`)
);


");

$installer->endSetup();