<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    const FLAG_REDIRECT = 'mpblog_redirect_flag';
    const CACHE_PREFIX = 'mpblog_router_';
    const CACHE_TAG = 'MPBLOG_ROUTE';

    public function initControllerRouters($observer)
    {
        $front = $observer->getEvent()->getFront();
        $router = new Magpleasure_Blog_Controller_Router();
        $front->addRouter('mpblog', $router);
    }

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Magpleasure Cookie Helper
     *
     * @return Magpleasure_Common_Helper_Cookie
     */
    protected function _cookie()
    {
        return Mage::helper('magpleasure')->getCookie();
    }

    public function redirectFlagUp()
    {
        $key = self::FLAG_REDIRECT;
        $flag = $this->getRedirectFlag();
        if (!$flag){
            $this->_cookie()->setValue($key, 1);
        } else {
            $this->_cookie()->setValue($key, ++$flag);
        }
        return $this;
    }

    public function redirectFlagDown()
    {
        $key = self::FLAG_REDIRECT;
        $this->_cookie()->delete($key);
        return $this;
    }

    public function getRedirectFlag()
    {
        $key = self::FLAG_REDIRECT;
        return $this->_cookie()->getValue($key);
    }

    protected function _getPageVarName()
    {
        return Mage::getBlockSingleton('page/html_pager') ? Mage::getBlockSingleton('page/html_pager')->getPageVarName() : 'p';
    }

    /**
     * Response Current Page
     *
     * @param string $url
     * @return int|boolean
     */
    public function responsePage($url)
    {
        $pattern = "/\/([\d]{1,}){$this->_helper()->getBlogPostfix()}$/i";
        preg_match_all($pattern, $url, $matches);
        if (count($matches[1])){
            $page = $matches[1][0];
            if ($page > 1){
                return (int)$page;
            }
        }
        return false;
    }

    /**
     * Router Action
     *
     * @return Magpleasure_Blog_Router_Action
     */
    protected function _getNewResultInstance()
    {
        return Mage::getModel('mpblog/router_action');
    }

    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        Varien_Profiler::start("mpblog::router_match");

        # Identifier is cache key to receive data
		$identifier = $request->getPathInfo();
        $identifierForCache = $request->getRequestUri();
        $cacheKey = self::CACHE_PREFIX.md5($identifierForCache);

        if (!($action = $this->_helper()->getCommon()->getCache()->getPreparedValue($cacheKey))){

            # Define actions if not defined in cache

            if ($identifier[0] == '/'){
                $identifier = substr($identifier, 1, strlen($identifier));
            }

            if ($request->getParam($this->_getPageVarName())){
                $wrongPage = $request->getParam($this->_getPageVarName());
            } else {
                $wrongPage = 1;
            }

            $page = $this->responsePage($identifier);

            $action = $this->_getNewResultInstance();

            if ($postId = $this->_helper()->_url()->getPostId($identifier)) {

                if ($postId && !$this->_helper()->_url()->isRightSyntax($identifier, $postId) && ($this->getRedirectFlag() < 3)){

                    $action
                        ->setIsRedirect(true)
                        ->setRedirectFlag($identifier)
                        ->setModuleName('mpblog')
                        ->setControllerName('index')
                        ->setActionName('redirect')
                        ->setParam('url', $this->_helper()->_url()->getUrl($postId))
                        ->setAlias($identifier)
                        ->setResult(true)
                    ;

                } else {

                    $action
                        ->setIsRedirect(false)
                        ->setRedirectFlag($identifier)
                        ->setModuleName('mpblog')
                        ->setControllerName('index')
                        ->setActionName('post')
                        ->setParam('id', $postId)
                        ->setAlias($identifier)
                        ->setResult(true)
                    ;

                }

            } elseif ($categoryId = $this->_helper()->_url()->getCategoryId($identifier, $page)) {

                if ($categoryId && !$this->_helper()->_url()->isRightSyntax($identifier, $categoryId, Magpleasure_Blog_Helper_Url::ROUTE_CATEGORY, $page ? $page : $wrongPage) && ($this->getRedirectFlag() < 3)){

                    $action
                        ->setIsRedirect(true)
                        ->setRedirectFlag($identifier)
                        ->setModuleName('mpblog')
                        ->setControllerName('index')
                        ->setActionName('redirect')
                        ->setParam('url', $this->_helper()->_url()->getUrl($categoryId, Magpleasure_Blog_Helper_Url::ROUTE_CATEGORY, $wrongPage ? $wrongPage : $page))
                        ->setAlias($identifier)
                        ->setResult(true)
                    ;

                } else {

                    $action
                        ->setIsRedirect(false)
                        ->setRedirectFlag($identifier)
                        ->setModuleName('mpblog')
                        ->setControllerName('index')
                        ->setActionName('category')
                        ->setParam('id', $categoryId)
                        ->setParam($this->_getPageVarName(), $page)
                        ->setAlias($identifier)
                        ->setResult(true)
                    ;

                }

            } elseif ($tagId = $this->_helper()->_url()->getTagId($identifier, $page)) {

                if ($tagId && !$this->_helper()->_url()->isRightSyntax($identifier, $tagId, Magpleasure_Blog_Helper_Url::ROUTE_TAG, $page ? $page : $wrongPage) && ($this->getRedirectFlag() < 3)){

                    $action
                        ->setIsRedirect(true)
                        ->setRedirectFlag($identifier)
                        ->setModuleName('mpblog')
                        ->setControllerName('index')
                        ->setActionName('redirect')
                        ->setParam('url', $this->_helper()->_url()->getUrl($tagId, Magpleasure_Blog_Helper_Url::ROUTE_TAG, $wrongPage ? $wrongPage : $page))
                        ->setAlias($identifier)
                        ->setResult(true)
                    ;

                } else {

                    $action
                        ->setIsRedirect(false)
                        ->setRedirectFlag($identifier)
                        ->setModuleName('mpblog')
                        ->setControllerName('index')
                        ->setActionName('tag')
                        ->setParam('id', $tagId)
                        ->setParam($this->_getPageVarName(), $page)
                        ->setAlias($identifier)
                        ->setResult(true)
                    ;

                }

            } elseif ($archiveId = $this->_helper()->_url()->getArchiveId($identifier, $page)) {

                if ($archiveId && !$this->_helper()->_url()->isRightSyntax($identifier, $archiveId, Magpleasure_Blog_Helper_Url::ROUTE_ARCHIVE, $page ? $page : $wrongPage) && ($this->getRedirectFlag() < 3)){

                    $action
                        ->setIsRedirect(true)
                        ->setRedirectFlag($identifier)
                        ->setModuleName('mpblog')
                        ->setControllerName('index')
                        ->setActionName('redirect')
                        ->setParam('url', $this->_helper()->_url()->getUrl($archiveId, Magpleasure_Blog_Helper_Url::ROUTE_ARCHIVE, $wrongPage ? $wrongPage : $page))
                        ->setAlias($identifier)
                        ->setResult(true)
                    ;

                } else {

                    $action
                        ->setIsRedirect(false)
                        ->setRedirectFlag($identifier)
                        ->setModuleName('mpblog')
                        ->setControllerName('index')
                        ->setActionName('archive')
                        ->setParam('id', $archiveId)
                        ->setParam($this->_getPageVarName(), $page)
                        ->setAlias($identifier)
                        ->setResult(true)
                    ;

                }

            } elseif ($this->_helper()->_url()->isIndexRequest($identifier, $page)) {

                if ($this->_helper()->_url()->isIndexRequest($identifier, $page) && !$this->_helper()->_url()->isRightSyntax($identifier, null, Magpleasure_Blog_Helper_Url::ROUTE_POST, $page ? $page : $wrongPage) && ($this->getRedirectFlag() < 3)){

                    $action
                        ->setIsRedirect(true)
                        ->setRedirectFlag($identifier)
                        ->setModuleName('mpblog')
                        ->setControllerName('index')
                        ->setActionName('redirect')
                        ->setParam('url', $this->_helper()->_url()->getUrl(null, Magpleasure_Blog_Helper_Url::ROUTE_POST, $wrongPage ? $wrongPage : $page))
                        ->setAlias($identifier)
                        ->setResult(true)
                    ;

                } else {

                    $action
                        ->setIsRedirect(false)
                        ->setRedirectFlag($identifier)
                        ->setModuleName('mpblog')
                        ->setControllerName('index')
                        ->setActionName('index')
                        ->setParam($this->_getPageVarName(), $page)
                        ->setAlias($identifier)
                        ->setResult(true)
                    ;

                }

            } elseif ($this->_helper()->_url()->getIsSearchRequest($identifier, $page)) {

                $action
                    ->setIsRedirect(false)
                    ->setRedirectFlag($identifier)
                    ->setModuleName('mpblog')
                    ->setControllerName('index')
                    ->setActionName('search')
                    ->setParam($this->_getPageVarName(), $page)
                    ->setAlias($identifier)
                    ->setResult(true)
                ;

            }

            Varien_Profiler::stop("mpblog::router_match");

            $this->_helper()->getCommon()->getCache()->savePreparedValue($cacheKey, $action, 2600, array(self::CACHE_TAG));
        }

        # Result Action
        if ($action->getResult()){

            # Redirect Flag
            if ($action->getIsRedirect()){
                $this->redirectFlagUp();
            } else {
                $this->redirectFlagDown();
            }

            # Request Route
            $request
                ->setModuleName($action->getModuleName())
                ->setControllerName($action->getControllerName())
                ->setActionName($action->getActionName())
                ;

            # Alias
            $request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $action->getAlias());

            # Transfer Params
            foreach ($action->getParams() as $key => $value){
                $request->setParam($key, $value);
            }

            return true;

        } else {
            return false;
        }
    }

}
