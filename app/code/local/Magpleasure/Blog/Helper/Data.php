<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
class Magpleasure_Blog_Helper_Data extends Mage_Core_Helper_Abstract
{
    const KEY_CUSTOMER_NAME = 'mpblog-customer-name';
    const KEY_CUSTOMER_EMAIL = 'mpblog-customer-email';
    const KEY_IS_SUBSCRIBED = 'mpblog-is-subscribed';

    const BLOG_LAYOUT = 'one_column';

    const CONFIG_REGISTRY = 'mpblog_config';
    const CONFIG_FILE = 'blog.xml';

    protected function _getConfig()
    {
        if (!Mage::registry(self::CONFIG_REGISTRY)) {

            $config = Mage::getConfig()->loadModulesConfiguration(self::CONFIG_FILE);
            Mage::register(self::CONFIG_REGISTRY, $config);
        }

        return Mage::registry(self::CONFIG_REGISTRY);
    }

    /**
     * Core
     *
     * @return Mage_Core_Helper_Data
     */
    public function _core()
    {
        return Mage::helper('core');
    }

    /**
     * Data about Uploaded Images
     *
     * @return Magpleasure_Blog_Helper_Provider_Images
     */
    public function getImagesProvider()
    {
        return Mage::helper('mpblog/provider_images');
    }

    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    public function getCommon()
    {
        return Mage::helper('magpleasure');
    }

    public function getFooterEnabled()
    {
        return Mage::getStoreConfig('mpblog/footer/enabled');
    }

    public function getFooterPosition()
    {
        return Mage::getStoreConfig('mpblog/footer/position');
    }

    public function getFooterLabel()
    {
        return Mage::getStoreConfig('mpblog/footer/label');
    }

    public function getMenuEnabled()
    {
        return Mage::getStoreConfig('mpblog/menu/enabled');
    }

    public function getMenuPosition()
    {
        return Mage::getStoreConfig('mpblog/menu/position');
    }

    public function getMenuLabel()
    {
        return Mage::getStoreConfig('mpblog/menu/label');
    }

    public function getUseCategories()
    {
        return !!Mage::getStoreConfig('mpblog/post/display_categories');
    }

    public function getUseTags()
    {
        return !!Mage::getStoreConfig('mpblog/post/display_tags');
    }

    public function getBlogPostfix()
    {
        return Mage::getStoreConfig('mpblog/redirect/url_postfix');
    }

    public function getBlogMetaTitle()
    {
        return Mage::getStoreConfig('mpblog/seo/meta_title');
    }

    public function getShowAuthor()
    {
        return Mage::getStoreConfig('mpblog/post/display_author');
    }

    public function getShowPrintLink()
    {
        return Mage::getStoreConfig('mpblog/post/display_print');
    }

    public function getBlogMetaTags()
    {
        return Mage::getStoreConfig('mpblog/seo/meta_tags');
    }

    public function getBlogMetaDescription()
    {
        return Mage::getStoreConfig('mpblog/seo/meta_description');
    }

    public function getIconColorClass()
    {
        return Mage::getStoreConfig('mpblog/style/color_sheme');
    }

    /**
     * Retrieves Layout Code
     *
     * @return string
     */
    public function getLayoutCode()
    {
        return self::BLOG_LAYOUT;
    }

    public function getSeoTitle()
    {
        return Mage::getStoreConfig('mpblog/seo/title');
    }

    public function getRecentPostsLimit()
    {
        return Mage::getStoreConfig('mpblog/recent_posts/record_limit');
    }

    public function getRecentPostsDisplayDate()
    {
        return Mage::getStoreConfig('mpblog/recent_posts/display_date');
    }

    public function getRecentCommentsDisplayDate()
    {
        return Mage::getStoreConfig('mpblog/comments/display_date');
    }

    public function getRecentCommentsDisplayShort()
    {
        return Mage::getStoreConfig('mpblog/comments/display_short');
    }

    public function getCommentsNoFollow()
    {
        return Mage::getStoreConfig('mpblog/comments/nofollow');
    }

    public function getRecentPostsDisplayShort()
    {
        return Mage::getStoreConfig('mpblog/recent_posts/display_short');
    }

    public function getRecentPostsShortLimit()
    {
        return Mage::getStoreConfig('mpblog/recent_posts/short_limit');
    }

    public function getTagsMinimalPostCount($storeId = null)
    {
        return Mage::getStoreConfig('mpblog/tags/minimal_post_count', $storeId);
    }

    public function getTagsMtWidth()
    {
        return Mage::getStoreConfig('mpblog/tags/mt_width');
    }

    public function getTagsMtHeight()
    {
        return Mage::getStoreConfig('mpblog/tags/mt_height');
    }

    public function getTagsMtBackground()
    {
        return Mage::getStoreConfig('mpblog/tags/mt_background');
    }

    public function getTagsMtTextcolor()
    {
        return Mage::getStoreConfig('mpblog/tags/mt_textcolor');
    }

    public function getTagsMtTextcolor2()
    {
        return Mage::getStoreConfig('mpblog/tags/mt_textcolor2');
    }

    public function  getTagsMtHiColor()
    {
        return Mage::getStoreConfig('mpblog/tags/mt_hicolor');
    }

    public function getTagsMtEnabled()
    {
        return Mage::getStoreConfig('mpblog/tags/mt_enabled');
    }

    public function getCommentNotificationsEnabled()
    {
        return Mage::getStoreConfig('mpblog/notify_customer_comment_replyed/enabled');
    }

    public function getCommentsEnabled()
    {
        return Mage::getStoreConfig('mpblog/comments/use_comments');
    }

    public function getCommentsAllowGuests()
    {
        return Mage::getStoreConfig('mpblog/comments/allow_guests');
    }

    public function getCommentsAutoapprove()
    {
        return Mage::getStoreConfig('mpblog/comments/autoapprove');
    }

    public function getCommentsLimit()
    {
        return Mage::getStoreConfig('mpblog/comments/record_limit');
    }

    public function getRssDisplayOnList($storeId = null)
    {
        return Mage::getStoreConfig('mpblog/rss/display_on_list', $storeId);
    }

    public function getRssPost($storeId = null)
    {
        return Mage::getStoreConfig('mpblog/rss/post_feed', $storeId);
    }

    public function getRssCatgeory($storeId = null)
    {
        return Mage::getStoreConfig('mpblog/rss/category_feed', $storeId);
    }

    public function getRssComment($storeId = null)
    {
        return Mage::getStoreConfig('mpblog/rss/comment_feed', $storeId);
    }

    public function getSocialEnabled()
    {
        return Mage::getStoreConfig('mpblog/social/enabled');
    }

    public function getPostsLimit()
    {
        return Mage::getStoreConfig('mpblog/list/count_per_page');
    }

    public function getDisplayViews()
    {
        return Mage::getStoreConfig('mpblog/post/display_views');
    }

    public function getDateFormat()
    {
        return Mage::getStoreConfig('mpblog/post/date_manner');
    }

    public function getPublisherFacebook()
    {
        return Mage::getStoreConfig('mpblog/publisher/facebook');
    }

    public function getPublisherTwitter()
    {
        return Mage::getStoreConfig('mpblog/publisher/twitter');
    }

    /**
     * Retrieves enabled social buttons
     *
     * @return array
     */
    public function getSocialNetworks()
    {
        return explode(",", Mage::getStoreConfig('mpblog/social/networks'));
    }

    /**
     * Sitemap Enabled
     *
     * @param null $storeId
     * @return mixed
     */
    public function getSitemapEnabled($storeId = null)
    {
        return Mage::getStoreConfig('mpblog/sitemap/enabled', $storeId);
    }

    public function getSitemapIncluded($storeId = null)
    {
        return explode(",", Mage::getStoreConfig('mpblog/sitemap/include', $storeId));
    }

    /**
     * Date Processing Helper
     *
     * @return Magpleasure_Blog_Helper_Date
     */
    public function _date()
    {
        /** @var $urlHelper Magpleasure_Blog_Helper_Date */
        $urlHelper = Mage::helper('mpblog/date');

        return $urlHelper;
    }

    /**
     * Url Helper
     *
     * @param null $storeId
     * @return Magpleasure_Blog_Helper_Url
     */
    public function _url($storeId = null)
    {
        /** @var $urlHelper Magpleasure_Blog_Helper_Url */
        $urlHelper = Mage::helper('mpblog/url');
        $urlHelper->setStoreId($storeId ? $storeId : Mage::app()->getStore()->getId());

        return $urlHelper;
    }

    /**
     * Page Layout Helper
     *
     * @return Mage_Page_Helper_Layout
     */
    public function _layout()
    {
        return Mage::helper('page/layout');
    }

    /**
     * Email Notifier
     *
     * @return Magpleasure_Blog_Helper_Notifier
     */
    public function _notifier()
    {
        return Mage::helper('mpblog/notifier');
    }

    /**
     * Strings
     *
     * @return Magpleasure_Blog_Helper_Strings
     */
    public function _strings()
    {
        return Mage::helper('mpblog/strings');
    }

    /*
     * Recursively searches and replaces all occurrences of search in subject values replaced with the given replace value
     *
     * @param string $search The value being searched for
     * @param string $replace The replacement value
     * @param array $subject Subject for being searched and replaced on
     * @return array Array with processed values
     */
    public function recursiveReplace($search, $replace, $subject)
    {
        if (!is_array($subject))
            return $subject;

        foreach ($subject as $key => $value)
            if (is_string($value))
                $subject[$key] = str_replace($search, $replace, $value);
            elseif (is_array($value))
                $subject[$key] = self::recursiveReplace($search, $replace, $value);

        return $subject;
    }

    public function getHeaderHtml($post = null)
    {
        $details = Mage::app()->getLayout()->createBlock('mpblog/content_post_details');
        if ($details) {
            $details
                ->setPost($post)
                ->setTemplate("mpblog/post/header.phtml");;
            return $details->toHtml();
        }

        return false;
    }

    public function getFooterHtml($post = null)
    {
        $details = Mage::app()->getLayout()->createBlock('mpblog/content_post_details');
        if ($details) {
            $details
                ->setPost($post)
                ->setTemplate("mpblog/post/footer.phtml");;
            return $details->toHtml();
        }
        return false;
    }

    /**
     * Render
     *
     * @return Magpleasure_Blog_Helper_Comment_Render
     */
    public function _render()
    {
        return Mage::helper("mpblog/comment_render");
    }

    /**
     * Importer
     *
     * @return Magpleasure_Blog_Helper_Import
     */
    public function _importer($type)
    {
        # Validate Type
        if (!$type) {
            Mage::throwException("There's no Type of Importer defined.");
        }

        # Try to get Helper Name
        $helper = $this
            ->getCommon()
            ->getConfig()
            ->getValueFromPath("import/{$type}/helper", $this->_getConfig());

        # Validate if we got Helper
        if (!$helper) {
            Mage::throwException("Import Helper isn't found. Sorry.");
        }

        $importer = Mage::helper($helper);

        return $importer;
    }

    /**
     * Comment Secure
     *
     * @return Magpleasure_Blog_Helper_Comment_Secure
     */
    public function _secure()
    {
        return Mage::helper("mpblog/comment_secure");
    }

    /**
     * FirePHP
     *
     * @return Inchoo_Developer_Helper_Firephp_Data
     */
    public function _console()
    {
        return Mage::helper('firephp');
    }

    public function checkForPrefix($title)
    {
        if ($prefix = Mage::getStoreConfig('mpblog/seo/title')) {
            $title = $prefix . " - " . $title;
        }
        return $title;
    }

    /**
     * Wrapper for standart strip_tags() function with extra functionality for html entities
     *
     * @param string $data
     * @param string $allowableTags
     * @param bool $escape
     * @return string
     */
    public function stripTags($data, $allowableTags = null, $escape = false)
    {
        $result = strip_tags($data, $allowableTags);
        return $escape ? $this->escapeHtml($result, $allowableTags) : $result;
    }

    public function getConfigValue($type, $name)
    {
        return $this->_getConfig()->getNode("import/{$type}/{$name}");
    }

    /**
     * Customer Session
     *
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function saveCommentorName($name)
    {
        $this->getCustomerSession()->setData(self::KEY_CUSTOMER_NAME, $name);
        return $this;
    }

    public function loadCommentorName()
    {
        return $this->getCustomerSession()->getData(self::KEY_CUSTOMER_NAME);
    }

    public function saveCommentorEmail($email)
    {
        $this->getCustomerSession()->setData(self::KEY_CUSTOMER_EMAIL, $email);
        return $this;
    }

    public function loadCommentorEmail()
    {
        return $this->getCustomerSession()->getData(self::KEY_CUSTOMER_EMAIL);
    }

    public function saveIsSubscribed($value)
    {
        $this->getCustomerSession()->setData(self::KEY_IS_SUBSCRIBED, $value);
        return $this;
    }

    public function loadIsSubscribed()
    {
        return $this->getCustomerSession()->getData(self::KEY_IS_SUBSCRIBED);
    }

    /**
     * Retrieves global timezone offset in seconds
     *
     * @param boolean $isMysql If true retrieves mysql formmatted offset (+00:00) in hours
     * @return int
     */
    public function getTimeZoneOffset($isMysql = false)
    {
        return $this->_date()->getTimeZoneOffset($isMysql);
    }

    /**
     * Prepare JSON
     *
     * @param array $data
     * @return string
     */
    public function getJSON($data)
    {
        $parts = array();
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $sVal = $value ? 'true' : 'false';
            } elseif (is_numeric($value)) {
                $sVal = (string)$value;
            } else {
                $sVal = "'{$value}'";
            }
            $parts[] = "{$key}:$sVal";
        }
        return "{" . implode(",", $parts) . "}";
    }

    /**
     * Redirect to Recent Post if requested Post is not found
     *
     * @return bool
     */
    public function getRedirectToSeoFormattedUrl()
    {
        return !!Mage::getStoreConfig('mpblog/redirect/redirect_to_seo_formatted_url');
    }

    /**
     * Category Data Helper
     *
     * @return Magpleasure_Blog_Helper_Data_Categories
     */
    public function getCategoryHelper()
    {
        return Mage::helper('mpblog/data_categories');
    }

    /**
     * Store Data Helper
     *
     * @return Magpleasure_Blog_Helper_Data_Store
     */
    public function getStoreHelper()
    {
        return Mage::helper('mpblog/data_store');
    }

    public function getDynamicCookieName()
    {
        return "mpblog_customer_comments";
    }

    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->getCommon()->getCore()->escapeHtml($data, $allowedTags);
    }

    /**
     * Admin Session
     *
     * @return Mage_Admin_Model_Session
     */
    public function getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }

    /**
     * Attribute Helper
     *
     * @return Magpleasure_Blog_Helper_Data_Attribute
     */
    public function getAttribute()
    {
        return Mage::helper('mpblog/data_attribute');
    }

    /**
     * Layot Helper
     *
     * @return Magpleasure_Blog_Helper_Data_Layout
     */
    public function getLayoutConfig()
    {
        return Mage::helper('mpblog/data_layout');
    }
}