<?php

/**
 * Customer model
 *
 * @method string getEmail()
 * @method string getUtmData()
 * @method string getWebsiteId()
 * 
 * @method $this setWebsiteId($value)
 */
class Zolago_Customer_Model_Customer extends Mage_Customer_Model_Customer
{

	/**
	 * Send corresponding email template
	 *
	 * @param string $emailTemplate configuration path of email template
	 * @param string $emailSender configuration path of email identity
	 * @param array $templateParams
	 * @param int|null $storeId
	 * @return Mage_Customer_Model_Customer
	 */
	protected function _sendEmailTemplate($template, $sender, $templateParams = array(), $storeId = null)
	{
        $templateParams['use_attachments'] = true;

		/** @var $mailer Mage_Core_Model_Email_Template_Mailer */
		$mailer = Mage::getModel('core/email_template_mailer');
		$emailInfo = Mage::getModel('core/email_info');
		$name = $this->getName();
		$email = $this->getEmail();
		
		$emailInfo->addTo($email,empty($name)? $email:$name);
		$mailer->addEmailInfo($emailInfo);

		// Set all required params and send emails
		$mailer->setSender(Mage::getStoreConfig($sender, $storeId));
		$mailer->setStoreId($storeId);
		$mailer->setTemplateId(Mage::getStoreConfig($template, $storeId));
		$mailer->setTemplateParams($templateParams);
		$mailer->send();
		return $this;
	}

	/**
	 * Validate customer attribute values.
	 * For existing customer password + confirmation will be validated only when password is set (i.e. its change is requested)
	 *
	 * @return bool
	 */
	public function validate()
	{
		$errors = array();
/*		if (!Zend_Validate::is( trim($this->getFirstname()) , 'NotEmpty')) {
			$errors[] = Mage::helper('customer')->__('The first name cannot be empty.');
		}

		if (!Zend_Validate::is( trim($this->getLastname()) , 'NotEmpty')) {
			$errors[] = Mage::helper('customer')->__('The last name cannot be empty.');
		}*/

		if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
			$errors[] = Mage::helper('customer')->__('Invalid email address "%s".', $this->getEmail());
		}

		$password = $this->getPassword();
		if (!$this->getId() && !Zend_Validate::is($password , 'NotEmpty')) {
			$errors[] = Mage::helper('customer')->__('The password cannot be empty.');
		}
		if (strlen($password) && !Zend_Validate::is($password, 'StringLength', array(6))) {
			$errors[] = Mage::helper('customer')->__('The minimum password length is %s', 6);
		}
		$confirmation = empty($this->getConfirmation())? $this->getPasswordConfirmation(): $this->getConfirmation();
		if ($password != $confirmation) {
			$errors[] = Mage::helper('customer')->__('Please make sure your passwords match.');
		}

		$entityType = Mage::getSingleton('eav/config')->getEntityType('customer');
		$attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'dob');
		if ($attribute->getIsRequired() && '' == trim($this->getDob())) {
			$errors[] = Mage::helper('customer')->__('The Date of Birth is required.');
		}
		$attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'taxvat');
		if ($attribute->getIsRequired() && '' == trim($this->getTaxvat())) {
			$errors[] = Mage::helper('customer')->__('The TAX/VAT number is required.');
		}
		$attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'gender');
		if ($attribute->getIsRequired() && '' == trim($this->getGender())) {
			$errors[] = Mage::helper('customer')->__('Gender is required.');
		}

		if (empty($errors)) {
			return true;
		}
		return $errors;
	}
	
    /**
     * add trim to getName
     */

	
	public function getName() {
	    return trim(parent::getName());
	}

}