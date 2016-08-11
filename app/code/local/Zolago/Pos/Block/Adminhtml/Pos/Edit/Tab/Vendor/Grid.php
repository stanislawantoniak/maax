<?php

class Zolago_Pos_Block_Adminhtml_Pos_Edit_Tab_Vendor_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('zolagopos_pos_vendor_grid');
        $this->setDefaultSort('vendor_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
        if(count($this->getSelectedIds())){
            $this->setDefaultFilter(array('in_pos'=>1));
        }
    }
    

    protected function _prepareCollection(){
        $this->setCollection($this->_getCollection());
        return parent::_prepareCollection();
    }
    
    /**
     * @return ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Collection
     */
    protected function _getCollection() {
        $collection = Mage::getResourceModel('udropship/vendor_collection');
        Mage::getResourceModel('zolagopos/pos')->addPosToVendorCollection($collection);
        return $collection;
    }
    
    /**
     * @return int
     */
    protected function _getPosId() {
        return Mage::app()->getRequest()->getParam("pos_id");
    }
    public function getGridUrl() {
        return $this->getUrl("*/*/vendorgrid", array("_current"=>true));
    }
    
    public function getPostSelectedIds(){
        return Mage::app()->getRequest()->getParam("entityCollection");
    }
    
    public function getSelectedVendors(){
        return $this->getSelectedIds();
    }

    public function getSelectedIds() {
        $postData = $this->getPostSelectedIds();
        if(is_array($postData)){
            return $postData;
        }
        return $this->getModel()->getVendorCollection()->getAllIds();
    }
    
    

    protected function _addColumnFilterToCollection($column)
    {
        switch ($column->getId()) {
            case "in_pos":
                $posId = $this->_getPosId();
                if ($posId) {
                    $this->getCollection()->addFieldToFilter('pos_id' , $posId);
                }
                $vendorIds = $this->getSelectedIds();
                if (empty($vendorIds)) {
                    $vendorIds = 0;
                }
                if ($column->getFilter()->getValue()) {
                    $this->getCollection()->addFieldToFilter('main_table.vendor_id', array('in'=>$vendorIds));
                }
                elseif(!empty($vendorIds)) {
                    $this->getCollection()->addFieldToFilter('main_table.vendor_id', array('nin'=>$vendorIds));
                }
            break;
            default:
                parent::_addColumnFilterToCollection($column);
            break;
        }
        return $this;
    }
    
    protected function _prepareColumns() {
        $this->addColumn('in_pos', array(
            'type'   => 'checkbox',
            'header_css_class'  => 'a-center',
            'align'  => 'center',
            'index'  => 'vendor_id',
            'name'   => 'in_pos',
            'editable' => true,
            'values' => $this->getSelectedIds()
        ));
        
        $this->addColumn('vendor_name', array(
            'header' => Mage::helper('zolagopos')->__('Vendor Name'),
            'index' => 'vendor_name',
        ));
        $this->addColumn('grid_email', array(
            'header'    =>  Mage::helper('zolagopos')->__('Email'),
            'index'     => 'email',
        ));
        $this->addColumn('status', array(
            'header'    => Mage::helper('zolagopos')->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('udropship/source')->setPath('vendor_statuses')->toOptionHash(),
        ));
        
        return parent::_prepareColumns();
    }

    /**
     * @return Zolago_Pos_Model_Pos
     */
    public function getModel() {
        return Mage::registry('zolagopos_current_pos');
    }

}