<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer model
 *
 * @category    Mage
 * @package     Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
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
		$emailInfo->addTo($this->getEmail(), $this->getName());
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
		$confirmation = $this->getConfirmation();
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

}