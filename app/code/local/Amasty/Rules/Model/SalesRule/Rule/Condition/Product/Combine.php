<?php
/**
 * @copyright   Copyright (c) 2009-14 Amasty
 */
class Amasty_Rules_Model_SalesRule_Rule_Condition_Product_Combine extends Mage_SalesRule_Model_Rule_Condition_Product_Combine
{
    public function validate(Varien_Object $object)
    {
        // for optimization if we no conditions
        if (!$this->getConditions()) {
            return true;
        }
        
        $origProduct  = null;
        if ($object->getHasChildren() && $object->getProductType() == 'configurable'){
            //remember original product
            $origProduct = $object->getProduct();


            $origSku     = $object->getSku();
            foreach ($object->getChildren() as $child) { 
                // only one itereation.
                $categoryIds = array_merge($child->getProduct()->getCategoryIds(),$origProduct->getCategoryIds());
                $categoryIds = array_unique($categoryIds);
                $object->setProduct($child->getProduct());
                $object->setSku($child->getSku());
                $object->getProduct()->setCategoryIds($categoryIds);
            }
        }

		/** @var Zolago_SalesRule_Helper_Data $helper */
		$helper = Mage::helper("zolagosalesrule");
		$helper->copySalesRuleAttrToQuoteItem($object, $object->getProduct());

        $result = @Mage_Rule_Model_Condition_Combine::validate($object);
        if ($origProduct){
            // restore original product
            $object->setProduct($origProduct);    
            $object->setSku($origSku);  
			$helper->copySalesRuleAttrToQuoteItem($object, $object->getProduct());
			// Override the result by configurable product
			if(false===$result){
				$result = @Mage_Rule_Model_Condition_Combine::validate($object);
			}
        }

        return $result;       
    }

	public function getNewChildSelectOptions() {
		$conditions = parent::getNewChildSelectOptions();
		$conditions = array_merge_recursive($conditions, array(
			array('value'=>'zolagosalesrule/rule_condition_product_found_configurable', 'label'=>Mage::helper('salesrule')->__('Configurable product attribute combination')),
		));
		return $conditions;
	}
}
