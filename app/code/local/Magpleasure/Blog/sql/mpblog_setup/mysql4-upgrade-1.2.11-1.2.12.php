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
  ADD COLUMN `thumbnail_url` VARCHAR(255) NULL AFTER `list_thumbnail`;

");


$installer->endSetup();