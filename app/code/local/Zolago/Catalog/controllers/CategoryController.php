<?php
require_once Mage::getModuleDir('controllers', "Mage_Catalog") . DS . "CategoryController.php";
class Zolago_Catalog_CategoryController extends Mage_Catalog_CategoryController {



	public function viewAction()
	{
		$this->getRequest()->setParam('q','');

		if ($category = $this->_initCatagory()) {

			$fq = $this->getRequest()->getParam('fq', '');
			if (!empty($fq)) {
				//if filter params then set display_mode to PRODUCTS
				$category->setDisplayMode(Mage_Catalog_Model_Category::DM_PRODUCT);

				if (in_array("campaign_regular_id", array_keys($fq)) || in_array("campaign_info_id", array_keys($fq))) {
					$campaign = $category->getCurrentCampaign();
					if (!$campaign) {
						//redirect to category
						unset($fq["campaign_regular_id"]);
						unset($fq["campaign_info_id"]);

						$url = Mage::getUrl($category->getUrlPath(), array("_query" => array("fq" => $fq)));
						header("Location: {$url}", true, 302);
						exit;
					}
				}
			}



			$design = Mage::getSingleton('catalog/design');
			$settings = $design->getDesignSettings($category);

			// apply custom design
			if ($settings->getCustomDesign()) {
				$design->applyCustomDesign($settings->getCustomDesign());
			}

			Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());

			$update = $this->getLayout()->getUpdate();
			$update->addHandle('default');

			if (!$category->hasChildren()) {
				$update->addHandle('catalog_category_layered_nochildren');
			}

			$this->addActionLayoutHandles();
			$update->addHandle($category->getLayoutUpdateHandle());
			$update->addHandle('CATEGORY_' . $category->getId());
			$this->loadLayoutUpdates();

			// apply custom layout update once layout is loaded
			if ($layoutUpdates = $settings->getLayoutUpdates()) {
				if (is_array($layoutUpdates)) {
					foreach($layoutUpdates as $layoutUpdate) {
						$update->addUpdate($layoutUpdate);
					}
				}
			}

			$this->generateLayoutXml()->generateLayoutBlocks();
			// apply custom layout (page) template once the blocks are generated
			if ($settings->getPageLayout()) {
				$this->getLayout()->helper('page/layout')->applyTemplate($settings->getPageLayout());
			}

			if ($root = $this->getLayout()->getBlock('root')) {
				$root->addBodyClass('categorypath-' . $category->getUrlPath())
					->addBodyClass('category-' . $category->getUrlKey());
			}

			$this->_initLayoutMessages('catalog/session');
			$this->_initLayoutMessages('checkout/session');
			$this->renderLayout();
		}
		elseif (!$this->getResponse()->isRedirect()) {
			$this->_forward('noRoute');
		}
	}
}