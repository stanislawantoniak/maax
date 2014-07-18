<?php
/**
 *
 */
class Zolago_Modago_Helper_Test extends Zolago_TestCase
{
    public function testCategoriesTree()
    {
        $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        $categories = Mage::getModel('catalog/category')->getCategories($rootCatId);

        $this->assertInstanceOf('Varien_Data_Tree_Node_Collection',$categories);

        $zolagoModagoH = Mage::helper('zolagomodago');
        $tree = $zolagoModagoH->getCategoriesTree($categories, 1, 3);
        $this->assertInstanceOf('Zolago_Modago_Helper_Data', $zolagoModagoH);
        $this->assertNotEmpty($tree);
    }
}
