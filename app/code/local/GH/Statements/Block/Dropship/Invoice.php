<?php

/**
 * Invoices / Faktury
 *
 * Class GH_Statements_Block_Dropship_Invoice
 */
class GH_Statements_Block_Dropship_Invoice extends Mage_Core_Block_Template {

    protected function _beforeToHtml() {
        $this->getGrid();
        return parent::_beforeToHtml();
    }

    public function getGridJsObjectName() {
        return $this->getGrid()->getJsObjectName();
    }

    protected function _prepareLayout() {
        $this->getLayout()
            ->getBlock('root')
            ->addBodyClass('grid-hscroll-fix')
            ->addBodyClass('grid-hscroll-700w');
        return parent::_prepareLayout();
    }

    public function getGrid() {
        if (!$this->getData("grid")) {
            $design = Mage::getDesign();
            $design->setArea("adminhtml");
            $block = $this->getLayout()->
            createBlock("ghstatements/dropship_invoice_grid");
            $block->setParentBlock($this);
            $this->setGridHtml($block->toHtml());
            $this->setData("grid", $block);
            $design->setArea("frontend");
        }
        return $this->getData("grid");
    }
}