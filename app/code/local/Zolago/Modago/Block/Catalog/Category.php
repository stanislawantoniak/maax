<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 09.07.2014
 */

class Zolago_Modago_Block_Catalog_Category extends Mage_Core_Block_Template
{

    /**
     * Returns main categories for dropdown menu
     *
     * @todo implement full login
     * @return array
     */
    public function getMainCategories()
    {
        return array(
            array(
                'name' => 'Ona',
                'url' => '/',
                'category_id' => 1,
                'has_dropdown' => (bool) $this->getLayout()->createBlock('cms/block')->setBlockId('navigation-dropdown-c-1')->toHtml()
            ),
            array(
                'name' => 'On',
                'url' => '/',
                'category_id' => 2,
                'has_dropdown' => (bool) $this->getLayout()->createBlock('cms/block')->setBlockId('navigation-dropdown-c-2')->toHtml()
            ),
            array(
                'name' => 'Dziecko',
                'url' => '/dziecko',
                'category_id' => 3,
                'has_dropdown' => (bool) $this->getLayout()->createBlock('cms/block')->setBlockId('navigation-dropdown-c-3')->toHtml()
            ),
        );
    }

    /**
     * Returns main categories for mobile navigation menu under black header
     *
     * @todo implement true login here, preserver data structure
     * @return array
     */
    public function getMainCategoriesMobile()
    {
        return array(
            array(
                'name' => 'Ona',
                'url' => '/',
                'category_id' => 1,
                'has_dropdown' => true,
                'children' => array(
                    array(
                        'name' => 'podkategoria 1',
                        'url' => '/pod-1',
                        'category_id' => 10
                    ),
                    array(
                        'name' => 'podkategoria 1',
                        'url' => '/pod-1',
                        'category_id' => 10
                    ),
                    array(
                        'name' => 'podkategoria 1',
                        'url' => '/pod-1',
                        'category_id' => 10
                    ),
                )
            ),
            array(
                'name' => 'On',
                'url' => '/',
                'category_id' => 2,
                'has_dropdown' => false,
                'children' => array()
            ),
            array(
                'name' => 'Dziecko',
                'url' => '/dziecko',
                'category_id' => 3,
                'has_dropdown' => false,
                'children' => array()
            ),
        );
    }

    /**
     * Returns categories for sliding menu(hamburger menu)
     *
     * @todo implement true logic here, preserve data structure.
     * @return array
     */
    public function getMainCategoriesForSlidingMenu()
    {
        return array(
            array(
                'name' => 'Ona',
                'url' => '/',
                'category_id' => 1,
                'has_dropdown' => true,
                'children' => array(
                    array(
                        'name' => 'podkategoria 1',
                        'url' => '/pod-1',
                        'category_id' => 10
                    ),
                    array(
                        'name' => 'podkategoria 1',
                        'url' => '/pod-1',
                        'category_id' => 10
                    ),
                    array(
                        'name' => 'podkategoria 1',
                        'url' => '/pod-1',
                        'category_id' => 10
                    ),
                )
            ),
            array(
                'name' => 'On',
                'url' => '/',
                'category_id' => 2,
                'has_dropdown' => false,
                'children' => array()
            ),
            array(
                'name' => 'Dziecko',
                'url' => '/dziecko',
                'category_id' => 3,
                'has_dropdown' => false,
                'children' => array()
            ),
        );
    }

    public function getCategoryLabel()
    {
        return 'Dla kobiet';
    }

    public function getMoveUpUrl()
    {
        return '/';
    }

    public function getCategoryCollection()
    {
        return array(
            array(
                'url' => '/dla-niej.html',
                'label' => 'Dla niej'
            ),
            array(
                'url' => '/dla-niej.html',
                'label' => 'Podwiazki'
            ),
            array(
                'url' => '/dla-niej.html',
                'label' => 'Staniki'
            ),
            array(
                'url' => '/dla-niej.html',
                'label' => 'Plaszcze'
            ),
            array(
                'url' => '/dla-niej.html',
                'label' => 'Kapelusze'
            ),
            array(
                'url' => '/dla-niej.html',
                'label' => 'Marynarki'
            ),
        );
    }
} 