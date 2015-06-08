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
	
	protected function _getFilterVendors() {
		$vendor = Mage::getSingleton('udropship/session')->getVendor();
		/* @var $vendor Zolago_Dropship_Model_Vendor */
		$vendorsIds = $vendor->getChildVendorIds();
		$vendorsIds[] = $vendor->getId();
		$collection = Mage::getModel('udropship/vendor')->getCollection()
		    -> addFieldToFilter('vendor_id',array('in',$vendorsIds),'vendor_name');
        $array = array();
        foreach ($collection as $item) {
            $array[$item['vendor_id']] = $item['vendor_name'];
        }
        return $array;
		    
	}
	protected function _prepareCollection(){
        $collection = Mage::getResourceModel('zolagorma/rma_collection');
        /* @var $collection Zolago_Rma_Model_Resource_Rma_Collection */
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        /* @var $vendor Zolago_Dropship_Model_Vendor */
        $vendorsIds = $vendor->getChildVendorIds();
        $vendorsIds[] = $vendor->getId();

        $collection->addFieldToFilter("udropship_vendor",
            array("in"=>  array_unique($vendorsIds)));
		
		$collection->addCustomerNames();
		$collection->addItemsData();
		
		$this->_applayExternalFilters($collection);
		
        $this->setCollection($collection);
		
        return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		
		$this->addColumn("increment_id", array(
			"type"		=>	"text",
			"align"		=>	"right",
			"index"		=>	"increment_id",
			"class"		=>	"form-controll",
			"header"	=>	Mage::helper("zolagorma")->__("RMA No."),
			"width"		=>	"100px"
		));

		$this->addColumn("udpo_increment_id", array(
			"type"		=>	"text",
			"align"		=>	"left",
			"index"		=>	"udpo_increment_id",
			"class"		=>	"form-controll",
			"header"	=>	Mage::helper("zolagorma")->__("Order No."),
			"width"		=>	"100px"
		));
		$this->addColumn("created_at", array(
			"type"		=>	"date",
			"index"		=>	"created_at",
			"align"		=>	"center",
			"header"	=>	Mage::helper("zolagorma")->__("RMA Date"),
			"filter"	=>	false,
			"width"		=>	"100px"
		));
		
		$this->addColumn("date_max", array(
			"type"		=>	"date",
			"index"		=>	"response_deadline",
			"header"	=>	Mage::helper("zolagorma")->__("Response deadline"),
			"filter"	=>	false,
			"width"		=>	"100px"
		));
		
		$this->addColumn("udropship_vendor", array(
			"type"		=>	"options",
			"options"	=>  $this->_getFilterVendors(),
			"align"		=>  "right",
			"index"		=>	"udropship_vendor",
			"class"		=>	"form-controll",
			"header"	=>	Mage::helper("zolagorma")->__("Vendor"),
			"width"		=>	"100px"
		));
		
		$this->addColumn("customer_fullname", array(
			"type"		=>	"text",
			"align"		=>	"right",
			"index"		=>	"customer_fullname",
			"class"		=>	"form-controll",
			"header"	=>	Mage::helper("zolagorma")->__("Customer"),
		));
		
		// Mage::getSingleton('urma/source')->setPath('rma_item_condition')->toOptionHash(),
		
		$this->addColumn("rma_items", array(
			"type"		=>	"text",
			"align"		=>	"right",
			"index"		=>	"rma_items",
			"renderer"	=>	Mage::getConfig()->
				getBlockClassName("zolagorma/vendor_rma_grid_column_renderer_products"),
			"sortable"	=>	false,
			"class"		=>	"form-controll",
			"header"	=>	Mage::helper("zolagorma")->__("Products"),
		));
		
		$this->addColumn("rma_status", array(
			"type"		=>	"options",
			"index"		=>	"rma_status",
			"class"		=>	"form-controll",
			"options"	=>	Mage::getSingleton('urma/source')->setPath('rma_status')->toOptionHash(),
			"header"	=>	Mage::helper("zolagorma")->__("Status"),
			"filter"	=>	false,
			"width"		=>	"100px"
		));
		
		$this->addColumn("new_customer_question", array(
			"type"		=>	"options",
			"index"		=>	"new_customer_question",
			"class"		=>	"form-controll",
			"options"	=>	Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
			"header"	=>	Mage::helper("zolagorma")->__("New customer question"),
			"width"		=>	"100px"
		));
		
		
		$this->addColumn("actions", array(
                'header'		=>	Mage::helper('zolagorma')->__('Action'),
				'renderer'		=>	Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
                'width'			=>	'50px',
                'type'			=>	'action',
				'index'			=>	'entity_id',
				'link_action'	=>	"*/*/edit",
				'link_param'	=>	'id',
				'link_label'	=>	Mage::helper('zolagorma')->__('Edit'),
				'link_target'	=>	'_self',
                'filter'		=>	false,
                'sortable'		=>	false
        ));
		
		return parent::_prepareColumns();
	}

	public function getRowUrl($row) {
		return $this->getUrl('*/*/edit', array(
				'id'=>$row->getId()
			)
		);
	}
	
	protected function _prepareLayout() {
		 $ret = parent::_prepareLayout();
		 $this->unsetChild("reset_filter_button");
		 $this->unsetChild("search_button");
		 return $ret;
	}
	
	/**
	 * @param Zolago_Rma_Model_Resource_Rma_Collection $collection
	 * @return Zolago_Rma_Block_Vendor_Rma_Grid
	 */
	protected function _applayExternalFilters(Zolago_Rma_Model_Resource_Rma_Collection $collection) {
		
		// Order Date
		if($date=$this->getFilterValueByIndex("created_at")){
			$this->_applayDateFilter($collection, "main_table.created_at", $date);
		}

        // Response Deadline
        if($max_date_exceed_array=$this->getFilterValueByIndex("max_date_exceed")){

            if(sizeof($max_date_exceed_array) === 1){

                $current_timestamp = Mage::getModel('core/date')->timestamp(time());

                $storeId = Mage::app()->getStore();
                $locale = Mage::getStoreConfig('general/locale/code', $storeId);

                $timezone = Mage::getStoreConfig('general/locale/timezone', $storeId);
                $date = new Zend_Date($current_timestamp, null, $locale);
                $date->setTimezone($timezone);

                $now_date = $date->toString('yyyy-MM-dd');

                foreach($max_date_exceed_array as $max_date_exceed){
                    if($max_date_exceed == '1'){

                        $collection->addAttributeToFilter("main_table.response_deadline", array('date' => true, 'lt' => $now_date));

                    }
                    elseif($max_date_exceed == '0'){

                        $collection->addAttributeToFilter("main_table.response_deadline", array('date' => true, 'gteq' => $now_date));

                    }
                }
            }
        }

		// Condition
		$conditions = $this->getFilterValueByIndex("rma_item_condition");
		if(is_null($conditions)){
			$conditions=$this->getParentBlock()->getDefaultItemCondition();
		}
		if($conditions){
			$collection->addItemConditionFilter($conditions);
		}
		
		// Status
		$statuses = $this->getFilterValueByColumn("rma_status");
		if(is_null($statuses)){
			$statuses=$this->getParentBlock()->getDefaultStatuses();
		}
		if($statuses){
			$collection->addAttributeToFilter("main_table.rma_status", array("in"=>$statuses));
		}

		return $this;
	}
	
	protected function _applayDateFilter(Zolago_Rma_Model_Resource_Rma_Collection $collection, $index, $date) {
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
	
	protected function _prepareMassaction()
    {
	    /* remove mass action for now */
	    return $this;

        $this->setMassactionIdField('main_table.entity_id');
        $this->getMassactionBlock()->setFormFieldName('rma');
		$this->getMassactionBlock()->setTemplate("zolagoadminhtml/widget/grid/massaction.phtml");
		
        $this->getMassactionBlock()->addItem('start_packing', array(
             'label'=> Mage::helper('zolagorma')->__('Example action [dev]'),
             'url'  => $this->getUrl('*/*/mass')
        ));

        return $this;
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
			case "rma_items":
				$this->getCollection()->addProductNameFilter(
					$column->getFilter()->getValue());
				return $this;
			break;
			case "has_new_question":
				$this->getCollection()->addHasNewQuestionFilter(
					$column->getFilter()->getValue());
				return $this;
			break;
			case "increment_id":
			case "entity_id":
				$this->getCollection()->addFieldToFilter(
						"main_table.{$column->getId()}", 
						array("like"=>"%".$column->getFilter()->getValue()."%")
				);
				return $this;
			break;
		}
		return parent::_addColumnFilterToCollection($column);
	}


	public function getVendor() {
		return $this->getParentBlock()->getVendor();
	}
	
}
