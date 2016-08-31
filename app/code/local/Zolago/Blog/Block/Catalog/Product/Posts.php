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

        $productBlogIds = $this->collectPostIdsForProduct($product);

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

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function collectPostIdsForProduct(Mage_Catalog_Model_Product $product)
    {
        $productBlogIds = [];

        $range = range(1, 3);
        foreach ($range as $i) {
            if (empty($product->getData("product_blog_post_id_$i")))
                continue;

            $productBlogIds[$product->getData("product_blog_post_id_$i")] = $product->getData("product_blog_post_id_$i");
        }
        unset($range);

        $postsCount = $this->getPostsCount();
        if (count($productBlogIds) >= $postsCount)
            return $productBlogIds;

        //Get products from the category
        $productCategoryId = $product->getCategory()->getId();

        $category = Mage::getModel('catalog/category')->load($productCategoryId);

        $range = range(1, 5);
        foreach ($range as $i) {
            if (empty($category->getData("product_blog_post_id_$i")))
                continue;

            if (count($productBlogIds) >= $postsCount)
                break;

            $productBlogIds[$category->getData("product_blog_post_id_$i")] = $category->getData("product_blog_post_id_$i");
        }
        unset($range);


        return $productBlogIds;
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


    public function hasThumbnail($post)
    {
        $src = $post->getListThumbnail() ? $post->getListThumbnail() : $post->getPostThumbnail();
        return !!$src;
    }

    public function getThumbnailSrc($post)
    {
        $src = $post->getListThumbnail() ? $post->getListThumbnail() : $post->getPostThumbnail();
        if ($src){
            return $this->_getThumbnailSrc($src, 100);
        }

        return false;
    }

    protected function _getThumbnailSrc($src, $width, $height = null)
    {
        $imageHelper = Mage::helper('mpblog')->getCommon()->getImage();
        $height = $height ? $height : $width;
        $imageHelper->init($src)->adaptiveResize($width, $height);
        return $imageHelper->__toString();
    }
}
