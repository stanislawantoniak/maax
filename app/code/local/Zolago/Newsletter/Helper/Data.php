<?php

/**
 * Zolago Newsletter Data Helper
 */
class Zolago_Newsletter_Helper_Data extends Mage_Newsletter_Helper_Data
{
	const INVITATION_EMAIL_TEMPLATE_XML_PATH = "newsletter/subscription/invitation_email_template";
	const INVITATION_EMAIL_SENDER_XML_PATH = "newsletter/subscription/invitation_email_identity";
	const INVITATION_XML_PATH = "newsletter/subscription/invitation";
	const INVITATION_REPEAT_XML_PATH = "newsletter/subscription/invitation_repeat";
	protected $key;
	protected $subscriberId;

	/**
	 * Simple function to check if newsletter invitation emails are enabled
	 * @return bool
	 */
	protected function isInvitationEmailEnabled() {
		return Mage::getStoreConfig(self::INVITATION_XML_PATH) ? true : false;
	}

	/**
	 * simple function that gets invitation email template id from system config
	 * @return string|mixed
	 */
	public function getInvitationEmailTemplateId() {
		return Mage::getStoreConfig(self::INVITATION_EMAIL_TEMPLATE_XML_PATH);
	}

	/**
	 * simple function that gets invitation email sender from config
	 * and then returns array with it's name and email
	 * @return string
	 */
	protected function getInvitationEmailSender() {
		return Mage::getStoreConfig(self::INVITATION_EMAIL_SENDER_XML_PATH);
	}

	/**
	 * Checks in config if user invitation should be repeated for subscriber statuses:
	 * Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE
	 * Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED
	 * Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED
	 * @return bool
	 */
	protected function canRepeatInvitation() {
		return Mage::getStoreConfig(self::INVITATION_REPEAT_XML_PATH) ? true : false;
	}

	/**
	 * function that sends newsletter invitation email for
	 * customers whom didn't want to subscribe to newsletter
	 * @param string $email
	 * @return bool
	 */
	public function sendInvitationEmail($email) {
		if ($this->isInvitationEmailEnabled()
			&& $this->validateEmail($email)
			&& $this->isEmailSuitableForInvitation($email)) {
			/** @var Mage_Core_Model_Email_Template $model */
			$model = Mage::getModel('core/email_template');
			$model->sendTransactional(
					$this->getInvitationEmailTemplateId(),
					$this->getInvitationEmailSender(),
					$email,
					null,
					$this->getInvitationEmailVars()
				);
			return true;
		}
		return false;
	}

	/**
	 * Function that gets all variables needed by invitation email
	 * @return array
	 */
	protected function getInvitationEmailVars() {
		return array(
			'store_name' => Mage::app()->getStore()->getName(),
			'confirmation_url' => Mage::helper('core/url')->getUrl("newsletter/subscriber/confirm",array("id"=>$this->subscriberId,"code"=>$this->code))
		);
	}

	/**
	 * Checks if provided email is suitable for invitation sending
	 * it's determined by config and current status in newsletter subscription
	 * in case that email is not in subscribers list we have to add it to subscribers with status
	 * Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE
	 * @param $email String
	 * @return bool
	 */
	protected function isEmailSuitableForInvitation($email) {
		/** @var Mage_Newsletter_Model_Subscriber $model */
		$model = Mage::getModel('newsletter/subscriber');
		$subscription = $model->loadByEmail($email);
		if ($subscription->getId()) {
			$status = $subscription->getSubscriberStatus();
			if ($status === Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
				return false;
			} else {
				return $this->canRepeatInvitation();
			}
		} else {
			return $this->addInactiveSubscriber($email);
		}
	}

	/**
	 * function that adds email to newsletter subscribers list with status set to:
	 * Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE
	 * @param $email
	 * @return bool
	 */
	protected function addInactiveSubscriber($email) {
		if($this->validateEmail($email)) {
			/** @var Mage_Customer_Model_Customer $customer */
			$customer = Mage::getModel("customer/customer")
				->setWebsiteId($this->getWebsiteId())
				->loadByEmail($email);
			//if customer with this email exists then save it to subscribers as user, otherwise as guest
			$type = $customer->getId() ? 2 : 1;

			/** @var Mage_Newsletter_Model_Subscriber $subscriber */
			$subscriber = Mage::getModel('newsletter/subscriber');
			$this->setCode();
			$subscriber
				->setEmail($email)
				->setSubscriberStatus(Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE)
				->setSubscriberConfirmCode($this->key);
				//->setType($type);
			$subscriber->save();
			$this->setSubscriberId($subscriber->getId());
			return true;
		} else {
			return false;
		}
	}

	/**
	 * simple zend based email validation
	 * @param $email
	 * @return bool
	 * @throws Exception
	 * @throws Zend_Validate_Exception
	 */
	public function validateEmail($email) {
		return Zend_Validate::is($email, 'EmailAddress');
	}

	protected function setCode() {
		$this->key = Mage::getModel('newsletter/subscriber')->randomSequence();
	}

	protected function setSubscriberId($sid) {
		$this->subscriberId = $sid;
	}

	protected function getWebsiteId() {
		return Mage::app()->getStore()->getWebsiteId();
	}
}
