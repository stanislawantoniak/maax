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
        $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        $categories = Mage::getModel('catalog/category')->getCategories($rootCatId);

//        return array(
//            array(
//                'name' => 'Ona',
//                'url' => '/',
//                'category_id' => 1,
//                'has_dropdown' => (bool) $this->getLayout()->createBlock('cms/block')->setBlockId('navigation-dropdown-c-1')->toHtml()
//            ),
//            array(
//                'name' => 'On',
//                'url' => '/',
//                'category_id' => 2,
//                'has_dropdown' => (bool) $this->getLayout()->createBlock('cms/block')->setBlockId('navigation-dropdown-c-2')->toHtml()
//            ),
//            array(
//                'name' => 'Dziecko',
//                'url' => '/dziecko',
//                'category_id' => 3,
//                'has_dropdown' => (bool) $this->getLayout()->createBlock('cms/block')->setBlockId('navigation-dropdown-c-3')->toHtml()
//            ),
//        );
        return self::getCategoriesTree($categories,1,3);
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
    static function  getCategoriesTree($categories, $level = 1, $span = false)
    {
        $tree = array();

        foreach ($categories as $category) {
            $cat = Mage::getModel('catalog/category')->load($category->getId());

            $tree[$category->getId()] = array(
                'name'           => $category->getName(),
                'url'            => Mage::getUrl($cat->getUrlPath()),
                'category_id'    => $category->getId(),
                'level'          => $level,
                'products_count' => $cat->getProductCount()
            );
            if($level == 1){
                $tree[$category->getId()]['image'] = $cat->getImage();
            }
            if ($span && $level >= $span) {
                continue;
            }
            if ($category->hasChildren()) {
                $children = Mage::getModel('catalog/category')->getCategories($category->getId());
                $tree[$category->getId()]['has_dropdown'] = self::getCategoriesTree($children, $level + 1, $span);
            }

        }

        return $tree;
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

    /**
     * Returns category label for mobile menu in main category page
     *
     * @todo implement full logic
     * @return string
     */
    public function getCategoryLabel()
    {
        return 'Dla kobiet';
    }

    /**
     * Returns go up url for mobile version of main category page's menu.
     * @return string
     */
    public function getMoveUpUrl()
    {
        return '/';
    }

    /**
     * Return array of mobile menu in main category page.
     *
     * @todo implement full logic
     * @return array
     */
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