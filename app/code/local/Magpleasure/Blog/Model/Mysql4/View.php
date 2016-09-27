<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_View extends Magpleasure_Common_Model_Resource_Abstract
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
        $this->_init('mpblog/view', 'view_id');
        $this->setUseUpdateDatetimeHelper(true);
    }

    public function loadByPostAndSession(Mage_Core_Model_Abstract $object, $postId, $sessionId)
    {
        /** @var $views Magpleasure_Blog_Model_Mysql4_View_Collection */
        $views = Mage::getModel('mpblog/view')->getCollection();

        $views->addFieldToFilter('post_id', $postId);
        $views->addFieldToFilter('session_id', $sessionId);

        foreach ($views as $view){
            $object->addData($view->getData());
            return $this;
        }

        return $this;
    }

    public function deleteRowsBefore($date)
    {
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        $write->delete($this->getMainTable(), "created_at <= '{$date}'");
        $write->commit();
        return $this;
    }

}