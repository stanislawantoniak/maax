<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Tag extends Magpleasure_Common_Model_Resource_Abstract
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function _construct()
    {    
        $this->_init('mpblog/tag', 'tag_id');
        $this->setUseUpdateDatetimeHelper(true);
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()){

            $name = str_replace("/", " ", $object->getName());
            $slug = $this
                ->_helper()
                ->getCommon()
                ->getStrings()
                ->generateSlug($name)
            ;

            $object->setUrlKey($slug);
        }
        return parent::_beforeSave($object);
    }

    /**
     * Link Tag with Post
     *
     * @param Mage_Core_Model_Abstract $object
     * @param int $postId
     * @return Magpleasure_Blog_Model_Mysql4_Tag
     */
    public function linkWith($object, $postId)
    {
        if ($object->getId()){
            $id = $object->getId();
            $this->unlinkWith($object, $postId);
            $writeAdapter = $this->_getWriteAdapter()->beginTransaction();
            $tableName = $this->getTable('mpblog/post')."_tag";
            $select = $writeAdapter->select();
            $writeAdapter->insert($tableName, array(
                'post_id' => $postId,
                'tag_id' => $id,
            ));
            $writeAdapter->commit();
        }
        return $this;
    }

    /**
     * Unlink Tag with Post
     *
     * @param Mage_Core_Model_Abstract $object
     * @param int $postId
     * @return Magpleasure_Blog_Model_Mysql4_Tag
     */
    public function unlinkWith($object, $postId)
    {
        if ($tagId = $object->getId()){

            $writeAdapter = $this->_getWriteAdapter()->beginTransaction();
            $tableName = $this->getTable('mpblog/post')."_tag";
            $writeAdapter->delete($tableName, array(
                'tag_id = ?' => $tagId,
                'post_id = ?' => $postId,
            ));
            $writeAdapter->commit();

        }
        return $this;
    }

}