<?php
/**
 * Model for keep columns defs
 */
class Zolago_Catalog_Model_Vendor_Product_Grid  extends Varien_Object {
	
	protected $_denyColumnList = null;
	protected $_cols = array();
	
	/**
	 * Get final columns
	 * @return array
	 */
	public function getColumns() {
		if($this->_cols){
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
				$columns[$key] = $column;
			}
			foreach($this->_prepareDynamicColumns() as $key=>$column){
				$columns[$key] = $column;
			}
			foreach($this->_prepareFixedEndColumns() as $key=>$column){
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
				"header"	=> $this->_getColumnLabel($thumbnail),
				"width"		=> "80",
				"filter"	=> false,
				"sortable"	=> false
			);

			// Name
			$name = Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "name");
			$name->setStoreId($this->getLabelStore()->getId());
			$columnStart[$name->getAttributeCode()] = array(
				"index"		=> $name->getAttributeCode(),
				"type"		=> "text",
				"width"		=> "200",
				"attribute" => $name,
				"clickable" => true,
				"header"	=> $this->_getColumnLabel($name),
			);


			// Status
			$status = Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "status");
			$status->setStoreId($this->getLabelStore()->getId());

			$columnStart[$status->getAttributeCode()] = array(
				"index"		=> $status->getAttributeCode(),
				"type"		=>"options",
				"attribute" => $status,
				"clickable" => true,
				"width"		=> "30",
				"header"	=> $this->_getColumnLabel($status),
			);

			// Image count
			$columnStart["images_count"] = array(
				"index"		=> "images_count",
				"type"		=> "text",
				"header"	=> $this->__("Im."),
				"width"		=> "30",
				"filter"	=> false
			);

			// Sku
			// @todo get attribute by unirgy dropship config
			$sku = Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "sku");
			$sku->setStoreId($this->getLabelStore()->getId());
			$columnStart[$sku->getAttributeCode()] = array(
				"index"		=> $sku->getAttributeCode(),
				"type"		=>"text",
				"width"		=> "80",
				"clickable" => true,
				"header"	=> $this->_getColumnLabel($sku),
			);

			// End columns
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
			$attribute->setStoreId($this->getLabelStore()->getId());
			$code = $attribute->getAttributeCode();
			$data = array(
				"index"     => $code,
				'type'		=> $this->_getColumnType($attribute),
				"header"    => $this->_getColumnLabel($attribute),
				"attribute"	=> $attribute,
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
					$extend['header']=$this->__("St.")." <span class=\"required\">*</span>";
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

		return new Varien_Object($config);
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


    public function isColumnVisible(Mage_Adminhtml_Block_Widget_Grid_Column $column)
    {
        $show = true;
        $data = $column->getData();
        switch($data['index']){

            case 'sku':
            case 'status':
            case 'images_count':
                $show = false;
                break;
        }

        return $show;
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

}