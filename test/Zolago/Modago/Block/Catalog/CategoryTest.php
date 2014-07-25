<?php
/**
 * block test
 */
class Zolago_Modago_Block_Catalog_CategoryTest extends Zolago_TestCase {

    /**
     * @requires function no_coverage
     */
    public function testHeaderMenuSliding()
    {
        if (!no_coverage()) {
            $this->markTestSkipped(
                'Coverage test'
            );
            return;
        }
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock(
            'zolagomodago/page_aside', 'aside.header.sliding',
            array('template' => 'page/html/aside/header.menu.sliding.phtml')
        );
        $this->assertNotEmpty($block);
        $this->assertInstanceOf('Zolago_Modago_Block_Catalog_Category',$block);

    }

    /**
     * @requires function no_coverage
     */
    public function testBottomCategoryItemsMobile()
    {
        if (!no_coverage()) {
            $this->markTestSkipped(
                'Coverage test'
            );
            return;
        }
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock(
            "zolagomodago/catalog_category", "page.header.bottom.category.items.mobile",
            array('template' => 'page/html/header/bottom.category.items.mobile.phtml')
        );
        $this->assertNotEmpty($block);
        $this->assertInstanceOf('Zolago_Modago_Block_Catalog_Category',$block);
    }

    /**
     * @requires function no_coverage
     */
    public function testBottomCategoryItems()
    {
        if (!no_coverage()) {
            $this->markTestSkipped(
                'Coverage test'
            );
            return;
        }
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock(
            "zolagomodago/catalog_category", "page.header.bottom.category.items",
            array('template' => 'page/html/header/bottom.category.items.phtml')
        );
        $this->assertNotEmpty($block);
        $this->assertInstanceOf('Zolago_Modago_Block_Catalog_Category',$block);
    }

    /**
     * @requires function no_coverage
     */
    public function testGetMainCategories()
    {
        if (!no_coverage()) {
            $this->markTestSkipped(
                'Coverage test'
            );
            return;
        }
        $categories = Zolago_Modago_Helper_Test::setMainCategoriesData();
        $this->assertNotEmpty($categories);
    }
    public function testGetMainCategoriesForSlidingMenu()
    {
        $block = new Zolago_Modago_Block_Catalog_Category();
        $menu = $block->getMainCategoriesForSlidingMenu();
        $this->assertNotEmpty($menu);
    }
    public function testGetCategoryLabel()
    {
        $block = new Zolago_Modago_Block_Catalog_Category();
        $label = $block->getCategoryLabel();
        $this->assertNotEmpty($label);
    }

    public function testGetMoveUpUrl()
    {
        $block = new Zolago_Modago_Block_Catalog_Category();
        $label = $block->getMoveUpUrl();
        $this->assertNotEmpty($label);
    }

    public function testGetCategoryCollection()
    {
        $block = new Zolago_Modago_Block_Catalog_Category();
        $categories = $block->getCategoryCollection();
        $this->assertNotEmpty($categories);
        $this->assertInternalType('array',$categories);
    }


}