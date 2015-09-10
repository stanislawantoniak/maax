<?php
/**
 * url rewrite resource 
 */
class GH_Rewrite_Model_Resource_Rewrite extends Mage_Core_Model_Resource_Url_Rewrite {

    protected $categoryPath = null;    
    /**
     * path for filters
     */

    protected function getCategoryPath($categoryId) {
        if (!isset($this->categoryPath[$categoryId])) {
            // cache
            $cache = Mage::app()->getCache();
            $list = $cache->load('filter_url_list');
            if (empty($list)) {
                // load full table
	            /** @var GH_Rewrite_Model_Resource_Url_Collection $collection */
                $collection = Mage::getModel('ghrewrite/url')->getCollection();
                $collection->joinRewriteUrl();
                $path = array();
                foreach ($collection->getData() as $item) {
                    $path[$item['store_id']][$item['category_id']][$item['target_path']] = $item['request_path'];
                }
                $list = serialize($path);
                /** @var Zolago_Modago_Helper_Category $helper */
                $helper = Mage::helper("zolagomodago/category");
                $time = $helper->getCacheLifeTime();
                $cache->save($list,'filter_url_list',array(Zolago_Modago_Helper_Category::CACHE_TAG), $time);
            }
            $this->categoryPath = unserialize($list);
        }
        $storeId = Mage::app()->getStore()->getId();
	    $returnUrl = empty($this->categoryPath[$storeId][$categoryId]) ? null : $this->categoryPath[$storeId][$categoryId];

        return $returnUrl;
    }
    public function loadByRequestPathForFilters($categoryId,$rawUrl) {
        if ($linkList = $this->getCategoryPath($categoryId)) {
            return empty($linkList[$rawUrl])? null:$linkList[$rawUrl];
        }
        return null;

        if (!is_array($path)) {
            $path = array($path);
        }

        $pathBind = array();
        foreach ($path as $key => $url) {
            $pathBind['path' . $key] = $url;
        }
        // Form select
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where('request_path IN (:' . implode(', :', array_flip($pathBind)) . ')')
            ->where('store_id IN(?)', array(Mage_Core_Model_App::ADMIN_STORE_ID, (int)$object->getStoreId()));

        $items = $adapter->fetchAll($select, $pathBind);

        // Go through all found records and choose one with lowest penalty - earlier path in array, concrete store
        $mapPenalty = array_flip(array_values($path)); // we got mapping array(path => index), lower index - better
        $currentPenalty = null;
        $foundItem = null;
        foreach ($items as $item) {
            if (!array_key_exists($item['request_path'], $mapPenalty)) {
                continue;
            }
            $penalty = $mapPenalty[$item['request_path']] << 1 + ($item['store_id'] ? 0 : 1);
            if (!$foundItem || $currentPenalty > $penalty) {
                $foundItem = $item;
                $currentPenalty = $penalty;
                if (!$currentPenalty) {
                    break; // Found best matching item with zero penalty, no reason to continue
                }
            }
        }

        // Set data and finish loading
        if ($foundItem) {
            $object->setData($foundItem);
        }

        // Finish
        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }
}