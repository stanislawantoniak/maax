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
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$this->startSetup();

$sEav = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
$conn = $this->_conn;

$conn->addColumn($this->getTable('sales_flat_quote_shipping_rate'), 'udropship_vendor', 'int unsigned');

if (!Mage::helper('udropship')->isSalesFlat()) {
    $sEav->addAttribute('quote_address_rate', 'udropship_vendor', array('type' => 'static'));
}

$this->endSetup();