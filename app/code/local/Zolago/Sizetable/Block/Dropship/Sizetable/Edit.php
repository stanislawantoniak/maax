<?php
class Zolago_Sizetable_Block_Dropship_Sizetable_Edit extends Mage_Core_Block_Template {
	protected $stores;
	protected $allowedStores;

	public function __construct() {
		$stores = array();
		foreach(Mage::app()->getStores() as $store) {
			$stores[] = $store->getData();
		}
		$this->stores = $stores;
	}

	protected function _getStores() {
		return $this->stores;
	}

	public function getSizeTable() {
		$sizetable = Mage::registry("sizetable");
		return Mage::registry("sizetable");
	}

	public function getAction() {
		return $this->getUrl("udropship/sizetable/save");
	}

	public function getImageUploadAction() {
		return $this->getUrl("udropship/sizetable/image");
	}

	public function getSizeTablesTemplates() {
		/** @var Zolago_Common_Helper_Data $hlp */
		$hlp = Mage::helper('zolagocommon');
		/** @var Mage_Cms_Model_Resource_Block_Collection $cmsCollection */
		$cmsCollection = Mage::getModel("cms/block")->getCollection();
		$cmsCollection->addFieldToFilter('identifier',array('like'=>'sizetable-%'));
		$out = array();
		foreach($cmsCollection as $template) {
			/** @var Mage_Cms_Model_Block $template */
			$out[] = array(
				'title' => $template->getTitle(),
				'content' => $hlp->stringForJs($template->getContent(),"'")
			);
		}

		return $out;
	}

	public function getAllowedStores() {
		if(!$this->allowedStores) {
			/** @var Zolago_Dropship_Model_Session $session */
			$session = Mage::getSingleton('udropship/session');
			/** @var Zolago_Dropship_Model_Vendor $vendor */
			$vendor = $session->getVendor();

			$allowedWebsites = $vendor->getWebsitesAllowed();
			$stores = $this->_getStores();

			foreach ($stores as $key => $store) {
				if (!in_array($store['website_id'], $allowedWebsites)) {
					unset($stores[$key]);
				}
			}
			$this->allowedStores = $stores;
		}
		return $this->allowedStores;
	}

	public function getSizeTablesStylesForJs() {
		$allowedStores = $this->getAllowedStores();
		$css = array();

		/** @var Mage_Cms_model_Block $defaultBlock */
		$defaultBlock = Mage::getModel('cms/block')
			->setStoreId(0) //all stores
			->load('sizetablecss');

		if(!$defaultBlock->getId()) {
			return array();
		}

		/** @var Zolago_Common_Helper_Data $commonHlp */
		$commonHlp = Mage::helper("zolagocommon");
		$defaultCss = $commonHlp->stringForJs($defaultBlock->getContent(),"\"",true);

		foreach($allowedStores as $storeData) {
			$storeId = $storeData['store_id'];

			/** @var Mage_Cms_model_Block $block */
			$block  = Mage::getModel('cms/block')
				->setStoreId($storeId)
				->load('sizetablecss');

			if($block && $block->getId()) {
				$css[$storeId] = $commonHlp->stringForJs($block->getContent(),"\"",true);
			} else {
				$css[$storeId] = $defaultCss;
			}
		}

		$css[0] = $defaultCss;

		return $css;
	}
}