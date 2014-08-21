<?php

class Zolago_Banner_Varien_Data_Form_Element_Thumbnail extends Varien_Data_Form_Element_Image
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('file');
    }

    public function getElementHtml()
    {
        $html = '';
        $html .= '<div class="banner-image-container">';
        $html .= '<div class="banner-thumbnail">';


        if ((string)$this->getValue()) {
            $url = $this->_getUrl();

            if( !preg_match("/^http\:\/\/|https\:\/\//", $url) ) {
                $url = Mage::getBaseUrl('media') . $url;
            }
            $html .= '<a href="' . $url . '"'
                . ' onclick="imagePreview(\'' . $this->getHtmlId() . '_image\'); return false;">'
                . '<img src="' . $url . '" id="' . $this->getHtmlId() . '_image" title="' . $this->getValue() . '"'
                . ' alt="' . $this->getValue() . '" height="200" width="200" class="small-image-preview v-middle" />'
                . '</a> ';
        }
        $html .= '</div>';

        $dataAttribute = $this->getDataAttribute();
        $data = "";
        if(!empty($dataAttribute)){
            foreach($dataAttribute as $attributeName => $attributeValue){
                $data .= ' data-' . $attributeName . '=' . $attributeValue;
            }
        }
        $this->setClass('input-file');

        $html .= '<input  id="'.$this->getHtmlId().'" name="'.$this->getName()
            .'" value="'.$this->getEscapedValue().'" '.$this->serialize($this->getHtmlAttributes()).' data-resolution="1" '.$data.' />'."\n";
        $html .= '<input type="text"  name="'.$this->getName()
            .'[value]" value="'.$this->getEscapedValue().'" style="background-color: transparent;
            border: medium none;
            height: 0;position: absolute;"  />'."\n";
        $html.= $this->getAfterElementHtml();
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