<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Widget_Form_Snippet
    extends Mage_Adminhtml_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Form element which re-rendering
     *
     * @var Varien_Data_Form_Element_Fieldset
     */
    protected $_element;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("magpleasure/widget/form/snippet.phtml");
    }

    /**
     * Retrieve an element
     *
     * @return Varien_Data_Form_Element_Fieldset
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Render element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        $this->addData($element->getData());
        return $this->toHtml();
    }

    public function getSnippedCodeHtml()
    {
        $html = $this->getSnippetTemplate();
        $bind = $this->getBind();
        if ($bind && is_array($bind)){
            foreach ($bind as $key => $value){
                $html = preg_replace("/{{".$key."}}/", $value, $html);
            }
        }
        return htmlspecialchars($html);
    }
}


