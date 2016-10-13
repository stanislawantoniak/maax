<?php
/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 */
$adminVersion = Mage::getConfig()->getModuleConfig('Mage_Admin')->version;
if (version_compare($adminVersion, '1.6.1.2', '>=')) {
    $whitelistBlock = Mage::getModel('admin/block')->load('lookbookslider/lookbookslider', 'block_name');
    $whitelistBlock->setData('block_name', 'lookbookslider/lookbookslider');
    $whitelistBlock->setData('is_allowed', 1);
    $whitelistBlock->save();
}

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->endSetup();
