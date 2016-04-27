<?php

class Zolago_Solrsearch_Block_Faces_Category extends Zolago_Solrsearch_Block_Faces_Abstract
{
    public function __construct()
    {
        $this->setTemplate('zolagosolrsearch/standard/searchfaces/category.phtml');
    }


    
    /**
     * override (need only val)
     */

    public function getAllItems() {
         return Mage_Core_Block_Template::getAllItems();
    }

    public function getParsedCategories() {
        return $this->getParentBlock()->parseCategoryPathFacet($this->getAllItems());
    }

    /**
     * @param null $facetCode
     * @return string
     */
    public function getFacetLabel($facetCode=null) {
        if(Mage::getModel('zolagosolrsearch/catalog_product_list')->getMode() === Zolago_Solrsearch_Model_Catalog_Product_List::MODE_CATEGORY) {
            return Mage::helper('catalog')->__('Category');
        } else {
            return Mage::helper('catalog')->__('narrow results');//search mode
        }
    }
    
    /**
     * preparing list of attributes used in filters
     *
     * @return array
     */

    protected function _getAttributeCodesForFilter() {
        $lambda = function() {
            $collection = Mage::getResourceModel("zolagocatalog/category_filter_collection");
            /* @var $collection Zolago_Catalog_Model_Resource_Category_Filter_Collection */
            $collection->joinAttributeCode();
            $result = array();
            foreach ($collection as $item) {
                $result[$item->getCategoryId()][] = $item->getAttributeCode();
            }
            return serialize($result);
        };
        $out = Mage::helper('zolagocommon')->getCache('attribute_codes_for_filter',self::CACHE_GROUP,$lambda,array());
        return unserialize($out);
    }
    
    /**
     * returns list of attributes used in filters by category id
     *
     * @param int $categoryId
     * @return 
     */

    public function getFilterCollection($categoryId)
    {
        if (!$this->hasData('all_filter_collection')) {
            $result = $this->_getAttributeCodesForFilter();
            $this->setData('all_filter_collection', $result);
        }
        $result = $this->getData('all_filter_collection');

        $collection = array();
        if (isset($result[$categoryId])) {
            $collection = $result[$categoryId];
        } else {
            //try to get filters from related category
            if (!$this->hasData('category_related_' . $categoryId)) {
                $this->setData('category_related_' . $categoryId, Mage::getResourceModel("zolagocatalog/category")->getRelatedId($categoryId));
            }
            $related = $this->getData('category_related_' . $categoryId);

            if (isset($result[$related]))
                $collection = $result[$related];

        }
        return $collection;
    }
    
    
    
    /**
     * return rewrite path to category by category id  (using cache)
     *
     * @param int $categoryId
     * @return string
     */

    protected function _getPathById($categoryId) {
        $lambda = function($params) {
            return Mage::getModel('core/url_rewrite')->loadByIdPath('category/' . $params['categoryId'])->getRequestPath();
        };
        return Mage::helper('zolagocommon')->getCache('category_rewrite_'.$categoryId,self::CACHE_GROUP,$lambda,array('categoryId' => $categoryId));
    }
    
    /**
     * preparing category url 
     *
     * @param array $item
     * @param array $param
     * @return string
     */

    public function getItemUrl($item,$param = array()) {

        if (!isset($param['categoryId'])) {
            $array = $this->pathToArray($item);
            $last = array_pop($array);
            $category_id = $last['id'];
        } else {
            $category_id = $param['categoryId'];
        }

        $params = $this->getRequest()->getParams();
        // keep only existing filters
        $codeList = $this->getFilterCollection($category_id);
        $codeList = array_merge($codeList,array('price','flags','product_rating', 'campaign_info_id', 'campaign_regular_id'));
        if (isset($params['fq'])) {
            foreach ($params['fq'] as $key => $val) {
                if (!in_array($key,$codeList)) {
                    unset($params['fq'][$key]);
                }
            }
        }

        /** @var Zolago_Modago_Block_Solrsearch_Faces $parentBlock */
        $parentBlock = $this->getParentBlock();
        if($parentBlock->getMode() == Zolago_Solrsearch_Block_Faces::MODE_CATEGORY) {
            if(isset($params['id'])) unset($params['id']);
            $tmp = array(
                       '_direct' => $this->_getPathById($category_id),
                       '_query' => $this->processFinalParams($params)
                   );
            $facetUrl = Mage::getUrl('',$tmp);
        }
        else
        {
            $facetUrl = $this->getFacesUrl(
                            array('scat' => $category_id)
                        );
        }
        return $facetUrl;
    }
/*  UNUSED
    public function isItemActive($item) {
        $filterQuery = $this->getFilterQuery();
        if(!isset($filterQuery["category_id"])) {
            return false;
        }

        $array = $this->pathToArray($item);

        foreach($array as $_item) {
            if(in_array($_item['id'], $filterQuery["category_id"])) {
                return true;
            }
        }
        return false;
    }
*/
    public function getItemText($item) {
        $array = explode('/', $item);
        $count = count($array);
        return $array[$count - 2];
    }

    public function pathToArray($path) {
        $chunks = explode('/', $path);
        $result = array();
        for ($i = 0; $i < sizeof($chunks) - 1; $i+=2)
        {
            $result[] = array('id' => $chunks[($i+1)], 'name' => $chunks[$i]);
        }

        return $result;
    }

    /**
     * Hide current category
     * @param type $item
     * @param type $count
     * @return boolean
     */

    public function getCanShowItem($count) {
        return ($count > 0) ? true : false;
    }

    public function getCanShow() {
        return true; // category always visible
    }

	/**
	 * @param array $data
	 * @param bool $show_brothers if true solr gets two queries (first about current category, second about brothers), if false is only one query
	 * @return array
	 */
    public function processCategoryData($data,$show_brothers = true)
    {   
        $lambda = function($category) {             
            /** @var Zolago_Catalog_Model_Category $modelCC */
            $modelCC = Mage::getModel('catalog/category');
            /** @var  Mage_Catalog_Model_Resource_Category_Tree $tree */
            
            $tree = $modelCC->getTreeModel()->load();
            $categoryChildren = $tree->getChildren($category['categoryId'],false);
            return serialize($categoryChildren);
        };
        // Specify root and parent categories
        $rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
        // Current category
        $category = Mage::registry('current_category');

        // Checking is root category
        $isRootCategory = false;
        if (!$category && !$category->getId()) {
            $modelCC = Mage::getModel('catalog/category');
            $category = $modelCC->load($rootCategoryId);
            $isRootCategory = TRUE;
        }
        if ($category->getId() == $rootCategoryId) {
            $isRootCategory = true;
        }
        
        /*
         * Convert $data to array( [category_id] => array(key, count)
         * where key is like: Bielizna/10
         * where count is int
         */
        $_data = array();
        foreach ($data as $key => $val) {
            $items = explode('/', $key);
            $catId = (int)$items[count($items)-1];
            $_data[$catId] = array('key' => $key, 'value' => $val);
        }
        $children = array();
        $categoryId = $category->getId();
        // get children using cache
        $categoryChildren = unserialize(Mage::helper('zolagocommon')->getCache('category_children_'.$categoryId,self::CACHE_GROUP,$lambda,array('categoryId' => $categoryId)));
        foreach ($categoryChildren as $id) {            
            if (isset($_data[$id])) {                
                $key = $_data[$id]['key'];
                $children[$key] = array (
                    'count' => $_data[$id]['value'],
                    'url' => $this->getItemUrl($key,array('categoryId' => $id)),
                    'text' => $this->getItemText($key),
                );
            } 
        }
		$chosen_key = $category->getName() . "/" . $category->getId();
		return array(
			'is_root_category' => $isRootCategory,
			'total' => isset($_data[$category->getId()]) ? $_data[$category->getId()]['value'] : array_sum($children),
			'children' => $children,
			'params' => $this->getItemJson($chosen_key),
			'text' => $this->getItemText($chosen_key),
		);
    }
	
}