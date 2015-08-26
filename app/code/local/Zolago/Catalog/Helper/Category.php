<?php

/**
 * Class Zolago_Catalog_Helper_Category
 */
class Zolago_Catalog_Helper_Category extends Mage_Catalog_Helper_Category
{
    private $all_categories = NULL;

    /**
     * @return array Array of categories in for of id => path
     */
    public function getPathArray()
    {

        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('id, path')
            ->addIsActiveFilter();

        $all_categories = array();
        foreach ($categories as $c) {
            $all_categories[$c->getId()] = $c->getPath();
        }

        $this->all_categories = $all_categories;

        return $all_categories;
    }

    /**
     * @param int $parent_cat_id
     * @param array $all_categories
     *
     * @return array
     */
    public function getChildrenIds($parent_cat_id, $all_categories = NULL)
    {
        $children_ids = array();
        if (!$all_categories) {
            $all_categories = ($this->all_categories) ? $this->all_categories : $this->getPathArray();
        }
        if ($all_categories) {
            foreach ($all_categories as $cat_id => $cat_path) {
                if (strpos($cat_path, '/' . $parent_cat_id . '/') !== FALSE) {
                    $children_ids[] = $cat_id;
                }
            }
        }
        return $children_ids;
    }

    /**
     * Check if a category can be shown
     * (Overwritten Mage_Catalog_Helper_Category->canShow to have an ability to see listing on root category )
     *
     * @param  Mage_Catalog_Model_Category|int $category
     * @return boolean
     */
    public function canShow($category)
    {
        if (is_int($category)) {
            $category = Mage::getModel('catalog/category')->load($category);
        }

        if (!$category->getId()) {
            return false;
        }

        if (!$category->getIsActive()) {
            return false;
        }
        //Allow to show listing on root category
//        if (!$category->isInRootCategoryList()) {
//            return false;
//        }

        return true;
    }
}