<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Category extends Magpleasure_Common_Model_Resource_Abstract
{
    public function _construct()
    {    
        $this->_init('mpblog/category', 'category_id');
        $this->setUseUpdateDatetimeHelper(true);
    }

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);

        if (!$object->getUrlKey() && ($object->getStatus() != Magpleasure_Blog_Model_Category::STATUS_DISABLED)){
            $object->setStatus(Magpleasure_Blog_Model_Category::STATUS_DISABLED);
            Mage::throwException($this->_helper()->__("Category can be disabled only without Url Key."));
        }

        if (!$this->_validateUrlKey($object) && ($object->getStatus() != Magpleasure_Blog_Model_Category::STATUS_DISABLED)){
            $object->setStatus(Magpleasure_Blog_Model_Category::STATUS_DISABLED);
            Mage::throwException($this->_helper()->__("Category '%s' can be disabled only. Some category has same Url Key for the same Store View.", $object->getName()));
        }

        if (!Mage::app()->isSingleStoreMode()){

            # Validate Stores
            $stores = $object->getStores();
            if (!$stores || !is_array($stores) || !count($stores)){
                Mage::throwException($this->_helper()->__("Category '%s' can't be saved. It need to be assigned to any Store View.", $object->getName()));
            }
        }

    }

    protected function _validateUrlKey($object)
    {
        /** @var $posts Magpleasure_Blog_Model_Mysql4_Category_Collection */
        $posts = Mage::getModel('mpblog/category')->getCollection();
        $posts
            ->addStoreData()
            ->addStoreFilter($object->getStores())
            ->addFieldToFilter('status', array('neq'=>Magpleasure_Blog_Model_Category::STATUS_DISABLED))
            ->addFieldToFilter('url_key', $object->getUrlKey())
            ->addFieldToFilter('category_id', array('neq'=>$object->getCategoryId()))
        ;

        return !$posts->getSize();
    }

    protected function _prepareCache(Mage_Core_Model_Abstract $object)
    {
        # Clean cache for Posts and Routes
        $this->_helper()->getCommon()->getCache()->cleanCachedData(
            array(
                Magpleasure_Blog_Model_Category::CACHE_TAG,
                Magpleasure_Blog_Controller_Router::CACHE_TAG
            )
        );

        # Invalidate Enterprise Cache
        if ($this->_helper()->getCommon()->getMagento()->isEnteprise()){
            Mage::app()->getCacheInstance()->invalidateType('full_page');
        }

        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);
        $this->_saveStores($object);
        $this->_prepareCache($object);

        return $this;
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        parent::_afterLoad($object);
        $this->_loadStores($object);

        return $this;
    }

    protected function _saveStores(Mage_Core_Model_Abstract $object)
    {
        if (is_array($object->getStores())){
            $storeTable = $this->getMainTable()."_store";
            $write = $this->_getWriteAdapter();
            $write->beginTransaction();
            $write->delete($storeTable, "`{$this->getIdFieldName()}` = '{$object->getId()}'");
            if (is_array($object->getStores())){
                foreach ($object->getStores() as $storeId){
                    $write->insert($storeTable, array(
                        $this->getIdFieldName() => $object->getId(),
                        'store_id' => $storeId,
                    ));
                }
            }
            $write->commit();
        }
        return $this;
    }

    public function loadStores(Mage_Core_Model_Abstract $object)
    {
        return $this->_loadStores($object);
    }

    protected function _loadStores(Mage_Core_Model_Abstract $object)
    {
        if ($object->getData($this->getIdFieldName())){
            $storeTable = $this->getMainTable()."_store";
            $read = $this->_getReadAdapter();
            $select = new Zend_Db_Select($read);
            $select
                ->from($storeTable, array('store_id') )
                ->where($this->getIdFieldName()." = ?", $object->getId());
            ;
            $result = array();
            foreach ($read->fetchAll($select) as $row){
                $result[] = $row['store_id'];
            }
            $object->setStores($result);
        }
        return $this;
    }

    protected function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        parent::_afterDelete($object);
        $this->_prepareCache($object);

        return $this;
    }
}