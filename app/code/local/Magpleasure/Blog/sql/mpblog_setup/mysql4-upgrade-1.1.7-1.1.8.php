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

ALTER TABLE `{$this->getTable('mp_blog_comments')}`
  ADD INDEX (`post_id`, `store_id`, `status`, `reply_to`),
  ADD INDEX (`store_id`, `status`, `reply_to`);

    ");

} catch (Exception $e) {

    /** @var Magpleasure_Common_Helper_Data $helper */
    $helper = Mage::helper('magpleasure');
    $helper->getException()->logException($e);
}

try {

    $installer->run("

ALTER TABLE `{$this->getTable('mp_blog_tags')}`
  ADD INDEX (`url_key`);

    ");

} catch (Exception $e) {

    /** @var Magpleasure_Common_Helper_Data $helper */
    $helper = Mage::helper('magpleasure');
    $helper->getException()->logException($e);
}

try {

    $installer->run("

ALTER TABLE `{$this->getTable('mp_blog_posts')}`
  ADD INDEX (`status`, `url_key`),
  ADD INDEX (`status`);

    ");

} catch (Exception $e) {

    /** @var Magpleasure_Common_Helper_Data $helper */
    $helper = Mage::helper('magpleasure');
    $helper->getException()->logException($e);
}


try {

    $installer->run("

ALTER TABLE `{$this->getTable('mp_blog_categories')}`
  ADD INDEX (`url_key`, `status`);

    ");

} catch (Exception $e) {

    /** @var Magpleasure_Common_Helper_Data $helper */
    $helper = Mage::helper('magpleasure');
    $helper->getException()->logException($e);
}

$installer->endSetup();