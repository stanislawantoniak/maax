<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Sitemap extends Mage_Sitemap_Model_Sitemap
{
    const MPBLOG_TYPE_BLOG = 'blog';
    const MPBLOG_TYPE_POST = 'post';
    const MPBLOG_TYPE_CATEGORY = 'category';
    const MPBLOG_TYPE_TAG = 'tag';
    const MPBLOG_TYPE_ARCHIVE = 'archive';

    /**
     * Add data to sitemap
     *
     * @param SimpleXMLElement $xml
     * @param array $data
     * @return SimpleXMLElement
     */
    protected function _addData(SimpleXMLElement $xml, $data = array())
    {
        $newNode = $xml->addChild("url");
        foreach ($data as $key=>$value){
            $newNode->addChild($key, $value);
        }
        return $xml;
    }

    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function generateLinks()
    {
        $links = array();

        $storeId = $this->getStoreId();
        $currentDate = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $includedParts = $this->_helper()->getSitemapIncluded($storeId ? $storeId : null);

        # Base Blog URL:
        if (in_array(self::MPBLOG_TYPE_BLOG, $includedParts)){

            $links[] = array(
                'url'  => $this->_helper()->_url($storeId)->getUrl(),
                'date' => $currentDate,
            );
        }

        # Import Posts
        if (in_array(self::MPBLOG_TYPE_POST, $includedParts)){

            /** @var Magpleasure_Blog_Model_Mysql4_Post_Collection $posts  */
            $posts = Mage::getModel('mpblog/post')->getCollection();
            if (!Mage::app()->isSingleStoreMode()){
                $posts->addStoreFilter($storeId);

            }

            $posts
                ->setDateOrder()
                ->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_ENABLED)
            ;

            foreach ($posts as $post){

                /** @var $post Magpleasure_Blog_Model_Post */
                $post->setStoreId($storeId);
                $links[] = array(
                    'url'  => $post->getPostUrl(),
                    'date' => $currentDate,
                );
            }
        }

        # Import Categories
        if (in_array(self::MPBLOG_TYPE_CATEGORY, $includedParts)){

            /** @var Magpleasure_Blog_Model_Mysql4_Category_Collection $categories  */
            $categories = Mage::getModel('mpblog/category')->getCollection();
            if (!Mage::app()->isSingleStoreMode()){
                $storeId = $this->getStoreId();
                $categories->addStoreFilter($storeId);

            }

            $categories
                ->setSortOrder('asc')
                ->addFieldToFilter('status', Magpleasure_Blog_Model_Category::STATUS_ENABLED)
            ;

            foreach ($categories as $category){

                /** @var $category Magpleasure_Blog_Model_Category */
                $category->setStoreId($storeId);
                $links[] = array(
                    'url'  => $category->getCategoryUrl(),
                    'date' => $currentDate,
                );
            }
        }

        # Import Tags
        if (in_array(self::MPBLOG_TYPE_TAG, $includedParts)){

            $storeId = Mage::app()->isSingleStoreMode() ? null : $this->getStoreId();

            /** @var Magpleasure_Blog_Model_Mysql4_Tag_Collection $tags  */
            $tags = Mage::getModel('mpblog/tag')->getCollection();
            $tags
                ->addWieghtData($storeId)
                ->setMinimalPostCountFilter($this->_helper()->getTagsMinimalPostCount($storeId))
                ->setPostStatusFilter(Magpleasure_Blog_Model_Post::STATUS_ENABLED)
                ->setNameOrder()
            ;

            foreach ($tags as $tag){

                /** @var $tag Magpleasure_Blog_Model_Tag */
                $tag->setStoreId($storeId);

                $links[] = array(
                    'url'  => $tag->getTagUrl(),
                    'date' => $currentDate,
                );
            }
        }

        # Import Archives
        if (in_array(self::MPBLOG_TYPE_ARCHIVE, $includedParts)){

            /** @var array $archives  */
            $archives = Mage::getModel('mpblog/archive')->getArchives($storeId);

            foreach ($archives as $archive){
                /** @var $archive Magpleasure_Blog_Model_Archive */
                $archive->setStoreId($storeId);
                $links[] = array(
                    'url'  => $archive->getArchiveUrl(),
                    'date' => $currentDate,
                );
            }
        }

        return $links;
    }

    public function generateXml()
    {
        parent::generateXml();

        Mage::dispatchEvent('sitemap_sitemap_generate', array('sitemap'=>$this));

        $storeId = $this->getStoreId();

        if (!$this->_helper()->getSitemapEnabled($storeId ? $storeId : null)){
            return $this;
        }

        $xmlPath = $this->getPath().$this->getSitemapFilename();
        $date = new Zend_Date();

        if (file_exists($xmlPath) && is_file($xmlPath) && is_writable($xmlPath) ){
            try {
                $xml = simplexml_load_file($xmlPath);

                foreach ($this->generateLinks() as $item){
                    $itemDate = isset($item['date']) ? $item['date'] : $date->toString('Y-m-d');
                    $this->_addData($xml, array(
                        'loc' => $item['url'],
                        'lastmod' => $itemDate,
                        'changefreq' => 'daily',
                        'priority' => '0.2',
                    ));
                }

                file_put_contents($xmlPath, $xml->asXML());

            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $this;
    }
}