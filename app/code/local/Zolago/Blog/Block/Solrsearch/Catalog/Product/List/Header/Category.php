<?php

/**
 * Class Zolago_Blog_Block_Solrsearch_Catalog_Product_List_Header_Category
 */
class Zolago_Blog_Block_Solrsearch_Catalog_Product_List_Header_Category
    extends Zolago_Solrsearch_Block_Catalog_Product_List_Header_Category
{

    /**
     *
     */
    public function getCategoryBlogLink()
    {
        $category = $this->getCurrentCategory();

        $categoryBlogPostId = (int)$category->getCategoryBlogPostId();

        if (!$categoryBlogPostId)
            return;


        $storeId = Mage::app()->getStore()->getId();

        $blogPostModel = Mage::getModel("mpblog/post");
        $blogPostCollection = $blogPostModel->getCollection()
            ->addFieldToFilter("post_id", $categoryBlogPostId)
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
        $blogPost = $blogPostCollection->getFirstItem();

        if (!$blogPost)
            return;

        $blogPostLinkBlock = $this
            ->getLayout()
            ->createBlock(
                'Mage_Core_Block_Template',
                'category_blog_link',
                array('template' => 'zolagoblog/categoryBlogLink.phtml')
            )
            ->setLinkTitle($blogPost->getTitle())
            ->setLinkUrl($blogPost->getUrl());

        return $blogPostLinkBlock->toHtml();

    }
}