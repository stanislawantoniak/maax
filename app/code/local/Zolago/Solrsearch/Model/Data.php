<?php
/**
 * rewrited solrsearch data model
 */
class Zolago_Solrsearch_Model_Data extends SolrBridge_Solrsearch_Model_Data {
	
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
    		    $tmp[] = $parent->getId();
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


>>>>>>> 07901b175755fd7e0c4145696e3dd3157bfe47f2
}