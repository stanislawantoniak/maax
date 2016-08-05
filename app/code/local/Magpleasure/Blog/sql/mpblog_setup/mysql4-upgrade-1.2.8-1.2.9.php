<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

$installer = $this;
$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('mp_blog_comments_notifications')}`
  ADD COLUMN `updated_at` TIMESTAMP NULL AFTER `created_at`;

");


$installer->endSetup();