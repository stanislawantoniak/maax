<?php

class Zolago_Catalog_Block_Vendor_Image_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
        parent::__construct();
        $this->setId('catalog_image_grid');
		// Need
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }
	
	protected function _prepareCollection(){
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        $vendorId = ($vendor)? ($vendor->getId()):0;
        $collection->addAttributeToFilter("udropship_vendor", $vendorId);
        $collection->addAttributeToSelect('skuv');
        $collection->addAttributeToSelect('name');

        
        $this->setCollection($collection);
		
        return parent::_prepareCollection();
	}
	
	
	public function getFilterValueByIndex($index) {
		$param = Mage::app()->getRequest()->getParam($this->getVarNameFilter());
		if($param){
			$param = $this->helper('adminhtml')->prepareFilterString($param);
			if(isset($param[$index])){
				return $param[$index];
			}
		}
		return null;
	}
	public function getFilterValueByColumn($columnId) {
		$index = $this->getColumn($columnId)->getIndex();
		return $this->getFilterValueByIndex($index);
	}
	
	protected function _prepareColumns() {
		$this->addColumn("skuv", array(
			"type"		=>	"text",
			"align"		=>  "right",
			"index"		=>	"skuv",
			"class"		=>  "form-controll",
			"header"	=>	Mage::helper("zolagocatalog")->__("Vendor sku"),
			"width"		=>	"40px"
		));
		$this->addColumn("name", array(
			"type"		=>	"text",
			"index"		=>	"name",
			"header"	=>	Mage::helper("zolagocatalog")->__("Product name"),
		));
		$this->addColumn("gallery_to_check", array(
			"type"		=>	"checkbox",
			"align"		=>  "center",
			"index"		=>	"gallery_to_check",
//			"class"		=>  "form-controll",
			"header"	=>	Mage::helper("zolagocatalog")->__("Gallery to check"),
			"width"		=>	"20px"
		));
		$this->addColumn("gallery", array(
                'header'    => Mage::helper('zolagocatalog')->__('Gallery'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_gallery"),
                'width'     => '500px',
                'type'      => 'gallery',
                'filter'    => false,
                'sortable'  => false
        ));
		return parent::_prepareColumns();
	}
	
	
	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.entity_id');
        $this->getMassactionBlock()->setFormFieldName('image');
		$this->getMassactionBlock()->setTemplate("zolagoadminhtml/widget/grid/massaction.phtml");

		
		$this->getMassactionBlock()->addItem('check_gallery', array(
             'label'=> Mage::helper('zolagocatalog')->__('Check gallery'),
             'url'  => $this->getUrl('*/*/check_gallery')
        ));

        return $this;
    }
	
	protected function _prepareLayout() {
		 $ret = parent::_prepareLayout();
		 $this->unsetChild("reset_filter_button");
		 $this->unsetChild("search_button");
		 return $ret;
	}
	

	public function getVendor() {
		return $this->getParentBlock()->getVendor();
	}
	
	public function getRowUrl($item) {
		return null;
	}
	
}
