<?php

/**
 * Rozliczenia okresowe / Periodic statements
 *
 * Class GH_Statements_Block_Dropship_Balance
 */
class GH_Statements_Block_Dropship_Periodic extends Mage_Core_Block_Template {

    protected function _beforeToHtml() {
        $this->getGrid();
        return parent::_beforeToHtml();
    }

    public function getGridJsObjectName() {
        return $this->getGrid()->getJsObjectName();
    }

    protected function _prepareLayout()
    {
        $this->getLayout()
            ->getBlock('root')
            ->addBodyClass('grid-hscroll-fix')
            ->addBodyClass('grid-hscroll-1500w');
        return parent::_prepareLayout();
    }

    public function getGrid() {
        if(!$this->getData("grid")){
            $design = Mage::getDesign();
            $design->setArea("adminhtml");
            $block = $this->getLayout()->
            createBlock("ghstatements/dropship_periodic_grid");
            $block->setParentBlock($this);
            $this->setGridHtml($block->toHtml());
            $this->setData("grid", $block);
            $design->setArea("frontend");
        }
        return $this->getData("grid");
    }
}