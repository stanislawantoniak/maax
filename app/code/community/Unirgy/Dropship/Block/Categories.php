<?php

class Unirgy_Dropship_Block_Categories extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('udropship/categories.phtml');
    }
    protected function _prepareLayout()
    {
        return Mage_Core_Block_Abstract::_prepareLayout();
    }
    protected $_oldStoreId;
    protected $_unregUrlStore;
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if (!Mage::registry('url_store')) {
            $this->_unregUrlStore = true;
            Mage::register('url_store', Mage::app()->getStore());
        }
        $this->_oldStoreId = Mage::app()->getStore()->getId();
        Mage::helper('udropship/catalog')->setDesignStore(0, 'adminhtml');
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        return $this;
    }
    protected function _getUrlModelClass()
    {
        return 'core/url';
    }
    public function getUrl($route = '', $params = array())
    {
        if (!isset($params['_store']) && $this->_oldStoreId) {
            $params['_store'] = $this->_oldStoreId;
        }
        return parent::getUrl($route, $params);
    }
    protected function _afterToHtml($html)
    {
        Mage::helper('udropship/catalog')->setDesignStore();
        if ($this->_unregUrlStore) {
            $this->_unregUrlStore = false;
            Mage::unregister('url_store');
        }
        Mage::app()->setCurrentStore($this->_oldStoreId);
        return parent::_afterToHtml($html);
    }
    public function getLoadTreeUrl($expanded=null)
    {
        if ($this->hasForcedIdsString()) {
            $idName = $this->getIdName() ? $this->getIdName() : 'product_categories';
            $nameName = $this->getNameName() ? $this->getNameName() : 'category_ids';
            $idsString = $this->getForcedIdsString();
            return $this->getUrl('udropship/index/categoriesJson', array(
                 '_current'=>true,
                 'name_name'=>$nameName,
                 'id_name'=>$idName,
                 '_secure'=>Mage::app()->getStore()->isCurrentlySecure(),
                 'ids_string'=>$idsString,
            ));
        } elseif (Mage::helper('udropship')->isModuleActive('udprod')) {
            return $this->getUrl('udprod/vendor/categoriesJson', array('_current'=>true));
        }
    }
    public function getCategoryIds()
    {
        return $this->hasForcedIdsString()
            ? explode(',', $this->getForcedIdsString())
            : parent::getCategoryIds();
    }
    public function isReadonly()
    {
        return $this->hasForcedIdsString() ? false : parent::isReadonly();
    }
    public function render()
    {
        return $this->toHtml();
    }

    public function getCategoryChildrenJson($categoryId)
    {
        if (Mage::getStoreConfigFlag('udprod/general/show_hidden_categories')
            || !Mage::getSingleton('udropship/session')->isLoggedIn()
        ) {
            return parent::getCategoryChildrenJson($categoryId);
        }
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $node = $this->getRoot($category, 1)->getTree()->getNodeById($categoryId);

        if (!$node || !$node->hasChildren()) {
            return '[]';
        }

        $children = array();
        foreach ($node->getChildren() as $child) {
            if (!$child->getIsActive()) continue;
            $children[] = $this->_getNodeJson($child);
        }

        return Mage::helper('core')->jsonEncode($children);
    }
}