<?php

class Zolago_Catalog_Block_Vendor_Image_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     // pager visible in footer
     protected $_isblockType = false;
     protected $pagerFooter = true;
    protected $_massactionBlockName = 'zolagocatalog/widget_grid_massaction';

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
		$collection->addAttributeToFilter("visibility", array('nin' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
		$collection->addAttributeToSelect($this->getVendorSku()->getAttributeCode());
        $collection->addAttributeToSelect('name');
		//$collection->addAttributeToSelect('attribute_set');
		$collection->addAttributeToSelect('description_status');
        $collection->addAttributeToSelect('gallery_to_check');
		$collection->setFlag("skip_price_data",true);

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

	public function getAttributeSets() {
		$vendor = Mage::getSingleton('udropship/session')->getVendor();
		$array = Mage::getResourceSingleton('zolagocatalog/vendor_mass')
			->getAttributeSetsForVendor($vendor);
		return $array;
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

		$attributeSets = $this->getAttributeSets();
		$this->addColumn("attribute_set_id", array(
			"type"		=>	"options",
			"index"		=>	"attribute_set_id",
			"width"     =>  "100px",
			"header"	=>	Mage::helper("zolagocatalog")->__("Attribute set"),
			"options"   => $attributeSets,
		));
		$this->addColumn("description_status", array(
			"type"		=>	"options",
			"index"		=>	"description_status",
			"width"     =>  "100px",
			"header"	=>	Mage::helper("zolagocatalog")->__("Description status"),
			"options"	=>	$this->_getAttributeOptions('description_status'),
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

	protected function _getAttributeOptions($attribute_code)
	{
		$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute_code);
		$options = array();
		foreach ($attribute->getSource()->getAllOptions(false, true) as $option) {
			$options[$option['value']] = $option['label'];
		}
		return $options;
	}

	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('image');
		$this->getMassactionBlock()->setTemplate("zolagocatalog/widget/grid/vendor_image/massaction.phtml");

		
		$this->getMassactionBlock()->addItem('check_gallery', array(
             'label'=> Mage::helper('zolagocatalog')->__('Check gallery'),
             'url'  => $this->getUrl('*/*/check_gallery')
        ));

        return $this;
    }
	
	

	public function getVendor() {
		return $this->getParentBlock()->getVendor();
	}
	
	public function getRowUrl($item) {
		return null;
	}
    public function isBlockType(){
        return $this->_isblockType;
    }

}
