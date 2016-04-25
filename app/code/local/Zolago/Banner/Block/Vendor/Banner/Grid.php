<?php

class Zolago_Banner_Block_Vendor_Banner_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
        parent::__construct();
        $this->setId('zolagobanner_banner_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
		// Need
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }

	protected function _prepareCollection(){
		$vendor = Mage::getSingleton('udropship/session')->getVendor();
		/* @var $vendor ZolagoOs_OmniChannel_Model_Vendor */
		$collection = Mage::getResourceModel("zolagobanner/banner_collection");
		$collection->addVendorFilter($vendor);

		$this->setCollection($collection);

        return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		$_helper = Mage::helper("zolagobanner");
		$this->addColumn("name", array(
			"type"		=>	"text",
			"index"		=>	"name",
			"class"		=>  "form-control",
			"header"	=>	$_helper->__("Banner"),
		));
		
		$this->addColumn("type", array(
            "type"		=>	"options",
            "options"	=> Mage::getSingleton('zolagobanner/banner_type')->toOptionHash(),
			"index"		=>	"type",
			"class"		=>  "form-control",
			"header"	=>	$_helper->__("Banner Type"),
		));

		
		$this->addColumn("actions", array(
                'header'    => Mage::helper('zolagopos')->__('Action'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
                'width'     => '50px',
                'type'      => 'action',
				'index'		=> 'banner_id',
				'link_action'=> "*/*/edit",
				'link_param'=> 'id',
				'link_label'=> $_helper->__('Edit'),
				'link_target'=>'_self',
                'filter'    => false,
                'sortable'  => false
        ));

		
		return parent::_prepareColumns();
	}
	
	
	public function getRowUrl($item) {
		return null;
	}
	
}