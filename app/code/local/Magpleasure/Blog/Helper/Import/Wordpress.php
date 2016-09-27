<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
class Magpleasure_Blog_Helper_Import_Wordpress
    extends Magpleasure_Blog_Helper_Import_Abstract
{
    protected $_url;

    protected $_baseUrl;
    protected $_items;

    protected $_categories = array();
    protected $_thumbnails = array();

    protected $_wpToMpCatgory = array();
    protected $_wpToMpPost = array();

    protected $_connector;
    protected $_data = array();
    protected $_blogId = null;

    protected $_postMask = array(
        'title' => array('title', 'meta_title'),
        'content:encoded' => array('full_content'),
        'wp:post_name' => 'url_key',
        'dc:creator' => 'posted_by',
        'wp:comment_status' => 'use_comments',
        'wp:post_date_gmt' => array('published_at', 'created_at'),
        'category' => array('categories', 'tags'),
        'wp:comment' => 'comments',
        'list_thumbnail' => 'list_thumbnail',
        'post_thumbnail' => 'post_thumbnail',
    );

    protected $_beforePostProcessors = array(
        'use_comments' => '_useComments',
        'categories' => '_prepareCategories',
        'tags' => '_prepareTags',
        'post_thumbnail' => '_prepareThumbnail',
        'list_thumbnail' => '_prepareThumbnail',
        'full_content' => '_prepareWpContent',
        'images' => '_prepareImages',
    );

    protected $_afterPostProcessors = array(
        'comments' => '_processComments',
    );

    protected $_commentMask = array(
        'wp:comment_date_gmt' => array('created_at', 'updated_at'),
        'wp:comment_approved' => 'status',
        'wp:comment_author' => 'name',
        'wp:comment_author_email' => 'email',
        'wp:comment_content' => 'message',
    );

    protected $_beforeCommentProcessors = array(
        'status' => '_prepareCommentStatus',
        'message' => '_prepareCommentMessage',
    );

    protected $_commentStatusConvert = array(
        '0' => Magpleasure_Blog_Model_Comment::STATUS_PENDING,
        '1' => Magpleasure_Blog_Model_Comment::STATUS_APPROVED,
    );

    protected function _getUploadedData()
    {
        if (
            isset($_FILES['export_xml']['type']) &&
            $_FILES['export_xml']['type'] !== 'text/xml'
        ) {
            Mage::throwException("Something wrong with your file. Please use XML files only.");
        }

        if (
            isset($_FILES['export_xml']['error']) &&
            isset($_FILES['export_xml']['tmp_name']) &&
            $_FILES['export_xml']['tmp_name'] &&
            !$_FILES['export_xml']['error'] &&
            file_exists($_FILES['export_xml']['tmp_name']) &&
            ($content = file_get_contents($_FILES['export_xml']['tmp_name']))
        ) {
            # No Try-Catch because it will be
            # catched on the upper level
            return $this->_xmlToArray($content);
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    /**
     * @param $baseUrl
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->_baseUrl = $baseUrl;
        return $this;
    }

    public function import($verbose = false, $data = array())
    {
        $this->setVerbose($verbose);

        parent::import($verbose, $data);

        if (isset($data['file'])){

            $fileName = $data['file'];
            if (file_exists($fileName)){

                $content = file_get_contents($fileName);
                if ($content){

                    $importedData = $this->_xmlToArray($content);
                }
            }

        } else {
            $importedData = $this->_getUploadedData();
        }

        if (isset($importedData['rss']['channel']['item'])) {

            $channel = $importedData['rss']['channel'];
            $this->setBaseUrl($channel['link']);

            $this->_printLn("Import data from %s", $this->getBaseUrl());

            if ($channel['item'] && is_array($channel['item']) && count($channel['item'])) {

                # Set import Flag
                Mage::register(Magpleasure_Blog_Model_Post::IMPORT_FLAG, true, true);
                $this->setItems($channel['item']);
                foreach ($channel['item'] as $item) {

                    $this->importPost($item, $verbose);
                }
            }
        }

        return $this;
    }

    protected function _prepareImages($images, $post)
    {
        /** @var Magpleasure_Blog_Model_Post $post */
        if (
            !$post->hasThumbnail() &&
            $images &&
            is_array($images) &&
            count($images)
        ){
            $thumbnailUrl = str_replace(Mage::getBaseUrl('media'), "", $images[0]);
            $this->_printLn("--> Oops, the post has no any thumbnail but has images...");
            $this->_printLn("    Apply %s as thumbnail.", $thumbnailUrl);
            $post->setData('post_thumbnail', $thumbnailUrl);
            $post->setData('list_thumbnail', $thumbnailUrl);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * @param $items
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->_items = $items;
        return $this;
    }

    protected function _useComments($content)
    {
        return ($content == "open") ? 1 : 0;
    }

    protected function _getCategoryId($urlKey, $name)
    {
        if (!isset($this->_categories[$urlKey])) {

            # Intelligent load of proper category
            /** @var Magpleasure_Blog_Model_Mysql4_Category_Collection $categories */
            $categories = Mage::getModel('mpblog/category')->getCollection();
            $categories
                ->addStoreFilter($this->_data['stores'])
                ->addFieldToFilter('url_key', $urlKey)
            ;

            if (count($categories)){

                $category = $categories->getFirstItem();
                $category->load($category->getId());

            } else {

                /** @var Magpleasure_Blog_Model_Category $category */
                $category = Mage::getModel('mpblog/category');
                $category
                    ->setName($name)
                    ->setUrlKey($urlKey)
                    ->setStatus(Magpleasure_Blog_Model_Category::STATUS_ENABLED)
                    ->setStores($this->_data['stores'])
                    ->save();
            }

            $this->_categories[$urlKey] = $category->getId();
        }

        return $this->_categories[$urlKey];
    }

    protected function _prepareCategories($content)
    {
        $categories = array();
        if ($content && is_array($content) && count($content)) {

            if (isset($content["@domain"])) {

                if ($content["@domain"] == "category") {
                    $urlKey = $content["@nicename"];
                    $name = $content["$"];

                    if ($categoryId = $this->_getCategoryId($urlKey, $name)) {
                        $categories[] = $categoryId;
                    }
                }

            } else {

                foreach ($content as $entity) {

                    if ($entity["@domain"] == "category") {

                        $urlKey = $entity["@nicename"];
                        $name = $entity["$"];

                        if ($categoryId = $this->_getCategoryId($urlKey, $name)) {
                            $categories[] = $categoryId;
                        }
                    }
                }
            }
        }
        return $categories;
    }

    protected function _prepareTags($content)
    {
        $tags = array();
        if ($content && is_array($content) && count($content)) {
            if (isset($content["@domain"])) {
                if ($content["@domain"] == "post_tag") {
                    $tags[] = $content["$"];
                }
            } else {
                foreach ($content as $entity) {
                    if ($entity["@domain"] == "post_tag") {
                        $tags[] = $entity["$"];
                    }
                }
            }
        }
        
        return implode(",", $tags);
    }

    public function importPost(array $post, $verbose = false)
    {
        # Validate if post is unpublished
        if ($post['wp:status'] !== 'publish') {
            $this->_printLn("Skip unpublished post.");
            return $this;
        }

        # Validate if post is page
        if ($post['wp:post_type'] !== 'post') {
            $this->_printLn("Skip page %s.", $post['title']);
            return $this;
        }

        # Try to find same post
        $urlKey = $post['wp:post_name'];

        /** @var Magpleasure_Blog_Model_Mysql4_Post_Collection $posts */
        $posts = Mage::getModel('mpblog/post')->getCollection();

        $posts
            ->addStoreFilter(
                $this->_data['stores']
            )
            ->addFieldToFilter('url_key', $urlKey)
            ->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_ENABLED)
        ;

        if (count($posts)){
            $this->_printLn("Skip post %s because it exists.", $post['title']);
            return $this;
        }

        # Echo about start
        $this->_printLn("Import post %s.", $post['title']);

        # Prepare thumbnail information
        $thumbnailId = false;
        if (
            isset($post['wp:postmeta']) &&
            $post['wp:postmeta'] &&
            is_array($post['wp:postmeta'])
        ) {

            foreach ($post['wp:postmeta'] as $meta) {

                if (isset($meta['wp:meta_key']) && ($meta['wp:meta_key'] == '_thumbnail_id')) {
                    $thumbnailId = @$meta['wp:meta_value'];
                }
            }
        }

        $post['post_thumbnail'] = $thumbnailId;
        $post['list_thumbnail'] = $thumbnailId;

        # Create Model
        /** @var Magpleasure_Blog_Model_Post $mpPost */
        $mpPost = Mage::getModel('mpblog/post');
        $mpPost->setData(
            $this->_prepareDataFromArray(
                $this->_postMask,
                $post
            )
        );

        # Process values
        $this->_processor($this->_beforePostProcessors, $mpPost);

        $mpPost
            ->setStatus(Magpleasure_Blog_Model_Post::STATUS_ENABLED)
            ->setStores($this->_data['stores'])
            ->setDisplayShortContent(0)
        ;

        try {
            $mpPost->save();
        } catch (Exception $e){
            $this->_printLn("Skip post %s due to some error. %s", $post['title'], $e->getMessage());
            return $this;
        }

        $this->_processor($this->_afterPostProcessors, $mpPost);

        # Profit! ヽ(^ᴗ^)丿

        return $this;
    }

    protected function _prepareCommentStatus($wpStatus)
    {
        if (isset($this->_commentStatusConvert[$wpStatus])) {
            return $this->_commentStatusConvert[$wpStatus];
        }
        return false;
    }

    protected function _processComments($comments, $post)
    {
        $this->_printLn("--> Save comments for %s", $post->getTitle());

        if ($post->getId()){

            if (!$comments || !is_array($comments) || !count($comments)){
                $this->_printLn("    No comments found.");
                return $this;
            }

            if (isset($comments['wp:comment_id'])){
                $comments = array($comments);
            }

            $this->_printLn("    Import %s comments.", count($comments));

            foreach ($comments as $comment){

                /** @var Magpleasure_Blog_Model_Comment $mpComment */
                $mpComment = Mage::getModel('mpblog/comment');
                $mpComment->setData(
                    $this->_prepareDataFromArray($this->_commentMask, $comment)
                );
                $this->_processor($this->_beforeCommentProcessors, $mpComment);
                $mpComment
                    ->setPostId($post->getId())
                    ->setStoreId($this->_getStoreId())
                ;

                $mpComment->save();
            }
        }

        return $this;
    }

    protected function _prepareWpContent($content, $post)
    {
        # 1. Remove Captions
        $pattern = '/\[(\/|)caption(([\w\s1-9\=\"]{1,}\])|(\]))/';
        if ($newContent = preg_replace($pattern, "", $content)) {
            $content = $newContent;
        }

        # 2. Import Images to Local Media Folder
        try {

            $from = array();
            $to = array();

            preg_match_all('!http://[a-z0-9\-\.\/]+\.(?:jpe?g|png|gif)!Ui', $content, $matches);
            if (isset($matches[0]) && $matches[0] && is_array($matches[0])) {

                $remoteUrls = array_unique($matches[0]);
                foreach ($remoteUrls as $remoteUrl) {

                    $localUrl = $this->_localizeSrc($remoteUrl);
                    if ($localUrl) {
                        $from[] = $remoteUrl;
                        $to[] = $localUrl;
                    }
                }
            }

            $content = str_replace($from, $to, $content);

            # Store local images to use it as thumbnails if has no one
            $post->setData("images", $to);

        } catch (Exception $e) {
            $this->_helper()->getCommon()->getException()->logException($e);
        }

        # 3. Parse Youtube Embedded Links
        $youtubePattern = '<iframe class="youtube-player" type="text/html" ' .
            '{{attributes}} src="//www.youtube.com/embed/{{v}}" ' .
            'frameborder="0" allowFullScreen></iframe>';

        if (strpos($content, "[youtube") !== false) {

            preg_match_all('/\[youtube(.*?)](.*?)\[\/youtube\]/msi', $content, $matches);

            if (count($matches[0])) {
                for ($i = 0; $i <= count($matches[0]) - 1; $i++) {
                    if (isset($matches[0][$i]) && isset($matches[1][$i]) && isset($matches[2][$i])) {


                        $youtubeBBCode = $matches[0][$i];
                        $attributesHtml = "";
                        $youtubeUrl = false;

                        if ($youtubeBBCode) {

                            $attributesCode = $matches[1][$i];
                            $youtubeUrl = $matches[2][$i];

                            if ($attributesCode) {

                                preg_match_all('/(\w*?)=\"(.*?)\"/msi', $youtubeBBCode, $attrMatches);

                                if (isset($attrMatches[0]) && is_array($attrMatches[0])) {
                                    $attributesHtml = implode(" ", $attrMatches[0]);
                                }
                            }
                        }

                        $keyValParams = array();

                        $youtubeQuery = parse_url($youtubeUrl, PHP_URL_QUERY);

                        if ($youtubeQuery) {
                            $youtubeQuery = explode("&", $youtubeQuery);

                            foreach ($youtubeQuery as $keyValString) {
                                list($key, $value) = explode("=", $keyValString);

                                if ($key && !is_null($value)) {
                                    $keyValParams[$key] = $value;
                                }
                            }
                        }

                        $vParam = isset($keyValParams['v']) ? $keyValParams['v'] : false;

                        if ($vParam) {
                            $readyYoutubeCode = str_replace(
                                array("{{attributes}}", "{{src}}", "{{v}}"),
                                array($attributesHtml, $youtubeUrl, $vParam),
                                $youtubePattern
                            );

                            $content = str_replace($youtubeBBCode, $readyYoutubeCode, $content);
                        }
                    }
                }

            }
        }

        # 4. Replace NL to Paragraphs
        $moreTag = "<!--more-->";
        if (strpos($content, $moreTag) === false) {

            $content = $this->_wrapWithP($content, true);

        } else {

            $beforeMore = preg_replace("/{$moreTag}(.*)$/is", "", $content);
            $afterMore = preg_replace("/^(.*){$moreTag}/is", "", $content);

            $content =
                $this->_wrapWithP($beforeMore) .
                Magpleasure_Blog_Model_Post::CUT_LIMITER_TAG .
                $this->_wrapWithP($afterMore);
        }

        return $content;
    }

    protected function _wrapWithP($content, $insertMoreTag = false)
    {
        $parts = explode("\n", $content);
        $newParts = array();
        # Clean parts
        foreach ($parts as $part){
            $part = preg_replace('/\<[\/]{0,1}div[^\>]*\>/i', '', $part);
            $part = preg_replace('/\<[\/]{0,1}p[^\>]*\>/i', '', $part);
            if ($part){
                $newParts[] = $part;
            }
        }
        $parts = $newParts;

        if ($insertMoreTag && (count($parts) > 1)){

            $this->_printLn("--> Insert <more tag> after first paragraph.");
            $content = "<p>".$parts[0]."</p>";
            $content .= Magpleasure_Blog_Model_Post::CUT_LIMITER_TAG;
            $content .= "<p>" . implode("</p><p>", array_slice($parts, 1, count($parts) - 1) ) . "</p>";

        } else {
            $content = "<p>" . implode("</p><p>", $parts) . "</p>";
        }

        return $content;
    }

    protected function _prepareThumbnail($content)
    {
        if (!isset($this->_thumbnails[$content])) {

            $this->_printLn("--> Looking for thumbnail", $content);
            $thumbnail = '';

            foreach ($this->getItems() as $item) {

                if (
                    ($item['wp:status'] == 'inherit') &&
                    ($item['wp:post_id'] == $content)
                ) {

                    if (isset($item['guid']['$']) && $item['guid']['$']) {

                        $remoteSrc = $item['guid']['$'];

                        $localSrc = $this->_localizeSrc($remoteSrc);

                        $localSrc = str_replace(Mage::getBaseUrl('media'), "/", $localSrc);
                        $localSrc = str_replace("//", "/", $localSrc);

                        $thumbnail = $localSrc;
                    }
                }
            }

            $this->_thumbnails[$content] = $thumbnail;
        }

        return $this->_thumbnails[$content];
    }

    protected function _prepareCommentMessage($message)
    {
        $message = html_entity_decode($message);
        return strip_tags($message);
    }

    protected function _getStoreId()
    {
        if (count($this->_data['stores'])) {
            return $this->_data['stores'][0];
        }

        return Mage::app()->getDefaultStoreView()->getId();
    }


    protected function _xmlToArray($xml, $options = array())
    {
        $xml = simplexml_load_string($xml);
        return $this->_simpleXmlToArray($xml);
    }

    /**
     * Thanks to http://outlandish.com/blog/xml-to-json/
     * We love this code ヽ(^ᴗ^)丿
     *
     * @param $xml string
     * @param array $options
     * @return array
     */
    protected function _simpleXmlToArray($xml, $options = array())
    {
        $defaults = array(
            'namespaceSeparator' => ':',# you may want this to be something other than a colon
            'attributePrefix' => '@',   # to distinguish between attributes and nodes with the same name
            'alwaysArray' => array(),   # array of xml tag names which should always become arrays
            'autoArray' => true,        # only create arrays for tags which appear more than once
            'textContent' => '$',       # key used for the text content of elements
            'autoText' => true,         # skip textContent key if node has no attributes or child nodes
            'keySearch' => false,       # optional search and replace on tag and attribute names
            'keyReplace' => false       # replace values for above search values (as passed to str_replace())
        );
        $options = array_merge($defaults, $options);

        if ($xml){

        }

        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null; # add base (empty) namespace

        # get attributes from all namespaces
        $attributesArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
                # replace characters in attribute name
                if ($options['keySearch']) $attributeName =
                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
                $attributesArray[$attributeKey] = (string)$attribute;
            }
        }

        # get child nodes from all namespaces
        $tagsArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->children($namespace) as $childXml) {
                # recurse into child nodes
                $childArray = $this->_simpleXmlToArray($childXml, $options);
                list($childTagName, $childProperties) = each($childArray);

                # replace characters in tag name
                if ($options['keySearch']) $childTagName =
                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
                # add namespace prefix, if any
                if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;

                if (!isset($tagsArray[$childTagName])) {
                    # only entry with this key
                    # test if tags of this type should always be arrays, no matter the element count
                    $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                            ? array($childProperties) : $childProperties;
                } elseif (
                    is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                    === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    # key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    # key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
                }
            }
        }

        # get text content of node
        $textContentArray = array();
        $plainText = trim((string)$xml);
        if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;

        # stick it all together
        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        # return node as array
        return array(
            $xml->getName() => $propertiesArray
        );
    }
}