<?php

class Zolago_Operator_Block_Dropship_Operator_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
        parent::__construct();
        $this->setId('zolagooperator_operator_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
		// Need
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }
	
	protected function _prepareCollection(){
		$vendor = Mage::getSingleton('udropship/session')->getVendor();
		/* @var $vendor ZolagoOs_OmniChannel_Model_Vendor */
		$collection = Mage::getResourceModel("zolagooperator/operator_collection");
		$collection->addVendorFilter($vendor);
		$this->setCollection($collection);
        return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		$_helper = Mage::helper("zolagooperator");
		$this->addColumn("email", array(
			"type"		=>	"text",
			"index"		=>	"email",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Operator login"),
		));
		
		$this->addColumn("firstname", array(
			"type"		=>	"text",
			"index"		=>	"firstname",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("First name"),
		));
		
		$this->addColumn("lastname", array(
			"type"		=>	"text",
			"index"		=>	"lastname",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Last name"),
		));
		
		$this->addColumn("phone", array(
			"type"		=>	"text",
			"index"		=>	"phone",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Phone"),
		));
		
		$this->addColumn("is_active", array(
			"type"		=>	"options",
			"options"	=> array(
				Zolago_Operator_Model_Operator::STATUS_ACTIVE => $this->__("Active"),
				Zolago_Operator_Model_Operator::STATUS_INACTIVE => $this->__("Not Active")
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
				'index'		=> 'operator_id',
				'link_action'=> "*/*/edit",
				'link_param'=> 'operator_id',
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