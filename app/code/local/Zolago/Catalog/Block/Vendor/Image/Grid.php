<?php

class Zolago_Catalog_Block_Vendor_Image_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     // pager visible in footer
     protected $pagerFooter = true;
     public function __construct() {
        parent::__construct();
        $this->setId('catalog_image_grid');
		// Need
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
		$this->setVendorSku(Mage::helper('udropship')->getVendorSkuAttribute());
    }
	
	protected function _prepareCollection(){
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        $vendorId = ($vendor)? ($vendor->getId()):0;
        $collection->addAttributeToFilter("udropship_vendor", $vendorId);
        $collection->addAttributeToFilter("visibility", array('in'=>array(2,3,4)));
        $collection->addAttributeToSelect($this->getVendorSku()->getAttributeCode());
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('gallery_to_check');

        
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
		$this->addColumn($this->getVendorSku()->getAttributeCode(), array(
			"type"		=>	"text",
			"align"		=>  "right",
			"index"		=>	$this->getVendorSku()->getAttributeCode(),
			"class"		=>  "form-controll",
			"header"	=>	Mage::helper("zolagocatalog")->__("Vendor sku"),
			"width"		=>	"40px"
		));
		$this->addColumn("name", array(
			"type"		=>	"text",
			"index"		=>	"name",
			"width"     =>  "100px",
			"header"	=>	Mage::helper("zolagocatalog")->__("Product name"),
		));
		$this->addColumn("gallery_to_check", array(
			"type"		=>	"options",
			'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_options"),
			"align"		=>  "center",
			"index"		=>	"gallery_to_check",
			"class"		=>  "form-controll",
			"header"	=>	Mage::helper("zolagocatalog")->__("Gallery to check"),
			"width"		=>	"20px",
			"style"     => array('0'=>'','1'=>'background-color:#55cc55;color:white'),
			"options"   => array('0'=>Mage::helper('zolagocatalog')->__('No'), '1'=>Mage::helper('zolagocatalog')->__('Yes')),
		));
		$this->addColumn("gallery", array(
                'header'    => Mage::helper('zolagocatalog')->__('Gallery'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_gallery"),
                'type'      => 'gallery',
                'filter'    => false,
                'sortable'  => false
        ));
		return parent::_prepareColumns();
	}
	
	
	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('image');
		$this->getMassactionBlock()->setTemplate("zolagocatalog/widget/grid/massaction.phtml");

		
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
