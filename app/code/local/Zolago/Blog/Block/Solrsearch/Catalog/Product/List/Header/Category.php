<?php

/**
 * Class Zolago_Blog_Block_Solrsearch_Catalog_Product_List_Header_Category
 */
class Zolago_Blog_Block_Solrsearch_Catalog_Product_List_Header_Category
    extends Zolago_Solrsearch_Block_Catalog_Product_List_Header_Category
{
    public function getCategoryBlogLink()
    {
        $category = $this->getCurrentCategory();

        $categoryBlogPostId = (int)$category->getCategoryBlogPostId();

        if (!$categoryBlogPostId)
            return;

        $blogPost = Mage::getModel("mpblog/post")->load($categoryBlogPostId);

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

        echo $blogPostLinkBlock->toHtml();

    }
}