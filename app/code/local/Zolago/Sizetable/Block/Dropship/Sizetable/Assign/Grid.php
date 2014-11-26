<?php

class Zolago_Sizetable_Block_Dropship_Sizetable_Assign_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
        parent::__construct();
        $this->setId('zolagosizetable_sizetable_assign_grid');
        $this->setDefaultSort('sizetable_name');
        $this->setDefaultDir('ASC');
		// Need
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }
	
	protected function _prepareCollection() {
		/** @var Zolago_Sizetable_Model_Resource_Sizetable_Rule_Collection $collection */
		$collection = Mage::getModel("zolagosizetable/sizetable_rule")->getCollection();

		$collection
			->addVendorFilter($this->getVendorId())
			->joinSizetables()
			->joinSizetableBrands()
			->joinSizetableAttributes()
			->getSelect()
			->group("main_table.rule_id");
		$collection->setOrder("sizetable_name", "ASC");

        $this->setCollection($collection);
        return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		$_helper = Mage::helper("zolagosizetable");
		
		$this->addColumn("sizetable_name", array(
			"type"		=>	"text",
			"index"		=>	"sizetable_name",
			"class"		=>  "form-control",
			"header"	=>	$_helper->__("Size table name"),
			"column_css_class" => "sizetable_name"
		));
		$this->addColumn("brand_name", array(
			"type"		=>	"text",
			"index"		=>	"brand_name",
			"class"		=>  "form-control",
			"header"	=>	$_helper->__("Brand"),
			"column_css_class" => "sizetable_brand"
		));
		$this->addColumn("attribute_set_name", array(
			"type"		=>	"text",
			"index"		=>	"attribute_set_name",
			"class"		=>  "form-control",
			"header"	=>	$_helper->__("Attribute set"),
			"column_css_class" => "sizetable_attribute"
		));
		$this->addColumn("actions", array(
                'header'    => $_helper->__('Actions'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
                'width'     => '50px',
                'type'      => 'action',
				'index'		=> 'rule_id',
				'link_action'=> "*/*/delete",
				'link_param'=> 'rule_id',
				'link_label'=> $_helper->__('Delete'),
				'link_target'=>'_self',
				'link_class' => 'delete confirm-dialog',
                'filter'    => false,
                'sortable'  => false,
				'attributes' => array(
					'data-rule_id' => 'id'
				),
				'column_css_class' => 'sizetable_assign_actions'
        ));
		
		
		return parent::_prepareColumns();
	}
	
	
	public function getRowUrl($item) {
		return null;
	}

	public function getVendorId() {
		return Mage::getSingleton('udropship/session')->getVendor()->getVendorId();
	}
}