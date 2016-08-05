<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Model_Observer_Layout
{
    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _getCommonHelper()
    {
        return Mage::helper('magpleasure');
    }

    /**
     * Layout
     * @param Event $event
     * @return Mage_Core_Model_Layout
     */
    protected function _getLayout($event)
    {
        return $event->getLayout();
    }

    public function layoutLoadBefore($event)
    {
        $layout = $this->_getLayout($event);

        # Load Angular cross all pages
        $layout->getUpdate()->addHandle("magpleasure_default");
    }
}
