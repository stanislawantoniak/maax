<?php
/**
 * Model for keep columns defs
 */
class Zolago_Catalog_Model_Vendor_Product_Grid  extends Varien_Object {
	
	protected $_denyColumnList = null;
	protected $_cols = array();
	
	/**
	 * @return array(attributeId=>"value", ...);
	 */
	public function getStaticFilters() {
		return Mage::app()->getRequest()->getParam("static", array());
	}
	
	/**
	 * Get final columns
	 * @return array
	 */
	public function getColumns() {
		if(!$this->_cols){
			$columns = array();
			foreach($this->_getAllPossibleColumns() as $key=>$column){
				if($this->_canShowColumn($key, $column)){
					$columns[$key] = $this->_processColumnConfig($key, $column);
				}
			}
			$this->_cols = $columns;
		}
		return $this->_cols;
    }
	
	/**
	 * @return array
	 */
	public function getAllColumns() {
		return $this->_getAllPossibleColumns();
	}
	
	/**
	 * @return array
	 */
	public function getDenyColumns() {
		return $this->_getDenyColumnList();
	}
	
	/**
	 * @param string $key
	 * @return bool
	 */
	public function isDenyColumn($key) {
		if(is_object($key)){
			$key = $key->getIndex();
		}
		if(is_null($this->_denyColumnList)){
			$this->_getDenyColumnList();
		}
		return isset($this->_denyColumnList[$key]);
	}
	
    /**
     * list of not allowed columns (from session)
     */
    protected function _getDenyColumnList() {
    	if (is_null($this->_denyColumnList)) {
    		$attributeSet = $this->getAttributeSet()->getId();
    		$list = Mage::getSingleton('udropship/session')->getData('denyColumnList');
 			$out = array();
    		if ($list && isset($list[$attributeSet])) {
				$out = $list[$attributeSet];
			}
			$this->_denyColumnList = $out;
    	}
    	return $this->_denyColumnList;
    }
	
	/**
	 * @return array
	 */
	protected function _getAllPossibleColumns(){
		if(!$this->getData("all_possible_columns")){
			$columns = array();
			// Build final columns
			foreach($this->_prepareFixedStartColumns() as $key=>$column){
				$column['fixed'] = true;
				$columns[$key] = $column;
			}
			foreach($this->_prepareDynamicColumns() as $key=>$column){
				$columns[$key] = $column;
			}
			foreach($this->_prepareFixedEndColumns() as $key=>$column){
				$column['fixed'] = true;
				$columns[$key] = $column;
			}
			$this->setData("all_possible_columns", $columns);
		}
		return $this->getData("all_possible_columns");
	}


	/**
	 * Prepare layout for static start columns
	 * @return array
	 */
	protected function _prepareFixedStartColumns(){
		 $static = $this->_getFixedColumns();
		 if(isset($static['start'])){
			 return $static['start'];
		 }
		 return array();
	}

	/**
	 * Prepare layout for static end columns
	 * @return array Description
	 */
	protected function _prepareFixedEndColumns(){
		 $static = $this->_getFixedColumns();
		 $columns = array();
		 if(isset($static['end'])){
			 return $static['end'];
		 }
		 return array();
	}

	/**
	 * @return array()
	 */
	protected function	_getFixedColumns(){
		if(!$this->getData("fixed_columns")){

			$columnStart = array();

			// Thumb
			$thumbnail =  Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "thumbnail");
			$thumbnail->setStoreId($this->getLabelStore()->getId());
			$columnStart[$thumbnail->getAttributeCode()] = array(
				"index"		=> $thumbnail->getAttributeCode(),
				"type"		=> "image",
				"attribute" => $thumbnail,
				"clickable" => true,
				"required"  => true,
				"header"	=> $this->_getColumnLabel($thumbnail),
				"filter"	=> false,
				"sortable"	=> false,
				"filterable "=>true
			);

			// Name
			$name = Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "name");
			$name->setStoreId($this->getLabelStore()->getId());
			$columnStart[$name->getAttributeCode()] = array(
				"index"		=> $name->getAttributeCode(),
				"type"		=> "text",
				"attribute" => $name,
				"clickable" => true,
				"required"  => true,
				"filterable"=>true,
				"header"	=> $this->_getColumnLabel($name)
			);
			
			$columnEnd = array();


			$this->setData("fixed_columns", array(
				"start" => $columnStart,
				"end" => $columnEnd
			));
		}
		return $this->getData("fixed_columns");
	}

	/**
	 * @return type
	 */
	protected function _prepareDynamicColumns(){
		$attributeCollection = $this->_getGridVisibleAttributes();
		$columns = array();
		foreach($attributeCollection as $attribute){
			/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
			$attribute->setStoreId((int)$this->getLabelStore()->getId());
			$code = $attribute->getAttributeCode();
			$data = array(
				"index"     => $code,
				"required"  => $attribute->getIsRequired(),
				'type'		=> $this->_getColumnType($attribute),
				"header"    => $this->_getColumnLabel($attribute),
				"attribute"	=> $attribute
			);
			$columns[$code] = $data;
		}
		return $columns;
	}



	/**
	 * use this to checkou out witch column display
	 * @param type $key
	 * @param array $column
	 * @return boolean
	 */
	protected function _canShowColumn($key, array $column) {
		$deny = $this->_getDenyColumnList();
		return empty($deny[$key]);
	}


	/**
	 * @param string $key
	 * @param array $config
	 * @return Varien_Object
	 */
	protected function _processColumnConfig($key, array $config){

		$attribute = null;
		if(isset($config['attribute']) && $config['attribute'] instanceof Mage_Catalog_Model_Resource_Eav_Attribute){
			$attribute = $config['attribute'];
		}
		/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */

		if($attribute){
			$extend = array();
			
			// Editable
			if($attribute->getGridPremission()==Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::INLINE_EDITION){
				$extend['editable_inline'] = true;
			}elseif($attribute->getGridPremission()==Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION){
				$extend['editable'] = true;
			}
			// Process select
			$frontendType = $attribute->getFrontendInput();
			if($this->isAttributeEnumerable($attribute)){
				if($frontendType=="multiselect"){
					$extend['type'] = "multiselect";
				}else{
					$extend['type'] = "options";
				}
				$extend['align'] = "center";
				if($attribute->getSource()){
					$extend['options']  = array();
					foreach($attribute->getSource()->getAllOptions(false) as $option){
						$extend['options'][$option['value']]=$option['label'];
					}
				}
				if($attribute->getAttributeCode()=="status"){
					$extend['type']="status";
					$extend['filter']=false;
					$extend['header']= Mage::helper("zolagocatalog")->__("St.")." *";
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
			
			
			return new Varien_Object(array_merge($config, $extend));
		}

		return new Varien_Object($config);
	}

	/**
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute | string $attribute
	 * @return Mage_Catalog_Model_Resource_Eav_Attribute
	 */
	public function getAttribute( $attribute){
		if($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute){
			return $attribute;
		}
		return Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY, $attribute
		);
	}
	
	/**
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
	 * @return boolean
	 */
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

	/**
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
	 * @return string
	 */
	protected function _getColumnLabel(Mage_Catalog_Model_Resource_Eav_Attribute $attribute){
		 return $attribute->getStoreLabel($this->getLabelStore()->getId());
	}

	/**
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
	 * @return string
	 */
	protected function _getColumnType(Mage_Catalog_Model_Resource_Eav_Attribute $attribute) {
		switch ($attribute->getBackendType()) {
			case "text":
			case "varchar":
				return "text";
			break;
			case "int":
			case "decimal":
				if($attribute->getFrontendInput()=="price"){
					return "price";
				}
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
		return Mage::getModel("eav/entity_attribute_set")->load(
			Mage::app()->getRequest()->getParam("attribute_set_id")
		);
	}
	
	/**
	 * @param array $in
	 * @return array
	 */
	public function optionsToHash(array $in) {
		$out = array();
		foreach($in as $opt){
			$out[$opt['value']] = $opt['label'];
		}
		return $out;
	}


}