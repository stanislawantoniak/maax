<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_System_Entity_Form_Element_Abstract extends Magpleasure_Common_Block_Adminhtml_Template
{


    public function getName()
    {
        return $this->getData('name') ? $this->getData('name') : $this->getData('html_id');
    }

    public function getClassName()
    {
        return $this->getData('class');
    }

    public function isAjax()
    {
        return $this->_commonHelper()->getRequest()->isAjax();
    }

    protected function _encodeJsonForDirective($object)
    {
        $json = Zend_Json::encode($object);
        return str_replace('"', '\"', $json);
    }
}