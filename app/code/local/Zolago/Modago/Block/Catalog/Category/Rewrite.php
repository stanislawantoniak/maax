<?php

class Zolago_Modago_Block_Catalog_Category_Rewrite extends Mage_Core_Block_Template
{
    public function _construct() {
        parent::_construct();
        // Override tmpalte
        $this->setTemplate('catalog/category/sidebar/rewrite.phtml');
    }

}