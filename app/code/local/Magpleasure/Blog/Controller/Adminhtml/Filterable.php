<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Controller_Adminhtml_Filterable extends Magpleasure_Common_Controller_Adminhtml_Action
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function getAppliedStoreId()
    {
        return $this->_helper()->getStoreHelper()->getAppliedStoreId();
    }

    public function isStoreFilterApplied()
    {
        return $this->_helper()->getStoreHelper()->isStoreFilterApplied();
    }

    protected function _getCommonParams()
    {
        return $this->_helper()->getStoreHelper()->getCommonParams();
    }

    protected function _prepareStoreFilter()
    {
        if (Mage::app()->isSingleStoreMode()){

            # Nothing to do
            return $this;
        }

        $request = $this->getRequest();
        $storeHelper = $this->_helper()->getStoreHelper();

        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();
        $route = "adminhtml/$controllerName/$actionName";
        $params = $request->getParams();

        if (!$request->getParam('store') && $storeHelper->getSavesStoreId()) {

            $params['store'] = $storeHelper->getSavesStoreId();

            $this->_redirectUrl($this->getUrl($route, $params));
            $this->getResponse()->sendHeaders();
            exit;

        } elseif ($request->getParam('store') == Magpleasure_Blog_Helper_Data_Store::RESET_VALUE) {

            $storeHelper->clearSavedStoreId();

            if (isset($params['store'])){
                unset($params['store']);
            }

            $this->_redirectUrl($this->getUrl($route, $params));
            $this->getResponse()->sendHeaders();
            exit;
        } elseif ($storeId = $request->getParam('store')){

            $storeHelper->saveStoreId($storeId);
        }

        return $this;
    }
}