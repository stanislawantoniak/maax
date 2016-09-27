<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Adminhtml_Magpleasure_AjaxdropdownController
    extends Magpleasure_Common_Controller_Adminhtml_Action_Service
{

    /**
     * Ajax Dropdown List Action
     */
    public function listAction()
    {
        /** @var $dataSource Magpleasure_Common_Model_Datasource */
        $dataSource = Mage::getModel('magpleasure/datasource')->setParams(array_merge(
            array(
                'query' => $this->getRequest()->getParam('q'),
                'limit' => $this->getRequest()->getParam('l') ? $this->getRequest()->getParam('l') : 10,
                'page' => $this->getRequest()->getParam('p') ? $this->getRequest()->getParam('p') : 1,
            ),
            $this->_commonHelper()->getHash()->getData($this->getRequest()->getParam('h'))
        ));

        $this->_jsonResponse($dataSource->getArrayData());
    }
}