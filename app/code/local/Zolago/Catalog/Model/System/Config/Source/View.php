<?php

/**
 * PO open for Magento
 *
 * @category    Zolago
 * @package     Zolago_Catalog
 */
class Zolago_Catalog_Model_System_Config_Source_View
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PENDING,
                  'label' => Mage::helper('adminhtml')->__('Pending')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_EXPORTED,
                  'label' => Mage::helper('adminhtml')->__('Exported')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_RETURNED,
                  'label' => Mage::helper('adminhtml')->__('Returned')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_ACK,
                  'label' => Mage::helper('adminhtml')->__('Acknowledged')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_BACKORDER,
                  'label' => Mage::helper('adminhtml')->__('Backorder')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_ONHOLD,
                  'label' => Mage::helper('adminhtml')->__('On Hold')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_READY,
                  'label' => Mage::helper('adminhtml')->__('Ready to Ship')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL,
                  'label' => Mage::helper('adminhtml')->__('Partially Shipped')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_SHIPPED,
                  'label' => Mage::helper('adminhtml')->__('Shipped')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_CANCELED,
                  'label' => Mage::helper('adminhtml')->__('Canceled')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_DELIVERED,
                  'label' => Mage::helper('adminhtml')->__('Delivered')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_READY,
                  'label' => Mage::helper('adminhtml')->__('Ready for stock PO')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_EXPORTED,
                  'label' => Mage::helper('adminhtml')->__('Exported stock PO')),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_RECEIVED,
                  'label' => Mage::helper('adminhtml')->__('Received stock PO'))
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PENDING => Mage::helper('adminhtml')->__('Pending'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_EXPORTED=> Mage::helper('adminhtml')->__('Exported'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_RETURNED => Mage::helper('adminhtml')->__('Returned'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_ACK => Mage::helper('adminhtml')->__('Acknowledged'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_BACKORDER => Mage::helper('adminhtml')->__('Backorder'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_ONHOLD => Mage::helper('adminhtml')->__('On Hold'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_READY => Mage::helper('adminhtml')->__('Ready to Ship'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL => Mage::helper('adminhtml')->__('Partially Shipped'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_SHIPPED => Mage::helper('adminhtml')->__('Shipped'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_CANCELED => Mage::helper('adminhtml')->__('Canceled'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_DELIVERED => Mage::helper('adminhtml')->__('Delivered'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_READY => Mage::helper('adminhtml')->__('Ready for stock PO'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_EXPORTED => Mage::helper('adminhtml')->__('Exported stock PO'),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_RECEIVED => Mage::helper('adminhtml')->__('Received stock PO')
        );
    }
}
