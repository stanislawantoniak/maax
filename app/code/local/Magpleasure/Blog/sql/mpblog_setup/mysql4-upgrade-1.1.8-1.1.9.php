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

ALTER TABLE `{$this->getTable('mp_blog_authors')}`
  ADD COLUMN `store_id` SMALLINT(2) UNSIGNED DEFAULT 0  NOT NULL AFTER `google_profile`,
  ADD COLUMN `updated_at` TIMESTAMP NOT NULL AFTER `created_at`;


    ");

} catch (Exception $e) {

    /** @var Magpleasure_Common_Helper_Data $helper */
    $helper = Mage::helper('magpleasure');
    $helper->getException()->logException($e);
}

$installer->endSetup();