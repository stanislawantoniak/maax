<?php
class Zolago_Mapper_Block_Adminhtml_Mapper_Edit_Form_Categories 
	extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
	protected $_categoryIds;
    protected $_selectedNodes = null;

    /**
     * Specify template to use
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("zolagomapper/mapper/edit/form/categories.phtml");
    }

    protected function getCategoryIds()
    {
        return $this->getModel()->getCategoryIds();
    }

	/**
     *  @return Zolago_Mapper_Model_Mapper
     */
    public function getModel() {
        return Mage::registry('zolagomapper_current_mapper');
    }
	
    protected function isReadonly() {
        return false;
    }

    /**
     * Forms string out of getCategoryIds()
     *
     * @return string
     */
    public function getIdsString()
    {
        return implode(',', $this->getCategoryIds());
    }

    /**
     * Returns root node and sets 'checked' flag (if necessary)
     *
     * @return Varien_Data_Tree_Node
     */
    public function getRootNode()
    {
        $root = $this->getRoot();
        if ($root && in_array($root->getId(), $this->getCategoryIds())) {
            $root->setChecked(true);
        }
        return $root;
    }


        
    protected function _isParentSelectedCategory($node)
    {
        $result = false;
        // Contains string with all category IDs of children (not exactly direct) of the node
        $allChildren = Mage::getModel("catalog/category")->load($node->getId())->getAllChildren();
        
        if ($allChildren) {
            $selectedCategoryIds = $this->getCategoryIds();
            $allChildrenArr = explode(',', $allChildren);
            for ($i = 0, $cnt = count($selectedCategoryIds); $i < $cnt; $i++) {
                $isSelf = $node->getId() == $selectedCategoryIds[$i];
                if (!$isSelf && in_array($selectedCategoryIds[$i], $allChildrenArr)) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }
    
    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if (!is_null($parentNodeCategory) && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = Mage::registry('root');
        if (is_null($root)) {

			$rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
            
            $getCategoryIds = Mage::getModel("catalog/category")->load($rootId);
            /* @var $getCategoryIds Mage_Catalog_Model_Category */
            
            $ids = $this->getSelectedCategoriesPathIds($rootId);
            $ids = array_merge($ids, $getCategoryIds->getAllChildren(true));
            
            $tree = Mage::getResourceSingleton('catalog/category_tree')
                ->loadByIds($ids, false, false);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($rootCat, $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
                if ($this->isReadonly()) {
                    $root->setDisabled(true);
                }
            }
            elseif($root && $root->getId() == Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setName(Mage::helper('catalog')->__('Root'));
            }
            Mage::register('root', $root);
        }

        return $root;
    }
    
    protected function _getNodeJson($node, $level = 1)
    {
        $item = parent::_getNodeJson($node, $level);

//        if ($this->_isParentSelectedCategory($node)) {
//            $item['expanded'] = true;
//        }

        if (in_array($node->getId(), $this->getCategoryIds())) {
            $item['checked'] = true;
        }

        if ($this->isReadonly()) {
            $item['disabled'] = true;
        }

        return $item;
    }
    

    /**
     * 
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    public function getCategoryChildrenJson($category)
    {
        $node = $this->getRoot($category, 1)->getTree()->getNodeById($category->getId());
        if (!$node || !$node->hasChildren()) {
            return '[]';
        }

        $children = array();
        foreach ($node->getChildren() as $child) {
            $children[] = $this->_getNodeJson($child);
        }

        return Mage::helper('core')->jsonEncode($children);
    }

    /**
     * Returns URL for loading tree
     *
     * @param null $expanded
     * @return string
     */
    public function getLoadTreeUrl($expanded = null)
    {
        return $this->getUrl('*/*/categoriesJson', array('_current' => true));
    }
    
    /**
     * Return distinct path ids of selected categories
     *
     * @param mixed $rootId Root category Id for context
     * @return array
     */
    public function getSelectedCategoriesPathIds()
    {
        if(!$this->_selectedNodes){
            $ids = array();
            $categoryIds = $this->getCategoryIds();
            if (empty($categoryIds)) {
                return array();
            }
            $collection = Mage::getResourceModel('catalog/category_collection');
            $collection->addFieldToFilter('entity_id', array('in'=>$categoryIds));
            foreach ($collection as $item) {
                foreach ($item->getPathIds() as $id) {
                    if (!in_array($id, $ids)) {
                        $ids[] = $id;
                    }
                }
            }
            $this->_selectedNodes=$ids;
        }
        return $this->_selectedNodes;
    }
    
}
