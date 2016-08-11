<?php

require_once Mage::getModuleDir('controllers', "Inic_Faq") . DS . "IndexController.php";

class Zolago_Faq_IndexController extends Inic_Faq_IndexController
{
    public function preDispatch() {
        parent::preDispatch();
        Mage::dispatchEvent('faq_controller_index');
        return $this;
    }

    /**
     * Displays the current Category's FAQ list view
     */
    public function resultAction()
    {
        $keyword = $this->getRequest()->getParam('keyword');
        $this->getRequest()->setParam('keyword', Zolago_Catalog_Helper_Data::secureInvisibleContent($keyword));
        $this->loadLayout()->renderLayout();
    }
}
