<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

$installer = $this;
$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('mp_blog_authors')}`
  ADD COLUMN `facebook_profile` VARCHAR(255) NULL AFTER `google_profile`,
  ADD COLUMN `twitter_profile` VARCHAR(255) NULL AFTER `facebook_profile`;

ALTER TABLE `{$this->getTable('mp_blog_posts')}`
  ADD COLUMN `facebook_profile` VARCHAR(255) NULL AFTER `google_profile`,
  ADD COLUMN `twitter_profile` VARCHAR(255) NULL AFTER `facebook_profile`;

");


$installer->endSetup();