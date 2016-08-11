<?php

/**
 * Sledzenie salda / Balance tracking
 *
 * Class GH_Regulation_Block_Dropship_Rules
 */
class GH_Statements_Block_Dropship_Balance extends Mage_Core_Block_Template {

    protected function _beforeToHtml() {
        $this->getGrid();
        return parent::_beforeToHtml();
    }

    public function getGridJsObjectName() {
        return $this->getGrid()->getJsObjectName();
    }

    protected function _prepareLayout()
    {
        //fix for horizontal scroll for grid
        $this->getLayout()
            ->getBlock('root')
            ->addBodyClass('grid-hscroll-fix')
            ->addBodyClass('grid-hscroll-700w');
        return parent::_prepareLayout();
    }

    public function getGrid() {
        if(!$this->getData("grid")){
            $design = Mage::getDesign();
            $design->setArea("adminhtml");
            $block = $this->getLayout()->
            createBlock("ghstatements/dropship_balance_grid");
            $block->setParentBlock($this);
            $this->setGridHtml($block->toHtml());
            $this->setData("grid", $block);
            $design->setArea("frontend");
        }
        return $this->getData("grid");
    }
}