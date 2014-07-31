<?php
/**
 *
 */
class Zolago_Modago_Helper_Test extends Zolago_TestCase
{
    public static function setMainCategoriesData()
    {
        $layout = Mage::app()->getLayout();
        return array(
            array(
                'name'         => 'Ona',
                'url'          => '/',
                'category_id'  => 1,
                'has_dropdown' => $layout->createBlock('cms/block')->setBlockId('navigation-dropdown-c-1')->toHtml()
            ),
            array(
                'name'         => 'On',
                'url'          => '/',
                'category_id'  => 2,
                'has_dropdown' => $layout->createBlock('cms/block')->setBlockId('navigation-dropdown-c-2')->toHtml()
            ),
            array(
                'name'         => 'Dziecko',
                'url'          => '/dziecko',
                'category_id'  => 3,
                'has_dropdown' => $layout->createBlock('cms/block')->setBlockId('navigation-dropdown-c-3')->toHtml()
            ),
        );
    }
}
