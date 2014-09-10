<?php
class Zolago_Checkout_Model_Type_Onepage extends  Mage_Checkout_Model_Type_Onepage
{
	protected $__customerForm;
	
	public function saveAccountData(array $accountData) {
		$quote = $this->getQuote();
		$isGuest = $quote->getCustomerIsGuest();
		$customer = $this->getQuote()->getCustomer();
        $form       = $this->_getCustomerForm();
        $form->setEntity($customer);

        // emulate request
        $request = $form->prepareRequest($accountData);
        $data    = $form->extractData($request);
        $form->restoreData($data);

        $data = array();
        foreach ($form->getAttributes() as $attribute) {
            $code = sprintf('customer_%s', $attribute->getAttributeCode());
            $data[$code] = $customer->getData($attribute->getAttributeCode());
        }

        if (isset($data['customer_group_id'])) {
            $groupModel = Mage::getModel('customer/group')->load($data['customer_group_id']);
            $data['customer_tax_class_id'] = $groupModel->getTaxClassId();
            $this->setRecollect(true);
        }

        $this->getQuote()->addData($data)->save();
        return $this;
		
	}
	
	/**
	 * @return Mage_Customer_Model_Form
	 */
	protected function _getCustomerForm()
    {
        if (is_null($this->_customerForm)) {
            $addressForm = Mage::getModel('customer/form');
			$addressForm->setFormCode('customer_address_edit')
				->setEntityType('customer_address')
				->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
        }
        return $this->_customerForm;
    }
}
