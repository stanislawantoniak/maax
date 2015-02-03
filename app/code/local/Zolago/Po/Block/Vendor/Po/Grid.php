<?php

class Zolago_Po_Block_Vendor_Po_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
        parent::__construct();
        $this->setId('zolagopo_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
		// Need
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }
	
	protected function _prepareCollection(){
        $collection = Mage::getResourceModel('zolagopo/po_collection');
        /* @var $collection Zolago_Po_Model_Resource_Po_Collection */
		
		$collection->addOrderData();
		$collection->addProductNames();
		$collection->addHasShipment();
		$collection->joinAggregatedNames();
		$collection->addPaymentStatuses();
		$this->_applyExternalFilters($collection);
        $this->setCollection($collection);

       parent::_prepareCollection();

	}
	
	/**
	 * @param Zolago_Po_Model_Resource_Po_Collection $collection
	 * @return Zolago_Po_Block_Vendor_Po_Grid
	 */
	protected function _applyExternalFilters(Zolago_Po_Model_Resource_Po_Collection $collection) {
		
		// Order Date
		if($date=$this->getFilterValueByIndex("created_at")){
			$this->_applayDateFilter($collection, "main_table.created_at", $date);
		}
		
		// Max shipment date
		if($date=$this->getFilterValueByIndex("max_shipment_date")){
			$this->_applayDateFilter($collection, "main_table.max_shipping_date", $date);
		}
		
		// Max shipment date
		if($date=$this->getFilterValueByIndex("shipment_date")){
			$this->_applayDateFilter($collection, "shipment.created_at", $date);
		}

		// Pos
		if(($pos=$this->getFilterValueByIndex("default_pos_id")) && 
			in_array($pos, $this->_getAllowedPosIds())){
			// specified and validated
			$collection->addFieldToFilter("main_table.default_pos_id", $pos);
		}else{
			// All allowed
			$collection->addFieldToFilter("main_table.default_pos_id", 
				array("in"=>$this->_getAllowedPosIds()));
		}
			
		// Status
		$statuses = $this->getFilterValueByColumn("udropship_status");
		if(is_null($statuses)){
			$statuses=$this->getParentBlock()->getDefaultStatuses();
		}
		if($statuses){
			$collection->addAttributeToFilter("main_table.udropship_status", array("in"=>$statuses));
		}

		//payment status
		$paymentStatus=$this->getFilterValueByIndex("payment_status");
		if(!is_null($paymentStatus)) {
			$collection->getSelect()->having("`payment_status` = ?",$paymentStatus);
		}

		return $this;
	}
	
	/**
	 * @return array
	 */
	protected function _getAllowedPosIds() {
		if(!$this->hasData("allowed_pos_ids")){
			$this->setData("allowed_pos_ids", $this->getParentBlock()->getPosCollection()->getAllIds());
		}
		return $this->getData("allowed_pos_ids") ? $this->getData("allowed_pos_ids") : array();
	}
	
	/**
	 * 
	 * @param Zolago_Po_Model_Resource_Po_Collection $collection
	 * @param string $index
	 * @param boolean $date
	 */
	protected function _applayDateFilter(Zolago_Po_Model_Resource_Po_Collection $collection, $index, $date) {
		if(is_array($date)){
			$date['date']=true;
			$collection->addFieldToFilter($index, $date);
		}
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
		$this->addColumn("increment_id", array(
			"type"		=>	"text",
			"align"		=>  "right",
			"index"		=>	"increment_id",
			"class"		=>  "form-controll",
			"header"	=>	Mage::helper("zolagopo")->__("Order No."),
			"width"		=>	"100px"
		));
		$this->addColumn("created_at", array(
			"type"		=>	"date",
			"index"		=>	"created_at",
			"align"		=>  "center",
			"header"	=>	Mage::helper("zolagopo")->__("Order date"),
			"filter"	=>	false,
			"width"		=>	"100px"
		));
		$this->addColumn("max_shipping_date", array(
			"type"		=>	"date",
			'index'     =>  "max_shipping_date",
			"align"		=>  "center",
			"header"	=>	Mage::helper("zolagopo")->__("Max ship. date"),
			"filter"	=>	false,
			"width"		=>	"100px"
		));
		$this->addColumn("customer_fullname", array(
			"type"		=>	"text",
			"index"		=>	"customer_fullname",
			"header"	=>	Mage::helper("zolagopo")->__("Customer"),
		));
		$this->addColumn("order_items", array(
			"type"		=>	"text",
			"width"		=>	"400px",
			"index"		=>	"order_items",
			"header"	=>	Mage::helper("zolagopo")->__("Products"),
			"renderer"	=>	Mage::getConfig()->
				getBlockClassName("zolagopo/vendor_po_grid_column_renderer_products"),
			"sortable"	=> false
		));
		$this->addColumn("grand_total_incl_tax", array(
            'index'		=> 'grand_total_incl_tax',
            'type'		=> 'price',
			'align'		=> 'right',
            'currency'	=> 'base_currency_code',
			"header"	=>	Mage::helper("zolagopo")->__("Total"),
			"filter"	=>	false,
			"width"		=> "100px"
		));
		$this->addColumn("udropship_status", array(
			"type"		=>	"options",
			"index"		=>	"udropship_status",
			"header"	=>	Mage::helper("zolagopo")->__("Status"),
			"options"	=>	Mage::getSingleton('udpo/source')->setPath('po_statuses')->toOptionHash(),
			"filter"	=>	false,
			"width"		=>	"100px"
		));
		
		$this->addColumn("payment_status", array(
			"header"	=>	Mage::helper("zolagopo")->__("Payment status"),
			"index"     => "payment_status",
			"type"		=>	"options",
			"options"	=> array(
				0=>Mage::helper("zolagopo")->__("Not Paid"), 
				1=>Mage::helper("zolagopo")->__("Paid")
			)
		));
		
		$this->addColumn("udropship_method", array(
			"type"		=>	"options",
			"index"		=>	"udropship_method",
			"header"	=>	Mage::helper("zolagopo")->__("Shipping method"),
			"options"	=>	$this->_getShippingMethodOptions()
		));
		
		$this->addColumn("has_shipment", array(
			"type"		=>	"options",
			"header"	=>	Mage::helper("zolagopo")->__("Shipping label"),
			"index"		=> "has_shipment",
			"align"		=> "center",
			"options"	=>	Mage::getSingleton("adminhtml/system_config_source_yesno")->toArray(),
			"width"		=> "50px"
		));
		
		$this->addColumn("aggregated", array(
			"type"		=>	"text",
			"index"		=> "aggregated_name",
			"header"	=>	Mage::helper("zolagopo")->__("Dispatch ref."),
			"width"		=> "50px"
		));
		
		$this->addColumn("alert", array(
			"type"		=> "options",
			"options"   => Zolago_Po_Model_Po_Alert::getAllOptions(),
			"header"	=>	Mage::helper("zolagopo")->__("Alert"),
			"width"		=> "150px",
			"index"		=> "alert",
			"renderer"	=>	Mage::getConfig()->
				getBlockClassName("zolagopo/vendor_po_grid_column_renderer_alert"),
		));
		
		$this->addColumn("actions", array(
                'header'    => Mage::helper('zolagopo')->__('Action'),
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
	
	protected function _getShippingMethodOptions() {
		$out = array();
		$config = Mage::getSingleton('shipping/config');
		/* @var $config Mage_Shipping_Model_Config */
		foreach($this->getVendor()->getShippingMethods() as $_array){
			foreach($_array as $method){
				if(isset($method["method_code"]) && isset($method["carrier_code"])){
					$carrier =  $config->getCarrierInstance($method["carrier_code"]);
					$allMethods = $carrier->getAllowedMethods();
					if(isset($allMethods[$method["method_code"]])){
						$out[ $method["carrier_code"]."_".$method["method_code"]] = 
							$carrier->getConfigData('name') . " - " . $allMethods[$method["method_code"]];
					}
				}
			}
		}
		return $out;
	}
	
	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.entity_id');
        $this->getMassactionBlock()->setFormFieldName('po');
		$this->getMassactionBlock()->setTemplate("zolagoadminhtml/widget/grid/massaction.phtml");
		
        $this->getMassactionBlock()->addItem('start_packing', array(
             'label'=> Mage::helper('zolagopo')->__('Start packing'),
             'url'  => $this->getUrl('*/*/massStartPacking')
        ));
        $this->getMassactionBlock()->addItem('print_aggregated', array(
             'label'=> Mage::helper('zolagopo')->__('Make dispatch list'),
             'url'  => $this->getUrl('*/*/massPrintAggregated')
        ));
		
        $this->getMassactionBlock()->addItem('confirm_stock', array(
             'label'=> Mage::helper('zolagopo')->__('Confirm reservation'),
             'url'  => $this->getUrl('*/*/massConfirmStock')
        ));
		
        $this->getMassactionBlock()->addItem('direct_relasiation', array(
             'label'=> Mage::helper('zolagopo')->__('Move to fulfilment'),
             'url'  => $this->getUrl('*/*/massDirectRealisation')
        ));

        return $this;
    }
	
	protected function _prepareLayout() {
		 $ret = parent::_prepareLayout();
		 $this->unsetChild("reset_filter_button");
		 $this->unsetChild("search_button");
		 return $ret;
	}
	
	/**
	 * Custom filter
	 * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
	 * @return Zolago_Po_Block_Vendor_Po_Grid
	 */
	protected function _addColumnFilterToCollection($column) {
		switch ($column->getId()) {
			case "customer_fullname":
				$this->getCollection()->addCustomerNameFilter(
					$column->getFilter()->getValue());
				return $this;
			break;
			case "product_names":
				$this->getCollection()->addProductNameFilter(
					$column->getFilter()->getValue());
				return $this;
			break;
			case "has_shipment":
				$this->getCollection()->addHasShipmentFilter(
					$column->getFilter()->getValue());
				return $this;
			break;
			case "alert":
				$this->getCollection()->addAlertFilter(
					$column->getFilter()->getValue());
				return $this;
			break;
			case "increment_id":
			case "entity_id":
			case "udropship_method":
				$this->getCollection()->addFieldToFilter(
						"main_table.{$column->getId()}", 
						array("like"=>"%".$column->getFilter()->getValue()."%")
				);
				return $this;
			break;
		}
		if($column->getId() != 'payment_status') {
			return parent::_addColumnFilterToCollection($column);
		}
	}


	public function getVendor() {
		return $this->getParentBlock()->getVendor();
	}
	
	public function getRowUrl($item) {
		return null;
	}
	
}
