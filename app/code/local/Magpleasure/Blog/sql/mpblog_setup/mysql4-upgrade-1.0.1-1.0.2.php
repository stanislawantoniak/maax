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
  ADD COLUMN `published_at` TIMESTAMP NULL AFTER `updated_at`,
  ADD COLUMN `notify_on_enable` SMALLINT(1) DEFAULT 0  NOT NULL AFTER `published_at`,
  ADD COLUMN `display_short_content` SMALLINT(1) DEFAULT 1  NOT NULL AFTER `notify_on_enable`,
  ADD COLUMN `user_define_publish` SMALLINT(1) DEFAULT 0  NOT NULL AFTER `published_at`,
  ADD COLUMN `comments_enabled` SMALLINT(1) DEFAULT 1  NOT NULL AFTER `display_short_content`;

ALTER TABLE `{$this->getTable('mp_blog_tags')}`
  ADD COLUMN `meta_title` VARCHAR(255) NULL AFTER `url_key`,
  ADD COLUMN `meta_tags` VARCHAR(255) NULL AFTER `meta_title`,
  ADD COLUMN `meta_description` TINYTEXT NULL AFTER `meta_tags`;

    ");

$installer->endSetup();