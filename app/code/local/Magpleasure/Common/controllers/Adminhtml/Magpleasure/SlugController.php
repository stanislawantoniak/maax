<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Adminhtml_Magpleasure_SlugController
    extends Magpleasure_Common_Controller_Adminhtml_Action_Service
{
    public function generateAction()
    {
        $result = array();
        if ($title = $this->getRequest()->getParam('title')){
            $result['slug'] = $this->_commonHelper()->getStrings()->generateSlug($title);
        }
        $this->_ajaxResponse($result);
    }
}