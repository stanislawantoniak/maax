<?php
class Zolago_DropshipVendorAskQuestion_Block_Contact_Vendor extends Mage_Core_Block_Template {

	public function getCmsBlock() {
		$poId = $this->getRequest()->getParam('po');
		$poToken = $this->getRequest()->getParam('token');
		if($poId && $poToken) {
			/** @var Zolago_Po_Model_Po $po */
			$po = Mage::getModel('zolagopo/po')->load($poId);
			if($po->getId() && ($po->getContactToken() == $poToken)) {
				$block = Mage::getModel('cms/block')
					->setStoreId(Mage::app()->getStore()->getId())
					->load('help-contact-vendor-po');

				$variables = array(
					'vendor_name'   => $po->getVendor()->getVendorName(),
					'order_number'  => $po->getOrder()->getIncrementId()
				);

				/* @var $filter Mage_Cms_Model_Template_Filter */
				$filter = Mage::getModel('cms/template_filter');
				$filter->setVariables($variables);

				return $filter->filter($block->getContent());
			}
		}
		return $this->getLayout()->createBlock('cms/block')->setBlockId('help-contact-vendor')->toHtml();
	}

}