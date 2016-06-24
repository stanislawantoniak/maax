<?php
/**
 * Altima Lookbook Free Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Altima
 * @package    Altima_LookbookFree
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


$adminVersion = Mage::getConfig()->getModuleConfig('Mage_Admin')->version;
if (version_compare($adminVersion, '1.6.1.2', '>=')) {
    $whitelistBlock = Mage::getModel('admin/block')->load('lookbook/lookbook', 'block_name');
    $whitelistBlock->setData('block_name', 'lookbook/lookbook');
    $whitelistBlock->setData('is_allowed', 1);
    $whitelistBlock->save();
}
