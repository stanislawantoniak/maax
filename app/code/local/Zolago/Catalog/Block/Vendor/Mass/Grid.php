<?php

class Zolago_Catalog_Block_Vendor_Mass_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('zolagocatalog_mass_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
    }

	protected function _prepareCollection(){
        $collection = Mage::getResourceModel('catalog/product_collection');
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		$collection->addAttributeToSelect("name");
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        
        $this->addColumn("entity_id", array(
			"index"=>"entity_id", 
			"header"=>Mage::helper("zolagocatalog")->__("Id"))
		);
        
        
        $this->addColumn("name", array(
            "index"     =>"name",
            "header"    => Mage::helper("zolagocatalog")->__("Name"),
        ));
        return parent::_prepareColumns();
    }


    public function getRowUrl($row){
        return null;
    }
    

}