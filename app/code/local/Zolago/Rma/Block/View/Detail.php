<?php

class Zolago_Rma_Block_View_Detail extends Zolago_Rma_Block_New_Abstract
{
    public function getFormAction()
    {
        return $this->getUrl('*/*/sendRmaDetail');
    }
}