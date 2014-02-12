<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$hlp = Mage::helper('udropship');
if (!$hlp->hasMageFeature('sales_flat')) Mage::throwException($hlp->__('Unirgy_DropshipPo module does not support this version of magento'));
if (!$hlp->isUdpoActive()) return false;

$this->startSetup();

$conn = $this->_conn;

$conn->addColumn($this->getTable('udpo/po_item'), 'vendor_sku', 'varchar(255)');
$conn->addColumn($this->getTable('udpo/po_item'), 'vendor_simple_sku', 'varchar(255)');

$this->endSetup();