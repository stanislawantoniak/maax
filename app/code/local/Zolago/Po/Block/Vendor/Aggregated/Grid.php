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
		$session = Mage::getSingleton('udropship/session');
		/* @var $session Zolago_Dropship_Model_Session */
		if($session->isOperatorMode()){
			$collection->addPosFilter($session->getOperator()->getAllowedPos());
		}else{
			$collection->addPosFilter($session->getVendor()->getAllowedPos());
		}
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
		));
		
		$this->addColumn("name", array(
			"type"		=>	"text",
			"index"		=>	"name",
			"class"		=>  "form-controll",
			"header"	=>	Mage::helper("zolagopo")->__("POS"),
		));
		
		$this->addColumn("status", array(
			"type"		=>	"options",
			"options"	=> array(
				Zolago_Po_Model_Aggregated_Status::STATUS_CONFIRMED => $this->__("Confirmed"),
				Zolago_Po_Model_Aggregated_Status::STATUS_NOT_CONFIRMED => $this->__("Not confirmed")
			),
			"index"		=>	"status",
			"class"		=>  "form-controll",
			"header"	=>	Mage::helper("zolagopo")->__("Status"),
			"width"		=>	"150px"
		));
		
		$this->addColumn("created_at", array(
			"type"		=>	"date",
			"index"		=>	"created_at",
			"align"		=>      "center",
			"header"	=>	Mage::helper("zolagopo")->__("Created date"),
			"width"		=>	"150px"
		));
		
		$this->addColumn("download", array(
                'header'    => Mage::helper('zolagopo')->__('Download report'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
                'width'     => '50px',
                'type'      => 'action',
				'index'		=> 'aggregated_id',
				'link_action'=> "*/*/download",
				'link_param'=> 'id',
				'link_label'=> $this->__('Download report'),
				'link_target'=>'_self',
                'filter'    => false,
                'sortable'  => false
        ));
		
		$this->addColumn("confirm", array(
                'header'    => Mage::helper('zolagopo')->__('Confirm shipment'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagopo/vendor_aggregated_grid_column_renderer_confirmbutton"),
                'width'     => '50px',
                'type'      => 'action',
				'index'		=> 'aggregated_id',
				'icon'		=> 'icon-ok',
				'width'		=> '50px',
				'link_action'=> "*/*/confirm",
				'link_param'=> 'id',
				'link_label'=> $this->__('Confirm shipment'),
                'filter'    => false,
                'sortable'  => false
        ));
		
		$this->addColumn("remove", array(
                'header'    => Mage::helper('zolagopo')->__('Delete'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagopo/vendor_aggregated_grid_column_renderer_deletebutton"),
                'width'     => '50px',
                'type'      => 'action',
				'icon'		=> 'icon-ok',
				'width'		=> '50px',
				'index'		=> 'aggregated_id',
				'tooltip'	=> Mage::helper('zolagopo')->__("If you want to change the contents (shipments) in dispatch list you can delete the dispatch list and make a new one in order list grid."),
				'link_action'=> "*/*/remove",
				'icon'		=> 'icon-remove',
				'link_param'=> 'id',
				'link_label'=> $this->__('Delete'),
                'filter'    => false,
                'sortable'  => false
        ));
		
		return parent::_prepareColumns();
	}
	
	
	public function _addColumnFilterToCollection($column) {		
		if($column->getIndex()=="created_at" && $column->getFilter()->getCondition()){
			$this->getCollection()->addFieldToFilter("main_table.created_at", $column->getFilter()->getCondition());
			return $this;
		}
		return parent::_addColumnFilterToCollection($column) ;
	}
	
	public function getRowUrl($item) {
		return null;
	}
	
}
