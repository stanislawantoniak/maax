<?php

/**
 * Description of Title
 */
class Zolago_Solrsearch_Block_Catalog_Product_List_Header_Category
    extends Zolago_Solrsearch_Block_Catalog_Product_List_Header_Abstract
{

    protected function _construct()
    {
        $this->setTemplate('zolagosolrsearch/catalog/product/list/header/category.phtml');
    }

    public function isContentMode()
    {
        $category = $this->getCurrentCategory();
        $res = false;
        if ($category) {
            if ($category->getDisplayMode() == Mage_Catalog_Model_Category::DM_PAGE) {
                $res = true;
            }
        }
        return $res;
    }

}