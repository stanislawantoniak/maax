<?php
class Zolago_Dropship_Adminhtml_WidgetController 
	extends Mage_Adminhtml_Controller_Action{
	
	public function chooserAction()
    {
        $request = $this->getRequest();
		$parentId = $request->getParam("parent_id");
		$ids = $request->getParam('selected', array());
		if (is_array($ids)) {
			foreach ($ids as $key => &$id) {
				$id = (int) $id;
				if ($id <= 0) {
					unset($ids[$key]);
				}
			}

			$ids = array_unique($ids);
		} else {
			$ids = array();
		}


		$block = $this->getLayout()->createBlock(
				'zolagodropship/adminhtml_vendor_helper_form_category_tree', 'promo_widget_chooser_category_ids',
				array('js_form_object' => $request->getParam('form'))
			)
			->setCategoryIds($ids)
			->setParentId($parentId);

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

    

    /**
     * Get tree node (Ajax version)
     */
    public function categoriesJsonAction()
    {
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                    ->getTreeJson($category)
            );
        }
    }

    /**
     * Initialize category object in registry
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCategory()
    {
        $categoryId = (int) $this->getRequest()->getParam('id',false);
        $storeId    = (int) $this->getRequest()->getParam('store');

        $category   = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                    return false;
                }
            }
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);

        return $category;
    }
	
}