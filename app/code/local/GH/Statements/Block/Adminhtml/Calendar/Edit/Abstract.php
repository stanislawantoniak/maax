<?php

class GH_Statements_Block_Adminhtml_Calendar_Edit_Abstract extends Mage_Adminhtml_Block_Widget
{

    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getIsNew()
    {
        return $this->getModel()->getId();
    }

}
