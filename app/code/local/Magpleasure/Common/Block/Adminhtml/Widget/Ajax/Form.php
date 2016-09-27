<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Widget_Ajax_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_container;

    public function setContainer($container)
    {
        $this->_container = $container;
        return $this;
    }

    /**
     * Container
     *
     * @return Magpleasure_Common_Block_Adminhtml_Widget_Ajax_Form_Container
     */
    public function getContainer()
    {
        return $this->_container;
    }

    public function getAction()
    {
        return $this->getUrl($this->getPostUrl(), array($this->getPostParam() => $this->getRequest()->getParam($this->getPostParam())));
    }

    public function getHtmlId()
    {
        return $this->getContainer()->getHtmlId();
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $covering = new Varien_Data_Form_Element_Hidden(array(
            'value' => $this->getContainer()->getCovering(),
            'name' => 'covering',
        ));

        $this->getForm()->addElement($covering);
        $covering->setId('covering');

        return $this;
    }

}