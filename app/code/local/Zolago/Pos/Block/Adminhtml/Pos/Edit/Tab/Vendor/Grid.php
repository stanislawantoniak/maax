<?php

class Zolago_Pos_Block_Adminhtml_Pos_Edit_Tab_Vendor_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('zolagopos_pos_vendor_grid');
        $this->setDefaultSort('vendor_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);

    }
    

    protected function _prepareCollection(){
        $collection = $this->_getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * @return Unirgy_Dropship_Model_Mysql4_Vendor_Collection
     */
    protected function _getCollection() {
        $collection = Mage::getResourceModel('udropship/vendor_collection');
        Mage::getResourceModel('zolagopos/pos')->addPosToVendorCollection($collection);
        return $collection;
    }

    public function getGridUrl() {
        return $this->getUrl("*/*/vendorgrid");
    }

    protected function _getInPosIds() {
        $postData = Mage::app()->getRequest()->getParam("entityCollection");
        if(is_array($postData)){
            return $postData;
        }
        return array_keys($this->getInPos());
    }
    
    public function getInPos() {
        return $this->getVendors();
    }
    
    protected function _getIsOwnerIds() {
        $postData = Mage::app()->getRequest()->getParam("entityCollection");
        if(is_array($postData)){
            return $postData;
        }
        return array_keys($this->getIsOwner());
    }
    
    public function getIsOwner() {
        $out = array();
        foreach($this->getVendors() as $key=>$vendor){
            if($vendor['is_owner']){
                $out[$key] = $vendor;
            }
        }
        return $out;
    }


    public function getVendors()
    {
        $collection = $this->_getCollection();
        $collection->addFieldToFilter("pos_id", array("notnull"=>true));
        $vendors = array();
        foreach ($collection as $vendor) {
            $vendors[$vendor->getId()] = array('in_pos' => 1, "is_owner"=>$vendor->getIsOwner());
        }
        return $vendors;
    }
    
    public function getCollectionData() {
        return $this->getVendors();
    }




    protected function _addColumnFilterToCollection($column)
    {
        switch ($column->getId()) {
            case "in_pos":
                $vendorIds = $this->_getInPosIds();
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
            case "is_owner":
                $ownerIds = $this->_getIsOwnerIds();
                if (empty($ownerIds)) {
                    $ownerIds = 0;
                }
                if ($column->getFilter()->getValue()) {
                    $this->getCollection()->addFieldToFilter('pos_vendor.vendor_id', array('in'=>$ownerIds));
                }
                elseif(!empty($ownerIds)) {
                    $this->getCollection()->addFieldToFilter('main_table.vendor_id', array('nin'=>$ownerIds));
                }
            break;
            default:
                parent::_addColumnFilterToCollection($column);
            break;
        }
        return $this;
    }
    
    protected function _prepareColumns() {
        if ($this->getModel()->getId()) {
            $this->setDefaultFilter(array('in_pos'=>1));
        }
        $this->addColumn('in_pos', array(
            'header' => Mage::helper('zolagopos')->__('Assign'),
            'type'   => 'checkbox',
            'index'  => 'vendor_id',
            'name'   => 'in_pos',
            'html_name' => 'is_pos',
            'align'  => 'center',
            'values' => $this->_getInPosIds()
        ));
        
        $this->addColumn('is_owner', array(
            'header'    => Mage::helper('adminhtml')->__('Is owner'),
            'type'      => 'radio',
            'name'      => 'is_owner',
            'html_name' => 'is_owner',
            'values'    => $this->_getIsOwnerIds(),
            'align'     => 'center',
            'index'     => 'is_owner'
        ));

        
        $this->addColumn('vendor_name', array(
            'header' => Mage::helper('zolagopos')->__('Vendor Name'),
            'index' => 'vendor_name',
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