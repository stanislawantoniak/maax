<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

$installer = $this;
$installer->startSetup();

try {

    $installer->run("

ALTER TABLE `{$this->getTable('mp_blog_posts')}`
  ADD COLUMN `recently_commented_at` timestamp NOT NULL AFTER `published_at`;

");

} catch (Exception $e) {

    /** @var Magpleasure_Common_Helper_Data $helper */
    $helper = Mage::helper('magpleasure');
    $helper->getException()->logException($e);
}

$installer->endSetup();