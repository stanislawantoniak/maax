<?php
/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Catalog_Controller_Vendor_Product_Abstract 
	extends Zolago_Catalog_Controller_Vendor_Abstract
{
	
	/**
	 * @return Mage_Core_Model_Store
	 */
	protected function _getStore() {
		$storeId = Mage::app()->getRequest()->getParam("store");
		$candidate = Mage::app()->getStore($storeId);
		if($candidate->getId()==$storeId){
			return $candidate;
		}
		return Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE);
	}
	

	/**
	 * @return Zolago_Catalog_Model_Vendor_Product_Grid
	 */
	public function getGridModel() {
		return Mage::getSingleton('zolagocatalog/vendor_product_grid');
	}
	
	/**
	 * @return Mage_Eav_Model_Entity_Attribute_Set
	 */
	public function getAttributeSet() {
		return $this->getGridModel()->getAttributeSet();
	}
	
	/**
	 * @return array
	 */
	public function getColumns() {
		return $this->getGridModel()->getColumns();
	}
	
	
	/**
	 * @return int
	 */
	protected function _getStoreId() {
		//$storeId = $this->getRequest()->getParam("store_id");
		return Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
	}

	/**
	 * @return int
	 */
	protected function _getLabelStoreId() {
		return $this->getGridModel()->getLabelStore()->getId();
	}
	
	/**
	 * collection dont use after load - just flat selects
	 * @return Varien_Data_Collection
	 */
	protected function _prepareBasciCollection() {
		$collection = Mage::getResourceModel("zolagocatalog/vendor_product_collection");
		
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
		
		return $collection;
	}
	
	/**
	 * collection dont use after load - just flat selects
	 * @param Varien_Data_Collection
	 * @return Varien_Data_Collection
	 */
	protected function _prepareCollection(Varien_Data_Collection $collection = null){
		
		if(!($collection instanceof Mage_Catalog_Model_Resource_Product_Collection)){
			$collection = $this->_prepareBasciCollection();
		}
		/* @var $collection Zolago_Catalog_Model_Resource_Vendor_Product_Collection */

		
		//Add Active Static Filters to Collection - Start
		$staticFilters = $this->getGridModel()->getStaticFilters();
		if (is_array($staticFilters) && count($staticFilters)) {
			foreach ($staticFilters as $staticFilter=>$value) {
				/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
				$attribute = $this->getGridModel()->getAttribute($staticFilter);
				if ($attribute->getGridPermission() == Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::USE_IN_FILTER && !empty($value)) {
					$collection->addAttributeToFilter($attribute->getAttributeCode(), $value);
				}
			}
		}
	
	    // Prepare collection data
		foreach($this->getColumns() as $key=>$column){
			$columnData = $column->getData();
			
			// Add regular dynamic attributes data
			if(isset($columnData['attribute']) &&
				$columnData['attribute'] instanceof Mage_Catalog_Model_Resource_Eav_Attribute){
				// By regular attribute
				$attributeCode = $columnData['attribute']->getAttributeCode();
				$collection->joinAttribute($attributeCode, 'catalog_product/'.$attributeCode, 'entity_id', null, 'left');
				//$collection->addAttributeToSelect($columnData['attribute']->getAttributeCode());
			}
			
			switch ($column->getIndex()) {
				case "thumbnail":
					$collection->addImagesCount(false);
				break;
				case "name":
					$collection->joinAttribute("skuv", 'catalog_product/skuv', 'entity_id', null, 'left');
				break;
				case "is_in_stock":
					/**
					 * NOTE: is_in_stock is added after collection load for better performance ( 1 subselect vs 2)
					 * @see Zolago_Catalog_Model_Resource_Vendor_Product_Collection::prepareRestResponse()
					 */
					$collection->joinChildQuantities();
					$collection->joinAllChildrenCount();
					$collection->joinAvailableChildrenCount();
					break;
			}
		}
		return $collection;
    }

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return array
	 */
	protected function _getSqlCondition($key, $value) {
		
		$attribute=$this->getGridModel()->getAttribute($key);
				
		if(is_array($value)){
			$isDate = in_array($attribute->getFrontendInput(), 
					array("datetime", "date"));
			
			if(isset($value['to']) && is_numeric($value['to']) && !$isDate){
				$value['to'] = (float)$value['to'];
			}
			if(isset($value['from']) && is_numeric($value['from']) && !$isDate){
				$value['from'] = (float)$value['from'];
			}
			if($isDate){
				$value['date'] = true;
			}
			
			if(isset($value['to']) && is_numeric($value['to']) && 
					(!isset($value['from']) || (isset($value['from']) && $value['from']==0))){
				$value = array($value, array("null"=>true));
			}
			
			return $value;
		}
		
		if($attribute && $attribute->getId()){
			// process name
			if($attribute->getAttributeCode()=="name"){
				$this->_getCollection()->addFieldToFilter(array(
						array("attribute"=>"name", "filter"=> array("like"=>"%".$value."%")),
						array("attribute"=>"skuv", "filter"=> array("like"=>"%".$value."%"))
				));
				return null;
			}
			// Proces enuberable attributes
			if($this->getGridModel()->isAttributeEnumerable($attribute)){
				// Process null
                if ($value === self::NULL_VALUE) {
                    $this->_getCollection()->addFieldToFilter(array(
                        array("attribute" => $attribute->getAttributeCode(), "filter" => array("null" => true)),
                        array("attribute" => $attribute->getAttributeCode(), "filter" => array("eq" => ""))
                    ));
                    return null;
                }
				// Process multiply select
				if($attribute->getFrontendInput()=="multiselect"){
					/**
					 * Do id by MySQL RegExp expression in applaying filter in gird
					 */
					$collection = $this->_getCollection();

					$code = $attribute->getAttributeCode();
					$aliasCode = $code ."_filter";


					$collection->joinAttribute($aliasCode, "catalog_product/$code", "entity_id", null, "left");


					$valueTable1 = "at_".$aliasCode."_default";
					$valueTable2 = "at_".$aliasCode;

					if($collection->getStoreId()){
						$valueExpr = $collection->getSelect()->getAdapter()
							->getCheckSql("{$valueTable2}.value_id > 0", "{$valueTable2}.value", "{$valueTable1}.value");

					}else{
						$valueExpr = "$valueTable2.value";
					}
					// Try use regexp to match vales with boundary (like comma, ^, $)  - (123,456,678) 
					$collection->getSelect()->where(
							$valueExpr." REGEXP ?", "[[:<:]]".$value."[[:>:]]"
					);

					return null;
				}
				return array("eq"=>$value);
			}
		} elseif ($key == 'is_in_stock') {
			if (((int)$value) == Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK) {
				$condition = 'stock_qty > ?';
			} else {
				$condition = 'stock_qty <= ?';
			}
			$collection = $this->_getCollection();
			$collection->getSelect()->having($condition, 0);
			return null;
		}
		
		// Return default
		return array("like"=>'%'.$value.'%');
	}
	
	/**
	 * @return array
	 */
	protected function _getAvailableQueryParams() {
		$out = array();
		foreach($this->getGridModel()->getColumns() as $column){
			if($column->getAttribute() && $column->getFilterable()!==false){
				$out[$column->getAttribute()->getAttributeCode()] = true;
			}
		}
		if(isset($out["thumbnail"])){
			$out["images_count"] = true;
		}
		$out["is_in_stock"] = true;
		return array_keys($out);
	}
	
	/**
	 * @return array
	 */
	protected function _getRestSort() {
		$sort = parent::_getRestSort();
		$attribute = null;
		
		if(isset($sort['order']) && isset($sort['dir'])){
			$attribute = $this->getGridModel()->getAttribute($sort['order']);
		}
		
		// Some special sort fixes
		if($attribute && $this->getGridModel()->isAttributeEnumerable($attribute)){
			$source = $attribute->getSource();
			$oldStoreId = $this->_getCollection()->getStoreId();
			if($source instanceof Mage_Eav_Model_Entity_Attribute_Source_Boolean){
				// Need fix 
				Mage::getResourceSingleton('zolagocatalog/vendor_mass')->
					addBoolValueSortToCollection(
						$attribute,
						$this->_getCollection(),
						$sort['dir']
				);
				return array();
			}elseif($source instanceof Mage_Eav_Model_Entity_Attribute_Source_Table){
				// Need fix - wrong sort - multiple values first
				if($attribute->getFrontendInput()=="multiselect"){
					Mage::getResourceSingleton('zolagocatalog/vendor_mass')->
						addMultipleValueSortToCollection(
							$attribute,
							$this->_getCollection(),
							$sort['dir']
					);
				// Need fix - need original values tot text values from eav!
				}else{
					$this->_getCollection()->setStoreId(
						$this->_getLabelStoreId()
					);
					Mage::getResourceSingleton('zolagocatalog/vendor_mass')->
						addEavTableSortToCollection(
							$attribute,
							$this->_getCollection(),
							$sort['dir']
					);
					$this->_getCollection()->setStoreId($oldStoreId);
					
				}
				return array();
			}
		}
		
		return $sort;
	}
	
	/**
	 * @return array
	 */
	protected function _getAvailableSortParams() {
		$out = array();
		foreach($this->getGridModel()->getColumns() as $column){
			if($column->getAttribute() && $column->getSortable()!==false){
				$out[] = $column->getAttribute()->getAttributeCode();
			}
		}
		$out[] = 'is_in_stock';
		return $out;
	}
	
	/**
	 * @param array $productIds
	 * @param array $attributesData
	 * @param int $storeId
	 * @throws Mage_Core_Exception
	 */
	protected function _processAttributresSave(array $productIds, array $attributesData, $storeId, array $data) {

		// Store
		$store = $this->_getStore();
		$helper = Mage::helper("zolagocatalog");
		$attributesObjects = array();
		$vendorStoreId = $this->getVendor()->getLabelStore();
		
		// Optional add/sub form multiselects
		$attributesMode = isset($data['attribute_mode']) ?
				$data['attribute_mode'] : array();
		// Do check ower ?
		$checkVendor =  isset($data['check_vendor']) ?
				$data['check_vendor'] : true;
		// Do check editable ?
		$checkEditable =  isset($data['check_editable']) ?
				$data['check_editable'] : true;
		// Do check required ?
		$checkRequired =  isset($data['check_required']) ?
				$data['check_required'] : true;
		
		$dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
		
		// Validate required data
		if(!is_array($attributesData) || !count($attributesData) || 
			!$store || !is_array($productIds) || !count($productIds)){
			throw new Mage_Core_Exception(
					$helper->__("No required data passed")
			);
		}
		
		// Validate products vendor
		if($checkVendor){
            /** @var Zolago_Catalog_Model_Resource_Product_Collection $collection */
			$collection = Mage::getResourceModel("catalog/product_collection");
			$collection->addAttributeToFilter('udropship_vendor', $this->getVendor()->getId());
			$collection->addIdFilter($productIds);

			if($collection->getSize()<count($productIds)){
				throw new Mage_Core_Exception($helper->__("You are trying to edit not your product"));
			}
		}
		// Collect validation data
		$notAllowed = array();
		$missings = array();
		$descriptionChildProds = array();
		$nameChildProds = array();
        /** @var Zolago_Catalog_Model_Resource_Product $resProduct */
        $resProduct = Mage::getResourceModel('catalog/product');
		foreach($attributesData as $attributeCode=>$value){
			$attribute = $this->getGridModel()->getAttribute($attributeCode);
			$attributesObjects[$attributeCode] = $attribute;
			// special check for brandshop
			if ($attributeCode == 'brandshop') {
			    $vendor = $this->getVendor();
			    if ($vendor->getVendorId() != $value) {			        
    			    $list = $vendor->getCanAddProduct();    			        			    
    			    $allow = false;
    			    foreach ($list as $vendor) {
                        if ($vendor['brandshop_id'] == $value) {
                            $allow = true;
                        }
			        }
			        if (!$allow) {
			            $notAllowed[] = $attribute->getStoreLabel($vendorStoreId);
			            continue;
			        }
			    }			    
			}
            // special check for description status
			if ($attributeCode == 'description_status') {
			    // add child 
			    $list = $resProduct->getRelatedProducts($productIds);
			    foreach ($list as $item) {
                    $descriptionChildProds[$item['product_id']] = $item['product_id'];
			    }
                			    
            }
            // special check for product name
            if ($attributeCode == 'name') {
                $list = $resProduct->getRelatedProducts($productIds);
                foreach ($list as $item) {
                    $nameChildProds[$item['product_id']] = $item['product_id'];
                }
            }
			/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
			if($checkEditable && !$this->getGridModel()->isAttributeEditable($attribute)){
				$notAllowed[] = $attribute->getStoreLabel($vendorStoreId);
			}
			if($checkRequired && $attribute->getIsRequired() && trim($value)==""){
				$missings[] = $attribute->getStoreLabel($vendorStoreId);
			}
			
			
		}
		// Validate grid permissions
		if($notAllowed){
			/**
			 * @todo missing attribute data from ex. special_from_data
			 */
			throw new Mage_Core_Exception($helper->__("You are trying to edit not editable attribute (%s)", implode($notAllowed)));
		}
		// Validate required
		if($missings){
			throw new Mage_Core_Exception($helper->__("Cannot pass empty required attribute (%s)", implode($missings)));
		}
		
		// Prepare save data
		foreach ($attributesData as $attributeCode => $value) {

			$attribute = $attributesObjects[$attributeCode];

			if(!$attribute || !$attribute->getId()){
				unset($attributesData[$attributeCode]);
				continue;
			}
			
			// Prepare date fileds
			if ($attribute->getBackendType() == 'datetime') {
				if (!empty($value)) {
					$filterInput    = new Zend_Filter_LocalizedToNormalized(array(
						'date_format' => $dateFormat
					));
					$filterInternal = new Zend_Filter_NormalizedToLocalized(array(
						'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
					));
					$value = $filterInternal->filter($filterInput->filter($value));
				} else {
					$value = null;
				}
				$attributesData[$attributeCode] = $value;
			// Prepare multiple input
			}elseif ($attribute->getFrontendInput() == 'multiselect') {
				if (is_array($value)) {
					$attributesData[$attributeCode] = implode(',', $value);
				}

				// Unset value if add mode active
				if(isset($attributesMode[$attributeCode])){
					switch ($attributesMode[$attributeCode]) {
						case "add":
						case "sub":
							$arrayValue = is_array($value) ? $value : 
								(is_string($value) ? explode(",", $value) : array());
							Mage::getResourceSingleton('zolagocatalog/vendor_mass')->addValueToMultipleAttribute(
								$productIds,
								$attribute, 
								$arrayValue,
								$store,
								$attributesMode[$attributeCode]	
							);
							unset($attributesData[$attributeCode]);
						break;
					}
				}
			}
		}	
		// if children exists update the children (only description_status)
		if ($descriptionChildProds) {
		    $childAttributes = array(
		        'description_status' => $attributesData['description_status']
            );
	    	Mage::getSingleton('catalog/product_action')
    			->updateAttributes($descriptionChildProds, $childAttributes, $store->getId());
		}
        // if children exists update the children (only product name)
        if ($nameChildProds) {
            // Simple collection
            /** @var Zolago_Catalog_Model_Resource_Product_Collection $collection */
            $collection = Mage::getResourceModel("zolagocatalog/product_collection");
            $collection->addFieldToFilter("entity_id", array("in" => $nameChildProds));
            $collection->setStoreId($store->getId());
            $collection->joinAttribute('size', 'catalog_product/size', 'entity_id', null, 'left');
            $collection->load();
            // make produt name for simple products like: <name from configurable><space><size text>
            $sizeAttr = $this->getGridModel()->getAttribute('size');
            $attrSource = $sizeAttr->getSource();
            foreach ($collection as $product) {
                $size = $attrSource->getOptionText($product->getData('size'));
                $childAttributes = array(
                    'name' => $attributesData['name'] . ' ' . $size
                );
                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes(array($product->getId()), $childAttributes, $store->getId());
            }
        }
        // Write attribs & make reindex
		Mage::getSingleton('catalog/product_action')
			->updateAttributes($productIds, $attributesData, $store->getId());
		
	}
	
	protected function _handleRestPut() {

		$reposnse = $this->getResponse();
		$data = Mage::helper("core")->jsonDecode(($this->getRequest()->getRawBody()));
		$storeId = 0;

		try{
			$productId = $data['entity_id'];
			$attributeChanged = $data['changed'];
			$attributeData = array();
			$storeId = $data['store_id'];

			/* @var $descriptionHistoryModel Zolago_Catalog_Model_Description_History */
			$descriptionHistoryModel = Mage::getModel("zolagocatalog/description_history");

			foreach($attributeChanged as $attribute){
				if(isset($data[$attribute])){
                    $attributeData[$attribute] = $data[$attribute];
                    if ($attribute == "description" || $attribute == "short_description") {
                        // Clear descriptions
                        $attributeData[$attribute] = Mage::helper("zolagocatalog")->secureInvisibleContent($data[$attribute]);
                    } elseif ($attribute == "name") {
                        // Clear product name
                        $attributeData[$attribute] = Mage::helper("zolagocatalog")->cleanProductName($data[$attribute]);
                    }

					/**
					 * Save attribute change history
					 */

					$descriptionHistoryModel->updateChangesHistory(
						$this->getVendorId(),
						array($productId),
						$attribute,
						$data[$attribute],
						Mage::getModel("catalog/product")->getCollection()->addAttributeToSelect($attribute),
						$data["attribute_mode"][$attribute]
					);
					/*Save attribute change history*/
				}
			}
			if($attributeData){
				$this->_processAttributresSave(array($productId), $attributeData, $storeId, $data);
			}

		} catch (Mage_Core_Exception $ex) {
			$reposnse->setHttpResponseCode(500);
			$reposnse->setBody($ex->getMessage());
			return;
		} catch (Exception $ex) {
			Mage::logException($ex);
			$reposnse->setHttpResponseCode(500);
			$reposnse->setBody("Some error occurred");
			return;
		}

		/** Include attribute set **/
		if(!$this->getRequest()->getParam("attribute_set_id")){
			$this->getRequest()->setParam("attribute_set_id", $data['attribute_set_id']);
		}
		
		/** Get current data **/
		$this->_handleRestGet($productId);
	}
}



