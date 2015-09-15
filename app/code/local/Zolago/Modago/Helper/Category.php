<?php

/**
 * For category caching
 *
 * Class Zolago_Modago_Helper_Category
 */
class Zolago_Modago_Helper_Category extends Mage_Core_Helper_Abstract
{
    const CACHE_TAG         = 'ZOLAGO_CATEGORY_CACHE';
    const CACHE_LIFE_TIME   =  900; // 15 min

    /**
     * Load from cache by key
     *
     * @param $key
     * @param bool $unserialize
     * @return false|mixed
     */
    public function loadFromCache($key, $unserialize = true) {
        $cacheData = Mage::app()->getCache()->load($key);
        if ($unserialize) {
            return unserialize($cacheData);
        }
        return $cacheData;
    }

    /**
     * Get unified prefix for category cache key
     *
     * @param $name
     * @return string
     */
    public function getPrefix($name) {
        return self::CACHE_TAG . '_' . $name;
    }

    /**
     * Check whether to use cache for category cache
     *
     * @return bool
     */
    public function useCache() {
        return Mage::app()->useCache('category_cache');
    }

    /**
     * Save to cache by key
     * Data will be serialized
     *
     * @param string $key
     * @param array $data
     */
    public function _saveInCache($key, $data) {
        $cache = Mage::app()->getCache();
        $oldSerialization = $cache->getOption("automatic_serialization");
        $cache->setOption("automatic_serialization", true);
        $cache->save($data, $key, array(self::CACHE_TAG), $this->getCacheLifeTime());
        $cache->setOption("automatic_serialization", $oldSerialization);
    }

    /**
     * Return time in seconds
     *
     * @return int
     */
    public function getCacheLifeTime() {
        $time = (int)Mage::getStoreConfig("zolagomodago_catalog/zolagomodago_cataloglisting/category_cache_lifetime", Mage::app()->getStore());
        if (!$time || empty($time)) {
            return self::CACHE_LIFE_TIME;
        }
        return $time;
    }
}
