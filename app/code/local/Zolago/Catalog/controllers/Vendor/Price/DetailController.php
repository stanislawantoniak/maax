<?php
class Zolago_Catalog_Vendor_Price_DetailController extends Zolago_Catalog_Controller_Vendor_Price_Abstract
{
	/**
	 * Stockmodal
	 */
	public function stockmodalAction() {

		$this->_registerProduct();
		
		$this->loadLayout();
		$this->renderLayout();
	}

    /**
     * Get html of remove-product-from-campaign modal (HTML)
     */
    public function removemodalAction() {
        $this->_registerProduct();

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * remove product from campaign
     */
    public function removemodalSaveAction() {
        $request = $this->getRequest();
        $productId = $request->getParam("entity_id");
        $campaignId = $request->getParam("campaignId");

        if (!empty($campaignId) && !empty($productId)) {
            /* @var $model Zolago_Campaign_Model_Resource_Campaign */
            $model = Mage::getResourceModel("zolagocampaign/campaign");
            $model->removeProduct($campaignId, $productId);
        }
        $this->_forward("get", "vendor_price", "udprod");
    }

	/**
	 * Save prices
	 * @return type
	 */
	public function pricemodalSaveAction() {
		
		$request = $this->getRequest();
		$productId = $request->getParam("entity_id");
		$storeId = $request->getParam("store_id");
		$attributes = $request->getParam("attributes");
		$currentPrice = $this->_formatNumber($request->getParam("price"));
		$msrp = $this->_formatNumber($request->getParam("msrp", 0));
		$margin = $this->_formatNumber($request->getParam("price_margin", 0));
		$product =  $this->_getProduct($productId, $storeId);
		$isConfigurable = $product->isComposite();
		
		$productData = array(
			"converter_price_type"	=> $request->getParam("converter_price_type"),
			"converter_msrp_type"	=> $this->_formatNumber($request->getParam("converter_msrp_type", 0)),
			"price_margin"			=> $margin != 0 ? $margin : null,
			"price"					=> $currentPrice,
			"msrp"					=> $msrp > 0 ? $msrp : null
		);
		
		try{
			$affectedIds = array($product->getId());
			
			$actionModel = Mage::getModel('catalog/product_action');
			/* @var $actionModel Zolago_Catalog_Model_Product_Action */
			$actionModel->setSkipPricetypeQueue(true);
			$actionModel->setSkipConfigurableQueue(true);
			
			// Save product data
			$actionModel->updateAttributesNoIndex(array($product->getId()), $productData, $storeId);
			
			// Process childs if needed
			if($attributes && is_array($attributes) && $isConfigurable){
				
				$data = array();
				$typeInstance = $product->getTypeInstance();
				/* @var $typeInstance Mage_Catalog_Model_Product_Type_Configurable */
				$usedProducts = $typeInstance->getUsedProducts();
				
				$eavModel = Mage::getSingleton('eav/config');
				/* @var $eavModel Mage_Eav_Model_Config */
				
				$priceVariations = array();
				
				foreach($attributes as $superAttrId=>$values){
					foreach($values as $valueId=>$pricing){
						$variation = $this->_formatNumber($pricing['pricing_value']);
						$data[$pricing['product_super_attribute_id']][] = array(
							"product_super_attribute_id" => 
								$pricing['product_super_attribute_id'],
							"value_index"		=> $pricing['value_index'],
							"pricing_value"		=> $variation != 0 ? $variation : "",
							"value_id"			=> $pricing['value_id'],
							"is_percent"		=> 0, // only fixed
							"use_default_value" => 0, // use website values
						);
						
						// Prepare child price
						foreach($usedProducts as $childProduct){
							/* @var $childProduct Mage_Catalog_Model_Product */
							$attribute = $eavModel->getAttribute(
								Mage_Catalog_Model_Product::ENTITY, 
								$pricing['attribute_id']
							);
							
							// Product matched
							if($childProduct->getData($attribute->getAttributeCode())==$pricing['value_index']){
								// Set product to save
								if(!isset($priceVariations[$childProduct->getId()])){
									$priceVariations[$childProduct->getId()] = $currentPrice;
								}
								// Include variation
								if($variation!=0){
									$priceVariations[$childProduct->getId()] += $variation;
								}
							}
						}
					}
				}
				
				////////////////////////////////////////////////////////////////
				// Save price variations
				////////////////////////////////////////////////////////////////
				$model = Mage::getModel('catalog/product_type_configurable_attribute');
				/* @var $model Mage_Catalog_Model_Product_Type_Configurable_Attribute */

				foreach($data as $superAttrId => $values){
					$model->setId($superAttrId);
					$model->setStoreId($storeId);
					$model->setProductId($productId);
					$model->setValues($values);
					$model->getResource()->savePrices($model);
				}
				
				////////////////////////////////////////////////////////////////
				// Save configurable prices
				////////////////////////////////////////////////////////////////
				
				
				$collection = Mage::getResourceModel('catalog/product_collection');
				/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
				
				// Check values are chaned?
				$collection->setStoreId($storeId);
				$collection->addIdFilter(array_keys($priceVariations));
				$collection->addPriceData();
				$collection->load();
				
				
				foreach($priceVariations as $childProductId=>$price){
					$collectionProduct = $collection->getItemById($childProductId);
					
					// Skip no diff prices
					if($collectionProduct && $collectionProduct->getId()){
						if((float)$collectionProduct->getPrice()==(float)$price){
							continue;
						}
					}
					
					$actionModel->updateAttributesNoIndex(
						array($childProductId), 
						array('price'=>$price), 
						$storeId
					);
					$affectedIds[] = $childProductId;
				}
			}
			
			// Reindex if needed
			if($affectedIds){
				$this->_reindexPrices($affectedIds, $product->getWebsiteIds());
			}
		}
		catch(Exception $e){
			Mage::logException($e);
			$this->getResponse()->
					setHttpResponseCode(500)->
					setBody($e->getMessage())->
					setHeader("content-type", "text/plain");
			return;
		}

        // Varnish & Turpentine
        $productsToReindex = array_merge(array($productId), $affectedIds);
        /** @var Zolago_Catalog_Model_Resource_Product_Collection $coll */
        $coll = Mage::getResourceModel('zolagocatalog/product_collection');
        $coll->addFieldToFilter('entity_id', array( 'in' => $productsToReindex));

        Mage::dispatchEvent(
            "vendor_manual_save_price_after",
            array(
                "products"    => $coll,
                "product_ids" => $coll->getAllIds()
            )
        );

		$this->_forward("get", "vendor_price", "udprod");
	}
	
	
	/**
	 * Get html of product price modal (HTML)
	 */
	public function pricemodalAction() {
		
		$this->_registerProduct();
		
		$this->loadLayout();
		$this->renderLayout();
	}

	/**
	 * Details action (JSON)
	 */
	public function detailAction() {
		$ids = $this->getRequest()->getParam("ids", array());
		
		$ids = array_map(function($item){return (int)$item;}, $ids);
		
		$storeId = $this->getRequest()->getParam("store");
		$out = array();
		
		$collection = $this->_prepareCollection();
		$collection->addIdFilter($ids);

		if($collection->getSize()<count($ids)){
			throw new Mage_Core_Exception("You are trying to edit not your product");
		}

        /** @var Zolago_Catalog_Model_Resource_Vendor_Price $model */
        $model = Mage::getResourceSingleton('zolagocatalog/vendor_price');
		$out = $model->getDetails($ids, $storeId, true, $this->_getSession()->isAllowed("campaign"));
		
		$this->getResponse()->
				setHeader('Content-type', 'application/json')->
				setBody(Mage::helper("core")->jsonEncode($out));
		
	}
	
	/**
	 * Reindex pricesif nedded
	 * @param array $ids
	 * @param array $websiteIds
	 */
	protected function _reindexPrices(array $ids = array(), array $websiteIds = array()) {
		
        Mage::getResourceSingleton('catalog/product_indexer_price')
            ->reindexProductIds($ids);
        $indexers = array(
            'source'  => Mage::getResourceModel('catalog/product_indexer_eav_source'),
            'decimal' => Mage::getResourceModel('catalog/product_indexer_eav_decimal'),
        );
        foreach ($indexers as $indexer) {
            /** @var $indexer Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract */
            $indexer->reindexEntities($ids);
        }
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $fI = new Mage_Catalog_Model_Resource_Product_Flat_Indexer();
            $entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();
            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode($entityTypeID, 'price');
			
			foreach($websiteIds as $websiteId){
				foreach(Mage::app()->getWebsite($websiteId)->getStores() as $storeId){
					$fI->updateAttribute($attribute, $storeId, $ids);
				}
			}
			
        }
	}
	
	/**
	 * @return void
	 */
	protected function _registerProduct() {
		$product = Mage::getModel('catalog/product')->
				setStoreId($this->getRequest()->getParam('store_id'))->
				load($this->getRequest()->getParam('id'));
		
		if($product->getUdropshipVendor()!=$this->_getSession()->getVendorId()){
			$this->norouteAction();
			return;
		}
		
		Mage::register("current_product", $product);
	}
	

}



