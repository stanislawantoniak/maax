<?php

class Zolago_Po_Block_Vendor_Aggregated_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
        parent::__construct();
        $this->setId('zolagopo_aggregated_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
		// Need
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }
	
	protected function _prepareCollection(){
        $collection = Mage::getResourceModel('zolagopo/aggregated_collection');
        /* @var $collection Zolago_Po_Model_Resource_Aggregated_Collection */
		$collection->addVendorFilter(Mage::getSingleton('udropship/session')->getVendor());
		$collection->joinPosNames();
        $this->setCollection($collection);
        return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		$this->addColumn("aggregated_name", array(
			"type"		=>	"text",
			"index"		=>	"aggregated_name",
			"class"		=>  "form-controll",
			"header"	=>	Mage::helper("zolagopo")->__("Name"),
			"width"		=>	"100px"
		));
		$this->addColumn("name", array(
			"type"		=>	"text",
			"index"		=>	"name",
			"class"		=>  "form-controll",
			"header"	=>	Mage::helper("zolagopo")->__("POS"),
			"width"		=>	"100px"
		));
		
		$this->addColumn("created_at", array(
			"type"		=>	"date",
			"index"		=>	"created_at",
			"align"		=>  "center",
			"header"	=>	Mage::helper("zolagopo")->__("Created date"),
			"width"		=>	"100px"
		));
		
		$this->addColumn("download", array(
                'header'    => Mage::helper('zolagopo')->__('Download'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
                'width'     => '50px',
                'type'      => 'action',
				'index'		=> 'aggregated_id',
				'link_action'=> "*/*/download",
				'link_param'=> 'id',
				'link_label'=> 'Download',
				'link_target'=>'_self',
                'filter'    => false,
                'sortable'  => false
        ));
		
		$this->addColumn("remove", array(
                'header'    => Mage::helper('zolagopo')->__('Remove'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_confirmlink"),
                'width'     => '50px',
                'type'      => 'action',
				'index'		=> 'aggregated_id',
				'link_action'=> "*/*/remove",
				'link_param'=> 'id',
				'link_label'=> 'Remove',
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
