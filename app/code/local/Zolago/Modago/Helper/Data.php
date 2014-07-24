<?php
class Zolago_Modago_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param      $categories
     * @param int  $level
     * @param int|bool $span
     *
     * @return array
     */
    public function  getCategoriesTree(Varien_Data_Tree_Node_Collection $categories, $level = 1, $span = false)
    {
        $tree = array();
        foreach ($categories as $category) {
            $cat = Mage::getModel('catalog/category')->load($category->getId());

            $tree[$category->getId()] = array(
                'name'           => $category->getName(),
                'url'            => rtrim(Mage::getUrl($cat->getUrlPath()), "/"),
                'category_id'    => $category->getId(),
                'level'          => $level,
                'products_count' => $cat->getProductCount()
            );
            if ($level == 1) {
                $tree[$category->getId()]['image'] = $cat->getImage();
            }
            if ($span && $level >= $span) {
                continue;
            }
            if ($category->hasChildren()) {
                $children = Mage::getModel('catalog/category')->getCategories($category->getId());
                $tree[$category->getId()]['has_dropdown'] = self::getCategoriesTree($children, $level + 1, $span);
            }else{
				$tree[$category->getId()]['has_dropdown']  = false;
			}
        }

        return $tree;
    }

    /**
     * @param $parentId
     *
     * @return array
     */
    public function getSubCategories($parentId)
    {
        $children = Mage::getModel('catalog/category')->getCategories($parentId);
        $subCategories = array();
        if (!empty($children)) {
            foreach ($children as $cat) {
                $subCategories[$cat->getId()] = array(
                    'url'   => $cat->getRequestPath(),
                    'label' => $cat->getName()
                );
            }
        }
        return $subCategories;
    }
}
