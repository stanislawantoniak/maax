<?php

class Zolago_Sizetable_Block_Dropship_Sizetable_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
        parent::__construct();
        $this->setId('zolagosizetable_sizetable_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
		// Need
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }
	
	protected function _prepareCollection(){
		$vendor = Mage::getSingleton('udropship/session')->getVendor();
		/* @var $vendor Unirgy_Dropship_Model_Vendor */
		$collection = Mage::getResourceModel("zolagosizetable/sizetable_collection");
		/* @var $collection Zolago_Sizetable_Model_Resource_Sizetable_Collection */
		$collection->addVendorFilter($vendor);
		$collection->setOrder("name", "ASC");
        $this->setCollection($collection);
        return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		$_helper = Mage::helper("zolagosizetable");
		
		$this->addColumn("name", array(
			"type"		=>	"text",
			"index"		=>	"name",
			"class"		=>  "form-control",
			"header"	=>	$_helper->__("Size table name"),
		));
		$this->addColumn("actions", array(
                'header'    => $_helper->__('Actions'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
                'width'     => '50px',
                'type'      => 'action',
				'index'		=> 'sizetable_id',
				'link_action'=> "*/*/edit",
				'link_param'=> 'sizetable_id',
				'link_label'=> 'Edit',
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