<?php
/**
 * brand settings grid
 */
class Zolago_Sizetable_Block_Adminhtml_Dropship_Settings_Grid_Brand extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct()
    {
        parent::__construct();
        $this->setId('sizetable_settings_brand');
        $this->setDefaultSort('value');
        $this->setUseAjax(true);
    }
    protected function _prepareCollection()
    {
        $vendorId = $this->getVendorId();
        
        $this->setDefaultFilter(array('vendor_id'=>$vendorId));
        $collection = Mage::getModel('zolagosizetable/vendor_brand')->getCollection();
        ;
        
        return parent::_prepareCollection();
        
//            ->addAttributeToSelect('name')
//            ->addAttributeToSelect('sku')
//            ->addAttributeToSelect('price')
//            ->addStoreFilter($this->getRequest()->getParam('store'))
//            ->addAttributeToFilter('udropship_vendor', $this->getVendor()->getId());
//            ->addAttributeToFilter('type_id', array('in'=>array('simple')))
//        ;
        
        $res = Mage::getSingleton('core/resource');
        $stockTable = $res->getTableName('cataloginventory/stock_item');
        $conn = $collection->getConnection();
        
        $collection->getSelect()->join(
            array('cisi' => $stockTable), 
            $conn->quoteInto('cisi.product_id=e.entity_id AND cisi.stock_id=?', Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID), 
            array('_stock_status'=>$this->_getStockField('status'))
        );
        
        if (Mage::helper('udropship')->isUdmultiAvailable()) {
            $collection->getSelect()->joinLeft(
                array('uvp' => $res->getTableName('udropship/vendor_product')), 
                $conn->quoteInto('uvp.product_id=e.entity_id AND uvp.vendor_id=?', $this->getVendor()->getId()), 
                array('*','_stock_qty'=>$this->_getStockField('qty'), 'vendor_sku'=>'uvp.vendor_sku', 'vendor_cost'=>'uvp.vendor_cost', 'backorders'=>'uvp.backorders')
            );
            $collection->getSelect()->columns(array('_stock_qty'=>$this->_getStockField('qty')));
            //$collection->getSelect()->columns(array('_stock_qty'=>'IFNULL(uvp.stock_qty,cisi.qty'));
        } else {
            $collection->getSelect()->columns(array('stock_qty'=>$this->_getStockField('qty')));
        }

        $this->setCollection($collection);

    }



}