<?php
/**
 * The flow of discount info:
 * 
 * 1. After process rule (salesrule_validator_process event) register discount_info
 * as part of quote item object. 
 * 
 * 2. During placing order via <fieldsets> config copy the discount info array
 * to order item
 * 
 * 3. After order item save make save the realtion 
 * 
 * During create the PO item need to update te relation with po_item_id
 * During update po item manualy need to remove an relation entry
 */
class Zolago_SalesRule_Model_Observer {
	
	/**
	 * Add rule_id=>price to quote item
	 * @param Varien_Event_Observer $observer
	 */
	public function salesruleValidatorProcess(Varien_Event_Observer $observer) {

		$event = $observer->getEvent();
		$item = $event->getItem();
		/* @var $item Mage_Sales_Model_Quote_Item */
		$result = $event->getResult();
		/* @var $result Varien_Object */
		
		$rule = $event->getRule();
		/* @var $rule Mage_SalesRule_Model_Rule */
		$ruleId = $rule->getId();
		
		$discount = $result->getDiscountAmount();
		
		$discountInfo = $item->getDiscountInfo();
		
		if(!is_array($discountInfo)){
			$discountInfo = array();
		}
		
		if($discount){
			$discountInfo[$ruleId] = array(
				"rule_id"			=> $ruleId,
				"discount_amount"	=> $discount,
				"name"				=> $rule->getName(),
				"payer"				=> $rule->getRulePayer(),
				"simple_action"		=> $rule->getSimpleAction()
			);
		}else{
			unset($discountInfo[$ruleId]);
		}
		// Set property of abstract object (property not stored)
		$item->setDiscountInfo($discountInfo);
	}
	
	/**
	 * Da save relation based on quote item -> convert -> order item flow
	 * @param Varien_Event_Observer $observer
	 */
	public function salesOrderItemSaveAfter(Varien_Event_Observer $observer) {
		$item = $observer->getEvent()->getItem();
		/* @var $item Mage_Sales_Model_Order_Item */
		if($item->getDiscountInfo()){
			Mage::getResourceSingleton('zolagosalesrule/relation')->saveForOrderItem($item);
			// Unset discount info to prevent next steps saving
			$item->setDiscountInfo(null);
		}
	}
	
	/**
	 * Register beofre save action
	 * @param Varien_Event_Observer $observer
	 */
	public function udpoPoItemSaveBefore(Varien_Event_Observer $observer) {
		$item = $observer->getEvent()->getPoItem();
		/* @var $item Unirgy_DropshipPo_Model_Po_Item */
		if($item->isObjectNew()){
			$item->setDoUpdateDiscountInfo(true);
		}elseif($this->_hasDiscountChanged($item)){
			$item->setDoRemoveDiscountInfo(true);
		}
	}
	
	/**
	 * @param Unirgy_DropshipPo_Model_Po_Item $item
	 * @return bool
	 */
	protected function _hasDiscountChanged(Unirgy_DropshipPo_Model_Po_Item $item) {
		return 
			(round($item->getData("discount_amount"), 4) !== round($item->getOrigData("discount_amount"), 4)) ||
			(round($item->getData("discount_percent"), 4) !== round($item->getOrigData("discount_percent"), 4));
	}
	
	/**
	 * Da save relation based on quote item -> convert -> order item -> po item 
	 * @param Varien_Event_Observer $observer
	 */
	public function udpoPoItemSaveAfter(Varien_Event_Observer $observer) {
		$item = $observer->getEvent()->getPoItem();
		/* @var $item Unirgy_DropshipPo_Model_Po_Item */
		if($item->getDoUpdateDiscountInfo()){
			Mage::getResourceSingleton('zolagosalesrule/relation')->updateForPoItem($item);
			$item->setDoUpdateDiscountInfo(null);
		}
		
		if($item->getDoRemoveDiscountInfo()){
			Mage::getResourceSingleton('zolagosalesrule/relation')->removeForPoItem($item);
			$item->setDoRemoveDiscountInfo(null);
		}
	}

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