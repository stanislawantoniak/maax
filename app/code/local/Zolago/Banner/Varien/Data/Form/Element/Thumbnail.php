<?php

class Zolago_Banner_Varien_Data_Form_Element_Thumbnail extends Varien_Data_Form_Element_Abstract
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('file');
    }

    public function getElementHtml()
    {
        $html = '';
        Mage::log($this->getDataAttribute());
        if ($this->getValue()) {
            $dataAttribute = $this->getDataAttribute();
            $data = "";
            $dataAttr  = array();
            if(!empty($dataAttribute)){
                foreach($dataAttribute as $attributeName => $attributeValue){
                    $data .= " data-{$attributeName}=$attributeValue";
                }
            }
            Mage::log($data);
            $url = $this->_getUrl();
            if (!preg_match("/^http\:\/\/|https\:\/\//", $url)) {
                $url = Mage::getBaseUrl('media') . $url;
            }
            $html = '<a href="' . $url . '" class="banner-thumbnail"'
                . ' onclick="imagePreview(\'' . $this->getHtmlId() . '_image\'); return false;">'
                . '<img src="' . $url . '" id="' . $this->getHtmlId() . '_image" title="' . $this->getValue() . '"'
                . ' alt="' . $this->getValue() . '" height="50" width="50" class="small-image-preview v-middle" />'
                . '</a> ';
        }
        $this->setClass('input-file');
        $html .= '<input id="'.$this->getHtmlId().'" '.$data.' name="'.$this->getName()
            .'" value="'.$this->getEscapedValue().'" '.$this->serialize($this->getHtmlAttributes()).'/>'."\n";
        $html.= $this->getAfterElementHtml();

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