<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Post extends Magpleasure_Blog_Model_Abstract implements Magpleasure_Blog_Model_Interface
{
    /**
     * Zend_Date date format for Mysql requests
     */
    const MYSQL_ZEND_DATE_FORMAT = 'yyyy-MM-dd HH:mm:ss';

    const DUPLICATE_FLAG = 'mp_blog_post_duplicate_flag';
    const IMPORT_FLAG = 'mp_blog_post_import_flag';

    const STATUS_DISABLED = 0;
    const STATUS_HIDDEN = 1;
    const STATUS_ENABLED = 2;
    const STATUS_SCHEDULED = 3;
    const STATUS_DELETED = 4;
    const STATUS_DRAFT = 5;

    const CACHE_TAG = 'MPBLOG_POST';

    const CUT_LIMITER = '<!-- blogcut -->';
    const CUT_LIMITER_TAG = "<hr class=\"cutter\">";

    const PATTERN_LIMITER_FIND = '/<hr\s+class\s*=\s*\".*?\bcutter\b.*?\".*?>/i';
    const PATTERN_LIMIRER_CUT_AFTER = '/<hr\s+class\s*=\s*\".*?\bcutter\b.*?\".*?>.*/ism';

    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/post');
    }

    public function getOptionsArray()
    {
        return array(
            self::STATUS_ENABLED => $this->_helper()->__("Enabled"),
            self::STATUS_DISABLED => $this->_helper()->__("Disabled"),
            self::STATUS_HIDDEN => $this->_helper()->__("Hidden"),
            self::STATUS_SCHEDULED => $this->_helper()->__("Scheduled"),
        );
    }

    public function toOptionArray()
    {
        return $this->_helper()
            ->getCommon()
            ->getArrays()
            ->paramsToValueLabel($this->getOptionsArray())
        ;
    }

    public function getPostUrl($page = 1)
    {
        return $this
            ->_helper()
            ->_url($this->getStoreId())
            ->getUrl($this->getId(), Magpleasure_Blog_Helper_Url::ROUTE_POST, $page);
    }

    public function getCommentsEnabled()
    {
        return $this->_helper()->getCommentsEnabled() && $this->getData('comments_enabled');
    }

    public function duplicate()
    {
        $data = array();
        $this->_setDuplicateFlag();
        foreach ($this->getData() as $key => $value){
            if (!in_array($key, array('post_id', 'notify_on_enable', 'views', 'published_at'))){
                $data[$key] = $this->getData($key);
            }
        }

        $newPost = Mage::getModel('mpblog/post');
        $newPost
            ->addData($data)
            ->setStatus(self::STATUS_DISABLED)
            ->save()
            ;

        return $newPost;
    }

    /**
     * Apply DOM manipulations to image
     *
     * @param Magpleasure_Common_Helper_Simpledom_Dom_Node $image
     * @return $this
     */
    protected function _processDomImage(Magpleasure_Common_Helper_Simpledom_Dom_Node &$image)
    {
        $width = $image->getAttribute('width');
        $height = $image->getAttribute('height');
        $style = $image->getAttribute('style');

        if ($width){
            $image->removeAttribute('width');
        }

        if ($height){
            $image->removeAttribute('height');
        }

        $styles = array();
        if ($style) {

            $parts = explode(";", $style);
            foreach ($parts as $part) {

                if ($part) {

                    $keyVal = explode(":", trim($part));
                    if (isset($keyVal[0]) && isset($keyVal[1])) {
                        $styles[trim($keyVal[0])] = trim($keyVal[1]);
                    }
                }
            }
        }

        if (isset($styles['width']) || $width){
            $styles['max-width'] = isset($styles['width']) ? $styles['width'] : $width."px";
        }

        if (isset($styles['width'])){
            unset($styles['width']);
        }

        if (isset($styles['height'])){
            unset($styles['height']);
        }

        if (count($styles)){
            $style = "";
            foreach ($styles as $key=>$value){
                $style .= sprintf("%s:%s; ", $key, $value);
            }

            $image->setAttribute('style', trim($style));
        }

        return $this;
    }

    /**
     * Apply DOM manipulations to video
     *
     * @param Magpleasure_Common_Helper_Simpledom_Dom_Node $video
     * @return $this
     */
    protected function _processDomVideo(Magpleasure_Common_Helper_Simpledom_Dom_Node &$video)
    {
        $width = $video->getAttribute('width');
        $height = $video->getAttribute('height');
        $style = $video->getAttribute('style');

        if ($width){
            $video->removeAttribute('width');
        }

        if ($height){
            $video->removeAttribute('height');
        }

        $styles = array();
        if ($style) {

            $parts = explode(";", $style);
            foreach ($parts as $part) {

                if ($part) {

                    $keyVal = explode(":", trim($part));
                    if (isset($keyVal[0]) && isset($keyVal[1])) {
                        $styles[trim($keyVal[0])] = trim($keyVal[1]);
                    }
                }
            }
        }

        if (isset($styles['width'])){
            unset($styles['width']);
        }

        if (isset($styles['height'])){
            unset($styles['height']);
        }

        $styles['width'] = '100%';
        $styles['display'] = 'block';
        $styles['margin'] = 'auto';

        if ($width){
            $styles['max-width'] = $width.'px';
        }

        if ($height){
            $styles['height'] = $height.'px';
        }

        if (count($styles)){
            $style = "";
            foreach ($styles as $key=>$value){
                $style .= sprintf("%s:%s; ", $key, $value);
            }

            $video->setAttribute('style', trim($style));
        }

        $video->setAttribute('class', 'blog-video');

        return $this;
    }

    /**
     * Find images and make it responsive
     *
     * @param $dom
     * @return bool
     */
    protected function _buildResponsiveImages(Magpleasure_Common_Helper_Simpledom_Dom &$dom)
    {

        Varien_Profiler::start("mp::blog::responsive_images");

        $changed = false;
        foreach ($dom->find('img') as $image) {

            /** @var $image Magpleasure_Common_Helper_Simpledom_Dom_Node */
            $this->_processDomImage($image);
            $changed = true;
        }

        Varien_Profiler::stop("mp::blog::responsive_images");

        return $changed;
    }

    /**
     * Find images and make it responsive
     *
     * @param $dom
     * @return bool
     */
    protected function _buildResponsiveVideos(Magpleasure_Common_Helper_Simpledom_Dom &$dom)
    {

        Varien_Profiler::start("mp::blog::responsive_videos");

        $changed = false;
        foreach ($dom->find('iframe') as $iframe) {

            /** @var $iframe Magpleasure_Common_Helper_Simpledom_Dom_Node */
            $this->_processDomVideo($iframe);
            $changed = true;
        }

        Varien_Profiler::stop("mp::blog::responsive_videos");

        return $changed;
    }

    protected function _activateLightBoxes(Magpleasure_Common_Helper_Simpledom_Dom &$dom)
    {

        Varien_Profiler::start("mp::blog::add_light_boxes");

        $changed = false;
        foreach ($dom->find('img') as $image) {

            /** @var $image Magpleasure_Common_Helper_Simpledom_Dom_Node */
            /** @var $parent Magpleasure_Common_Helper_Simpledom_Dom_Node */
            $parent = $image->parent();

            if ($parent){
                if (strtolower($parent->tag) != "a"){

                    $imageSrc = $image->getAttribute("src");
                    $imageAlt = $image->getAttribute("alt");
                    $postId = $this->getId();

                    $imageHtml = $image->outertext;
                    $image->outertext =
                        '<a title="'.$imageAlt.'" href="'.$imageSrc.'" rel="lightbox[mpblog_'.$postId.']" target="_blank">'.
                        $imageHtml.
                        '</a>'
                    ;

                    $changed = true;

                } else {

                    if (!$parent->getAttribute("title")){

                        $imageAlt = $image->getAttribute("alt");
                        $parent->setAttribute("title", $imageAlt);
                        $changed = true;
                    }
                }
            }

        }

        Varien_Profiler::stop("mp::blog::add_light_boxes");

        return $changed;
    }

    /**
     * Inject changes to DOM
     *
     * @param $content
     * @return mixed
     */
    protected function _processResponsifyObjectsInDom($content)
    {
        Varien_Profiler::start("mp::blog::process_dom");

        $domHelper = $this->_helper()->getCommon()->getSimpleDOM();
        $dom = $domHelper->str_get_dom($content);


        $changed = false;

        # 1. Build responsive images
        if ($this->_buildResponsiveImages($dom)){
            $changed = true;
        }

        # 2. Build responsive videos
        if ($this->_buildResponsiveVideos($dom)){
            $changed = true;
        }

        # 3. Activate Light Boxes
        if ($this->_activateLightBoxes($dom)){
            $changed = true;
        }

        if ($changed){
            $content = $dom->__toString();
        }

        Varien_Profiler::stop("mp::blog::process_dom");

        return $content;
    }

    protected function _getContent($key)
    {
        $content = $this->getData($key);
        /** @var Mage_Widget_Model_Template_Filter $processor  */
        $processor = Mage::getModel('widget/template_filter');
        Varien_Profiler::start("mp::blog::filter_content");
        $content = $processor->filter($content);
        $content = str_replace('target="_self"', "", $content);

        $content = $this->_processResponsifyObjectsInDom($content);

        Varien_Profiler::stop("mp::blog::filter_content");
        return $content;
    }

    public function getShortContent()
    {
        if ($this->getDisplayShortContent()){
            return $this->_getContent('short_content');
        } else {
            $content = $this->_getContent('full_content');
            $content = str_replace(
                Magpleasure_Blog_Model_Post::CUT_LIMITER,
                Magpleasure_Blog_Model_Post::CUT_LIMITER_TAG,
                $content);

            preg_match_all(self::PATTERN_LIMITER_FIND, $content, $matches);

            if (isset($matches[0][0])){

                $pattern = self::PATTERN_LIMIRER_CUT_AFTER;
                $test = preg_replace($pattern, "", $content);

                return $test;

            } else {
                return $content;
            }
        }
    }

    protected function _setDuplicateFlag()
    {
        Mage::register(self::DUPLICATE_FLAG, true, true);
        return $this;
    }

    public function getFullContent()
    {
        $content = $this->_getContent('full_content');
        $content = str_replace(array(self::CUT_LIMITER, self::CUT_LIMITER_TAG), "", $content);
        $content = preg_replace(self::PATTERN_LIMITER_FIND, "", $content);

        return $content;
    }

    public function isScheduled()
    {
        return $this->getStatus() == self::STATUS_SCHEDULED;
    }

    public function isHidden()
    {
        return $this->getStatus() == self::STATUS_HIDDEN;
    }

    public function activateScheduled()
    {
        if ($this->isScheduled()){
            $this->getResource()->forceSave();
            $this
                ->setStatus(self::STATUS_ENABLED)
                ->save()
                ;

            if ($this->getNotifyOnEnable()){
                $this->_helper()->_notifier()->notifyAboutPostPublish($this);
            }

        }
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->getPublishedAt() ? $this->getPublishedAt() : $this->getData('created_at');
    }

    public function getUrl($params = array(), $page = 1)
    {
        return $this->getPostUrl($page);
    }

    public function getViews()
    {
        return $this->getData('views') + $this->getFlyViews();
    }

    public function getFlyViews()
    {
        /** @var $views Magpleasure_Blog_Model_Mysql4_View_Collection  */
        $views = Mage::getModel('mpblog/view')->getCollection();
        $views
            ->addFieldToFilter('post_id', $this->getPostId())
            ;

        return $views->getSize();
    }

    public function getScheduledDate()
    {
        $date = new Zend_Date($this->getPublishedAt());
        $date->subSecond($this->_helper()->getTimezoneOffset());
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        return $date->toString($format);
    }

    public function getRecentPostId()
    {
        /** @var $collection Magpleasure_Blog_Model_Mysql4_Post_Collection */
        $collection = $this->getCollection();

        $collection
            ->setDateOrder()
            ->addFieldToFilter('status', self::STATUS_ENABLED)
            ->setPageSize(1)
        ;

        if (!Mage::app()->isSingleStoreMode()){
            $collection->addStoreFilter(Mage::app()->getStore()->getId());
        }

        if ($collection->getSize()){
            foreach ($collection as $post){
                return $post->getId();
            }
        }

        return false;
    }

    public function getCategoriesText()
    {
        $categoryLabels = array();

        $categories = $this->getCategories();
        if ($categories && is_array($categories) && count($categories)){
            foreach ($this->getCategories() as $categoryId){
                $categoryLabels[] = $this->_helper()->getCategoryHelper()->getCategoryName($categoryId);
            }
        }

        return implode(",", $categoryLabels);
    }

    public function getCommentMessages(Magpleasure_Blog_Model_Comment $parentComment = null)
    {
        $commentMessages = array();

        /** @var Magpleasure_Blog_Model_Mysql4_Comment_Collection $comments  */
        $comments = Mage::getModel('mpblog/comment')->getCollection();

        if (!Mage::app()->isSingleStoreMode()){
            $comments->addStoreFilter($this->getStoreId());
        }

        if ($parentComment){

            $comments->setReplyToFilter($parentComment->getId());
        } else {

            $comments
                ->addPostFilter($this->getId())
                ->setNotReplies()
            ;
        }

        $comments
            ->addActiveFilter()
            ->setDateOrder('ASC')
        ;

        foreach ($comments as $comment){

            /** @var Magpleasure_Blog_Block_Comments $comment */
            if ($message = $this->_helper()->escapeHtml($comment->getMessage())){
                $commentMessages[] = $this->_helper()->escapeHtml($comment->getMessage());
            }

            $commentMessages = array_merge($commentMessages, $this->getCommentMessages($comment));
        }

        return $commentMessages;
    }


    public function getCommentsText()
    {
        $commentMessages = array();
        if ($this->getCommentsEnabled()){
            $commentMessages = $this->getCommentMessages();
        }
        return implode("|", $commentMessages);
    }

    public function getDefaultStoreId()
    {
        $stores = $this->getStores();
        if ($stores && is_array($stores) && count($stores) && isset($stores[0])){
            $storeId = $stores[0];
        } else {
            $storeId = Mage::app()->getDefaultStoreView()->getId();
        }

        return $storeId;
    }

    /**
     * Process images of old WYSIWYG format
     * Just change it to simple image format
     * Because it will be broken in ne editor
     *
     * @return $this
     */
    public function processMediaTemplates()
    {
        $this->getResource()->processMediaTemplates($this);
        return $this;
    }

    /**
     * Process old content cutter
     *
     * @return $this
     */
    public function processCutter()
    {
        $this->getResource()->processCutter($this);
        return $this;
    }

    public function hasThumbnail()
    {
        return !! $this->getData('post_thumbnail') || !! $this->getData('list_thumbnail');
    }

    public function getPostThumbnailSrc()
    {
        $src = $this->getData('post_thumbnail') ? $this->getData('post_thumbnail') : $this->getData('list_thumbnail');
        return $this->_thumbnailSrc($src);
    }

    public function getListThumbnailSrc()
    {
        $src = $this->getData('list_thumbnail') ? $this->getData('list_thumbnail') : $this->getData('post_thumbnail');
        return $this->_thumbnailSrc($src);
    }

    protected function _thumbnailSrc($src)
    {
        if ($src){
            $imageHelper = $this->_helper()->getCommon()->getImage();
            $imageHelper->init($src);
            return $imageHelper->__toString();
        }

        return false;
    }


}