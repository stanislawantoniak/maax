<?php

class Zolago_Rma_Block_Vendor_Rma_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
        parent::__construct();
        $this->setId('zolagorma_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
		// Need
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }
	
	protected function _prepareCollection(){
        $collection = Mage::getResourceModel('urma/rma_collection');
        /* @var $collection Unirgy_Rma_Model_Mysql4_Rma_Collection */
		$collection->addFieldToFilter("udropship_vendor", Mage::getSingleton('udropship/session')->getVendorId());
		
		// $this->_applayExternalFilters($collection);
		
        $this->setCollection($collection);
		
        return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		
		$this->addColumn("increment_id", array(
			"type"		=>	"text",
			"align"		=>  "right",
			"index"		=>	"increment_id",
			"class"		=>  "form-controll",
			"header"	=>	Mage::helper("zolagorma")->__("RMA No."),
			"width"		=>	"100px"
		));
		
		$this->addColumn("created_at", array(
			"type"		=>	"date",
			"index"		=>	"created_at",
			"align"		=>  "center",
			"header"	=>	Mage::helper("zolagorma")->__("RMA Date"),
			"filter"	=>	false,
			"width"		=>	"100px"
		));
		
		$this->addColumn("rma_status", array(
			"type"		=>	"options",
			"index"		=>	"rma_status",
			"header"	=>	Mage::helper("zolagorma")->__("Status"),
			"options"	=>	Mage::helper('zolagorma')->getVendorRmaStatuses(),
			"filter"	=>	false,
			"width"		=>	"100px"
		));
		
		
		$this->addColumn("actions", array(
                'header'    => Mage::helper('zolagorma')->__('Action'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
                'width'     => '50px',
                'type'      => 'action',
				'index'		=> 'entity_id',
				'link_action'=> "*/*/edit",
				'link_param'=> 'id',
				'link_label'=> 'Edit',
				'link_target'=>'_self',
                'filter'    => false,
                'sortable'  => false
        ));
		
		return parent::_prepareColumns();
	}
	
	protected function _prepareLayout() {
		 $ret = parent::_prepareLayout();
		 $this->unsetChild("reset_filter_button");
		 $this->unsetChild("search_button");
		 return $ret;
	}
	
//	/**
//	 * @param Zolago_Po_Model_Resource_Po_Collection $collection
//	 * @return Zolago_Po_Block_Vendor_Po_Grid
//	 */
//	protected function _applayExternalFilters(Zolago_Po_Model_Resource_Po_Collection $collection) {
//		
//		// Order Date
//		if($date=$this->getFilterValueByIndex("created_at")){
//			$this->_applayDateFilter($collection, "main_table.created_at", $date);
//		}
//		
//		// Max shipment date
//		//if($date=$this->getFilterValueByIndex("max_shipment_date")){
//		//	$this->_applayDateFilter($collection, "main_table.max_shipment_date", $date);
//		//}
//		
//		// Max shipment date
//		if($date=$this->getFilterValueByIndex("shipment_date")){
//			$this->_applayDateFilter($collection, "shipment.created_at", $date);
//		}
//		
//		// Pos
//		if($pos=$this->getFilterValueByIndex("default_pos_id")){
//			$collection->addFieldToFilter("main_table.default_pos_id", $pos);
//		}
//			
//		// Status
//		$statuses = $this->getFilterValueByColumn("udropship_status");
//		if(is_null($statuses)){
//			$statuses=$this->getParentBlock()->getDefaultStatuses();
//		}
//		if($statuses){
//			$collection->addAttributeToFilter("main_table.udropship_status", array("in"=>$statuses));
//		}
//		return $this;
//	}
//	
//	protected function _applayDateFilter(Zolago_Po_Model_Resource_Po_Collection $collection, $index, $date) {
//		if(is_array($date)){
//			$date['date']=true;
//			$collection->addFieldToFilter($index, $date);
//		}
//	}
//	
//	public function getFilterValueByIndex($index) {
//		$param = Mage::app()->getRequest()->getParam($this->getVarNameFilter());
//		if($param){
//			$param = $this->helper('adminhtml')->prepareFilterString($param);
//			if(isset($param[$index])){
//				return $param[$index];
//			}
//		}
//		return null;
//	}
//	public function getFilterValueByColumn($columnId) {
//		$index = $this->getColumn($columnId)->getIndex();
//		return $this->getFilterValueByIndex($index);
//	}
	
	
	
//	protected function _getShippingMethodOptions() {
//		$out = array();
//		$config = Mage::getSingleton('shipping/config');
//		/* @var $config Mage_Shipping_Model_Config */
//		foreach($this->getVendor()->getShippingMethods() as $_array){
//			foreach($_array as $method){
//				if(isset($method["method_code"]) && isset($method["carrier_code"])){
//					$carrier =  $config->getCarrierInstance($method["carrier_code"]);
//					$allMethods = $carrier->getAllowedMethods();
//					if(isset($allMethods[$method["method_code"]])){
//						$out[ $method["carrier_code"]."_".$method["method_code"]] = 
//							$carrier->getConfigData('name') . " - " . $allMethods[$method["method_code"]];
//					}
//				}
//			}
//		}
//		return $out;
//	}
//	
//	protected function _prepareMassaction()
//    {
//        $this->setMassactionIdField('main_table.entity_id');
//        $this->getMassactionBlock()->setFormFieldName('po');
//		$this->getMassactionBlock()->setTemplate("zolagoadminhtml/widget/grid/massaction.phtml");
//		
//        $this->getMassactionBlock()->addItem('start_packing', array(
//             'label'=> Mage::helper('zolagorma')->__('Start packing'),
//             'url'  => $this->getUrl('*/*/massStartPacking')
//        ));
//        $this->getMassactionBlock()->addItem('print_aggregated', array(
//             'label'=> Mage::helper('zolagorma')->__('Make dispatch list'),
//             'url'  => $this->getUrl('*/*/massPrintAggregated')
//        ));
//		
//        $this->getMassactionBlock()->addItem('confirm_stock', array(
//             'label'=> Mage::helper('zolagorma')->__('Check stock'),
//             'url'  => $this->getUrl('*/*/massConfirmStock')
//        ));
//		
//        $this->getMassactionBlock()->addItem('direct_relasiation', array(
//             'label'=> Mage::helper('zolagorma')->__('Move to fulfilment'),
//             'url'  => $this->getUrl('*/*/massDirectRealisation')
//        ));
//
//        return $this;
//    }
	

	
//	/**
//	 * Custom filter
//	 * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
//	 * @return Zolago_Po_Block_Vendor_Po_Grid
//	 */
//	protected function _addColumnFilterToCollection($column) {
//		switch ($column->getId()) {
//			case "customer_fullname":
//				$this->getCollection()->addCustomerNameFilter(
//					$column->getFilter()->getValue());
//				return $this;
//			break;
//			case "product_names":
//				$this->getCollection()->addProductNameFilter(
//					$column->getFilter()->getValue());
//				return $this;
//			break;
//			case "has_shipment":
//				$this->getCollection()->addHasShipmentFilter(
//					$column->getFilter()->getValue());
//				return $this;
//			break;
//			case "alert":
//				$this->getCollection()->addAlertFilter(
//					$column->getFilter()->getValue());
//				return $this;
//			break;
//			case "increment_id":
//			case "entity_id":
//			case "udropship_method":
//				$this->getCollection()->addFieldToFilter(
//						"main_table.{$column->getId()}", 
//						array("like"=>"%".$column->getFilter()->getValue()."%")
//				);
//				return $this;
//			break;
//		}
//		return parent::_addColumnFilterToCollection($column);
//	}


	public function getVendor() {
		return $this->getParentBlock()->getVendor();
	}
	
	public function getRowUrl($item) {
		return null;
	}
	
}
