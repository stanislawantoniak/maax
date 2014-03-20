<?php
class Zolago_Dropship_Block_Adminhtml_Vendor_Helper_Form_Category_Tree extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    protected $_selectedIds = array();

    protected function _prepareLayout()
    {
        $this->setTemplate('zolagodropship/vendor/helper/form/category/tree.phtml');
    }

    public function getCategoryIds()
    {
        return $this->_selectedIds;
    }

    public function setCategoryIds($ids)
    {
        if (empty($ids)) {
            $ids = array();
        }
        elseif (!is_array($ids)) {
            $ids = array((int)$ids);
        }
        $this->_selectedIds = $ids;
        return $this;
    }

    protected function _getNodeJson($node, $level = 1)
    {
        $item = array();
        $item['text']= $this->escapeHtml($node->getName());

        if ($this->_withProductCount) {
             $item['text'].= ' ('.$node->getProductCount().')';
        }
        $item['id']  = $node->getId();
        $item['path'] = $node->getData('path');
        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        $item['allowDrop'] = false;
        $item['allowDrag'] = false;

        if ($node->hasChildren()) {
            $item['children'] = array();
            foreach ($node->getChildren() as $child) {
                $item['children'][] = $this->_getNodeJson($child, $level + 1);
            }
        }

        if (empty($item['children']) && (int)$node->getChildrenCount() > 0) {
            $item['children'] = array();
        }

        if (!empty($item['children'])) {
            $item['expanded'] = true;
        }

        if (in_array($node->getId(), $this->getCategoryIds())) {
            $item['checked'] = true;
        }

        return $item;
    }

    public function getRoot($parentNodeCategory=null, $recursionLevel=3)
    {
		if($this->getParentId()){
			$ids = $this->getCategoryIds();
			$categoryTreeResource = Mage::getResourceSingleton('catalog/category_tree');
            $ids    = $categoryTreeResource->getExistingCategoryIdsBySpecifiedIds($ids);
            $tree   = $categoryTreeResource->loadByIds($ids);			
			return $tree->getNodeById($this->getParentId());
		}
        return $this->getRootByIds($this->getCategoryIds());
    }
}
