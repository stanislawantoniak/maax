<?php
class Gh_Regulation_Model_Observer
{
	/**
	 * Add tab to vendor view
	 *
	 * @param $observer Varien_Event_Observer
	 * @return Gh_Regulation_Model_Observer
	 */
	public function udropship_adminhtml_vendor_tabs_after($observer)
	{
		$block = $observer->getBlock();
		if (!$block instanceof Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tabs
			|| !Mage::app()->getRequest()->getParam('id', 0)
		) {
			return;
		}

		if ($block instanceof Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tabs) {
			$block->addTab('regulation_kind', array(
				'label'     => Mage::helper('ghregulation')->__('Regulation kind settings'),
				'content'	=> Mage::app()->getLayout()->createBlock('ghregulation/adminhtml_dropship_settings_kind_grid', 'admin.regulation.settings.kind')
					->setVendorId(Mage::app()->getRequest()->getParam('id'))
					->toHtml(),
			));
			$block->addTabToSection('regulation_kind','vendor_rights',30);
		}
	}
	public function udropship_adminhtml_vendor_edit_prepare_form($observer) {
		$block = $observer->getEvent()->getBlock();
		$form = $block->getForm();
		$fieldset = $form->getElement('vendor_form');
		$fieldset->addField('vendor_kind', 'hidden', array(
			'name' => 'vendor_kind',
		));
	}
}