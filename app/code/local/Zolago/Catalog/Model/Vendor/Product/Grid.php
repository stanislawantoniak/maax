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
				"filter"	=> true,
				"sortable"	=> false,
				"filterable"=> true
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
				"filterable"=> true,
				"header"	=> $this->_getColumnLabel($name),
                "htmlspecialchars_decode" => true
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
	 * @return array
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
				"required"  => (int)$attribute->getIsRequired(),
				'type'		=> $this->_getColumnType($attribute),
				"header"    => $this->_getColumnLabel($attribute),
				"attribute"	=> $attribute
			);
			$columns[$code] = $data;
		}
		$isInStock = array(
			'is_in_stock'		=> array(
				"index"			=> 'is_in_stock',
				"required"		=> 0,
				'type'			=> "options",
				"clickable"		=> true,
				"filterable"	=> true,
				"header"		=> Mage::helper('zolagocatalog')->__("Total quantity"),
				"attribute"		=> false,
				"allow_empty"	=> 0
			)
		);

		// Insert after description status column
		$p1 = array_slice($columns, 0, ((int)array_search('description_status', array_keys($columns))) + 1 ,true);
		$p2 = array_slice($columns, ((int)array_search('description_status', array_keys($columns))) + 1, null, true);
		$columns = $p1 + $isInStock + $p2;
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
		$extend = array();
		if($attribute){
			
			// Editable
			if($attribute->getGridPermission()==Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::INLINE_EDITION){
				$extend['editable_inline'] = true;
			}elseif($attribute->getGridPermission()==Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION){
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
				// Process attribute source options
				if($attribute->getSource()){
					// Trim manufacturer options
					switch ($attribute->getAttributeCode()) {
						case "manufacturer":
							/** @todo Implement with method **/
							$extend['filter_options'] = array();
							$extend['options'] = Mage::helper("zolagosizetable")->getBrands(
								$this->getVendor(), 
								$this->getLabelStore()->getId()
							);
							foreach($extend['options'] as $value=>$label){
								$extend['filter_options'][] = array(
									"label" => $label,
									"value" => $value
								);
							}
						break;

						case "brandshop":
						    $vendors = $this->getVendor()->getCanAddProduct();
						    foreach ($vendors as $vendor) {
						        $extend['filter_options'][] = array (
						            'value' => $vendor->getBrandshopId(),
						            'label' => $vendor->getVendorName(),
                                );
						    }
						    // vendor himself
						    $extend['filter_options'][] = array(
						        'value' => $this->getVendor()->getId(),
						        'label' => $this->getVendor()->getVendorName(),
						    );
							$options  = $attribute->getSource()->getAllOptions(false);
							foreach($options as $option){
								if($option['value']!==""){
									$extend['options'][$option['value']]=$option['label'];
								}
							}
						    break;
						default:
							$extend['options']  = array();
							$extend['filter_options']  = $attribute->getSource()->getAllOptions(false);
							foreach($extend['filter_options'] as $option){
								if($option['value']!==""){
									$extend['options'][$option['value']]=$option['label'];
								}
							}
						break;
					}
					if(isset($extend['filter_options'])){
						$extend['edit_options'] = $extend['filter_options'];
					}
				}
			}elseif($frontendType=="price"){
				$extend['type'] = "price";
				$extend['currency_code'] = $this->getStore()->getBaseCurrency()->getCode();
			}
		} elseif (isset($config['index']) && $config['index'] == 'is_in_stock') {
			$extend['options'] = array();
			$extend['filter_options'] = Mage::getSingleton('cataloginventory/source_stock')->toOptionArray();
			foreach ($extend['filter_options'] as $option) {
				$extend['options'][$option['value']] = $option['label'];
			}
			$extend['edit_options'] = $extend['filter_options'];
		}
		return empty($extend) ? new Varien_Object($config) : new Varien_Object(array_merge($config, $extend));
	}

	/**
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute | string $attribute
	 * @return Mage_Catalog_Model_Resource_Eav_Attribute
	 */
	public function getAttribute( $attribute){
		if($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute){
			return $attribute;
		}
		
		$object = Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY, $attribute
		);
		
		/**
		 * Fixing cache problem with no attrbiute full data
		 * is_required need to be set. if null just reload attrbiute
		 */
		if(null===$object->getIsRequired()){
			$object = Mage::getModel("catalog/resource_eav_attribute")->load($object->getId());
		}
		
		return $object;
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
				return "textarea";
			break;
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
	 * @param string | Mage_Catalog_Model_Resource_Eav_Attribute $attribute
	 * @return boolean
	 */
	public function isAttributeEditable($attribute) {
		$attribute = $this->getAttribute($attribute);
		switch($attribute->getGridPermission()){
			case Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::INLINE_EDITION:
			case Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION:
				return true;
			break;
		}
		return false;
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
			$collection->addFieldToFilter("grid_permission", array("in"=>$this->getGridAttributeTypes()));

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
	 * @return array
	 */
	public function getGridAttributeTypes() {
		return array(
			Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::DISPLAY,
			Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION,
			Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::INLINE_EDITION,
		);
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
	 * @return ZolagoOs_OmniChannel_Model_Vendor
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