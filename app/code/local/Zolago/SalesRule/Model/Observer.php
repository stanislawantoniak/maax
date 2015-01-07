<?php
class Zolago_SalesRule_Model_Observer {
	
	/**
	 * Add a payer field to form
	 * @param Varien_Event_Observer $observer
	 */
	public function adminhtmlPromoQuoteEditTabMainPrepareForm(Varien_Event_Observer $observer) {
		$form = $observer->getEvent()->getForm();
		/* @var $form Varien_Data_Form */
		$fieldset = $form->getElement("base_fieldset");
		/* @var $fieldset Varien_Data_Form_Element_Fieldset */
		$model = Mage::registry('current_promo_quote_rule');
		/* @var $model Mage_SalesRule_Model_Rule */
		$fieldset->addField("rule_payer", "select", array(
			"label"		=> Mage::helper("zolagosalesrule")->__("Payer"),
			"values"	=> Mage::getSingleton('zolagosalesrule/rule_payer')->toOptionArray(),
			"name"		=> "rule_payer",
			"value"     => $model->getRulePayer()
		), "name");
		
	}
}