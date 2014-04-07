<?php

class Zolago_Catalog_Block_Vendor_Mass_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('zolagocatalog_mass_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
		
		// Add custom renderes
		$this->setColumnRenderers(array(
			'multiselect'=>'zolagoadminhtml/widget_grid_column_renderer_multiselect'
		));
		$this->setColumnFilters(array(
			"multiselect"=>'zolagoadminhtml/widget_grid_column_filter_multiselect'
		));
    }

	protected function _setCollectionOrder($column) {
		$attribute = $column->getAttribute();
		if($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute && $this->isAttributeEnumerable($attribute)){
			$source = $column->getAttribute()->getSource();
			if($source instanceof Mage_Eav_Model_Entity_Attribute_Source_Boolean){
				// Need fix
				Mage::getResourceSingleton('zolagocatalog/vendor_mass')->addBoolValueSortToCollection(
						$attribute, 
						$this->getCollection(), 
						$column->getDir()
				);
				return $this;
			}elseif($attribute->getFrontendInput()=="multiselect"){
				// Need fix - comma 
				Mage::getResourceSingleton('zolagocatalog/vendor_mass')->addMultipleValueSortToCollection(
						$attribute, 
						$this->getCollection(), 
						$column->getDir()
				);
				return $this;
			}
		}
		return parent::_setCollectionOrder($column);
	}

	protected function _prepareCollection(){
        $collection = Mage::getResourceModel('catalog/product_collection');
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		
		$collection->setFlag("skip_price_data", true);
		
		// Set store id
		$store = $this->getStore();
		$collection->setStoreId($store->getId());
				
		if($store->getId()){
			$collection->addStoreFilter($store);
		}
		
		// Add non-grid filters
		$collection->addAttributeToFilter("udropship_vendor", $this->getVendorId());
		$collection->addAttributeToFilter("attribute_set_id", $this->getAttributeSet()->getId());
		
		// Add fixed column data
	    foreach($this->_getFixedColumns() as $position){
			 foreach($position as $key=>$column){
				$collection->addAttributeToSelect($key);
			 }
		 }
		
		foreach($this->_getGridVisibleAttributes() as $attribute){
			/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
			$collection->addAttributeToSelect($attribute->getAttributeCode());
		}
		
		//Add Active Static Filters to Collection - Start
		$staticFilters		= $this->getStaticFilters();
		$staticFilterValues	= $this->getStaticFilterValues();
		if ($staticFilters && $staticFilterValues) {
			foreach ($staticFilters as $staticFilter) {
				/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
				$attribute = Mage::getModel('eav/entity_attribute')->load($staticFilter);
				if ($attribute->getGridPermission() == Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::USE_IN_FILTER
					&& array_key_exists($staticFilter, $staticFilterValues)) {
					$collection->addAttributeToFilter($attribute->getAttributeCode(), $staticFilterValues[$staticFilter]);
				}
			}
		}
		//Add Active Static Filters to Collection - End
		
        $this->setCollection($collection);
		
        return parent::_prepareCollection();
    }
	
	
	protected function _prepareStaticStartColumns(){
		 $static = $this->_getFixedColumns();
		 if(isset($static['start'])){
			 foreach($static['start'] as $key=>$column){
				 $this->addColumn($key, $column);
			 }
		 }
	}
	
	protected function _prepareStaticEndColumns(){
		 $static = $this->_getFixedColumns();
		 if(isset($static['end'])){
			 foreach($static['end'] as $key=>$column){
				 $this->addColumn($key, $column);
			 }
		 }
	}

	protected function	_getFixedColumns(){
		return array(
			"start" => array(
				"entity_id"=> array(
					"index"=>"entity_id", 
					"type"=>"number",
					"header"=> Mage::helper("zolagocatalog")->__("ID"),
					"width" => "50px"
				)
			),
			/*
			"end" => array(
				"price" => array(
					"index"			=> "price",
					'type'			=> 'price',
					'currency_code' => $this->getStore()->getBaseCurrency()->getCode(),
					"header"		=> Mage::helper("zolagocatalog")->__("Price"),
				)
			) 
			*/
		);
	}

    protected function _prepareColumns() {
       
		$this->_prepareStaticStartColumns();
        
		$attributeCollection = $this->_getGridVisibleAttributes();
		
		foreach($attributeCollection as $attribute){
			/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
			$attribute->setStoreId($this->getStore()->getId());
			$code = $attribute->getAttributeCode();
			$data = array(
				"index"     => $code,
				'type'		=> $this->_getColumnType($attribute),
				"header"    => $this->_getColumnLabel($attribute),
				"attribute"	=> $attribute
			);
			$this->addColumn($code,  $this->_processColumnConfig($attribute, $data));
		}
		
        $this->_prepareStaticEndColumns();
		
        return parent::_prepareColumns();
    }

	public function getGridUrl() {
		return $this->getUrl("*/*/grid", array("_current"=>true));
	}
	
	protected function _processColumnConfig(Mage_Catalog_Model_Resource_Eav_Attribute $attribute, array $config){
		$extend = array();
		// Process select
		$frontendType = $attribute->getFrontendInput();
		if($this->isAttributeEnumerable($attribute)){
			if($frontendType=="multiselect"){
				$extend['type'] = "multiselect";
			}else{
				$extend['type'] = "options";
			}
			if($attribute->getSource()){
				$extend['options']  = array();
				foreach($attribute->getSource()->getAllOptions(false) as $option){
					$extend['options'][$option['value']]=$option['label'];
				}
			}
		}elseif($frontendType=="price"){
			$extend['type'] = "price";
			$extend['currency_code'] = $this->getStore()->getBaseCurrency()->getCode();
		}
		return array_merge($config, $extend);
	}

	public function isAttributeEnumerable(Mage_Catalog_Model_Resource_Eav_Attribute $attribute){
		switch ($attribute->getFrontendInput()){
			case "select":
			case "multiselect":
			case "boolean":
				return true;
			break;
		}
		return false;
	}
	
	protected function _getColumnLabel(Mage_Catalog_Model_Resource_Eav_Attribute $attribute){
		 return $attribute->getStoreLabel($this->getStore()->getId());
	}


	protected function _getColumnType(Mage_Catalog_Model_Resource_Eav_Attribute $attribute) {
		switch ($attribute->getBackendType()) {
			case "text":
			case "varchar":
				return "text";
			break;
			case "int":
			case "decimal":
				return "number";
			break;
			case "datetime":
				return "datetime";
			break;
			
		}
		return "text";
	}
	
	/**
	 * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
	 */
	public function getGridVisibleAttributes(){
		return $this->_getGridVisibleAttributes();
	}


	/**
	 * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
	 */
	protected function _getGridVisibleAttributes() {
		if(!$this->getData("grid_visible_attributes")){
			$collection = Mage::getResourceModel("catalog/product_attribute_collection");
			/* @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection */

			Mage::getResourceSingleton('zolagocatalog/vendor_mass')->addAttributeSetFilterAndSort(
					$collection,
					$this->getAttributeSet()
			);

            //$collection->addIsNotUniqueFilter();
			$collection->addFieldToFilter("grid_permission", array("in"=>array(
				Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::DISPLAY,
				Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION,
				Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::INLINE_EDITION,
			)));
			$this->setData("grid_visible_attributes", $collection);
		}
		return $this->getData("grid_visible_attributes");
	}

	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('catalog')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));
        return $this;
    }

    public function getRowUrl($row){
        return null;
    }
	
	/**
	 * @return Zolago_Dropship_Model_Session
	 */
	protected function _getSession(){
		return Mage::getSingleton('udropship/session');
	}
	
	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	public function getVendorId() {
		return $this->_getSession()->getVendorId();
	}
	
	/**
	 * @return Mage_Eav_Model_Entity_Attribute_Set
	 */
	public function getAttributeSet() {
		if($this->getParentBlock()){
			return $this->getParentBlock()->getCurrentAttributeSet();
		}
		return Mage::getModel("eav/entity_attribute_set")->load(
			Mage::app()->getRequest()->getParam("attribute_set")
		);
	}
	
	public function getStaticFilters() {
		if($this->getParentBlock()){
			return $this->getParentBlock()->getCurrentStaticFilters();
		}
		
		$staticFilters		= Mage::app()->getRequest()->getParam("staticFilters", 0);
		$staticFiltersIds	= false;

		for ($i = 1; $i <= $staticFilters; $i++) {
			if (Mage::app()->getRequest()->getParam("staticFilterId-".$i)) {
				$staticFiltersIds[] = Mage::app()->getRequest()->getParam("staticFilterId-".$i);
			}
		}
		
		return $staticFiltersIds;
	}
	
	/**
	 * @return Mage_Eav_Model_Entity_Attribute
	 */
	public function getStaticFilterValues() {
		if($this->getParentBlock()){
			return $this->getParentBlock()->getCurrentStaticFilterValues();
		}
		
		$staticFilters			= Mage::app()->getRequest()->getParam("staticFilters", 0);
		$staticFiltersValues	= false;
		
		for ($i = 1; $i <= $staticFilters; $i++) {
			if (Mage::app()->getRequest()->getParam("staticFilterId-".$i) && Mage::app()->getRequest()->getParam("staticFilterValue-".$i)) {
				$staticFiltersValues[Mage::app()->getRequest()->getParam("staticFilterId-".$i)] = Mage::app()->getRequest()->getParam("staticFilterValue-".$i);
			}
		}
		
		return $staticFiltersValues;
	}	
	
	/**
	 * @return Mage_Core_Model_Store
	 */
	public function getStore() {
		if($this->getParentBlock()){
			return $this->getParentBlock()->getCurrentStore();
		}
		return Mage::app()->getStore(
			Mage::app()->getRequest()->getParam("store", 0)
		);
	}
    

}