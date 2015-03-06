<?php

class Zolago_Customer_Model_Attachtoken extends Mage_Core_Model_Abstract
{

	const EMAIL_TEMPLATE_PATH = "customer/orders_attach/email_template";
	const EMAIL_SENDER_PATH = "customer/orders_attach/email_identity";
	const CONFIRM_PATH = "sales/order/attach";
	const HOURS_EXPIRE = 24;

	protected function _construct()
	{
		$this->_init('zolagocustomer/attachtoken');
	}

	/**
	 * @return boolean
	 */
	public function sendMessage()
	{
		if (!($this->getId() && $this->getToken() && $this->getCustomerId())) {
			return false;
		}

		$customer = Mage::getModel("customer/customer")
			->load($this->getCustomerId());
		/* @var $customer Mage_Customer_Model_Customer */

		if (!$customer->getId()) {
			return false;
		}

		$store = $customer->getStore();

		$templateParams = array(
			"customer" => $customer,
			"store" => $store,
			"confirm_link" => $this->getConfirmLink()
		);

		$template = Mage::getStoreConfig(self::EMAIL_TEMPLATE_PATH);

		return $this->_sendEmailTemplate($customer,
			$template, $templateParams, $store->getId());
	}

	public function getConfirmLink($token = null)
	{
		if ($token === null) {
			$token = $this->getToken();
		}
		return Mage::getUrl(self::CONFIRM_PATH, array("token" => $token));
	}

	protected function _sendEmailTemplate($customer,
	                                      $template, $templateParams = array(), $storeId = null)
	{
		$templateParams['use_attachments'] = true;

		/* @var $mailer Zolago_Common_Model_Core_Email_Template_Mailer */
		$mailer = Mage::getModel('core/email_template_mailer');
		$mailer->setTemplateParams($templateParams);
		$templateParams = $mailer->getTemplateParams();

		$emailTemplate = Mage::getModel("core/email_template");
		/* @var $emailTempalte Mage_Core_Model_Email_Template */


		// Set required design parameters
		// and delegate email sending to Mage_Core_Model_Email_Template
		$emailTemplate->
		setDesignConfig(array('area' => 'frontend', 'store' => $storeId));

		if (is_numeric($template)) {
			$emailTemplate->load($template);
		} else {
			$localeCode = Mage::getStoreConfig('general/locale/code', $storeId);
			$emailTemplate->loadDefault($template, $localeCode);
		}

		$identity = Mage::getStoreConfig(self::EMAIL_SENDER_PATH);
		$senderName = Mage::getStoreConfig('trans_email/ident_'.$identity.'/name', $storeId);
		$senderEmail = Mage::getStoreConfig('trans_email/ident_'.$identity.'/email', $storeId);

		$emailTemplate->setSenderEmail($senderEmail);
		$emailTemplate->setSenderName($senderName);

		return $emailTemplate->send(
			$customer->getEmail(),
			$customer->getName(),
			$templateParams
		);

	}
}
