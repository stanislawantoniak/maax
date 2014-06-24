<?php
/**
 * rewrited solrsearch data model
 */
class Zolago_Solrsearch_Model_Data extends SolrBridge_Solrsearch_Model_Data {
	
	/**
	 * @param Varien_Object $product
	 * @return Zolago_Solrsearch_Model_Data
	 */
	public function prepareImproveFinalProductData(Varien_Object $product, &$docData) {
		return $this;
	}
	
	/**
	 * @param Varien_Object $product
	 * @return Zolago_Solrsearch_Model_Data
	 */
	public function getImprovedProductAttributesData(Varien_Object $product, &$docData) {
		return $this;
	}
	
	/**
	 * Fix instock_int param
	 * @param type $_product
	 * @param int $docData
	 */
	public function prepareFinalProductData($_product, &$docData){
		parent::prepareFinalProductData($_product, $docData);
		if($docData['instock_int']==0){
			try{
				$stock = Mage::getModel ( 'cataloginventory/stock_item' )->loadByProduct ( $_product );
				if ($stock->getIsInStock() /* && $stock->getQty() > 0*/) {
					$docData['instock_int'] = 1;
				} 
			}
			catch (Exception $e){}
		}
	}
	
	public function getProductOrderedQty($_product, $store)
	{
		$visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds();
		if ($_product->getId() && in_array($_product->getVisibility(), $visibility) && $_product->getStatus())
		{
			$oldStore = Mage::app ()->getStore ();
			
			// Fix dla plaskiego: zmiana store view w adminie powduje przelaczenie 
			// ktore przestaje korzysatac z EAV tylko plaskiego katalog
			// metoda addOrderedQty nastepnie przywraca tabelke eav (catalog_product_entity)
			// a potem odaje atrybuty jak do plskiego katalogu
			
			//Mage::app ()->setCurrentStore ( $store );

			$storeId    = $store->getId();
			$products = Mage::getResourceModel('reports/product_collection')
				->addOrderedQty()
				->addAttributeToSelect(array('name')) //edit to suit tastes
				->setStoreId($storeId)
				->addStoreFilter($storeId)
				->addIdFilter($_product->getId())->setOrder('ordered_qty', 'desc'); //best sellers on top
			$data = $products->getFirstItem()->getData();

			//Mage::app ()->setCurrentStore ( $oldStore );

			if(isset($data['ordered_qty']) && (int) $data['ordered_qty'])
			{
				return (int)$data['ordered_qty'];
			}
		}else{
			return 0;
		}
		return 0;
	}
	
	/**
	 * Get allow categories by store
	 * @param Mage_Core_Model_Store $store
	 * @return array
	 */
	public function getAllowCategoriesByStore($store)
	{
		$cachedKey = 'solrbridge_solrsearch_indexing_allowcategories_' . $store->getId ();

		$useCache = Mage::app()->useCache('solrbridge_solrsearch');
		
		if ((false !== ($returnData = Mage::app ()->getCache ()->load ( $cachedKey ))) && $useCache) {
			return unserialize ( $returnData );
		}

		$rootCatId = $store->getRootCategoryId();

		$rootCat = Mage::getModel('catalog/category')->load($rootCatId);

		$allowCatIds = Mage::getModel('catalog/category')->getResource()->getChildren($rootCat, true);

		$excludedCategoriesIds = Mage::helper('solrsearch')->getSetting('excluded_categories');
		$excludedCategoriesIdsArray = array();

		if (!empty($excludedCategoriesIds)) {

			$excludedCategoriesIdsArray = explode(',', trim($excludedCategoriesIds, ','));
			//Loaded categories recusive for excluding
			$recusiveExcludedCategory = Mage::helper('solrsearch')->getSetting('excluded_categories_recusive');

			if (isset($recusiveExcludedCategory) && intval($recusiveExcludedCategory) > 0) {

				$excludedChildrenCategoriesIdsArray = array();

				foreach ( $excludedCategoriesIdsArray as $catId ) {
					$parentCat = Mage::getModel('catalog/category')->load($catId);
					$excludedChildrenCategoriesIds = Mage::getModel('catalog/category')->getResource()->getChildren($parentCat, true);
					if (count($excludedChildrenCategoriesIds)) {
						$excludedChildrenCategoriesIdsArray = array_merge($excludedChildrenCategoriesIdsArray, $excludedChildrenCategoriesIds);
					}
				}
				//Merge categories id from settings and its children,
				$excludedCategoriesIdsArray = array_merge($excludedCategoriesIdsArray, $excludedChildrenCategoriesIdsArray);
			}

			if (count($excludedCategoriesIdsArray)) {
				$allowCatIds = array_diff($allowCatIds, $excludedCategoriesIdsArray);
			}
		}

		if (! empty ( $allowCatIds ) && $useCache) {
			Mage::app ()->getCache ()->save ( serialize ( $allowCatIds ), $cachedKey, array ('SOLRBRIDGE_SOLRSEARCH') );
		}

		return $allowCatIds;
	}

	
	public function prepareCategoriesData($_product, &$docData)
	{
		$store = $this->store;

		//is category name searchable
		$solr_include_category_in_search = Mage::helper('solrsearch')->getSetting('solr_search_in_category');
		//use category for facets
		$use_category_as_facet = Mage::helper('solrsearch')->getSetting('use_category_as_facet');

		//Calculate allow categories
		if( !isset($this->allowCategoryIds[$store->getId()]) )
		{
			$this->allowCategoryIds[$store->getId()] = ($allowCatIds = $this->getAllowCategoriesByStore($store))?$allowCatIds:array();
		}

		$cats = $_product->getCategoryIds();
		$categoryModel = Mage::getModel('catalog/category');
		$tmp = array();
		foreach ($cats as $catid) {
		    $category = $categoryModel->load($catid);
		    $parents = $category->getParentCategories();
		    foreach ($parents as $parent) {
		        if ($parent->getIsAnchor()) {
        		    $tmp[] = $parent->getId();
                }
            }
		}
		$cats = array_unique(array_merge($cats,$tmp));
		$catNames = array();
		$categoryPaths = array();
		$categoryIds = array();
		foreach ($cats as $category_id) {
			$storeid = $this->store->getId();
			if (in_array($category_id, $this->allowCategoryIds[$storeid])) {
				$_cat = Mage::getModel('catalog/category')->setStoreId($storeid)->load($category_id) ;
				if ( $_cat && $_cat->getIsActive() && $_cat->getIncludeInMenu() ) {
					$catNames[] = $_cat->getName().'/'.$_cat->getId();
					$categoryPaths[] = $this->getCategoryPath($_cat, $this->store);
					$categoryIds[] = $_cat->getId();
				}
			}

		}

		if ($use_category_as_facet) {
			$docData['category_facet'] = $catNames;
			$docData['category_text'] = $catNames;
			$docData['category_boost'] = $catNames;
			$docData['category_boost_exact'] = $catNames;
			$docData['category_relative_boost'] = $catNames;
		}
		$docData['category_path'] = $categoryPaths;
		$docData['category_id'] = $categoryIds;

		//Extend text search
		if ($solr_include_category_in_search > 0) {
			$this->textSearch = array_merge($this->textSearch, $catNames);
		}
		return array(
				'catNames' => $catNames,
				'catPaths' => $categoryPaths,
				'catIds'   => $categoryIds,
		);
	}

}