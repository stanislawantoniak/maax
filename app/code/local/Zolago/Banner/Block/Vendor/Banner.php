<?php

class Zolago_Banner_Block_Vendor_Banner extends Mage_Core_Block_Template{
    protected function _beforeToHtml() {
        $this->getGrid();
        return parent::_beforeToHtml();
    }

    public function getGridJsObjectName() {
        return $this->getGrid()->getJsObjectName();
    }

    /**
     * @return Zolago_Po_Block_Vendor_Po_Grid
     */
    public function getGrid() {
        if(!$this->getData("grid")){
            $design = Mage::getDesign();
            $design->setArea("adminhtml");
            $block = $this->getLayout()->
                createBlock("zolagobanner/vendor_banner_grid");
            $block->setParentBlock($this);
            $this->setGridHtml($block->toHtml());
            $this->setData("grid", $block);
            $design->setArea("frontend");
        }
        return $this->getData("grid");
    }

    /**
     * @return ZolagoOs_OmniChannel_Model_Session
     */
    protected function _getSession(){
        return Mage::getSingleton('udropship/session');
    }
}