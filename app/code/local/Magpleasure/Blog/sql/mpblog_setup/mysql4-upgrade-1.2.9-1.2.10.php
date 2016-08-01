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
  DROP COLUMN `thumbnail_width_value`,
  DROP COLUMN `thumbnail_width_dimension`,
  DROP COLUMN `thumbnail_height_value`,
  DROP COLUMN `thumbnail_height_dimension`,
  DROP COLUMN `thumbnail_layout`,
  ADD COLUMN `list_thumbnail` VARCHAR(255) NULL AFTER `post_thumbnail`,
  ADD COLUMN `grid_class` VARCHAR(2) DEFAULT 'w1' NOT NULL AFTER `list_thumbnail`;


");


$installer->endSetup();