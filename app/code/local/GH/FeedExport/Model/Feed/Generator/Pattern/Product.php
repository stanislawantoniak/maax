<?php

/**
 * Class GH_FeedExport_Model_Feed_Generator_Pattern_Product
 */
class GH_FeedExport_Model_Feed_Generator_Pattern_Product extends Mirasvit_FeedExport_Model_Feed_Generator_Pattern_Product
{

    protected function _prepareProductCategory(&$product)
    {
        $category = null;
        $currentPosition = null;

        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->getSelect()
            ->joinInner(
                array('category_product' => $collection->getTable('catalog/category_product')),
                'category_product.category_id = entity_id AND category_product.product_id = ' . $product->getId(),
                array('product_position' => 'position')
            )
            ->order(new Zend_Db_Expr('`category_product`.`position` asc'));

        foreach ($collection as $cat) {
            $categoryInStoreTree = $this->getCategory($cat->getId());
            if ($categoryInStoreTree &&
                (is_null($category) || $cat->getLevel() > $category->getLevel()) &&
                (is_null($currentPosition) || $cat->getProductPosition() <= $currentPosition)
            ) {
                $category = $categoryInStoreTree;
                $currentPosition = $category->getProductPosition();
            }
        }

        if ($category
            //&& $category = $this->getCategory($category->getId())
        ) {
            $categoryPath = array($category->getName());
            $parentId = $category->getParentId();

            if ($category->getLevel() > $this->getRootCategory()->getLevel()) {
                $i = 0;
                while ($_category = $this->getCategory($parentId)) {

                    if ($_category->getLevel() <= $this->getRootCategory()->getLevel()) {
                        break;
                    }
                    $categoryPath[] = $_category->getName();
                    $parentId = $_category->getParentId();

                    $i++;
                    if ($i > 10 || $parentId == 0) {
                        break;
                    }
                }
            }

            $product->setCategory($category->getName());
            $product->setCategoryModel($category);
            $product->setCategoryId($category->getEntityId());
            $product->setCategoryPath(implode(' > ', array_reverse($categoryPath)));
        } else {
            $product->setCategory('');
            $product->setCategorySubcategory('');
        }
    }

}