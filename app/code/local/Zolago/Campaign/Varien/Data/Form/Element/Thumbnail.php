<?php

class Zolago_Campaign_Varien_Data_Form_Element_Thumbnail extends Varien_Data_Form_Element_Image
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('file');
    }

    public function getElementHtml()
    {
        $html = '';
        $html .= '<div class="campaign-image-container">';
        $html .= '<div class="campaign-image-thumbnail">';


        if ((string)$this->getValue()) {
            $url = Mage::getUrl() . DS . $this->_getUrl();

            if (!preg_match("/^http\:\/\/|https\:\/\//", $url)) {
                $url = Mage::getBaseUrl('media') . $url;
            }
            $html .= '<a href="' . $url . '"'
                . ' onclick="imagePreview(\'' . $this->getHtmlId() . '_image\'); return false;">'
                . '<img src="' . $url . '" id="' . $this->getHtmlId()
                . ' alt="' . $this->getValue() . '" height="200" width="200" class="small-image-preview v-middle" />'
                . '</a> ';
        }
        $html .= '</div>';

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