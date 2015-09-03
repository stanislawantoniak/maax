<?php

class Zolago_Catalog_Block_Product_List extends Mage_Catalog_Block_Product_List
{
    protected function _beforeToHtml()
    {
        return $this;
    }
}
