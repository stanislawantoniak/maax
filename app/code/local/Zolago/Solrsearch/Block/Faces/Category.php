<?php

class Zolago_Solrsearch_Block_Faces_Category extends Zolago_Solrsearch_Block_Faces_Abstract
{
    public function __construct()
    {
        $this->setTemplate('zolagosolrsearch/standard/searchfaces/category.phtml');
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
    public function getFilterCollection($categoryId) {
        if (!$this->hasData('all_filter_collection')) {
            $list = $this->getAllItems();
            if (is_array($list)) {
                $items = array_pop($list);
            } else {
                $items = array();
            }
            $children = isset($items['children'])? $items['children']:array();
            $categoryList = array();
            foreach ($children as $child => $out) {
                $tmp = $this->pathToArray($child);
                if (isset($tmp[0]['id'])) {
                    $categoryList[] = $tmp[0]['id'];
                }
            }
            $collection = Mage::getResourceModel("zolagocatalog/category_filter_collection");
            /* @var $collection Zolago_Catalog_Model_Resource_Category_Filter_Collection */
            $collection->joinAttributeCode();
            $collection->addCategoryFilter($categoryList);
            $result = array();
            foreach ($collection as $item) {
                $result[$item->getCategoryId()][] = $item->getAttributeCode();
            }
            $this->setData('all_filter_collection',$result);
        }
        $result = $this->getData('all_filter_collection');        
        return isset($result[$categoryId])? $result[$categoryId]:array();
    }
    public function getItemUrl($item,$param = array()) {

        $array = $this->pathToArray($item);
        $last = array_pop($array);
        $categoty_id = $last['id'];
        $category = Mage::getModel('catalog/category')->load($categoty_id);

        $params = $this->getRequest()->getParams();
        // keep only existing filters
        $codeList = $this->getFilterCollection($categoty_id);
        if (isset($params['fq'])) {
            foreach ($params['fq'] as $key => $val) {
                if (in_array($key,array('price','flags','product_rating', 'campaign_info_id', 'campaign_regular_id'))) continue; // price is always
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
                       '_direct' => Mage::getModel('core/url_rewrite')->loadByIdPath('category/' . $category->getId())->getRequestPath(),
                       '_query' => $this->processFinalParams($params)
                   );
            $facetUrl = Mage::getUrl('',$tmp);
        }
        else
        {

            $names = array();
            $ids   = array();

            $names[] = $last['name'];
            $parent_category_id = $last['id'];
            // $ids[] = $last['id'];
            // $children_category_ids = $category->getResource()->getChildren($category, true);
            // if($children_category_ids){
//
            // foreach($children_category_ids as $child_cat_id){
//
            // $ids[] = $child_cat_id;
//
            // }
            // }
            // // All category links need to have links to fresh categories
            // // No appending to current params
            // if(isset($params['fq']['category_id'])) unset($params['fq']['category_id']);
            // if(isset($params['parent_cat_id'])) unset($params['parent_cat_id']);
//
            // //Remove scat parameter in order to display siblings in layered navigation
            // if(isset($params['scat'])) unset($params['scat']);

            $facetUrl = $this->getFacesUrl(
                            array('scat' => $parent_category_id)
                        );

            // if($this->isItemActive($item)){
            // $facetUrl = $this->getRemoveFacesUrl("category", array($last['name']));
            // }



//            if(isset($params['id'])) unset($params['id']);
//            $tmp = array(
//                '_direct' => 'search/index/index/',
//                '_query' => $this->processFinalParams($params)
//            );
//            $facetUrl = Mage::getUrl('',$tmp);
        }

        return $facetUrl;
    }

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

    public function getItemText($item) {
        $array = $this->pathToArray($item);
        $last = array_pop($array);
        return $last['name'];
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

    public function getCanShowItem($item, $count) {
        return ($count > 0) ? true : false;
    }

    // public function getCanShow() {
    // if($this->getParentBlock()->getMode()==Zolago_Solrsearch_Block_Faces::MODE_CATEGORY){
    // $category = $this->getParentBlock()->getCurrentCategory();
    // $all = $this->getAllItems();
    // // One item with couurent cat
    // if(count($all)==1){
    // list($item, $count) = each($all);
    // $array = $this->pathToArray($item);
    // if($array){
    // $last = array_pop($array);
    // if(isset($last['id']) && $last['id']==$category->getId()){
    // return false;
    // }
    // }
    // }
    // }
//
    // return parent::getCanShow();
    // }

}