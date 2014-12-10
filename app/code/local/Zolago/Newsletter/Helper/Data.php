<?php

/**
 * Zolago Newsletter Data Helper
 *
 * @category   Mage
 * @package    Mage_Newsletter
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Zolago_Newsletter_Helper_Data extends Mage_Newsletter_Helper_Data
{
	const INVITATION_EMAIL_TEMPLATE_XML_PATH = "newsletter/subscription/invitation_email_template";
	const INVITATION_EMAIL_SENDER_XML_PATH = "newsletter/subscription/invitation_email_identity";
	const INVITATION_XML_PATH = "newsletter/subscription/invitation";
	const INVITATION_REPEAT_XML_PATH = "newsletter/subscription/invitation_repeat";


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
	public function getInvitationEmailSender() {
		return Mage::getStoreConfig(self::INVITATION_EMAIL_SENDER_XML_PATH);
	}


	/**
	 * Simple function to check if newsletter invitation emails are enabled
	 * @return bool
	 */
	public function isInvitationEmailEnabled() {
		return Mage::getStoreConfig(self::INVITATION_XML_PATH) ? true : false;
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
	 * function that sends invitation email for customers that didn't want to subscribe to newsletter
	 * @param string $email
	 * @return bool
	 */
	public function sendInvitationEmail($email) {
		if ($this->isInvitationEmailEnabled()
			&& Zend_Validate::is($email, 'EmailAddress')
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
			//todo: add more variables
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
		if ($subscription instanceof Mage_Newsletter_Model_Subscriber && $subscription->getId()) {
			$status = $subscription->getSubscriberStatus();
			if ($status === Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
				return false;
			} else {
				return $this->canRepeatInvitation();
			}
		} else {
			/** @var Mage_Newsletter_Model_Subscriber $subscriber */
			$subscriber = Mage::getModel('newsletter/subscriber');
			$subscriber
				->setEmail($email)
				->setSubscriberStatus(Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE);
			$subscriber->save();
			return true;
		}
	}

}
