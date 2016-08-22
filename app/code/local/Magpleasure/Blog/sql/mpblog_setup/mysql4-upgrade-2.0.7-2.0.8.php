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
  ADD COLUMN `canonical_url` varchar(255) NULL;
");

$installer->endSetup();