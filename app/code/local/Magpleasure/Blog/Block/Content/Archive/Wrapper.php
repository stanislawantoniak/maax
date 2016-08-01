<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Archive_Wrapper extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("mpblog/archive/wrapper.phtml");
    }

    /**
     * Archive Model
     *
     * @return Magpleasure_Blog_Model_Archive
     */
    public function getArchive()
    {
        return Mage::getModel('mpblog/archive');
    }

    public function getIsEnabled()
    {
        return Mage::getStoreConfig("mpblog/general/display_archives");
    }

    public function getCollection()
    {
        return $this->getArchive()->getArchives();
    }
}