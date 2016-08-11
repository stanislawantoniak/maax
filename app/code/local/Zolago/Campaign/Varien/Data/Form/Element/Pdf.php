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
        $html .= '<div class="row">';

        $value = $this->getValue();
        if ($value) {
            if (is_string($value)) {
                $value = $value;
            }
            if (is_array($value)) {
                $value = $value["value"];
            }
            $html .= '<div class="col-md-12">';
            $html .= '<div  class="col-md-4 no-padding-left">';
            $url = Mage::getBaseUrl("media") .  $this->getFolderStorage() . DS . $value;

            $html .= '<a href="' . $url . '" target="_blank"><i class="icon-file"></i> PDF</a> ';
            $html .= '</div>';

            //checkbox to remove pdf if exist
            $html .= '<div>';
            $html .= '<input type="checkbox" name="remove_' . $this->getName() . '" />';
            $html .= ' ' . Mage::helper("zolagocampaign")->__("Remove");
            $html .= '</div>';
            //--checkbox to remove pdf if exist

            $html .= '</div>';
        }


        $this->setClass('input-file');
        $html .= '<div class="col-md-12">';
        $html .= '<input  id="' . $this->getHtmlId() . '" name="' . $this->getName()
            . '" value="' . $value . '" ' . $this->serialize($this->getHtmlAttributes()) . ' data-resolution="1"  />' . "\n";
        $html .= '<input id="' . $this->getHtmlId() . '_value" type="hidden"  name="' . $this->getName()
            . '[value]" value="' . $value . '"   />' . "\n";
        $html .= $this->getAfterElementHtml();
        $html .= '</div>';

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