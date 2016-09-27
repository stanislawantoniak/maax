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
      ADD COLUMN `thumbnail_width_value` SMALLINT(3) UNSIGNED NULL AFTER `post_thumbnail`,
      ADD COLUMN `thumbnail_width_dimension` VARCHAR(5) DEFAULT 'none' NOT NULL AFTER `thumbnail_width_value`,
      ADD COLUMN `thumbnail_height_value` SMALLINT(3) UNSIGNED NULL AFTER `thumbnail_width_dimension`,
      ADD COLUMN `thumbnail_height_dimension` VARCHAR(5) DEFAULT 'none' NOT NULL AFTER `thumbnail_height_value`,
      ADD COLUMN `thumbnail_layout` VARCHAR(5) DEFAULT 'top' NOT NULL AFTER `thumbnail_height_dimension`;

");


$installer->endSetup();