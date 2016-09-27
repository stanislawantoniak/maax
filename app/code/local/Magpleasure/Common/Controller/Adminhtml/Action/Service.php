<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Controller_Adminhtml_Action_Service
    extends Magpleasure_Common_Controller_Adminhtml_Action
{
    /**
     * Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _helper()
    {
        return $this->_commonHelper();
    }

    /**
     * Override the method to check if
     * Service Could Be used for this action
     *
     * @return mixed
     */
    protected function _isAllowed()
    {
        $aclRoute = $this->_getSession()->getControlRoutePath();
        if ($aclRoute){
            return Mage::getSingleton('admin/session')->isAllowed($aclRoute);
        } else {
            return parent::_isAllowed();
        }
    }
}