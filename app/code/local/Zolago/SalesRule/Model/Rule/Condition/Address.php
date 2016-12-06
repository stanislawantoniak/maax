<?php
/**
 * nowy warunek do zniżki - suma całkowita (base_grand_total)
 */
class Zolago_SalesRule_Model_Rule_Condition_Address extends 
    Mage_SalesRule_Model_Rule_Condition_Address {
        
        
    public function loadAttributeOptions()
    {
        $attributes = array(
            'base_subtotal' => Mage::helper('salesrule')->__('Subtotal'),
            'total_qty' => Mage::helper('salesrule')->__('Total Items Quantity'),
            'weight' => Mage::helper('salesrule')->__('Total Weight'),
            'payment_method' => Mage::helper('salesrule')->__('Payment Method'),
            'shipping_method' => Mage::helper('salesrule')->__('Shipping Method'),
            'postcode' => Mage::helper('salesrule')->__('Shipping Postcode'),
            'region' => Mage::helper('salesrule')->__('Shipping Region'),
            'region_id' => Mage::helper('salesrule')->__('Shipping State/Province'),
            'country_id' => Mage::helper('salesrule')->__('Shipping Country'),
            'total_value' => Mage::helper('salesrule')->__('Total basket value'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getInputType() {
        if ($this->getAttribute() == 'total_value') {
            return 'numeric';
        }
        return parent::getInputType();
    }
    public function validate(Varien_Object $object) {
        if ($this->getAttribute() == 'total_value') {
            $object->setData('total_value',($object->getData('base_subtotal')+$object->getData('base_discount_amount')));
        }        
        return parent::validate($object);
    }
}