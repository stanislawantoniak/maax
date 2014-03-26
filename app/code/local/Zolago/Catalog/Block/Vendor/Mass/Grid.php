<?php

class Zolago_Catalog_Block_Vendor_Mass_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('zolagocatalog_mass_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

	protected function _prepareCollection(){
        $collection = Mage::getResourceModel('catalog/product_collection');
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		$collection->addAttributeToSelect("sku");
		$collection->addAttributeToSelect("name");
		$collection->addPriceData();
		$collection->addAttributeToFilter("udropship_vendor", $this->getVendorId());
		$collection->addAttributeToFilter("attribute_set_id", $this->getAttributeSet()->getId());
		$store = $this->getStore();
		if(!Mage::app()->isSingleStoreMode() && !$store->isAdmin()){
			$collection->setStoreId($store->getId());
			$collection->addWebsiteFilter($store->getWebsite());
		}
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $store = Mage::app()->getStore();
        $this->addColumn("entity_id", array(
			"index"=>"entity_id", 
			"type"=>"number",
			"header"=> Mage::helper("zolagocatalog")->__("ID"),
			"width" => "100px"
		));
        
        $this->addColumn("sku", array(
			"index"=>"sku", 
			"width" => "150px",
			"header"=>Mage::helper("zolagocatalog")->__("SKU"))
		);
        
        
        $this->addColumn("name", array(
            "index"     =>"name",
            "header"    => Mage::helper("zolagocatalog")->__("Name"),
        ));
        $this->addColumn("price", array(
            "index"     => "price",
			'type'  => 'price',
			'currency_code' => $store->getBaseCurrency()->getCode(),
            "header"    => Mage::helper("zolagocatalog")->__("Price"),
        ));
        return parent::_prepareColumns();
    }

	public function getGridUrl() {
		return $this->getUrl("*/*/grid", array("_current"=>true));
	}


	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('catalog')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));
        return $this;
    }

    public function getRowUrl($row){
        return null;
    }
	
	/**
	 * @return Zolago_Dropship_Model_Session
	 */
	protected function _getSession(){
		return Mage::getSingleton('udropship/session');
	}
	
	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	public function getVendorId() {
		return $this->_getSession()->getVendorId();
	}
	
	/**
	 * @return Mage_Eav_Model_Entity_Attribute_Set
	 */
	public function getAttributeSet() {
		return $this->getParentBlock()->getCurrentAttributeSet();
	}
	
	/**
	 * @return Mage_Core_Model_Store
	 */
	public function getStore() {
		return $this->getParentBlock()->getCurrentStore();
	}
    

}