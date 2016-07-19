<?php

class Zolago_Catalog_Model_Resource_Vendor_Price
	extends Mage_Core_Model_Resource_Db_Abstract{
	
	protected $_options = array();

	protected $_attributeLabelCache = array();
	protected $_optionLabelCache = array();
	
	protected $_campaignAttribute;
	
	protected function _construct() {
		$this->_init("catalog/product", null);
	}
	
	/**
	 * @param array $ids
	 * @return array
	 * 
	 * The struc of data:
	 * 
	 * [  
		{  
		   "entity_id":"1096",
		   "entity_type_id":"4",
		   "attribute_set_id":"15",
		   "type_id":"configurable",
		   "sku":"4-20375-00X",
		   "has_options":"1",
		   "required_options":"1",
		   "created_at":"2014-04-04 13:20:11",
		   "updated_at":"2014-05-13 16:10:58",
		   "var":2861,
		   "children":[  
				 {  
					"label":"Rozmiar",
					"attribute_id":"281",
					"product_super_attribute_id":"8",
					"children":[  
					   {  
						  "option_text":"65B",
						  "value_id":"1228",
						  "value":"399",
						  "price":"1.0000",
						  "children":[  
							 {  
								"entity_id":"168",
								"qty":"115.0000",
								"is_in_stock":"1"
							 }
						  ]
					   },
					   {  
						  "option_text":"65C",
						  "value_id":"1232",
						  "value":"403",
						  "price":"0.0000",
						  "children":[  
							 {  
								"entity_id":"169",
								"qty":"115.0000",
								"is_in_stock":"1"
							 }
						  ]
					   },
					   ...
					]
				 }
			  ]
		   },
		   ...
		]
	 */
	public function getDetails($ids=array(), $storeId, $includeCampaign=true, $isAllowedToCampaign=false) {

		$out = array();
		
		
		$adapter = $this->getReadConnection();
		$baseSelect = $adapter->select();
		
		$baseSelect->from(array("product"=>$this->getMainTable()));
		$baseSelect->where("product.entity_id IN (?)", $ids);
		
		// Tmp var
		foreach($adapter->fetchAll($baseSelect) as $row){
			$out[$row['entity_id']] = array_merge($row, array(
				"var" => rand(0,10000),
			));
		}
		
		// Child data
		foreach($this->getChilds($ids, $storeId) as $child){
			if(!isset($out[$child['parent_id']]['children'][$child['attribute_id']])){
				$out[$child['parent_id']]['children'][$child['attribute_id']] = array(
					"children"		=> array(),
					"label"			=> $this->_getAttributeLabel($child['attribute_id'], $storeId),
					"attribute_id"	=> $child['attribute_id'],
					"product_super_attribute_id" => $child['product_super_attribute_id']
				);
			}
			
			// Group products by option
			if(!isset($out[$child['parent_id']]['children'][$child['attribute_id']]['children'][$child['value_id']])){
				$out[$child['parent_id']]['children'][$child['attribute_id']]['children'][$child['value_id']] = array(
					'option_text' => $this->_getAttributeOption($child['value'], $child['attribute_id'], $storeId),
					'value_id'=> $child['value_id'],
					'value' => $child['value'],
					'price' => $child['price'],
                    'sku'   => $child['sku'],
                    'skuv'  => Mage::helper('core')->escapeHtml($child['skuv']),
					'children'=>array()
				);
			}
			$out[$child['parent_id']]['children'][$child['attribute_id']]
					['children'][$child['value_id']]['children'][]  = array(
				'entity_id'=>$child['product_id'],
				'qty'=>$child['qty'],
				'is_in_stock' => $child['is_in_stock']
			);
		}
		
		// Camapign data
		
		foreach($this->_getCampaign($ids, $storeId, $isAllowedToCampaign) as $campaign){
			$out[$campaign['entity_id']]['campaign'] = $campaign;
		}
		
		// Make flat arrays
		foreach ($out as &$item){
			if(isset($item['children'])){
				foreach($item['children'] as &$option){
					$option['children'] = array_values($option['children']);
				}
				$item['children'] = array_values($item['children']);
			}
		}
		return array_values($out);
	}
	
	/**
	 * @param array $ids
	 * @param int $storeId
	 * @param bool $storeId
	 * @return array
	 */
	protected function _getCampaign(array $ids, $storeId, $isAllowedToCampaign) {
		$websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
		$collection = Mage::getResourceModel('catalog/product_collection')
		->setStore($storeId);
		/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

		$collection->addAttributeToSelect(array(
			"price",
			"special_price",
			"campaign_regular_id",
			"msrp"
		), 'left');
		
		$select = $collection->getSelect();
		
		$joinConds = array(
			"camapign.campaign_id=at_campaign_regular_id.value"
		);
		
		$select->join(
				array("camapign"=>$this->getTable("zolagocampaign/campaign")),
				implode(" AND ", $joinConds)
		);
		
		$joinConds = array(
			"camapign_website.campaign_id=camapign.campaign_id",
			$this->getReadConnection()->quoteInto("camapign_website.website_id=?", $websiteId)
		);
		
		$select->join(
				array("camapign_website"=>$this->getTable("zolagocampaign/campaign_website")),
				implode(" AND ", $joinConds)
		);
		
		$select->where("e.entity_id IN (?)", $ids);
		
		$results = $this->getReadConnection()->fetchAll($select);

		
		$statuses = Mage::getSingleton("zolagocampaign/campaign_status")->toOptionHash();
		
		// Add some data
		foreach($results as &$campaign){
			$campaign['price_source_id_text'] = $this->_getAttributeOption(
					$campaign['price_source_id'], 
					Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE,
					$storeId
			);
			$campaign['price_margin'] = $campaign['percent'];
			$campaign['status_text'] = isset($statuses[$campaign['status']]) ? $statuses[$campaign['status']] : "";
			$campaign['type_text'] = isset($campaign['type']) ? ucfirst($campaign['type']) : "";
			$campaign['is_allowed'] = $isAllowedToCampaign;
			$campaign['url'] = Mage::getUrl("campaign/vendor/edit", array("id"=>$campaign['campaign_id']));
		}
		
		return $results;
	}
	
	/**
	 * @return Mage_Catalog_Model_Resource_Eav_Attribute
	 */
	protected function _getCampaignAttribute() {
		if(!$this->_campaignAttribute){
			$attribute = Mage::getSingleton('eav/config')->getAttribute(
				Mage_Catalog_Model_Product::ENTITY, 
				"campaign_regular_id"
			);
			$this->_campaignAttribute = $attribute;
		}
		return $this->_campaignAttribute;
	}
	
	/**
	 * @param int $attributeId
	 * @param int $storeId
	 * @return string
	 */
	protected function _getAttributeLabel($attributeId, $storeId) {
		if(!isset($this->_attributeLabelCache[$storeId][$attributeId])){
			$attribute = Mage::getSingleton('eav/config')->
				getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeId);
				/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
			$this->_attributeLabelCache[$storeId][$attributeId] = $attribute->getStoreLabel($storeId);
		}
		return $this->_attributeLabelCache[$storeId][$attributeId];
	}
	
	/**
	 * @param int $attributeId
	 * @param int $storeId
	 * @return string
	 */
	protected function _getAttributeOption($optionId, $attributeId, $storeId) {
		if(!isset($this->_optionLabelCache[$storeId][$attributeId][$optionId])){
			$attribute = Mage::getSingleton('eav/config')->
				getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeId)->
				setStoreId($storeId);
				/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
			$this->_optionLabelCache[$storeId][$attributeId][$optionId] = $attribute->
					getSource()->getOptionText($optionId);
		}
		return $this->_optionLabelCache[$storeId][$attributeId][$optionId];
	}
	
	
	/**
	 * @param array $ids
	 * @param int $storeId
	 * @return type
	 */
	public function getChilds(array $ids, $storeId) {
		$websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
		
		$select = $this->getReadConnection()->select();
		$select->from(
			array("link"=>$this->getTable("catalog/product_super_link")),
			array("parent_id", "product_id")
		);
		
		// Add configurable attribute
		$select->join(
			array("sa"=>$this->getTable("catalog/product_super_attribute")),
			"sa.product_id=link.parent_id",
			array("attribute_id", "product_super_attribute_id")
		);
		
		// Add values of attributes
		$select->join(
			array("product_int"=>$this->getValueTable("catalog/product", "int")),
			"product_int.entity_id=link.product_id AND product_int.attribute_id=sa.attribute_id",
			array("value", "value_id")
		);
		
		// Add stock
		$select->join(
			array("stock"=>$this->getTable("cataloginventory/stock_item")),
			"stock.product_id=link.product_id AND stock.stock_id=" . Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID,
			array("is_in_stock", "qty")
		);
				
		// Add optional pricing
		$conds = array(
			"sa_price.product_super_attribute_id=sa.product_super_attribute_id",
			"sa_price.value_index=product_int.value",
			$this->getReadConnection()->quoteInto("sa_price.website_id=?", $websiteId)
		);
		
		$select->joinLeft(
			array("sa_price"=>$this->getTable("catalog/product_super_attribute_pricing")),
			implode(" AND ", $conds),
			array()
		);

        // Add sku
        $select->joinLeft(
            array("cpe" => $this->getTable("catalog/product")),
            "cpe.entity_id = link.product_id",
            array("sku")
        );

        // Add skuv
        $skuvCode  = Mage::getStoreConfig('zolagoos/vendor/vendor_sku_attribute');
        /** @var Mage_Eav_Model_Config $model */
        $model     = Mage::getSingleton('eav/config');
        $attribute = $model->getAttribute('catalog_product', $skuvCode);
        $skuvTable = $attribute->getBackendTable();

        $select->joinLeft(
            array('skuvtable' => $skuvTable),
            implode(" AND ", array(
                "skuvtable.entity_id = link.product_id",
                $this->getReadConnection()->quoteInto("skuvtable.entity_type_id=?", $attribute->getEntityTypeId()),
                $this->getReadConnection()->quoteInto("skuvtable.attribute_id=?", $attribute->getId()),
                $this->getReadConnection()->quoteInto("skuvtable.store_id=?", 0), // For now skuv is only for default store
            )),
            array("skuv" => "skuvtable.value")
        );

		
		// Optional price
		$select->columns(array("price"=>new Zend_Db_Expr("IF(sa_price.value_id>0, sa_price.pricing_value, 0)")));

		$select->where("link.parent_id IN (?)", $ids);
		$select->order("sa.position");
		
		return $this->getReadConnection()->fetchAll($select);
	}
}