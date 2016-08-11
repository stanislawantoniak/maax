<?php
/**
  
 */

class ZolagoOs_OmniChannelMicrosite_Block_Adminhtml_Cms_Block_Grid extends Mage_Adminhtml_Block_Cms_Block_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cms/block')->getCollection();

        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
            $collection->addFieldToFilter('udropship_vendor', $vendor->getId());
        }

        /* @var $collection Mage_Cms_Model_Mysql4_Block_Collection */
        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
}