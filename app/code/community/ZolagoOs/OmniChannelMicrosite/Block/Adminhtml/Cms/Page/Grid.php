<?php
/**
  
 */

class ZolagoOs_OmniChannelMicrosite_Block_Adminhtml_Cms_Page_Grid extends Mage_Adminhtml_Block_Cms_Page_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cms/page')->getCollection();

        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
            $collection->addFieldToFilter('udropship_vendor', $vendor->getId());
        }

        /* @var $collection Mage_Cms_Model_Mysql4_Page_Collection */
        $collection->setFirstStoreFlag(true);
        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        if (!Mage::helper('umicrosite')->getCurrentVendor()) {
            $this->addColumn('udropship_vendor', array(
                'header'  => Mage::helper('cms')->__('Dropship Vendor'),
                'index'   => 'udropship_vendor',
                'type'    => 'options',
                'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
                'filter' => 'udropship/vendor_gridColumnFilter'
            ));
        }
    }
}
