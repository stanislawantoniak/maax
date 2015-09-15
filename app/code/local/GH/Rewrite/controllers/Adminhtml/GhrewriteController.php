<?php

/**
 * GH Urlrewrites adminhtml controller
 */
class GH_Rewrite_Adminhtml_GhrewriteController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction() {
        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        $this->_title($rewriteHelper->__('GH URL Rewrite Management'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/rewrite');
        $this->_addContent($this->getLayout()->createBlock('ghrewrite/adminhtml_ghrewrite'));
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ghrewrite/adminhtml_ghrewrite_grid')->toHtml()
        );
    }

    public function loadcsvAction() {
        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        $this->_title($rewriteHelper->__('GH URL Rewrite Management'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/rewrite');
        $this->_addContent($this->getLayout()->createBlock('ghrewrite/adminhtml_ghrewrite_csv'));
        $this->renderLayout();
    }

    public function massDeleteAction() {
        $ids = $this->getRequest()->getParam('url_id');
        if(!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
	            /** @var GH_Rewrite_Helper_Data $hlp */
	            $hlp = Mage::helper('ghrewrite');
				$suffix = $hlp::GH_URL_REWRITE_REDIRECTION_SUFFIX;
                foreach ($ids as $id) {
                    $row = Mage::getModel('ghrewrite/url')->load($id);
	                /** @var Mage_Core_Model_Resource_Url_Rewrite_Collection $rewrite */
	                $rewrite = Mage::getModel('core/url_rewrite')->getCollection();
	                $rewrite->addFieldToFilter('id_path',array("in"=>array($row['url'],$row['url'].$suffix)));
	                foreach($rewrite as $rewriteToRemove) {
		                if($rewriteToRemove->getIdPath() == $row['url'].$suffix) {
		                //when removing  don't just remove redirect, but reverse it for search engines to keep links where they were before
			                $targetPath = $rewriteToRemove->getRequestPath();
			                $requestPath = $rewriteToRemove->getTargetPath();
			                $rewriteToRemove
				                ->setRequestPath($requestPath)
				                ->setTargetPath($targetPath)
				                ->save();
		                } else {
			                $rewriteToRemove->delete();
		                }
	                }
                    $row->delete();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );

	            //clear categories url rewrites cache:
	            $this->clearCategoriesUrlRewriteCache();

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massGenerateAction() {
        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        $ids = $this->getRequest()->getParam('url_id');
        if(!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
	            $new = 0;
	            $updated = 0;

	            $coreUrlRewriteColumns = array(
		            'url_rewrite_id',
		            'store_id',
		            'id_path',
		            'request_path',
		            'target_path',
		            'is_system',
		            'options'
	            );


                foreach ($ids as $id) {
	                $ghUrl = Mage::getModel('ghrewrite/url')->load($id);

	                if ($ghUrl->getId()) {
		                /** @var Zolago_Catalog_Model_Category $category */
		                $category = Mage::getModel('zolagocatalog/category')->setStoreId($ghUrl->getStoreId())->load($ghUrl->getCategoryId());


		                //create http get string with filters
		                $getFiltersString = urldecode(http_build_query(array('fq' => json_decode($ghUrl['filters'], 1))));

		                //create redirect or update existing one
		                /** @var Mage_Core_Model_Url_Rewrite $redirect */
		                $redirect = Mage::getModel('core/url_rewrite');
		                $redirectCollection = $redirect->getCollection()->addFieldToFilter('id_path', $ghUrl['url'] . $rewriteHelper::GH_URL_REWRITE_REDIRECTION_SUFFIX)->load();
		                $redirectOld = $redirectCollection->getFirstItem();
		                if ($redirectOld->getId()) {
			                $redirect = $redirectOld;
		                }
		                $redirectTmp = array();
		                if ($redirect->getId()) {
			                $redirectTmp[$coreUrlRewriteColumns[0]] = $redirect->getId();
			                $updated++;
		                } else {
			                $new++;
		                }
		                $redirectTmp[$coreUrlRewriteColumns[1]] = $ghUrl['store_id'];
		                $redirectTmp[$coreUrlRewriteColumns[2]] = $ghUrl['url'] . $rewriteHelper::GH_URL_REWRITE_REDIRECTION_SUFFIX;
		                $redirectTmp[$coreUrlRewriteColumns[3]] = $category->getUrlPath() . '?' . $getFiltersString;
		                $redirectTmp[$coreUrlRewriteColumns[4]] = $ghUrl['url'];
		                $redirectTmp[$coreUrlRewriteColumns[5]] = 0;
		                $redirectTmp[$coreUrlRewriteColumns[6]] = 'RP'; //redirect permanently (301) - read from const
		                $redirect->setData($redirectTmp);
		                $redirect->save();

		                //create rewrite or update exiting one
		                /** @var Mage_Core_Model_Url_Rewrite $rewrite */
		                $rewrite = Mage::getModel('core/url_rewrite');
		                if ($ghUrl->getData('url_rewrite_id')) {
			                $rewriteOld = Mage::getModel('core/url_rewrite')->load($ghUrl->getData('url_rewrite_id'));
			                if ($rewriteOld->getId()) {
				                $rewrite = $rewriteOld;
			                }
		                }
		                $rewriteTmp = array();
		                if ($rewrite->getId()) {
			                $updated++;
			                $rewriteTmp[$coreUrlRewriteColumns[0]] = $rewrite->getId();
		                } else {
			                $new++;
		                }
		                $rewriteTmp[$coreUrlRewriteColumns[1]] = $ghUrl['store_id'];
		                $rewriteTmp[$coreUrlRewriteColumns[2]] = $ghUrl['url'];
		                $rewriteTmp[$coreUrlRewriteColumns[3]] = $ghUrl['url'];
		                $rewriteTmp[$coreUrlRewriteColumns[4]] = 'catalog/category/view/id/' . $ghUrl['category_id'] . '?' . $getFiltersString;
		                $rewriteTmp[$coreUrlRewriteColumns[5]] = 0;
		                $rewrite->setData($rewriteTmp);
		                $rewrite->save();

		                $ghUrl->setData('url_rewrite_id', $rewrite->getId());
		                $ghUrl->save();
	                } else {
		                Mage::throwException($rewriteHelper->__('Some other error occurred, please contact admin'));
	                }
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $rewriteHelper->__(
	                    "Total of generated rewrites: %d<br />New: %d<br />Updated: %d", $new+$updated, $new, $updated
                    )
                );

	            //clear categories url rewrites cache:
	            $this->clearCategoriesUrlRewriteCache();

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

	protected function clearCategoriesUrlRewriteCache() {
		$cache = Mage::app()->getCache();
		$cache->remove('filter_url_list');
	}
}
