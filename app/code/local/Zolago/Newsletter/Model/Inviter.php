<?php

/**
 * Zolago Newsletter Data Helper
 */
class Zolago_Newsletter_Model_Inviter extends Zolago_Newsletter_Model_Subscriber
{
	const INVITATION_EMAIL_TEMPLATE_XML_PATH = "newsletter/subscription/invitation_email_template";
	const INVITATION_EMAIL_SENDER_XML_PATH = "newsletter/subscription/invitation_email_identity";
	const INVITATION_XML_PATH = "newsletter/subscription/invitation";
	const INVITATION_REPEAT_XML_PATH = "newsletter/subscription/invitation_repeat";
	protected $_invitationCode;
	protected $_subscriberId;

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
	 * self::STATUS_NOT_ACTIVE
	 * self::STATUS_UNSUBSCRIBED
	 * self::STATUS_UNCONFIRMED
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
		if (
			$this->getImportMode()
			|| !$this->getInvitationEmailTemplateId()
			|| !$this->getInvitationEmailSender()
		) {
			return false;
		}

		if ($this->isInvitationEmailEnabled()
			&& $this->validateEmail($email)
			&& $this->isEmailSuitableForInvitation($email)) {
			/** @var Zolago_Common_Helper_Data $helper */
			$helper = Mage::helper("zolagocommon");
			return $helper->sendEmailTemplate(
				$email,
				'',
				$this->getInvitationEmailTemplateId(),
				$this->getInvitationEmailVars(),
				true,
				$this->getInvitationEmailSender()
			);
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
			'confirmation_url' => $this->getInvitationUrl(),
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
	protected function isEmailSuitableForInvitation($email) {
		/** @var Mage_Newsletter_Model_Subscriber $model */
		$model = Mage::getModel("newsletter/subscriber");
		$subscription = $model->loadByEmail($email);
		$sid = $subscription->getId();
		if ($sid) {
			$status = $subscription->getSubscriberStatus();
			if ($status == self::STATUS_SUBSCRIBED) {
				return false;
			} else {
				if($this->canRepeatInvitation()) {
					$this->setSubscriberId($sid);
					$confirm_code = $subscription->getSubscriberConfirmCode();
					if(!$confirm_code) {
						$subscription->setSubscriberConfirmCode($this->getInvitationCode());
						$subscription->save();
					} else {
						$this->setInvitationCode($subscription->getSubscriberConfirmCode());
					}
					return true;
				} else {
					return false;
				}
			}
		} else {
			return $this->addInactiveSubscriber($email);
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

    /**
     * When customer confirm changing email
     * (on the /customer/account/edit page)
     * all his subscriptions should be set in status STATUS_NOT_ACTIVE
     * @param $customerId
     */
    public function changeAllCustomerSubscriptionsStatus($customerId, $status = self::STATUS_NOT_ACTIVE)
    {
        $customerSubscriptions = Mage::getModel('newsletter/subscriber')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId);

        foreach ($customerSubscriptions as $customerSubscription) {
            $customerSubscription->setStatus($status);
            $customerSubscription->save();
        }

    }

	public function addSubscriber($email,$status=self::STATUS_NOT_ACTIVE) {
		if($this->validateEmail($email)) {
			/** @var Mage_Newsletter_Model_Subscriber $model */
			$model = Mage::getModel("newsletter/subscriber");
			$subscriber = $model->loadByEmail($email);
			$subscriberId = $subscriber->getId();
			if(!$subscriberId) {
				/** @var Mage_Customer_Model_Customer $customer */
				$customer = Mage::getModel("customer/customer")
					->setData('website_id', $this->getWebsiteId())
					->loadByEmail($email);
				$customerId = $customer->getId();

				$model
					->setEmail($email)
					->setSubscriberStatus($status)
					->setSubscriberConfirmCode($this->getInvitationCode())
					->setUseAttachments(true);

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
				if ($sid) {
					$this->setSubscriberId($sid);
					if($status == self::STATUS_UNCONFIRMED) {
						$this->sendConfirmationRequestEmail($sid);
					}
					return true;
				}
			} else {
				$oldStatus = $subscriber->getStatus();
                Mage::log('addSubscriber');
                Mage::log($oldStatus);
                Mage::log($status);
				if($oldStatus == self::STATUS_UNSUBSCRIBED
					&& ($status == self::STATUS_UNCONFIRMED || $status == self::STATUS_SUBSCRIBED)) {
                    Mage::log("1111111111111");
					$subscriber->setStatus(self::STATUS_SUBSCRIBED);
					$subscriber->save();
					$this->sendConfirmationSuccessEmail($subscriberId);
					return true;
				} elseif(($oldStatus == self::STATUS_NOT_ACTIVE || $oldStatus == self::STATUS_UNCONFIRMED)
					&& ($status == self::STATUS_SUBSCRIBED || $status == self::STATUS_UNCONFIRMED)) {
                    Mage::log("22222222222222222");
					$subscriber->setStatus($status);
					$subscriber->save();
					$this->sendConfirmationRequestEmail($subscriberId);
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
	protected function addInactiveSubscriber($email) {
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
	protected function setInvitationCode($code=null) {
		$this->_invitationCode = $code ? $code : $this->randomSequence();
	}

	/**
	 * gets confirmation code from local variable
	 * @return int
	 */
	protected function getInvitationCode() {
		if(is_null($this->_invitationCode)) {
			$this->setInvitationCode();
		}
		return $this->_invitationCode;
	}

	/**
	 * sets subscriber id in local variable
	 * @param $sid
	 * @return void
	 */
	protected function setSubscriberId($sid) {
		$this->_subscriberId = (int) $sid;
	}

	/**
	 * gets subscriber id from local variable
	 * @return int|null
	 */
	protected function getSubscriberId() {
		return $this->_subscriberId;
	}

	/**
	 * gets current store website id
	 * @return int|null|string
	 */
	protected function getWebsiteId() {
		return Mage::app()->getStore()->getWebsiteId();
	}

	/**
	 * Gets invitation link from local variables,
	 * if they're not set returns false
	 * @return bool|string
	 */
	protected function getInvitationUrl() {
		$sid = $this->getSubscriberId();
		$code = $this->getInvitationCode();
		if($sid && $code) {
			return Mage::getUrl("newsletter/subscriber/invitation",array("id"=>$sid,"code"=>$code));
		} else {
			return false;
		}
	}
}
