<?php
/**
  
 */
 
class ZolagoOs_Rma_Model_Pdf_PoItems_Grouped extends ZolagoOs_Rma_Model_Pdf_PoItems_Default
{
    public function draw()
    {
        $type = $this->getItem()->getOrderItem()->getRealProductType();
        $renderer = $this->getRenderedModel()->getRenderer($type);
        $renderer->setOrder($this->getOrder());
        $renderer->setItem($this->getItem());
        $renderer->setPdf($this->getPdf());
        $renderer->setPage($this->getPage());

        $renderer->draw();
    }
}