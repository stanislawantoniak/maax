<?php

require_once Mage::getModuleDir("controllers", "Mage_Customer") . 
		DS . "AddressController.php";

class Zolago_Customer_AddressController extends Mage_Customer_AddressController
{
	/**
	 * Delte address by ajax
	 * @return void
	 */
	public function deleteAjaxAction() {
		$addressId = $this->getRequest()->getParam('id', false);

        if ($addressId) {
            $address = Mage::getModel('customer/address')->load($addressId);

            // Validate address_id <=> customer_id
            if ($address->getCustomerId() != $this->_getSession()->getCustomerId()) {
				$this->_prepareJsonResponse(array(
					"status"=>0, 
					"content"=>$this->__('The address does not belong to this customer.'))
				);
                return;
            }

            try {
                $address->delete();
				$this->_prepareJsonResponse(array(
					"status"=>1, 
					"content"=>$address->getData())
				);
				return;
            } catch (Exception $e){
				$this->_prepareJsonResponse(array(
					"status"=>0, 
					"content"=>$this->__('An error occurred while deleting the address.'))
				);
				return;
            }
        }
		$this->_prepareJsonResponse(array(
			"status"=>0, 
			"content"=>$this->__('No address specified'))
		);
	}
	
	/**
	 * Save address data
	 * @return void
	 */
	public function saveAjaxAction() {
		if(!$this->_validateFormKey()) {
            $this->_prepareJsonResponse(array(
				"status"=>0, 
				"content"=>array($this->__('No form key'))
			));
			return;
        }
        // Save data
        if($this->getRequest()->isPost()) {
			$customer = $this->_getSession()->getCustomer();
            /* @var $address Mage_Customer_Model_Address */
            $address  = Mage::getModel('customer/address');
            $addressId = $this->getRequest()->getParam('id');
            if ($addressId) {
                $existsAddress = $customer->getAddressById($addressId);
                if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                    $address->setId($existsAddress->getId());
                }
            }
			
			$errors = array();

            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
                ->setEntity($address);
            $addressData    = $addressForm->extractData($this->getRequest());
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                $errors = $addressErrors;
            }
			
			try {
                $addressForm->compactData($addressData);
                $address->setCustomerId($customer->getId())
                    ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                    ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));

                $addressErrors = $address->validate();
                if ($addressErrors !== true) {
                    $errors = array_merge($errors, $addressErrors);
                }

                if (count($errors) === 0) {
                    $address->save();
					$this->_prepareJsonResponse(array(
						"status"=>1, 
						"content"=>$address->getData())
					);
                } else {
					$this->_prepareJsonResponse(array(
						"status"=>0, 
						"content"=>$errors
					));
                }
            } catch (Mage_Core_Exception $e) {
				$this->_prepareJsonResponse(array(
					"status"=>0, 
					"content"=>array($e->getMessage()))
				);
            } catch (Exception $e) {
				$this->_prepareJsonResponse(array(
					"status"=>0, 
					"content"=>array($this->__('Cannot save address.')))
				);
            }
			
		}
	}
	
	
	/**
	 * @param mixed $response
	 */
	protected function _prepareJsonResponse($response) {
		$this->getResponse()->setHeader('Content-type', 'application/x-json');
		$this->getResponse()->setBody(Mage::helper("core")->jsonEncode($response));
	}
}
