<?php

class Zolago_Sales_Model_Quote  extends Mage_Sales_Model_Quote
{

    public function setCustomer(Mage_Customer_Model_Customer $customer){
		$this->_customer = $customer;
        $this->setCustomerId($customer->getId());
		
		// Fix prevent copy personal data from presistant
		if(!$customer->getSkipCopyPersonalData()){
			$keepData = array(
				"customer_email" => $this->getCustomerEmail(),
				"customer_firstname" => $this->getCustomerFirstname(),
				"customer_lastname" => $this->getCustomerLastname(),
			);

			Mage::helper('core')->copyFieldset('customer_account', 'to_quote', $customer, $this);
			
			// Restore origin data if not nulled
			// Helps to skip override 
			foreach($keepData as $key=>$value){
				if(!is_null($value)){
					$this->setData($key, $value);
				}
			}
		}
		
		
		
        return $this;
	}

}