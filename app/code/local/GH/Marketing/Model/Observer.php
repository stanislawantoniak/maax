<?php

class GH_Marketing_Model_Observer {

	/**
	 * Marketing cost integration settings
	 *
	 * @param $observer
	 * @throws Exception
	 */
	public function udropship_adminhtml_vendor_tabs_after($observer) {
		if (!Mage::helper("core")->isModuleEnabled('ZolagoOs_OutsideStore')) {
			/** @var Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tabs $block */
			$block = $observer->getEvent()->getBlock();
			$id = $observer->getEvent()->getId();
			$v = Mage::helper('udropship')->getVendor($id);

			if (!$block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs
				|| !Mage::app()->getRequest()->getParam('id', 0)
			) {
				return;
			}

			if ($block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs) {

				$marketingCost = Mage::app()
					->getLayout()
					->createBlock('ghmarketing/adminhtml_dropship_settings_marketing_cost_form', 'vendor.marketing.cost.form')
					->setVendorId($v)
					->toHtml();

				$block->addTab('marketing_cost_settings', array(
					'label' => Mage::helper('ghmarketing')->__('Configuration billing for marketing'),
					'content' => $marketingCost
				));
				$block->addTabToSection('marketing_cost_settings', 'logistic', 71);
			}
		}
	}

}
