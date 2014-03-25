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
		$collection->addAttributeToSelect("sku");
		$collection->addAttributeToSelect("name");
		$collection->addPriceData();
		$collection->addAttributeToFilter("udropship_vendor", $this->_getSession()->getVendorId());
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

	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('catalog')->__('Delete'),
//             'url'  => $this->getUrl('*/*/massDelete'),
			 'onclick' => "alert(1);",
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        //$statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

//        array_unshift($statuses, array('label'=>'', 'value'=>''));
//        $this->getMassactionBlock()->addItem('status', array(
//             'label'=> Mage::helper('catalog')->__('Change status'),
//             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
//             'additional' => array(
//                    'visibility' => array(
//                         'name' => 'status',
//                         'type' => 'select',
//                         'class' => 'required-entry',
//                         'label' => Mage::helper('catalog')->__('Status'),
//                         'values' => $statuses
//                     )
//             )
//        ));

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
    

}