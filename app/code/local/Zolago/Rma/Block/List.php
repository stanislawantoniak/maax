<?php

class Zolago_Rma_Block_List extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('zolagorma/list.phtml');
    }
    
    public function getRmaList() {
        return array();
    }
}
