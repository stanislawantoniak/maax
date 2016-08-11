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


        $value = $this->getValue();
        if ($value) {
            if(is_string($value)){
                $value = $value;
            }
            if(is_array($value)){
                $value = $value["value"];
            }
            $url = Mage::getBaseUrl("media") . $this->getFolderStorage(). DS . $value;

            $html .= '<a href="' . $url . '"'
                . ' onclick="imagePreview(\'' . $this->getHtmlId() . '_image\'); return false;">'
                . '<img src="' . $url . '" id="' . $this->getHtmlId()
                . ' alt="' . $value . '" height="200" width="200" class="small-image-preview v-middle" />'
                . '</a> ';
        }
        $html .= '</div>';

        $this->setClass('input-file');

        $html .= '<input  id="' . $this->getHtmlId() . '" name="' . $this->getName()
            . '" value="' . $value . '" ' . $this->serialize($this->getHtmlAttributes()) . ' data-resolution="1"  />' . "\n";
        $html .= '<input id="' . $this->getHtmlId() . '_value" type="hidden"  name="' . $this->getName()
            . '[value]" value="' . $value . '"   />' . "\n";
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