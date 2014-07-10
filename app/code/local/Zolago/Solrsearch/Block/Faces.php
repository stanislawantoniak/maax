<?php
class Zolago_Solrsearch_Block_Faces extends SolrBridge_Solrsearch_Block_Faces
{
    const MODE_CATEGORY = 1;
    const MODE_SEARCH = 2;

    const DEFAULT_RNDERER = "zolagosolrsearch/faces_enum";


    public function _construct() {
        parent::_construct();
        // Override tmpalte
        $this->setTemplate('zolagosolrsearch/standard/searchfaces.phtml');
    }
	
	public function _prepareLayout(){
		
		// Build breadcrumbs
		if($this->getMode()==self::MODE_SEARCH){
			
			$helper = Mage::helper('solrsearch');
			$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
			
			if ($breadcrumbs) {
				
				$root_category_id = Mage::app()->getStore()->getRootCategoryId();
				
				$params = $this->getRequest()->getParams();
				if(isset($params['parent_cat_id'])){
					
					$parent_cat_id = $params['parent_cat_id'];
					
					// Remove fq from params
					// Clicking on breadcrumb link resets all filters
					if(isset($params['fq'])) unset($params['fq']);
					if(isset($params['parent_cat_id'])) unset($params['parent_cat_id']);
					
					$urlParams = array();
			        $urlParams['_current']  = false;
			        $urlParams['_escape']   = true;
			        $urlParams['_use_rewrite']   = true;

		            $urlParams['_query']    = $params;
					
					$search_link = Mage::getUrl('*/*/*', $urlParams);
					
					// Make 'search' breadcrumb a link
					$search_title = $this->__("Search results for: '%s'", $this->helper('catalogsearch')->getQueryText());
				    $breadcrumbs->addCrumb('search', array('label' => $search_title, 'title' => $search_title, 'link' => $search_link));
					
					$category = Mage::getModel('catalog/category')->load($parent_cat_id);
					
					if($category){
						
						// Add breadcrumbs for parent categories
						$parent_category = $category->getParentCategory();
						
						if($parent_category){
							
							$category_breadcrumbs = array();
							
							while($parent_category->getId() != $root_category_id){
								$parent_category_name = $parent_category->getName();
								
								$children_category_ids = $parent_category->getResource()->getChildren($parent_category, true);
								if($children_category_ids){
									
									foreach($children_category_ids as $child_cat_id){
										
										$ids[] = $child_cat_id;
											
									}
								}
								
								$params['fq'] = array('category_id' => $ids);
								$params['parent_cat_id'] = $parent_category->getId();
								
								$urlParams = array();
						        $urlParams['_current']  = false;
						        $urlParams['_escape']   = true;
						        $urlParams['_use_rewrite']   = true;
			
					            $urlParams['_query']    = $params;
								
								$category_link = Mage::getUrl('*/*/*', $urlParams);
								
								$bc = array(
									'key' => $parent_category_name,
									'data' => array('label'=>$helper->__($parent_category_name), 'title'=>$helper->__($parent_category_name), 'link'=>$category_link)
								);
								
								$category_breadcrumbs[] = $bc;
								
							    
								$parent_category = $parent_category->getParentCategory();
							}
							
							// Loop though creadcrumbs and add them to existing breadcrumb
							if(sizeof($category_breadcrumbs) > 0){
								
								$category_breadcrumbs = array_reverse($category_breadcrumbs);
								
								foreach($category_breadcrumbs as $category_breadcrumb){
								    $breadcrumbs->addCrumb($category_breadcrumb['key'], $category_breadcrumb['data']);
								}
								
							}
						}
						
						// Add breadcrumb for current category
					    $breadcrumbs->addCrumb($category->getName(), array('label'=>$helper->__($category->getName()), 'title'=>$helper->__($category->getName())));
					}
				}
	        }
        }
		
		return parent::_prepareLayout();
	}
	
    public function setSolrData($data) {
        $this->solrData = $data;
    }

    protected function _checkFilterDepedncy($filter) {

        if(!($parentAttributeId = $filter->getParentAttributeId())) {
            return true;
        }
        if($parentAttributeCode = $this->getAttributeCodeById($parentAttributeId)) {
            return $this->isFilterActive($parentAttributeCode);
        }
        return false;
    }
		
	protected function getFilterQuery()
    {
    	$filterQuery = parent::getFilterQuery();
		
		if(isset($filterQuery['category_id'])){
			unset($filterQuery['category_id']);
		}
    	return $filterQuery;
    }
	
    public function isFilterActive($attrCode) {
        $filterQuery = $this->getFilterQuery();
        if (isset($filterQuery[$attrCode."_facet"])) {
            return true;
        }
        return false;
    }

    public function getDependAttributes($attributeCode) {
        $depends = array();
        $attributeId = $this->getAttributeIdByCode($attributeCode);
        foreach($this->getFilterCollection() as $fiter) {
            if($fiter->getParentAttributeId()==$attributeId) {
                $depends[] = $this->getAttributeCodeById($fiter->getAttributeId());
            }
        }
        return $depends;
    }
	
	public function getRemoveAllUrl(){
		return Mage::getUrl('*/*', $this->_parseRemoveAllUrl());
	}
	
	public function getRemoveAllJson(){
		return Mage::helper("core")->jsonEncode($this->_parseRemoveAllUrl());
	}
	
	protected function _parseRemoveAllUrl(){
    	$_solrDataArray = $this->getSolrData();

    	$paramss = $this->getRequest()->getParams();

    	if(!isset($paramss['q'])){
	    	if( isset($_solrDataArray['responseHeader']['params']['q']) && !empty($_solrDataArray['responseHeader']['params']['q']) ) {
	        	if (isset($paramss['q']) && $paramss['q'] != $_solrDataArray['responseHeader']['params']['q']) {
	        		$paramss['q'] = $_solrDataArray['responseHeader']['params']['q'];
	        	}
	        }
    	}

        $finalParams = array();
        if(isset($paramss['q'])) {
        	$finalParams['q'] = $paramss['q'];
        }
		
		if(isset($paramss['fq']['category_id'])){
			$finalParams['fq']['category_id'] = $paramss['fq']['category_id'];
		}
		if(isset($paramss['parent_cat_id'])){
			$finalParams['parent_cat_id'] = $paramss['parent_cat_id'];
		}
		
        $urlParams = array();
        //$urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;

        if (isset($finalParams)) {

        	if (Mage::app()->getRequest()->getRouteName() == 'catalog') {
        		if (isset($finalParams['q'])) {
        			unset($finalParams['q']);
        		}
        		if (isset($finalParams['id'])) {
        			unset($finalParams['id']);
        		}
        	}

        	$urlParams['_query']    = $finalParams;
        }

        return $urlParams;
    }

    public function getRemoveFacesUrl($key,$value)
    {
		return Mage::getUrl('*/*/*', $this->_parseRemoveFacesUrl($key, $value));
	}
	
	public function getRemoveFacesJson($key,$value) {
		return Mage::helper('core')->jsonEncode($this->_parseRemoveFacesUrl($key, $value));
	}
	
	
    public function _parseRemoveFacesUrl($key,$value)
    {
        $paramss = $this->getRequest()->getParams();

        $finalParams = $paramss;

        if (!is_array($key)) {
            $key = array($key);
        }

        if (!is_array($value)) {
            $value = array($value);
        }


        foreach ($key as $item)
        {
            if (isset($finalParams['fq'][$item]) && !is_array($finalParams['fq'][$item]) && !empty($finalParams['fq'][$item])) {
                unset($finalParams['fq'][$item]);
                if ($item == 'category' && isset($finalParams['fq'][$item.'_id'])) {
                    unset($finalParams['fq'][$item.'_id']);
                }
            } else if (isset($finalParams['fq'][$item]) && is_array($finalParams['fq'][$item]) && count($finalParams['fq'][$item]) > 0) {
                foreach ($finalParams['fq'][$item] as $k=>$v) {
                    foreach($value as $_value) {
                        if ($v == $_value) {
                            unset($finalParams['fq'][$item][$k]);
                            if ($item == 'category' && isset($finalParams['fq'][$item.'_id']) && isset($finalParams['fq'][$item.'_id'][$k])) {
                                unset($finalParams['fq'][$item.'_id'][$k]);
                            }
                        }
                    }
                }
            }

            // Unset all depended fileds
            foreach($this->getDependAttributes($item) as $depend) {
                if(isset($finalParams['fq'][$depend])) {
                    if((is_array($paramss['fq'][$item]) && count($paramss['fq'][$item])<2) || !is_array($paramss['fq'][$item])) {
                        unset($finalParams['fq'][$depend]);
                    }
                }
            }
        }

        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;

        if (isset($finalParams)) {
            if (Mage::app()->getRequest()->getRouteName() == 'catalog') {
                if (isset($finalParams['q'])) {
                    unset($finalParams['q']);
                }
                if (isset($finalParams['id'])) {
                    unset($finalParams['id']);
                }
            }

            $urlParams['_query']    = $finalParams;
        }

        return $urlParams;
    }


    /**
     * merge special blocks like category, price, typ flag, rating
     * @return array(attrCode=>blockObject, ...)
     */
    public function getFilterBlocks() {

        $solrData = $this->getSolrData();
        $outBlock = $this->_getRegularFilterBlocks($solrData);
		
        $additionalBlocks = array(
                                $this->getCategoryBlock($solrData),
                                $this->getPriceBlock($solrData),
                                $this->getFlagBlock($solrData),
                                $this->getRatingBlock($solrData),
                            );
        $additionalBlocks = array_reverse($additionalBlocks);
        foreach($additionalBlocks as $block) {
            if($block) {
                array_unshift($outBlock, $block);
            }
        }
        foreach($outBlock as $block) {
            $block->setFilterContainer($this);
            $block->setSolrModel($this->solrModel);
        }
        return $outBlock;
    }
	
	/**
	 * @param array $data
	 * 
	 * @return array
	 */
    protected function _processCategoryData($data) {
    	
		$category = NULL;
		
        $out = array();
		$children = array();
		
		// Get all categories as array
		// id => path
		$all_categories = Mage::helper('zolagocatalog/category')->getPathArray();
		
		// Get all category data from Solr		
		$all_data = Mage::helper('zolagosolrsearch')->getAllCatgoryData();
		
		// Get current category
		if($this->getMode()==self::MODE_CATEGORY){
	        $category = $this->getCurrentCategory();
		}
		else{
			$params = $this->getRequest()->getParams();
			
			if(isset($params['parent_cat_id'])) $category = Mage::getModel('catalog/category')->load($params['parent_cat_id']);
		}
		
		// Specify root and parent categories
		$root_category_id = Mage::app()->getStore()->getRootCategoryId();
		$is_root_category = FALSE;
		if($category){
			
			// Display only children categories when in the vendor context
			$_vendor = Mage::helper('umicrosite')->getCurrentVendor();
			if ($_vendor && $_vendor->getId()) {
				
				$vendor_root_category = $_vendor->rootCategory();
				
				if($vendor_root_category->getId() == $category->getId()){
					$is_root_category = TRUE;
				}
				$parent_category = $category;
			}
			else{
				$parent_category = $category->getParentCategory();
			}
		}
		else{
			$category = Mage::getModel('catalog/category')->load($root_category_id);
			$is_root_category = TRUE;
		}
		
		// Get first level categories of current category
        $first_level_categories = $category->getCategories($category->getId(),1);
        foreach ($first_level_categories as $first_level_cat_tree) {
        	
			$first_level_cat = Mage::getModel('catalog/category')->load($first_level_cat_tree->getId());
			$first_level_cat_key = $first_level_cat->getName() . "/" . $first_level_cat->getId();
			
			$children_category_ids = Mage::helper('zolagocatalog/category')->getChildrenIds($first_level_cat->getId());
			
			if($children_category_ids){
				
				$children_total = 0;				
				
				foreach($children_category_ids as $child_cat_id){
					
					$children_total += $this->getCategoryCount($data, $child_cat_id);
					
				}
				
				$children[$first_level_cat_key] = $children_total;
			}
			else{
				
				$children[$first_level_cat_key] = $this->getCategoryCount($data, $first_level_cat->getId(), TRUE);
				
			}
			
        }
		
		// Chosen category		
		$chosen_cat_total = 0;
		if($this->getMode()==self::MODE_CATEGORY){
			
	        foreach ($data as $key=>$val) {
				$chosen_cat_total += (int)$val;
	        }
			
		}
		else{
			
			$children_category_ids = Mage::helper('zolagocatalog/category')->getChildrenIds($category->getId());
			
			if($children_category_ids){
				
				$children_total = 0;				
				
				foreach($children_category_ids as $child_cat_id){
					
					$chosen_cat_total += $this->getCategoryCount($data, $child_cat_id);
					
				}
			}
			else{
				$chosen_cat_total = $this->getCategoryCount($data, $category->getId(), TRUE);
			}
		}
		
		$chosen_key = $category->getName() . "/" . $category->getId();	
		
		$out[$chosen_key] = array(
			'is_root_category' => $is_root_category,
			'total' => $chosen_cat_total,
			'children' => $children
		);
		// Sibling categories		
		if(!$is_root_category){
			
			$siblings = $parent_category->getChildrenCategories();
		
			foreach($siblings as $sibling_cat){
				
				$sibling_total = 0;
				
				// Get siblings of parent category 
				// but not selected category
				if($sibling_cat->getId() != $category->getId()){
					$current_key = $sibling_cat->getName() . "/" . $sibling_cat->getId();
					
					$sibling_children_category_ids = Mage::helper('zolagocatalog/category')->getChildrenIds($sibling_cat->getId());
					if($sibling_children_category_ids){
						
						foreach($sibling_children_category_ids as $sibling_children_category_id){
							
							$sibling_total += $this->getCategoryCount($all_data, $sibling_children_category_id);
							
						}
					}
					else{
						
						$sibling_total = $this->getCategoryCount($all_data, $sibling_cat->getId());
						
					}
					$out[$current_key] = $sibling_total;
				}
			}
		}
		
        return $out;
    }
	
	/**
	 * @param array $data 
	 * @param int $category_id
	 * @param boolean $break Break when cagegory is found
	 * 
	 * @return int
	 */
	public function getCategoryCount($data, $category_id, $break = FALSE){
		
		$count = 0;
		foreach($data as $key => $value){
								
			$items = explode('/',$key);
			$current_category_id = (int)$items[count($items)-1];
			
			if($category_id == $current_category_id){
				
				$count += $value;
				
				if($break){
					break;
				}
			}
		}
		
		return $count;
	}
	
    public function getCategoryBlock($solrData) {
    	
        $facetFileds = array();
        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }
        if(isset($facetFileds['category_facet'])) {
            $data = $facetFileds['category_facet'];
            if($this->getSpecialMultiple()) {
                $data = $this->_prepareMultiValues('category_facet', $data);
            }
            $data = $this->_processCategoryData($data);
            $block = $this->getLayout()->createBlock($this->_getCategoryRenderer());
            $block->setParentBlock($this);
            $block->setAllItems($data);
            $block->setFacetKey("category_facet");
            return $block;
        }
        return null;
    }

    public function getPriceBlock($solrData) {
        if($this->getMode()==self::MODE_CATEGORY&& !$this->getCurrentCategory()->getUsePriceFilter()) {
            return null;
        }
        $block = $this->getLayout()->createBlock($this->_getPriceRenderer());
        $block->setParentBlock($this);
        return $block;
    }

    public function getFlagBlock($solrData) {
        if($this->getMode()==self::MODE_CATEGORY&& !$this->getCurrentCategory()->getUseFlagFilter()) {
            return null;
        }
		
		//Emulate Multi-Filter Behavior - Start
		$cleanSolrData = $this->getCleanFlagFacetData();
		//Emulate Multi-Filter Behavior - End
		
        $facetFileds		= array();
        $bestsellerFacet	= array();
        $isNewFacet			= array();
        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }

        if (isset($facetFileds['is_bestseller_facet'][Mage::helper('core')->__('Yes')])) {
            $bestsellerFacet	= array(Mage::helper('zolagosolrsearch')->__('Bestseller') => $facetFileds['is_bestseller_facet'][Mage::helper('core')->__('Yes')]);
        }

        if (isset($facetFileds['is_new_facet'][Mage::helper('core')->__('Yes')])) {
            $isNewFacet			= array(Mage::helper('zolagosolrsearch')->__('New') => $facetFileds['is_new_facet'][Mage::helper('core')->__('Yes')]);
        }
		
        if(isset($facetFileds['product_flag_facet'])) {
            $data = $facetFileds['product_flag_facet'];
			
            if($this->getSpecialMultiple()) {
                $data = $this->_prepareMultiValues('product_flag_facet', $data);
            }
			$data = array_merge($data, $bestsellerFacet, $isNewFacet, $cleanSolrData);
			//Remove Boolean "False" Values
			if (isset($data[Mage::helper('core')->__('No')])) {
				unset($data[Mage::helper('core')->__('No')]);
			}
			
			ksort($data);
			
            $block = $this->getLayout()->createBlock($this->_getFlagRenderer());
            $block->setParentBlock($this);
            $block->setAllItems($data);
            $block->setAttributeCode("product_flag");
            $block->setFacetKey("product_flag_facet");
            return $block;
        }
    }

    public function getRatingBlock($solrData) {
        if($this->getMode()==self::MODE_CATEGORY&& !$this->getCurrentCategory()->getUseReviewFilter()) {
            return null;
        }
        $facetFileds = array();
        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }
        if(isset($facetFileds['product_rating_facet'])) {
            $data = $facetFileds['product_rating_facet'];

            if($this->getSpecialMultiple()) {
                $data = $this->_prepareMultiValues('product_rating_facet', $data);
            }
            if(isset($data['No rating'])) {
                unset($data['No rating']);
            }
			
			//Remove Boolean "False" Values
			if (isset($data[Mage::helper('core')->__('No')])) {
				unset($data[Mage::helper('core')->__('No')]);
			}			
			
            $block = $this->getLayout()->createBlock($this->_getRatingRenderer());
            $block->setParentBlock($this);
            $block->setAllItems($data);
            $block->setAttributeCode("product_rating");
            $block->setFacetKey("product_rating_facet");
            return $block;
        }
    }


    /**
     * @return string
     */
    protected function _getRatingRenderer() {
        return "zolagosolrsearch/faces_rating";
    }
    /**
     * @return string
     */
    protected function _getFlagRenderer() {
        return "zolagosolrsearch/faces_flag";
    }
    /**
     * @return string
     */
    protected function _getPriceRenderer() {
        return "zolagosolrsearch/faces_price";
    }
    /**
     * @return string
     */
    protected function _getCategoryRenderer() {
        return "zolagosolrsearch/faces_category";
    }
    /**
     * @return boolean
     */
    public function getSpecialMultiple() {
        return Mage::helper('solrsearch')->getSetting('allow_multiple_filter') > 0;
    }

    /**
     * @return array(attrCode=>blockObject, ...)
     */
    protected function _getRegularFilterBlocks(array $solrData) {

        $priceFieldName = Mage::helper('solrsearch')->getPriceFieldName();
        $facetFileds = array();
        $sorted = array();

        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }
		
        //Ignore the price_decimal
        if (isset($facetFileds[$priceFieldName])) {
            unset($facetFileds[$priceFieldName]);
        }
        
		//Ignore the category
		if(isset($facetFileds['category_facet'])){
			unset($facetFileds['category_facet']);
		}

        foreach($facetFileds as $key=>$data) {

            $attrCode = $this->_extractAttributeCode($key);
            $block = null;
            $sortOrder = 0;

            switch ($attrCode) {
                // Skip special facets
				case "category_path":
				case "category_id":
				case "product_flag":
				case "is_new":
				case "is_bestseller":
				case "product_rating":
					continue 2;
				break;
			
				// Skip vendor facet in vendor scope
				case "udropship_vendor":
					if($this->getVendor()){
						continue 2;
					}
				break;
            }
            // In category mode
            if($this->getMode()==self::MODE_CATEGORY) {
				
                $filter = $this->getFilterByAttribute($attrCode);

                // Skip attribs with no custom filter
                if(!$filter || !$filter->getId()) {
                    continue;
                }

                // Check is filter depended - if not - skip
                if($filter->getParentAttributeId() && !$this->_checkFilterDepedncy($filter)) {
                    continue;
                }
				
                // Is multiple values
                if($filter->getShowMultiple()) {
                    $data = $this->_prepareMultiValues($key, $data);
                }

                if(count($data)) {
                    $renderer = $this->getDefaultRenderer();
                    if($filter->getFrontendRenderer()) {
                        $renderer = $filter->getFrontendRenderer();
                    }

                    $block = $this->getLayout()->createBlock($renderer);
                    /* @var $block Zolago_Solrsearch_Block_Faces_Abstract */


                    if(! ($block instanceof  Zolago_Solrsearch_Block_Faces_Abstract)) {
                        throw new Exception("Unknow block type $renderer");
                    }

                    $block->setFilterModel($filter);
                    $sortOrder = $filter->getSortOrder();
                }
            } else {
                // Search mode - unknow category filters
                if(count($data)) {
                    $block= $this->getLayout()->createBlock(
                                $this->getDefaultRenderer()
                            );
                }
            }

            if($block) {
                $block->setAllItems($data);
                $block->setAttributeCode($attrCode);
                $block->setFacetKey($key);
                if(!isset($sorted[$sortOrder])) {
                    $sorted[$sortOrder] = array();
                }
                $sorted[$sortOrder][] = $block;
            }
        }

        ksort($sorted);

        $blocks = array();
        foreach($sorted as $ordered) {
            foreach($ordered as $block) {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    protected function _extractAttributeCode($facet) {
        return preg_replace("/_facet$/", "", $facet);
    }


    /**
     * @return string
     */
    public function getDefaultRenderer() {
        return self::DEFAULT_RNDERER;
    }

    /**
     * @return int
     */
    public function getMode() {
    	
		$queryText = Mage::helper('solrsearch')->getParam('q');
        if($this->getCurrentCategory() && !Mage::registry('current_product') && !$queryText) {
            return self::MODE_CATEGORY;
        }
        return self::MODE_SEARCH;
    }

    /**
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory() {
		if(Mage::registry('current_category')){
			return Mage::registry('current_category');
		}
		return  Mage::registry('vendor_current_category');
    }

    /**
     * @return Zolago_Catalog_Model_Resource_Category_Filter_Collection
     */
    public function getFilterCollection() {
        if(!$this->hasData("filter_collection")) {
            $collection = Mage::getResourceModel("zolagocatalog/category_filter_collection");
            /* @var $collection Zolago_Catalog_Model_Resource_Category_Filter_Collection */
            $collection->joinAttributeCode();
            if($this->getCurrentCategory()) {
                $collection->addCategoryFilter($this->getCurrentCategory());
            }
            $this->setData("filter_collection", $collection);

        }
        return $this->getData("filter_collection");
    }


    public function getFilterByAttribute($attrCode) {
        if(!$this->hasData("filter_by_attribute")) {
            $attributeByCode = array();
            foreach($this->getFilterCollection() as $filter) {
                $attributeByCode[$filter->getAttributeCode()] = $filter;
            }
            $this->setData("filter_by_attribute", $attributeByCode);
        }
        return $this->getData("filter_by_attribute", $attrCode);
    }

    public function getAttributeCodeById($attrCode) {
        if(!$this->hasData("attribute_code_by_id")) {
            $attributeCodeById = array();
            foreach($this->getFilterCollection() as $filter) {
                $attributeCodeById[$filter->getAttributeId()] = $filter->getAttributeCode();
            }
            $this->setData("attribute_code_by_id", $attributeCodeById);
        }
        return $this->getData("attribute_code_by_id", $attrCode);
    }

    public function getAttributeIdByCode($attrCode) {
        if(!$this->hasData("attribute_id_by_code")) {
            $attributeCodeById = array();
            foreach($this->getFilterCollection() as $filter) {
                $attributeCodeById[$filter->getAttributeCode()] = $filter->getAttributeId();
            }
            $this->setData("attribute_id_by_code", $attributeCodeById);
        }
        return $this->getData("attribute_id_by_code", $attrCode);
    }

    /**
     * @return SolrBridge_Solrsearch_Model_Solr
     */
    protected function _getHelpedSolrModel() {
        if(!$this->getData("helped_solr_model")) {
            $this->setData("helped_solr_model", Mage::getModel('solrsearch/solr'));
        }
        return $this->getData("helped_solr_model");
    }




    protected function _prepareMultiValues($facetkey, $fallbackData=array()) {
        // @todo check is filter already active?
        // If not dont try re-request
        //
        // Remove this key from query params\
        $req = Mage::app()->getRequest();

        $oldParams = $req->getParams();
        $params = $oldParams;

        $paramKey = $this->_extractAttributeCode($facetkey);
		
        if(isset($params['fq'][$paramKey])) {
            unset($params['fq'][$paramKey]);
        }

        $filters = $this->getFilterQuery();


        // Force unset category id
        if($paramKey=="category_path") {
            if(!isset($filters['category_id'])) {
                return $fallbackData;
            }
            if(isset($params['fq']['category_id'])) {
                unset($params['fq']['category_id']);
            }
            if(isset($params['fq']['category'])) {
                unset($params['fq']['category']);
            }
            // No data changed
        } 
        elseif(!isset($filters[$facetkey])) {
            return $fallbackData;
        }
        try {
            $model = $this->_getHelpedSolrModel();
            $queryText = Mage::helper('solrsearch')->getParam('q');

            $req->setParams($params);
            $result = $model->query($queryText);
            $req->setParams($oldParams);
            if(isset($result['facet_counts']['facet_fields'][$facetkey])) {
                return $result['facet_counts']['facet_fields'][$facetkey];
            }

        } catch(Exception $e) {
            Mage::logException($e);
        }

        return $fallbackData;

    }
	
	/**
	 * @param array $params params to be set
	 * @param array $paramss current params (if not set will take current params from current request)
	 */
    public function getFacesUrl($params=array(), $paramss = NULL)
    {
        return Mage::getUrl('*/*/*', $this->_parseQueryData($params, $paramss));
	}
	
	/**
	 * @param array $params params to be set
	 * @param array $paramss current params (if not set will take current params from current request)
	 */
	public function getFacesJson($params=array(), $paramss = NULL){
		return Mage::helper("core")->jsonEncode($this->_parseQueryData($params, $paramss));
	}
		
	/**
	 * @param array $params params to be set
	 * @param array $paramss current params (if not set will take current params from current request)
	 */
    protected function _parseQueryData($params=array(), $paramss = NULL)
    {
        $_solrDataArray = $this->getSolrData();
		
		if(!$paramss) $paramss = $this->getRequest()->getParams();

        if( isset($_solrDataArray['responseHeader']['params']['q']) && !empty($_solrDataArray['responseHeader']['params']['q']) ) {
            if (isset($paramss['q']) && $paramss['q'] != $_solrDataArray['responseHeader']['params']['q']) {
                $paramss['q'] = $_solrDataArray['responseHeader']['params']['q'];
            }
        }
		
        foreach ($params as $key=>$item) {
            $key = trim($key);
			
            if( in_array($key, array('min', 'max')) ) {
                if (isset($paramss[$key])) {
                    unset($paramss[$key]);
                    $finalParams = array_merge_recursive($params, $paramss);
                }
            }

            if ($key == 'fq') {
                foreach ($item as $k=>$v) {
                    if (isset($paramss[$key][$k]) && $v == $paramss[$key][$k]) {

                    } else {
                        if( $k == 'price' && isset($paramss[$key][$k])/* || $k == 'category' || $k == 'category_id'*/) {
                            unset($paramss[$key][$k]);
                        }

                        $finalParams = array_merge_recursive($params, $paramss);

                    }
                }
            }
        }

        if (isset($finalParams['p'])) {
            $finalParams['p'] = 1;
        }
		
        if (isset($finalParams['fq'])) {
            if(isset($finalParams['fq']['category_id']) && is_array($finalParams['fq']['category_id'])) {
                $finalParams['fq']['category_id'] = array_unique($finalParams['fq']['category_id']);
            }
        }
		
        $urlParams = array();
        $urlParams['_current']  = false;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        if (isset($finalParams)) {

            if (Mage::app()->getRequest()->getRouteName() == 'catalog') {
                if (isset($finalParams['q'])) {
                    unset($finalParams['q']);
                }
                if (isset($finalParams['id'])) {
                    unset($finalParams['id']);
                }
            }

            $urlParams['_query']    = $finalParams;
        }

        return $urlParams;
    }
	
	/**
	 * @return Unirgy_Dropship_Model_Vendor|null
	 */
	public function getVendor() {
		if(!$this->hasData("vendor")){
			$this->setData("vendor", Mage::helper('umicrosite')->getCurrentVendor());
		}
		return $this->getData("vendor");
	}
	
	public function getCleanFlagFacetData()
	{
		$cleanSolrData = $this->solrModel->prepareCleanFlagQueryData()->execute();

		$facetCleanFileds	= array();
		$productFlagFacet	= array();
        $bestsellerFacet	= array();
        $isNewFacet			= array();
		
        if (isset($cleanSolrData['facet_counts']['facet_fields']) && is_array($cleanSolrData['facet_counts']['facet_fields'])) {
            $facetCleanFileds = $cleanSolrData['facet_counts']['facet_fields'];
        }
		
        if (isset($facetCleanFileds['product_flag_facet'])) {
            $productFlagFacet	= $facetCleanFileds['product_flag_facet'];
        }
		
		//Remove Boolean "False" Values
		if (isset($productFlagFacet[Mage::helper('core')->__('No')])) {
			unset($productFlagFacet[Mage::helper('core')->__('No')]);
		}		
		
		if (!isset($productFlagFacet[Mage::helper('zolagocatalog')->__('Promotion')])) {
			$productFlagFacet[Mage::helper('zolagocatalog')->__('Promotion')] = 0;
		}
		
		if (!isset($productFlagFacet[Mage::helper('zolagocatalog')->__('Sale')])) {
			$productFlagFacet[Mage::helper('zolagocatalog')->__('Sale')] = 0;
		}		
		
        if (isset($facetCleanFileds['is_bestseller_facet'][Mage::helper('core')->__('Yes')])) {
            $bestsellerFacet	= array(Mage::helper('zolagosolrsearch')->__('Bestseller') => $facetCleanFileds['is_bestseller_facet'][Mage::helper('core')->__('Yes')]);
        } else {
			$bestsellerFacet	= array(Mage::helper('zolagosolrsearch')->__('Bestseller') => 0);
		}

        if (isset($facetCleanFileds['is_new_facet'][Mage::helper('core')->__('Yes')])) {
            $isNewFacet			= array(Mage::helper('zolagosolrsearch')->__('New') => $facetCleanFileds['is_new_facet'][Mage::helper('core')->__('Yes')]);
        } else {
			$isNewFacet	= array(Mage::helper('zolagosolrsearch')->__('New') => 0);
		}
		
		return array_merge($productFlagFacet, $bestsellerFacet, $isNewFacet);
	}
}