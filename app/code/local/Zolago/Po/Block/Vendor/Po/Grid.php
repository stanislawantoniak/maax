<?php

class Zolago_Po_Block_Vendor_Po_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
        parent::__construct();
        $this->setId('zolagopo_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }
	
	protected function _prepareCollection(){
        $collection = Mage::getResourceModel('zolagopo/po_collection');
        /* @var $collection Zolago_Po_Model_Resource_Po_Collection */
		$collection->addOrderData();
		$collection->addProductNames();
		$collection->addHasShipment();
		
		$this->_applayExternalFilters($collection);
		
        $this->setCollection($collection);
		
        return parent::_prepareCollection();
	}
	
	/**
	 * @param Zolago_Po_Model_Resource_Po_Collection $collection
	 * @return Zolago_Po_Block_Vendor_Po_Grid
	 */
	protected function _applayExternalFilters(Zolago_Po_Model_Resource_Po_Collection $collection) {
		
		// Order Date
		if($date=$this->getFilterValueByIndex("created_at")){
			$this->_applayDateFilter($collection, "main_table.created_at", $date);
		}
		
		// Max shipment date
		//if($date=$this->getFilterValueByIndex("max_shipment_date")){
		//	$this->_applayDateFilter($collection, "main_table.max_shipment_date", $date);
		//}
		
		// Max shipment date
		if($date=$this->getFilterValueByIndex("shipment_date")){
			$this->_applayDateFilter($collection, "shipment.created_at", $date);
		}
		
		// Pos
		if($pos=$this->getFilterValueByIndex("default_pos_id")){
			$collection->addFieldToFilter("main_table.default_pos_id", $pos);
		}
			
		// Status
		$statuses = $this->getFilterValueByColumn("udropship_status");
		if(is_null($statuses)){
			$statuses=$this->getParentBlock()->getDefaultStatuses();
		}
		if($statuses){
			$collection->addAttributeToFilter("main_table.udropship_status", array("in"=>$statuses));
		}
		return $this;
	}
	
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
		$this->addColumn("max_order_date", array(
			"type"		=>	"date",
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
		$this->addColumn("product_names", array(
			"type"		=>	"text",
			"width"		=>	"400px",
			"index"		=>	"product_names",
			"header"	=>	Mage::helper("zolagopo")->__("Products"),
			"renderer"	=>	Mage::getConfig()->
				getBlockClassName("zolagopo/vendor_po_grid_column_renderer_products"),
			"sortable"	=> false
		));
		$this->addColumn("total_value", array(
            'index'		=> 'total_value',
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
			"type"		=>	"options",
			"options"	=> array(
				0=>Mage::helper("zolagopo")->__("Not Payed"), 
				1=>Mage::helper("zolagopo")->__("Payed")
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
			"header"	=>	Mage::helper("zolagopo")->__("Shipping letter"),
			"index"		=> "has_shipment",
			"align"		=> "center",
			"options"	=>	Mage::getSingleton("adminhtml/system_config_source_yesno")->toArray(),
			"width"		=> "50px"
		));
		
		$this->addColumn("aggregated", array(
			"type"		=>	"text",
			"header"	=>	Mage::helper("zolagopo")->__("Aggregated"),
			"width"		=> "50px"
		));
		
		$this->addColumn("alert", array(
			"type"		=>	"options",
			"header"	=>	Mage::helper("zolagopo")->__("Alert"),
			"width"		=> "150px",
			"options"	=> array(
				0=>Mage::helper("zolagopo")->__("Alert 1"), 
				1=>Mage::helper("zolagopo")->__("Alert 2"),
				1=>Mage::helper("zolagopo")->__("Alert 3")
			)
		));
		
		$this->addColumn("actions", array(
                'header'    => Mage::helper('zolagopo')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('zolagopo')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit'
                        ),
                        'field'   => 'id'
                    )
                ),
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
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('po');
		$statuses=array();
		
		foreach(Mage::helper('udpo')->getVendorUdpoStatuses() as $key=>$label){
			$statuses[$key]=$label;
		}
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('catalog')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('catalog')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

		
		$this->getMassactionBlock()->addItem('shipping_letters', array(
             'label'=> Mage::helper('zolagopo')->__('Generate shipping letters'),
             'url'  => $this->getUrl('*/*/shipping_letters')
        ));
        $this->getMassactionBlock()->addItem('print_aggregated', array(
             'label'=> Mage::helper('zolagopo')->__('Print aggregated'),
             'url'  => $this->getUrl('*/*/print_aggregated')
        ));
        $this->getMassactionBlock()->addItem('confirm_shipment', array(
             'label'=> Mage::helper('zolagopo')->__('Confirm shipment'),
             'url'  => $this->getUrl('*/*/confirm_shipment')
        ));
        $this->getMassactionBlock()->addItem('confirm_backorder', array(
             'label'=> Mage::helper('zolagopo')->__('Confirm bacorder'),
             'url'  => $this->getUrl('*/*/confirm_backorder')
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
		}
		return parent::_addColumnFilterToCollection($column);
	}


	public function getVendor() {
		return $this->getParentBlock()->getVendor();
	}
	
	public function getRowUrl($item) {
		return null;
	}
}
