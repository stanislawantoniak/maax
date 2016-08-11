<?php

/**
 * Zolago Newsletter Data Helper
 */
class Zolago_Newsletter_Model_Inviter extends Zolago_Newsletter_Model_Subscriber
{
	const INVITATION_EMAIL_TEMPLATE_XML_PATH = "newsletter/zolagosubscription/invitation_email_template";
	const INVITATION_EMAIL_SENDER_XML_PATH = "newsletter/zolagosubscription/invitation_email_identity";
	const INVITATION_XML_PATH = "newsletter/zolagosubscription/invitation";
	const INVITATION_REPEAT_XML_PATH = "newsletter/zolagosubscription/invitation_repeat";
	protected $_invitationCode;
	protected $_subscriberId;

	/**
	 * Simple function to check if newsletter invitation emails are enabled
	 * @return bool
	 */
	protected function _isInvitationEmailEnabled() {
		return Mage::getStoreConfig(self::INVITATION_XML_PATH) ? true : false;
	}

	/**
	 * simple function that gets invitation email template id from system config
	 * @return string|mixed
	 */
	protected function _getInvitationEmailTemplateId() {
		return Mage::getStoreConfig(self::INVITATION_EMAIL_TEMPLATE_XML_PATH);
	}

	/**
	 * simple function that gets invitation email sender from config
	 * and then returns array with it's name and email
	 * @return string
	 */
	protected function _getInvitationEmailSender() {
		return Mage::getStoreConfig(self::INVITATION_EMAIL_SENDER_XML_PATH);
	}

	/**
	 * Checks in config if user invitation should be repeated for subscriber statuses:
	 * self::STATUS_NOT_ACTIVE
	 * self::STATUS_UNSUBSCRIBED
	 * self::STATUS_UNCONFIRMED
	 * @return bool
	 */
	protected function _canRepeatInvitation() {
		return Mage::getStoreConfig(self::INVITATION_REPEAT_XML_PATH) ? true : false;
	}

	/**
	 * function that sends newsletter invitation email for
	 * customers whom didn't want to subscribe to newsletter
	 * @param string $email
	 * @return bool
	 */
	public function sendInvitationEmail($email) {
		if (!Mage::helper("zolagonewsletter")->isModuleActive())
			return false;

		if (
			$this->getImportMode()
			|| !$this->_getInvitationEmailTemplateId()
			|| !$this->_getInvitationEmailSender()
		) {

			return false;
		}

        if ($this->_isInvitationEmailEnabled()
	        && Mage::getSingleton("customer/session")->isLoggedIn()
			&& $this->validateEmail($email)
			&& $this->_isEmailSuitableForInvitation($email)
        ) {

			/** @var Zolago_Common_Helper_Data $helper */
			$helper = Mage::helper("zolagocommon");

			return $helper->sendEmailTemplate(
				$email,
				'',
				$this->_getInvitationEmailTemplateId(),
				$this->_getInvitationEmailVars(),
				true,
				$this->_getInvitationEmailSender()
			);
		}
		return false;
	}

	/**
	 * Function that gets all variables needed by invitation email
	 * @return array
	 */
	protected function _getInvitationEmailVars() {
		return array(
			'store_name' => Mage::app()->getStore()->getName(),
			'confirmation_url' => $this->_getInvitationUrl(),
			'use_attachments' => true
		);
	}

	/**
	 * Checks if provided email is suitable for invitation sending
	 * it's determined by config and current status in newsletter subscription
	 * in case that email is not in subscribers list we have to add it to subscribers with status
	 * self::STATUS_NOT_ACTIVE
	 * @param $email String
	 * @return bool
	 */
	protected function _isEmailSuitableForInvitation($email) {
		/** @var Mage_Newsletter_Model_Subscriber $model */

		$model = Mage::getModel("newsletter/subscriber");
		$subscription = $model->loadByEmail($email);
		$sid = $subscription->getId();

		$save = false;
		if ($sid) {
			$status = $subscription->getSubscriberStatus();

			if ($status == self::STATUS_SUBSCRIBED) {

				return false;
			} elseif($this->_canRepeatInvitation() || is_null($status) || $status == 0) {
				$this->_setSubscriberId($sid);
				$confirm_code = $subscription->getSubscriberConfirmCode();
				if(!$confirm_code) {
					$subscription->setSubscriberConfirmCode($this->_getInvitationCode());
					$save = true;
				} else {
					$this->_setInvitationCode($subscription->getSubscriberConfirmCode());
				}
				if(is_null($status) || $status == 0) {
					$subscription->setSubscriberStatus(self::STATUS_NOT_ACTIVE);
					$save = true;
				}
				if($save) {
					$subscription->save();
				}
				return true;

			} else {

				return false;
			}
		} else {

			return $this->_addInactiveSubscriber($email);
		}
	}

	public function isEmailSubscribed($email) {
		/** @var Mage_Newsletter_Model_Subscriber $model */
		$model = Mage::getModel("newsletter/subscriber");
		$subscription = $model->loadByEmail($email);
		$sid = $subscription->getId();
		if ($sid) {
			$status = $subscription->getSubscriberStatus();
			if ($status == self::STATUS_SUBSCRIBED) {
				return true;
			}
		}
		return false;
	}

	public function addSubscriber($email,$status=self::STATUS_NOT_ACTIVE) {
		if($this->validateEmail($email)) {
			/** @var Mage_Newsletter_Model_Subscriber $model */
			$model = Mage::getModel("newsletter/subscriber");
			$subscriber = $model->loadByEmail($email);
			$subscriberId = $subscriber->getId();

			//check newsletter subscription confirmation in config
			$confirmationNeeded = Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1;
			if(!$confirmationNeeded && $status == self::STATUS_UNCONFIRMED) {
				$status = self::STATUS_SUBSCRIBED;
			}

			if(!$subscriberId) {
				/** @var Mage_Customer_Model_Customer $customer */
				$customer = Mage::getModel("customer/customer")
					->setData('website_id', $this->_getWebsiteId())
					->loadByEmail($email);
				$customerId = $customer->getId();

				$model
					->setEmail($email)
					->setSubscriberStatus($status)
					->setSubscriberConfirmCode($this->_getInvitationCode());

				//if customer with this email exists then save it to subscribers as user, otherwise as guest
				if ($customerId) {
					$model->setStoreId($customer->getData('store_id'));
					$model->setCustomerId($customerId);
				} else {
					$model->setStoreId(Mage::app()->getStore()->getId());
					$model->setCustomerId(0);
				}
				$model->save();
				$sid = $model->getId();
				if (!is_null($sid)) {
					$this->_setSubscriberId($sid);
					if($status == self::STATUS_UNCONFIRMED) {
						$this->sendConfirmationRequestEmail($sid);
					} elseif($status == self::STATUS_SUBSCRIBED) {
						$this->sendConfirmationSuccessEmail($sid);
					}
					return true;
				}
			} else {
				$oldStatus = $subscriber->getStatus();

				if($oldStatus == self::STATUS_UNSUBSCRIBED
					&& ($status == self::STATUS_UNCONFIRMED || $status == self::STATUS_SUBSCRIBED)) {

					$subscriber->setStatus(self::STATUS_SUBSCRIBED);
					$subscriber->save();
					$this->sendConfirmationSuccessEmail($subscriberId);
					return true;
				} elseif(($oldStatus == self::STATUS_NOT_ACTIVE || $oldStatus == self::STATUS_UNCONFIRMED)
					&& ($status == self::STATUS_SUBSCRIBED || $status == self::STATUS_UNCONFIRMED)) {
					if($confirmationNeeded) {
						$subscriber->setStatus(self::STATUS_UNCONFIRMED);
						$subscriber->save();
						$this->sendConfirmationRequestEmail($subscriberId);
					} else {
						$subscriber->setStatus(self::STATUS_SUBSCRIBED);
						$subscriber->save();
						$this->sendConfirmationSuccessEmail($subscriberId);
					}
					return true;
				} else {
                    $subscriber->setStatus($status);
                    $subscriber->save();
                    if($status == self::STATUS_UNCONFIRMED) {
                        $this->sendConfirmationRequestEmail($subscriberId);
                    } elseif($status == self::STATUS_SUBSCRIBED) {
                        $this->sendConfirmationSuccessEmail($subscriberId);
                    }
                    return true;
                }
			}
		}
		return false;
	}

	/**
	 * function that adds email to newsletter subscribers list with status set to:
	 * self::STATUS_NOT_ACTIVE
	 * @param $email
	 * @return bool
	 */
	protected function _addInactiveSubscriber($email) {
		return $this->addSubscriber($email);
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

	/**
	 * @param string|null $code
	 */
	protected function _setInvitationCode($code=null) {
		$this->_invitationCode = $code ? $code : $this->randomSequence();
	}

	/**
	 * gets confirmation code from local variable
	 * @return int
	 */
	protected function _getInvitationCode() {
		if(is_null($this->_invitationCode)) {
			$this->_setInvitationCode();
		}
		return $this->_invitationCode;
	}

	/**
	 * sets subscriber id in local variable
	 * @param $sid
	 * @return void
	 */
	protected function _setSubscriberId($sid) {
		$this->_subscriberId = (int) $sid;
	}

	/**
	 * gets subscriber id from local variable
	 * @return int|null
	 */
	protected function _getSubscriberId() {
		return $this->_subscriberId;
	}

	/**
	 * gets current store website id
	 * @return int|null|string
	 */
	protected function _getWebsiteId() {
		return Mage::app()->getStore()->getWebsiteId();
	}

	/**
	 * Gets invitation link from local variables,
	 * if they're not set returns false
	 * @return bool|string
	 */
	protected function _getInvitationUrl() {
		$sid = $this->_getSubscriberId();
		$code = $this->_getInvitationCode();
		$subscriber = Mage::getModel("newsletter/subscriber")->load($sid);
		$store = Mage::app()->getStore($subscriber->getStoreId());
		if($sid && $code) {
			return $store->getUrl("newsletter/subscriber/invitation",array("id"=>$sid,"code"=>$code));
		} else {
			return false;
		}
	}
}
