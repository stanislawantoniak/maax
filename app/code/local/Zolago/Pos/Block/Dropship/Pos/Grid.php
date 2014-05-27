<?php

class Zolago_Pos_Block_Dropship_Pos_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
        parent::__construct();
        $this->setId('zolagopos_pos_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
		// Need
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }
	
	protected function _prepareCollection(){
		$vendor = Mage::getSingleton('udropship/session')->getVendor();
		/* @var $vendor Unirgy_Dropship_Model_Vendor */
		$collection = Mage::getResourceModel("zolagopos/pos_collection");
		/* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
		$collection->addVendorFilter($vendor);
		$collection->setOrder("priority", "DESC");
        $this->setCollection($collection);
        return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		$_helper = Mage::helper("zolagopos");
		
		$this->addColumn("name", array(
			"type"		=>	"text",
			"index"		=>	"name",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("POS Name"),
		));
		
		$this->addColumn("client_number", array(
			"type"		=>	"text",
			"index"		=>	"client_number",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Client number"),
		));
		
		$this->addColumn("phone", array(
			"type"		=>	"text",
			"index"		=>	"phone",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Phone"),
		));
		
		$this->addColumn("external_id", array(
			"type"		=>	"text",
			"index"		=>	"external_id",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("External ID"),
		));
		
		$this->addColumn("priority", array(
			"type"		=>	"number",
			"index"		=>	"priority",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Priority"),
		));
		
		$this->addColumn("minimal_stock", array(
			"type"		=>	"number",
			"index"		=>	"minimal_stock",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Minimal stock"),
		));
		
		$this->addColumn("is_active", array(
			"type"		=>	"options",
			"options"	=> array(
				Zolago_Pos_Model_Pos::STATUS_ACTIVE => $this->__("Active"),
				Zolago_Pos_Model_Pos::STATUS_INACTIVE => $this->__("Not Active")
			),
			"index"		=>	"is_active",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Is active"),
		));
		
		$this->addColumn("actions", array(
                'header'    => Mage::helper('zolagopos')->__('Action'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
                'width'     => '50px',
                'type'      => 'action',
				'index'		=> 'pos_id',
				'link_action'=> "*/*/edit",
				'link_param'=> 'id',
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