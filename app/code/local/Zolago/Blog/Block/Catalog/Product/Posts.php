<?php

/**
 * Class Zolago_Blog_Block_Catalog_Product_Posts
 */
class Zolago_Blog_Block_Catalog_Product_Posts extends Mage_Catalog_Block_Product_Abstract
{

    protected $_productBlogItems;

    protected function _prepareData()
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = Mage::registry('product');

        $productBlogIds = [];

        $range = range(1, 3);

        foreach ($range as $i) {
            if (!empty($product->getData("product_blog_post_id_$i"))) {
                $productBlogIds[] = $product->getData("product_blog_post_id_$i");
            }
        }
        //Zend_Debug::dump($productBlogIds);


        $storeId = Mage::app()->getStore()->getId();

        $blogPostModel = Mage::getModel("mpblog/post");
        $blogPostCollection = $blogPostModel->getCollection()
            ->addFieldToFilter("post_id", array("in" => $productBlogIds))
            ->addFieldToFilter("status", Magpleasure_Blog_Model_Post::STATUS_ENABLED);

        $blogPostCollection->getSelect()
            ->joinLeft(
                array(
                    'post_store' => 'mp_blog_posts_store'
                ),
                "post_store.post_id = main_table.post_id",
                array()
            )
            ->where("post_store.store_id=?", $storeId);


        $this->_productBlogItems = $blogPostCollection;

        return $this;
    }

    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    /**
     * Count of blog post links
     * on the product page
     * @return int
     */
    public function getPostsCount()
    {
        return (int)Mage::getStoreConfig('mpblog/product/posts_count');
    }

}
