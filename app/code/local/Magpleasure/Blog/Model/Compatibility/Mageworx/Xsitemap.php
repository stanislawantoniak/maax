<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Compatibility_Mageworx_Xsitemap extends MageWorx_XSitemap_Model_Sitemap
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _generateBlogProPiece()
    {
        $this->_useIndex = Mage::getStoreConfigFlag('mageworx_seo/google_sitemap/use_index');
        $this->_splitSize = (int) Mage::getStoreConfig('mageworx_seo/google_sitemap/split_size') * 1024;
        $this->_maxLinks = (int) Mage::getStoreConfig('mageworx_seo/google_sitemap/max_links');

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));
        $this->_openXml($io, true);
        $storeId = $this->getStoreId();
        $date = Mage::getSingleton('core/date')->gmtDate('Y-m-d');

        $changefreq = (string) Mage::getStoreConfig('mageworx_seo/google_sitemap/blog_changefreq');
        $priority = (string) Mage::getStoreConfig('mageworx_seo/google_sitemap/blog_priority');

        if (!$this->_helper()->getSitemapEnabled()){
            return $this;
        }

        /** @var Magpleasure_Blog_Model_Sitemap $sitemapModel */
        $sitemapModel = Mage::getModel('mpblog/sitemap');
        $sitemapModel->setStoreId($storeId);

        foreach ($sitemapModel->generateLinks() as $item) {

            $itemDate = isset($item['date']) ? $item['date'] : $date;

            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                $item['url'],
                $itemDate,
                $changefreq,
                $priority
            );

            $io->streamWrite($xml);
            $this->_checkSitemapLimits($io);
        }

        return $this;
    }

    public function generateXml($entity=false)
    {
        if ($entity == 'blog'){
            $this->_generateBlogProPiece();
        } else {
            parent::generateXml($entity);
        }
        return $this;
    }

}