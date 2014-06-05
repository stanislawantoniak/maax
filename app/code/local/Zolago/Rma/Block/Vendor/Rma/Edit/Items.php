<?php

class Zolago_Rma_Block_Vendor_Rma_Edit_Items extends Mage_Sales_Block_Items_Abstract
{
    protected function _construct()
    {
        Mage_Core_Block_Template::_construct();
        $this->addItemRender('default', 'sales/order_item_renderer_default', 'zolagorma/vendor/rma/edit/renderer/default.phtml');
    }

  

}
