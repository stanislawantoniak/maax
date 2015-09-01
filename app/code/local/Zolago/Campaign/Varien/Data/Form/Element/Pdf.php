<?php

class Zolago_Campaign_Varien_Data_Form_Element_Pdf extends Varien_Data_Form_Element_Image
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('file');
    }

    public function getElementHtml()
    {
        $html = '';
        $html .= '<div>';


        if ((string)$this->getValue()) {
            $url = Mage::getBaseUrl().$this->_getUrl();

            $html .= '<a href="' . $url . '" target="_blank">PDF</a> ';
        }

        $dataAttribute = $this->getDataAttribute();
        $data = "";
        if (!empty($dataAttribute)) {
            foreach ($dataAttribute as $attributeName => $attributeValue) {
                $data .= ' data-' . $attributeName . '=' . $attributeValue;
            }
        }
        $this->setClass('input-file');

        $html .= '<input  id="' . $this->getHtmlId() . '" name="' . $this->getName()
            . '" value="' . $this->getEscapedValue() . '" ' . $this->serialize($this->getHtmlAttributes()) . ' data-resolution="1" ' . $data . ' />' . "\n";
        $html .= '<input id="' . $this->getHtmlId() . '_value" type="hidden"  name="' . $this->getName()
            . '[value]" value="' . $this->getEscapedValue() . '"   />' . "\n";
        $html .= $this->getAfterElementHtml();
        $html .= '</div>';

        return $html;
    }

    protected function _getUrl()
    {
        return $this->getValue();
    }

    public function getName()
    {
        return $this->getData('name');
    }
}