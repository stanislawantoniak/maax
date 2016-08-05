<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Widget_Button extends Mage_Core_Block_Template
{
    public function getType()
    {
        return ($type=$this->getData('type')) ? $type : 'button';
    }

    public function getOnClick()
    {
        if (!$this->getData('on_click')) {
            return $this->getData('onclick');
        }
        return $this->getData('on_click');
    }

    public function getTitle()
    {
        return $this->getData('title') ? $this->getData('title') : $this->getData('label');
    }

    protected function _toHtml()
    {
        $html = $this->getBeforeHtml().'<button '
            . ($this->getId()?' id="'.$this->getId() . '"':'')
            . ($this->getElementName()?' name="'.$this->getElementName() . '"':'')
            . ($this->getTitle()?' title="'.$this->getTitle() . '"':'')
            . ' title="'.$this->getTitle() . '"'
            . ' type="'.$this->getType() . '"'
            . ' class="button ' . $this->getClass() . ($this->getDisabled() ? ' disabled' : '') . '"'
            . ' onclick="'.$this->getOnClick().'"'
            . ' style="'.$this->getStyle() .'"'
            . ' '.$this->getAdditionalAttributes() .' '
            . ($this->getValue()?' value="'.$this->getValue() . '"':'')
            . ($this->getDisabled() ? ' disabled="disabled"' : '')
            . '><span><span>' .$this->getLabel().'</span></span></button>'.$this->getAfterHtml();

        return $html;
    }

}