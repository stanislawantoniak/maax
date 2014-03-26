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
 * @package    Unirgy_DropshipVendorAskQuestion
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipVendorAskQuestion_Model_Mysql4_Question extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('udqa/question', 'question_id');
    }
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $this->joinShipments($select);
        $this->joinProducts($select);
        $this->joinVendors($select);
        return $select;
    }
    public function joinShipments($select)
    {
        $select
            ->joinLeft(
            array('shipment_grid' => $this->getTable('sales/shipment_grid')),
            $this->getMainTable().'.shipment_id = shipment_grid.entity_id',
            array(
                'shipment_name' => 'shipment_grid.shipping_name',
                'shipment_grid.order_increment_id',
                'shipment_grid.order_id',
                'shipment_increment_id'=>'shipment_grid.increment_id',
                'shipment_id'=>'shipment_grid.entity_id'
            )
        )
        ;
        return $this;
    }
    public function joinProducts($select)
    {
        $select
            ->joinLeft(
            array('product' => $this->getTable('catalog/product')),
            $this->getMainTable().'.product_id = product.entity_id',
            array(
                'product_id' => 'product.entity_id',
                'product_sku' => 'product.sku'
            )
        )
        ;
        $this->addProductAttributeToSelect($select, array('product_name'=>'name'));
        return $this;
    }
    public function joinVendors($select)
    {
        $select
            ->joinLeft(
            array('vendor' => $this->getTable('udropship/vendor')),
            $this->getMainTable().'.vendor_id = vendor.vendor_id',
            array(
                'vendor_name'  => 'vendor.vendor_name',
                'vendor_email' => 'vendor.email',
                'vendor_id'    => 'vendor.vendor_id'
            )
        )
        ;
        return $this;
    }
    public function addProductAttributeToSelect($select, $attrCode)
    {
        Mage::helper('udqa')->addProductAttributeToSelect($select, $attrCode, $this->getMainTable().'.product_id');
        return $this;
    }
}