<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Abstract extends Magpleasure_Blog_Block_Layout_Abstract
{


    /**
     * Route to get configuration
     *
     * @var string
     */
    protected $_route = 'abstract';

    protected $_title = 'Default Blog Title';

    /**
     * Page Config
     *
     * @return Mage_Page_Model_Config
     */
    protected function _getPageConfig()
    {
        return Mage::getSingleton('page/config');
    }

    protected function _prepareBreadcrumbs()
    {
        /** @var Mage_Page_Block_Html_Breadcrumbs $breadcrumbs  */
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs){
            $breadcrumbs->addCrumb('home', array(
                                'label' => $this->_helper()->__("Home"),
                                'title' => $this->_helper()->__("Home"),
                                'link' => Mage::getBaseUrl('web')
                            ));
        }
        return $this;
    }

    protected function _preparePage()
    {
        /** @var Mage_Page_Block_Html_Head $head  */
        $head = $this->getLayout()->getBlock('head');
        if ($head){
            $head->setTitle($this->getMetaTitle());
            $head->setKeywords($this->getKeywords());
            $head->setDescription($this->escapeHtml(str_replace("\n", "", $this->getDescription())));
        }

        $root = $this->getLayout()->getBlock('root');
        if ($root){
            
            $layout = $this->_getPageConfig()->getPageLayout($this->_helper()->getLayoutCode());
            if ($layout){
                $root->setTemplate($layout->getTemplate());
            }
        }

        $this->_prepareBreadcrumbs();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->_preparePage();

        return $this;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getMetaTitle()
    {
        return $this->getTitle();
    }

    public function getKeywords()
    {
        return '';
    }

    public function getDescription()
    {
        return '';
    }

    public function getHeaderHtml($post = null)
    {
        return $this->_helper()->getHeaderHtml($post);
    }

    public function getFooterHtml($post = null)
    {
        return $this->_helper()->getFooterHtml($post);
    }

}
