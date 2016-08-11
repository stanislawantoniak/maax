<?php

/**
 * Class Zolago_Newsletter_Model_Subscriber
 *
 * @method string getSubscriberFirstname()
 * @method string getSubscriberLastname()
 *
 * @method $this setSubscriberFirstname($value)
 * @method $this setSubscriberLastname($value)
 */
class Zolago_Newsletter_Model_Subscriber extends Mage_Newsletter_Model_Subscriber
{
	const NEWSLETTER_CONFIRMATION_SALES_RULE_PATH = "newsletter/zolagosubscription/confirmation_sales_rule";

	protected $_mailFlag = false;
	
	public function getMailSendFlag() {
	    return $this->_mailFlag;
	}
	public function setMailSendFlag() {
	    $this->_mailFlag = true;
	}
   /**
     * Send new coupons to one subscriber
     */
    public function sendSubscriberCouponMail()
    {
        $email = $this->getSubscriberEmail();
        $customerId = $this->getCustomerId();
        $subscribersCustomersId = array ($customerId);
        $subscribers[$this->getId()] = $email;
        $subscribersCustomersId[$email] = $customerId;
        $subscribersCustomersSubscribers[$customerId] = $email;
        $subscribersStore[$customerId] = $this->getStoreId();
        return Mage::helper('zolagosalesrule')->sendCouponMails($subscribers, $subscribersCustomersId, $subscribersCustomersSubscribers,$subscribersStore);
    }    


	/**
	 * Sends out confirmation success email by Subscriber ID
	 * @param int $sid
	 * @return Mage_Newsletter_Model_Subscriber
	 */
	public function sendConfirmationSuccessEmail($sid=null)
	{
		if (!Mage::helper("zolagonewsletter")->isModuleActive())
			return parent::sendConfirmationSuccessEmail();

		$couponData = null;
		$confirmationSalesRuleId = $this->getConfirmationSalesRule();

		if($confirmationSalesRuleId && $this->emailIsSuitableForCoupon($sid)) {
			/** @var Zolago_SalesRule_Helper_Data $helper */
			$helper = Mage::helper("zolagosalesrule");

			/** @var Mage_SalesRule_Model_Rule $rule */
			$rule = Mage::getModel("salesrule/rule")->load($confirmationSalesRuleId);

			/** @var Mage_SalesRule_Model_Coupon $coupon */
			$coupon = $helper->getUnusedCouponByRule($rule);

			if($rule && $coupon) {
				$couponData = array(
					'rule' => $rule,
					'coupon' => $coupon
				);
			}
		}
		return $this->_sendNewsletterEmail(
			$sid,
			Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE),
			Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY),
			$couponData
		);
	}


	public function getConfirmationSalesRule() {
		return Mage::getStoreConfig(self::NEWSLETTER_CONFIRMATION_SALES_RULE_PATH);
	}

	protected function emailIsSuitableForCoupon($sid=null) {
		if(!is_null($sid)) {
			$model = Mage::getModel("newsletter/subscriber");
			$subscriber = $model->load($sid);
		} else {
			$subscriber = $this;
		}

		return !$subscriber->getCouponId() ? true : false;
	}

    /**
     * Saving customer subscription status
     *
     * @param   Zolago_Customer_Model_Customer $customer
     * @return  Zolago_Newsletter_Model_Subscriber
     */
    public function subscribeCustomer($customer)
    {
		if (!Mage::helper("zolagonewsletter")->isModuleActive())
			return parent::subscribeCustomer($customer);

	    $subscriber = $this;
	    $confirmationNeeded = Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1;

	    if ($customer->getImportMode()) {
		    $subscriber->setImportMode(true);
	    }

	    $customerStoreId = $this->_getCustomerStoreId($customer);

	    if(!$subscriber->getId()) {
		    // get all subscribers with this email
		    $existingSubscriber = $this->rawLoadByCustomer($customer);

		    if(!is_null($existingSubscriber) && $existingSubscriber->getId()) {
			    $subscriber->setData($existingSubscriber->getData());
                // Handle situation when customer's email was in subscribers list as guest
                // If it was then just assign customer to this email
                $subscriber->setCustomerId($customer->getId());
                $confirmCode = $subscriber->getSubscriberConfirmCode();
                if (empty($confirmCode)) {
                    $subscriber->setSubscriberConfirmCode($this->randomSequence());
                }
		    } else {
			    $subscriber->unsData();
		    }
	    }

        $subscriberStatus = $subscriber->getStatus();
        //handle situation when user was in newsletter subscribers list
        if($subscriber->getId()) {
            /*
             * Note:
             * Change is from 0 to 1 or from 1 to 0 [ getCustomerIsSubscribed() ]
             * const STATUS_SUBSCRIBED   -> 1
             * const STATUS_NOT_ACTIVE   -> 0
             * const STATUS_UNSUBSCRIBED -> 0
             * const STATUS_UNCONFIRMED  -> 0
             */
	        if($customer->hasIsSubscribedHasChanged() ||
                // never confirmed newsletter and now don't want newsletter
                // STATUS_UNCONFIRMED -> STATUS_NOT_ACTIVE
                (!$customer->getIsSubscribed() && $subscriberStatus == self::STATUS_UNCONFIRMED)
            ) {
		        $customer->unsIsSubscribedHasChanged();

                if ($customer->getIsSubscribed()) {
                    // customer wants to be subscribed

                    if ($subscriberStatus == self::STATUS_UNSUBSCRIBED) {
                        // customer is unsubscribed and he wants to be subscribed
                        $subscriber->setStatus(self::STATUS_SUBSCRIBED);
                        $subscriber->sendConfirmationSuccessEmail();
                    }
                    elseif ($subscriberStatus == self::STATUS_UNCONFIRMED) {
                        // customer don't confirm newsletter but he wants to be subscribed
                        $customer->setConfirmMsg(true);
                        $subscriber->sendConfirmationRequestEmail();
                    } elseif ($subscriberStatus == self::STATUS_SUBSCRIBED) {
						//do nothing
                    } else { //if $subscriberStatus == NULL || $subscriberStatus == self::STATUS_NOT_ACTIVE
	                    if($confirmationNeeded && !$customer->getCedSocialloginFid() && !$customer->getCedSocialloginGid()) {
		                    $customer->setConfirmMsg(true);
		                    $subscriber->setStatus(self::STATUS_UNCONFIRMED);
		                    $subscriber->sendConfirmationRequestEmail();
	                    } else {
		                    $subscriber->setStatus(self::STATUS_SUBSCRIBED);
		                    $subscriber->sendConfirmationSuccessEmail();
	                    }
                    }
                } else {
                    // customer don't wont to be subscribed
	                if ($subscriberStatus == self::STATUS_SUBSCRIBED) {
		                // he don't wont to be subscribed and he is subscribed
		                $subscriber->setStatus(self::STATUS_UNSUBSCRIBED);
		                $subscriber->sendUnsubscriptionEmail();
	                }
                }
	        }

            if($customer->hasIsEmailHasChanged()) {
                //called on the /zolagocustomer/confirm/confirm/
                $newCustomerEmail = $customer->getEmail();

                //1. do not replace old email in case when customer change account email
                //insert another one db row with the new email (for future use: ex. do not send coupon code twice)
                $m = clone $subscriber;
                $m->setId(null)->setEmail($newCustomerEmail);
                $m->save();

                //2. for other emails set customer_id=0
                $collection = Mage::getModel('newsletter/subscriber')
                    ->getCollection();
                $collection->addFieldToFilter('customer_id', array('eq' => $customer->getId()));
                $collection->addFieldToFilter('subscriber_email', array('neq' => $newCustomerEmail));
	            $collection->addFieldToFilter('store_id', array('eq' => $customerStoreId));

                foreach ($collection as $subscriberM) {
                    $subscriberM->setCustomerId(0);
                    $subscriberM->setStatus(self::STATUS_NOT_ACTIVE);
                    $subscriberM->save();
                }

                //remove duplicated email
                $collectionD = Mage::getModel('newsletter/subscriber')
                    ->getCollection();
                $collectionD->addFieldToFilter('customer_id', array('eq' => 0));
                $collectionD->addFieldToFilter('subscriber_email', array('eq' => $newCustomerEmail));
	            $collectionD->addFieldToFilter('store_id', array('eq' => $customerStoreId));

                foreach ($collectionD as $subscriberDM) {
                    $subscriberDM->delete();
                }

                $customer->unsIsEmailHasChanged();
                return $subscriber;
            }

	        $subscriber->save();
        }
        //and if he wasn't add it as new one with status NOT_ACTIVE if he didn't agree or as UNCONFIRMED if he agreed
        else {
            $confirmCode = $this->randomSequence();
	        if($customer->getIsSubscribed() && $confirmationNeeded && !$customer->getCedSocialloginFid() && !$customer->getCedSocialloginGid()) {
		        $newStatus = self::STATUS_UNCONFIRMED;
	        } elseif($customer->getIsSubscribed() && !$confirmationNeeded) {
		        $newStatus = self::STATUS_SUBSCRIBED;
	        } else {
		        $newStatus = null;
	        }
            if ($customer->getCedSocialloginFid() || $customer->getCedSocialloginGid()) {
                $confirmCode = null;
                if ($customer->getIsSubscribed()) {
                    $newStatus = self::STATUS_SUBSCRIBED;
                } else {
                    $newStatus = self::STATUS_UNSUBSCRIBED;
                }
            }
            if(!is_null($customer->getIsEmailHasChanged()) && is_null($customer->getIsJustRegistered())) {
                $newStatus = self::STATUS_NOT_ACTIVE;
                $customer->unsIsEmailHasChanged();
            }
            $subscriber
                ->setStoreId($customerStoreId)
                ->setCustomerId($customer->getId())
                ->setSubscriberConfirmCode($confirmCode)
                ->setEmail($customer->getEmail())
                ->setStatus($newStatus)
                ->setId(null);

			//if customer agreed to newsletter send him a confirmation email
	        $subscriber->save();
	        $this->setImportMode(false);
            if($newStatus == self::STATUS_UNCONFIRMED) {
	            $this->sendConfirmationRequestEmail();
	            $customer->setConfirmMsg(true);
            } elseif($newStatus == self::STATUS_SUBSCRIBED) {
	            $this->sendConfirmationSuccessEmail();
            }
        }

        return $subscriber;
    }

	protected function _getCustomerStoreId($customer) {
		return
			$customer->getStoreId()
			? $customer->getStoreId()
			: Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
	}

	/**
	 * @param int|null $sid
	 * @return Mage_Core_Model_Abstract|Zolago_Newsletter_Model_Subscriber
	 */
	public function sendUnsubscriptionEmail($sid=null)
	{
		if (!Mage::helper("zolagonewsletter")->isModuleActive())
			return parent::sendUnsubscriptionEmail();

		return $this->_sendNewsletterEmail(
			$sid,
			Mage::getStoreConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE),
			Mage::getStoreConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY)
		);
	}

	/**
	 * @param int|null $sid
	 * @return $this|Mage_Core_Model_Abstract|Zolago_Newsletter_Model_Subscriber
	 */
	public function sendConfirmationRequestEmail($sid=null)
	{
		if (!Mage::helper("zolagonewsletter")->isModuleActive())
			return parent::sendConfirmationRequestEmail();

		return $this->_sendNewsletterEmail(
			$sid,
			Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_TEMPLATE),
			Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_IDENTITY)
		);
	}


	protected function _sendNewsletterEmail($sid=null,$template,$sender,$couponData=null) {
		if ($this->getImportMode() || !$template || !$sender) {
			return $this;
		}

		if(!is_null($sid)) {
			$model = Mage::getModel("newsletter/subscriber");
			$subscriber = $model->load($sid);
		} else {
			$subscriber = $this;
		}

		$translate = Mage::getSingleton('core/translate');
		/* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline(false);

		/** @var Mage_Customer_Model_Customer $customer */
		$customer = Mage::getModel("customer/customer")->load($subscriber->getCustomerId());

		$data = array(
			'store_name' => Mage::app()->getStore()->getName(),
			'subscriber' => $subscriber,
			'use_attachments' => true
		);

		if(is_array($couponData)) {
			$data['use_coupon'] = true;
			$data['coupon'] = $couponData['coupon'];
			$data['rule'] = $couponData['rule'];
		} else {
			$data['use_coupon'] = false;
		}

		/** @var Zolago_Common_Helper_Data $helper */
		$helper = Mage::helper("zolagocommon");
		$helper->sendEmailTemplate(
			$subscriber->getEmail(),
			$subscriber->getName(),
			$template,
			$data,
			$this->_getCustomerStoreId($customer),
			$sender
		);

		if($data['use_coupon']) {
			$couponData['coupon']->setNewsletterSent(1);
			$couponData['coupon']->save();
			$subscriber->setCouponId($couponData['coupon']->getCouponId());
			$subscriber->save();
		}

		$translate->setTranslateInline(true);

		return $subscriber;
	}





    /**
     * Changes is from 0 to 1 or from 1 to 0
     * const STATUS_SUBSCRIBED   -> 1
     * const STATUS_NOT_ACTIVE   -> 0
     * const STATUS_UNSUBSCRIBED -> 0
     * const STATUS_UNCONFIRMED  -> 0
     *
     * @param $customer Zolago_Customer_Model_Customer
     * @return int
     */
	public function getCustomerIsSubscribed($customer) {
		return  $this->rawLoadByCustomer($customer)->getSubscriberStatus() == self::STATUS_SUBSCRIBED ? 1 : 0;
	}

    /**
     * Load subscriber info by customer override
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Newsletter_Model_Subscriber
     */
    public function loadByCustomer(Mage_Customer_Model_Customer $customer, $save = true)
    {
		if (!Mage::helper("zolagonewsletter")->isModuleActive())
			return parent::loadByCustomer($customer);

        $data = $this->getResource()->loadByCustomer($customer);
        $this->addData($data);
        if (!empty($data) && $customer->getId() && !$this->getCustomerId()) {
            $this->setCustomerId($customer->getId());
            if (empty($data['subscriber_confirm_code'])) {
                $this->setSubscriberConfirmCode($this->randomSequence());
            }
            if ($this->getStatus()==self::STATUS_NOT_ACTIVE) {
                $this->setStatus($customer->getIsSubscribed() ? self::STATUS_SUBSCRIBED : self::STATUS_UNSUBSCRIBED);
            }
            if ($save) {
                $this->save();
            }
        }
        return $this;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param null $storeId
     * @return Zolago_Newsletter_Model_Subscriber
     */
    public function rawLoadByCustomer(Mage_Customer_Model_Customer $customer, $storeId = null) {
        $_storeId = $this->_getCustomerStoreId($customer);
        if (!is_null($storeId)) {
            $_storeId = $storeId;
        }

        /** @var Zolago_Newsletter_Model_Subscriber $subscriber */
        $subscriber = Mage::getModel('newsletter/subscriber')
            ->getCollection()
            ->addFieldToFilter('subscriber_email', array('eq' => $customer->getEmail()))
            ->addFieldToFilter('store_id', array('eq' => $_storeId))
            ->getFirstItem();
        return $subscriber;
    }

    public function rawLoadByEmail($email, $storeId) {

        /** @var Zolago_Newsletter_Model_Subscriber $subscriber */
        $subscriber = Mage::getModel('newsletter/subscriber')
            ->getCollection()
            ->addFieldToFilter('subscriber_email', array('eq' => $email))
            ->addFieldToFilter('store_id', array('eq' => $storeId))
            ->getFirstItem();
        return $subscriber;
    }

    /**
     * Simplified version of subscribe
     *
     * @return $this
     * @throws Exception
     */
    public function simpleSubscribe()
    {
        try {
            $this->setSubscriberStatus(self::STATUS_SUBSCRIBED)
                ->save();
            $this->sendConfirmationSuccessEmail();
            return $this;
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
