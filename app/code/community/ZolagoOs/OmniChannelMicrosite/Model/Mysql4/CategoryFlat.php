<?php

class ZolagoOs_OmniChannelMicrosite_Model_Mysql4_CategoryFlat extends Mage_Catalog_Model_Resource_Category_Flat
{
    protected $_lolaNodes=array();
    protected $_lolaLoaded=array();
    public function getNodes($parentId, $recursionLevel = 0, $storeId = 0)
    {
        $lolaKey = '0';
        if (Mage::helper('lolavendor')->useVendorFilter()) {
            $lolaKey = Mage::helper('lolavendor')->useVendorFilter()->getId();
        }
        if (empty($this->_lolaLoaded[$lolaKey])) {
            $selectParent = $this->_getReadAdapter()->select()
                ->from($this->getMainStoreTable($storeId))
                ->where('entity_id = ?', $parentId);
            if ($parentNode = $this->_getReadAdapter()->fetchRow($selectParent)) {
                $parentNode['id'] = $parentNode['entity_id'];
                $parentNode = Mage::getModel('catalog/category')->setData($parentNode);
                $this->_lolaNodes[$lolaKey][$parentNode->getId()] = $parentNode;
                $nodes = $this->_loadNodes($parentNode, $recursionLevel, $storeId);
                $childrenItems = array();
                foreach ($nodes as $node) {
                    $pathToParent = explode('/', $node->getPath());
                    array_pop($pathToParent);
                    $pathToParent = implode('/', $pathToParent);
                    $childrenItems[$pathToParent][] = $node;
                }
                $this->addChildNodes($childrenItems, $parentNode->getPath(), $parentNode);
                $childrenNodes = $this->_lolaNodes[$lolaKey][$parentNode->getId()];
                if ($childrenNodes->getChildrenNodes()) {
                    $this->_lolaNodes[$lolaKey] = $childrenNodes->getChildrenNodes();
                }
                else {
                    $this->_lolaNodes[$lolaKey] = array();
                }
                $this->_lolaLoaded[$lolaKey] = true;
            }
        }
        return $this->_lolaNodes[$lolaKey];
    }
}