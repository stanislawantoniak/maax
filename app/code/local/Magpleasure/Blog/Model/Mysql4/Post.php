<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Post extends Magpleasure_Common_Model_Resource_Abstract
{
    protected $_force = false;

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function _construct()
    {    
        $this->_init('mpblog/post', 'post_id');
    }

    protected function _getImportFlag()
    {
        return Mage::registry(Magpleasure_Blog_Model_Post::IMPORT_FLAG);
    }

    protected function _getDuplicateFlag()
    {
        return Mage::registry(Magpleasure_Blog_Model_Post::DUPLICATE_FLAG);
    }

    protected function _validateUrlKey($object)
    {
        /** @var $posts Magpleasure_Blog_Model_Mysql4_Post_Collection */
        $posts = Mage::getModel('mpblog/post')->getCollection();
        $posts
            ->addStoreData()
            ->addStoreFilter($object->getStores())
            ->addFieldToFilter('status', array('neq'=>Magpleasure_Blog_Model_Post::STATUS_DISABLED))
            ->addFieldToFilter('url_key', $object->getUrlKey())
            ->addFieldToFilter('post_id', array('neq'=>$object->getPostId()))
        ;

        $store = $object->getStores();
        if (!is_array($store)){
            $store = array($store);
        }

        $table = $table = $this->getMainTable()."_store";
        $storesFilter = "'".implode("','", $store)."'";
        $posts->getSelect()
            ->joinInner(array('store'=> $table), "store.post_id = main_table.post_id", array())
            ->where("store.store_id IN ({$storesFilter})");

        return !$posts->getSize();
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);
        $isSchedule = false;
        $now = new Zend_Date();
        $now = $now->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        if (($object->getUserDefinePublish() || $object->getId()) && $object->getPublishedAt()){
            $publishedAt = $object->getPublishedAt();
            $isSchedule = ($publishedAt > $now);

        } else {
            if (!$this->_getDuplicateFlag() && !$this->_getImportFlag()){
                $object->setPublishedAt($now);
            }
        }

        if(Mage::app()->getLayout()->getArea() == 'adminhtml'){
            $object->setUpdatedAt($now);
        }

        if ($this->_force){
            return $this;
        }

        if (!$object->getUrlKey() && ($object->getStatus() != Magpleasure_Blog_Model_Post::STATUS_DISABLED)){
            $object->setStatus(Magpleasure_Blog_Model_Post::STATUS_DISABLED);
            Mage::throwException($this->_helper()->__("Post can be only disabled without Url Key."));
        }

        if (!$this->_validateUrlKey($object) && ($object->getStatus() != Magpleasure_Blog_Model_Post::STATUS_DISABLED)){
            $object->setStatus(Magpleasure_Blog_Model_Post::STATUS_DISABLED);
            Mage::throwException($this->_helper()->__("Post '%s' can be disabled only. Some post has same Url Key for the same Store View.", $object->getTitle()));
        }

        if ($isSchedule && ($object->getStatus() == Magpleasure_Blog_Model_Post::STATUS_ENABLED)){
            $object->setStatus(Magpleasure_Blog_Model_Post::STATUS_SCHEDULED);
        }

        if (!$isSchedule && ($object->getStatus() == Magpleasure_Blog_Model_Post::STATUS_SCHEDULED)){
            $object->setStatus(Magpleasure_Blog_Model_Post::STATUS_ENABLED);
        }

        if (!Mage::app()->isSingleStoreMode()){

            # Validate Stores
            $stores = $object->getStores();
            if (!$stores || !is_array($stores) || !count($stores)){
                Mage::throwException($this->_helper()->__("Post '%s' can't be saved. It need to be assigned to any Store View.", $object->getTitle()));
            }
        }

        # Auto fill Meta Data for Post
        if (!$object->getId()){

            if (!$object->getData('meta_title')){
                $object->setData('meta_title', $object->getData('title'));
            }

            if (!$object->getData('meta_tags')){
                $object->setData('meta_tags', $object->getData('tags'));
            }

            if (!$object->getData('meta_description')){
                $description = $object->getShortContent();
                $description = $this->_helper()->_strings()->htmlToText($description);
                $object->setData('meta_description', $this->_helper()->_strings()->strLimit($description, 200));
            }
        }

        return $this;
    }

    public function forceSave()
    {
        $this->_force = true;
        return $this;
    }

    protected function _prepareCache(Mage_Core_Model_Abstract $object)
    {
        # Clean cache for Posts and Routes
        $this->_helper()->getCommon()->getCache()->cleanCachedData(
            array(
                Magpleasure_Blog_Model_Post::CACHE_TAG."_".$object->getId(),
                Magpleasure_Blog_Controller_Router::CACHE_TAG
            )
        );

        # Invalidate Enterprise Cache if there's not comment update
        if ($this->_helper()->getCommon()->getMagento()->isEnteprise()){

            if (!$object->getIsCommentUpdateFlag()){
                Mage::app()->getCacheInstance()->invalidateType('full_page');
            }
        }

        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);
        $this->_saveStores($object);
        $this->_saveCategories($object);
        $this->_saveTags($object);
        $this->_prepareCache($object);

        return $this;
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        parent::_afterLoad($object);
        $this->_loadStores($object);
        $this->_loadCategories($object);
        $this->_loadTags($object);
        $this->_preparePublishedAt($object);

        return $this;
    }

    protected function _preparePublishedAt(Mage_Core_Model_Abstract $object)
    {
        if ($object->getCreatedAt() && !$object->getPublishedAt()){
            $object->setPublishedAt($object->getCreatedAt());
        }
        return $this;
    }

    protected function _saveAbstractLink(Mage_Core_Model_Abstract $object, $dataKey, $dbPostfix, $dbDataKey)
    {
        if (is_array($object->getData($dataKey))){
            $storeTable = $this->getMainTable().$dbPostfix;
            $write = $this->_getWriteAdapter();
            $write->beginTransaction();
            $write->delete($storeTable, "`{$this->getIdFieldName()}` = '{$object->getId()}'");
            if (is_array($object->getData($dataKey))){
                foreach ($object->getData($dataKey) as $storeId){
                    $write->insert($storeTable, array(
                        $this->getIdFieldName() => $object->getId(),
                        $dbDataKey => $storeId,
                    ));
                }
            }
            $write->commit();
        }
        return $this;
    }

    protected function _saveTags(Mage_Core_Model_Abstract $object)
    {
        if ($object->getData('tags')){
            $tags = explode(",", $object->getTags());
            foreach ($tags as $tag){
                $clearTag = trim($tag);
                if ($clearTag){
                    /** @var Magpleasure_Blog_Model_Tag  $tagModel  */
                    $tagModel = Mage::getModel('mpblog/tag')->load($clearTag, 'name');
                    if (!$tagModel->getId()){
                        $tagModel
                            ->setName($clearTag)
                            ->save();
                    }
                    $tagModel->linkWith($object->getId());
                }
            }
        }

        if ($object->getOrigData('tags')){
            $tags = explode(",", $object->getData('tags'));
            $origTags = explode(",", $object->getOrigData('tags'));

            foreach ($origTags as $origTag){
                $clearOrigTag = trim($origTag);
                $removed = true;
                foreach ($tags as $tag){
                    $clearTag = trim($tag);
                    if ($clearTag == $clearOrigTag){
                        $removed = false;
                    }
                }
                if ($removed){
                    /** @var Magpleasure_Blog_Model_Tag  $tagModel  */
                    $tagModel = Mage::getModel('mpblog/tag')->load($clearOrigTag, 'name');
                    if ($tagModel->getId()){
                        $tagModel->unlinkWith($object->getId());
                    }
                }


            }
        }
        return $this;
    }

    protected function _saveStores(Mage_Core_Model_Abstract $object)
    {
        $this->_saveAbstractLink($object, 'stores', '_store', 'store_id');
        return $this;
    }

    protected function _saveCategories(Mage_Core_Model_Abstract $object)
    {
        $this->_saveAbstractLink($object, 'categories', '_category', 'category_id');
        return $this;
    }

    public function loadAdditionalData(Mage_Core_Model_Abstract $object)
    {
        $this->_loadStores($object);
        $this->_loadCategories($object);
        $this->_loadTags($object);
        return $this;
    }

    protected function _loadAbstractLink(Mage_Core_Model_Abstract $object, $dataKey, $dbPostfix, $dbDataKey)
    {
        if ($object->getData($this->getIdFieldName())){
            $storeTable = $this->getMainTable().$dbPostfix;
            $read = $this->_getReadAdapter();
            $select = new Zend_Db_Select($read);
            $select
                ->from($storeTable, array($dbDataKey) )
                ->where($this->getIdFieldName()." = ?", $object->getId());
            ;
            $result = array();
            foreach ($read->fetchAll($select) as $row){
                $result[] = $row[$dbDataKey];
            }
            $object->setData($dataKey, $result);
            $object->setOrigData($dataKey, $result);
        }

        return $this;
    }

    protected function _loadTags(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()){
            $names = array();
            $readAdapter = $this->_getReadAdapter();
            $tableName = $this->getMainTable()."_tag";

            $tagModel = Mage::getModel('mpblog/tag');
            $tagTableName = $tagModel->getResource()->getMainTable();

            $select = $readAdapter->select();
            $select
                ->from(array('link' => $tableName), array())
                ->join(array('tag'=>$tagTableName), "link.tag_id = tag.tag_id", array('name'=>'tag.name'))
                ->where("link.post_id = ?", $object->getId())
                ;

            foreach ($readAdapter->fetchAll($select) as $row){
                $names[] = $row['name'];
            }

            $object->setData('tags', implode(", ", $names));
            $object->setOrigData('tags', implode(", ", $names));
        }
        return $this;
    }

    protected function _loadStores(Mage_Core_Model_Abstract $object)
    {
        $this->_loadAbstractLink($object, 'stores', '_store', 'store_id');
        return $this;
    }

    protected function _loadCategories(Mage_Core_Model_Abstract $object)
    {
        $this->_loadAbstractLink($object, 'categories', '_category', 'category_id');
        return $this;
    }

    protected function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        parent::_afterDelete($object);
        $this->_prepareCache($object);

        return $this;
    }

    public function processMediaTemplates(Mage_Core_Model_Abstract $object)
    {
        $keys = array('short_content', 'full_content');
        foreach ($keys as $key){
            if ($object->getData($key)){

                $value = $object->getData($key);
                $pattern = '/\{\{media(.*)url=\"(.*)\"\}\}/i';
                preg_match_all($pattern, $value, $matches);

                if (count($matches[0])){

                    for ($i = 0; $i < count($matches[0]); $i++){
                        if (isset($matches[0][$i]) && isset($matches[2][$i])){
                            $value = str_replace($matches[0][$i], Mage::getBaseUrl('media').trim($matches[2][$i]), $value);
                        }
                    }
                }

                $object->setData($key, $value);
            }
        }

        return $this;
    }

    public function processCutter(Mage_Core_Model_Abstract $object)
    {
        $key = "full_content";
        if ($object->getData($key)){
            $value = $object->getData($key);
            $value = str_replace(Magpleasure_Blog_Model_Post::CUT_LIMITER, Magpleasure_Blog_Model_Post::CUT_LIMITER_TAG, $value);
            $object->setData($key, $value);
        }

        return $this;
    }
}