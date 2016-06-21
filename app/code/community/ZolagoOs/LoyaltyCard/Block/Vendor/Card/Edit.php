<?php

class ZolagoOs_LoyaltyCard_Block_Vendor_Card_Edit extends ZolagoOs_LoyaltyCard_Block_Vendor_Card_Abstract {

	public function _prepareLayout() {
		$this->_prepareForm();
		parent::_prepareLayout();
	}

	public function _prepareForm() {
		/** @var ZolagoOs_LoyaltyCard_Helper_Data $helper */
		$helper = Mage::helper("zosloyaltycard");

		/** @var Zolago_Dropship_Model_Form $form */
		$form = Mage::getModel('zolagodropship/form');
		$form->setAction($this->getUrl("loyalty/card/save", array("_secure" => true)));

		$values = $this->getModel()->getData();

		$general = $form->addFieldset("general", array(
			"legend" => $helper->__("Card data"),
			"icon_class" => "icon-user"
		));

		$general->addField("cart_number", "text", array(
			"name" => "cart_number",
			"class" => "form-control",
			"required" => true,
			"label" => $helper->__('Card number'),
			"label_wrapper_class" => "col-md-3",
			"wrapper_class" => "col-md-6"
		));
		//todo: fields

		$form->setValues($values);
		$this->setForm($form);
	}

	/**
	 * @return ZolagoOs_LoyaltyCard_Model_Card
	 */
	public function getModel() {
		if (!Mage::registry("current_loyalty_card")) {
			Mage::register("current_loyalty_card", Mage::getModel("zosloyaltycard/card"));
		}
		return Mage::registry("current_loyalty_card");
	}

	/**
	 * @return bool
	 */
	public function isModelNew() {
		return $this->getModel()->isObjectNew();
	}
}