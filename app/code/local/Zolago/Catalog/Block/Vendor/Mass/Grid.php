<?php

class Zolago_Catalog_Block_Vendor_Mass_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('zolagocatalog_mass_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setGridClass('z-grid');
        $this->setUseAjax(true);
		$this->setTemplate("zolagocatalog/widget/grid.phtml");
		// Add custom renderes
		$this->setColumnRenderers(array(
			'multiselect'	=>	'zolagoadminhtml/widget_grid_column_renderer_multiselect',
			'image'			=>	'zolagoadminhtml/widget_grid_column_renderer_image',
			'link'			=>	'zolagoadminhtml/widget_grid_column_renderer_link'
		));
		$this->setColumnFilters(array(
			"multiselect"	=>	'zolagoadminhtml/widget_grid_column_filter_multiselect'
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
		$collection->addAttributeToFilter("visibility", array("in"=>array(
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, 
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH, 
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH 
		)));
		
		// Add fixed column attributes
	    foreach($this->_getFixedColumns() as $position){
			 foreach($position as $key=>$column){
				if(isset($column['attribute']) && 
					$column['attribute'] instanceof Mage_Catalog_Model_Resource_Eav_Attribute && 
					$this->_canShowColumnByAttrbiute($column['attribute'])){
					
					$collection->addAttributeToSelect($column['attribute']->getAttributeCode());
				}
					
			 }
		 }
		
	    // Add regular dynamic attributes
		foreach($this->_getGridVisibleAttributes() as $attribute){
			/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
			if($this->_canShowColumnByAttrbiute($attribute)){
				$collection->addAttributeToSelect($attribute->getAttributeCode());
			}
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
	
	/**
	 * @todo Przemek: implement using custom column setting
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
	 * @return boolean
	 */
	protected function _canShowColumnByAttrbiute(Mage_Catalog_Model_Resource_Eav_Attribute $attribute) {
		return true; //$attribute->getAttributeCode()!="name";
	}
	
	
	/**
	 * Prepare layout for static start columns
	 */
	protected function _prepareStaticStartColumns(){
		 $static = $this->_getFixedColumns();
		 if(isset($static['start'])){
			 foreach($static['start'] as $key=>$column){
				 if(!isset($column['attribute'])){
					$this->addColumn($key, $column);
				 }elseif($column['attribute'] instanceof Mage_Catalog_Model_Resource_Eav_Attribute && 
						 $this->_canShowColumnByAttrbiute($column['attribute'])){
					$this->addColumn($key, $column);
				 }
			 }
		 }
	}
	
	/**
	 * Prepare layout for static end columns
	 */
	protected function _prepareStaticEndColumns(){
		 $static = $this->_getFixedColumns();
		 if(isset($static['end'])){
			 foreach($static['end'] as $key=>$column){
				 if(!isset($column['attribute'])){
					$this->addColumn($key, $column);
				 }elseif($column['attribute'] instanceof Mage_Catalog_Model_Resource_Eav_Attribute && 
						 $this->_canShowColumnByAttrbiute($column['attribute'])){
					$this->addColumn($key, $column);
				 }
			 }
		 }
	}

	protected function	_getFixedColumns(){
		if(!$this->getData("fixed_columns")){
			
			// Thumb
			$thumbnail =  Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "thumbnail");
			$thumbnail->setStoreId($this->getLabelStore()->getId());
			
			// Name
			$name = Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "name");
			$name->setStoreId($this->getLabelStore()->getId());
			
			// Status
			$status = Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "status");
			$status->setStoreId($this->getLabelStore()->getId());
			
			// Sku
			$sku = Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "sku");
			$sku->setStoreId($this->getLabelStore()->getId());

			
			$this->setData("fixed_columns", array(
				"start" => array(
					$thumbnail->getAttributeCode() => $this->_processColumnConfig($thumbnail, array(
						"index"		=> $thumbnail->getAttributeCode(), 
						"type"		=> "image",
						"attribute" => $thumbnail,
						"clickable" => true,
						"header"	=> $this->_getColumnLabel($thumbnail),
						"width"		=> "100px",
						"filter"	=> false,
						"sortable"	=> false
					)),
					$name->getAttributeCode() => $this->_processColumnConfig($name, array(
						"index"		=> $name->getAttributeCode(), 
						"type"		=>"text",
						"attribute" => $name,
						"clickable" => true,
						"header"	=> $this->_getColumnLabel($name),
					)),
					/**
					 * @todo add custom renderer & filter
					 */
					$status->getAttributeCode() => $this->_processColumnConfig($status, array(
						"index"		=> $status->getAttributeCode(), 
						"type"		=>"options",
						"attribute" => $status,
						"clickable" => true,
						"header"	=> $this->_getColumnLabel($status),
					)),
					$sku->getAttributeCode() => $this->_processColumnConfig($sku, array(
						"index"		=> $sku->getAttributeCode(), 
						"type"		=>"text",
						"attribute" => $sku,
						"clickable" => true,
						"header"	=> $this->_getColumnLabel($sku),
					)),
				),
				"end" => array(
					"edit" => array(
						"index"			=> "entity_id",
						'type'			=> 'link',
						'link_action'	=> 'udprod/vendor/productEdit',
						'link_param'	=> 'id',
						'link_label'	=> Mage::helper("zolagocatalog")->__("Edit form"),
						"header"		=> Mage::helper("zolagocatalog")->__("Edit"),
						"width"			=> "100px",
						"filter"		=> false,
						"sortable"		=> false)
					)
			));
		}
		return $this->getData("fixed_columns");
	}

    protected function _prepareColumns() {
       
		$this->_prepareStaticStartColumns();
        
		$attributeCollection = $this->_getGridVisibleAttributes();
		
		foreach($attributeCollection as $attribute){
			/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
			$attribute->setStoreId($this->getLabelStore()->getId());
			if(!$this->_canShowColumnByAttrbiute($attribute)){
				continue;
			}
			$code = $attribute->getAttributeCode();
			$data = array(
				"index"     => $code,
				'type'		=> $this->_getColumnType($attribute),
				"header"    => $this->_getColumnLabel($attribute),
				"attribute"	=> $attribute,
			);
			$this->addColumn($code,  $this->_processColumnConfig($attribute, $data));
		}
		
        $this->_prepareStaticEndColumns();
		
		parent::_prepareColumns();
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
		}elseif($frontendType=="media_image"){
			$extend['type'] = "image";
			$extend['clickable'] = true;
			$extend['width'] = "100px";
			$extend['filter'] = false;
			$extend['sortable'] = false;
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
		 $label = $attribute->getStoreLabel($this->getLabelStore()->getId());
		 if($attribute->getIsRequired()){
			 $label .= " <span class=\"required\">*</span>";
		 }
		 return $label;
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
			
			// Exclude static columns
			$static = $this->_getStaticAttributes();
			if(count($static)){
				$collection->addFieldToFilter("attribute_code", array("nin"=>$static));
			}
			
			$this->setData("grid_visible_attributes", $collection);
		}
		return $this->getData("grid_visible_attributes");
	}
	
	/**
	 * Get static columns
	 * @return array
	 */
	protected function _getStaticAttributes(){
		$static = array();
		foreach($this->_getFixedColumns() as $position){
			foreach($position as $column){
				$static[] = $column['index'];
			}
		}
		return $static;
	}

	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('zolagocatalog')->__('Disabled'),
            'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true, 'status' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED)),
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
	 * @return int
	 */
	public function getVendorId() {
		return $this->_getSession()->getVendorId();
	}
	
	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	public function getVendor() {
		return $this->_getSession()->getVendor();
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
	
	/**
	 * Returns store used form labels values
	 * @return Mage_Core_Model_Store
	 */
	public function getLabelStore(){
		if(!$this->getData("label_store")){
			$store = null;
			if($this->getVendor() && $this->getVendor()->getLabelStore()){
				Mage::helper('udropship')->loadCustomData($this->getVendor());
				$store = Mage::app()->getStore($this->getVendor()->getLabelStore());
			}
			if(!$store || !$store->getId()){
				$store = $this->getStore();
			}
			$this->setData("label_store", $store);
		}
		return $this->getData("label_store");
	}
    
	/**
	 * @param Varien_Object $row
	 * @return string|null
	 */
	public function getRowClass(Varien_Object $row) {
		return null;
	}

	
	public function getCellClass(Mage_Adminhtml_Block_Widget_Grid_Column $column, Varien_Object $row) {
		if($column->getAttribute() instanceof Mage_Catalog_Model_Resource_Eav_Attribute){
			if($column->getAttribute()->getIsRequired() && $row->getData($column->getIndex())==""){
				return "required-cell";
			}
		}
		return null;
	}
}