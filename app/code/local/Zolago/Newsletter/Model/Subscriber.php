<?php
class Zolago_Newsletter_Model_Subscriber extends Mage_Newsletter_Model_Subscriber
{
	const NEWSLETTER_CONFIRMATION_SALES_RULE_PATH = "newsletter/subscription/confirmation_sales_rule";

	/**
	 * Sends out confirmation success email by Subscriber ID
	 * @param int $sid
	 * @return Mage_Newsletter_Model_Subscriber
	 */
	public function sendConfirmationSuccessEmail($sid=null)
	{
		$confirmationSalesRule = $this->getConfirmationSalesRule();

		if($confirmationSalesRule && $this->emailIsSuitableForCoupon($sid)) {
			/** @var Zolago_SalesRule_Helper_Data $helper */
			$helper = Mage::helper("zolagosalesrule");
			$coupon = $helper->getUnusedCouponByRuleId($confirmationSalesRule);
		} else {
			$coupon = null;
		}

		return $this->_sendNewsletterEmail(
			$sid,
			Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE),
			Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY),
			$coupon
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
     * @param   Mage_Customer_Model_Customer $customer
     * @return  Mage_Newsletter_Model_Subscriber
     */
    public function subscribeCustomer($customer)
    {
	    if ($customer->getImportMode()) {
		    $this->setImportMode(true);
	    }

	    $customerStoreId = $this->_getCustomerStoreId($customer);
        $this->loadByCustomer($customer);

	    //if load by customer don't return valid object then try to load by it's email
	    $guestSubscriber = false;
	    if(!$this->getId()) {
		    // get all subscribers with this email
		    $subscriber = Mage::getModel('newsletter/subscriber')
			    ->getCollection()
			    ->addFieldToFilter('subscriber_email', array('eq' => $customer->getEmail()))
			    ->addFieldToFilter('store_id', array('eq' => $customerStoreId))
			    ->getFirstItem();

		    if(!is_null($subscriber) && $subscriber->hasId() && $subscriber->getId()) {
			    $this->setData($subscriber->getData());
			    $guestSubscriber = true;
		    } else {
			    $this->unsData();
		    }
	    }

        $status = $this->getStatus();
        //handle situation when user was in newsletter subscribers list
        if($this->getId()) {
	        if($customer->hasIsSubscribedHasChanged()) {
		        $customer->unsIsSubscribedHasChanged();
		        //if customer wants to unsubscribe then unsubscribe him and send an unsubscription email
		        if (!$customer->getIsSubscribed() && $status == self::STATUS_SUBSCRIBED) {
			        $this->setStatus(self::STATUS_UNSUBSCRIBED);
			        $this->sendUnsubscriptionEmail();
		        } //otherwise check if customer wants to subscribe
		        elseif ($customer->getIsSubscribed() && $status != self::STATUS_SUBSCRIBED) {
			        //if he want to subscribe and he was subscribed before (right now is unsubscribed) just make him subscribed
			        if ($status == self::STATUS_UNSUBSCRIBED) {
				        $this->setStatus(self::STATUS_SUBSCRIBED);
				        $this->sendConfirmationSuccessEmail();
			        } //otherwise set his status to unconfirmed and send confirmation request email
			        else {
				        $this->setStatus(self::STATUS_UNCONFIRMED);
				        $this->sendConfirmationRequestEmail();
				        $customer->setConfirmMsg(true);
			        }
		        }
	        }
	        //handle situation when customer's email was in subscribers list as guest
	        //if it was then just assign customer to this email
	        if($guestSubscriber) {
		        $this->setCustomerId($customer->getId());
	        }

            if ($customer->hasIsEmailHasChanged()) {
                //called on the /zolagocustomer/confirm/confirm/
                $newCustomerEmail = $customer->getEmail();

                //1. do not replace old email in case when customer change account email
                //insert another one db row with the new email (for future use: ex. do not send coupon code twice)
                $m = clone $this;
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
                return $this;
            }

	        $this->save();
        }
        //and if he wasn't add it as new one with status NOT_ACTIVE if he didn't agree or as UNCONFIRMED if he agreed
        else {
            $newStatus = $customer->getIsSubscribed() ? self::STATUS_UNCONFIRMED : null;
            if(!is_null($customer->getIsEmailHasChanged()) && is_null($customer->getIsJustRegistered())) {
                $newStatus = self::STATUS_NOT_ACTIVE;
                $customer->unsIsEmailHasChanged();
            }
            $this
                ->setStoreId($customerStoreId)
                ->setCustomerId($customer->getId())
                ->setSubscriberConfirmCode($this->randomSequence())
                ->setEmail($customer->getEmail())
                ->setStatus($newStatus)
                ->setId(null);

			//if customer agreed to newsletter send him a confirmation email
	        $this->save();
            if($newStatus == self::STATUS_UNCONFIRMED) {
	            $this->sendConfirmationRequestEmail();
	            $customer->setConfirmMsg(true);
            }
        }

        return $this;
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
		Mage::log("trying to send...");
		return $this->_sendNewsletterEmail(
			$sid,
			Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_TEMPLATE),
			Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_IDENTITY)
		);
	}


	protected function _sendNewsletterEmail($sid=null,$template,$sender,$coupon=null) {
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

		if($coupon instanceof Mage_SalesRule_Model_Coupon) {
			$data['coupon'] = $coupon->getCode();

			/** @var Zolago_SalesRule_Helper_Data $helper */
			$helper = Mage::helper("zolagosalesrule");
			$data['coupon_desc'] = $helper->getSalesRuleDesc($coupon->getRuleId());

			$coupon->setNewsletterSent(1);
			$coupon->save();

			$subscriber->setCouponId($coupon->getCouponId());
			$subscriber->save();
		} else {
			$data['coupon'] = false;
		}

		/** @var Zolago_Common_Helper_Data $helper */
		$helper = Mage::helper("zolagocommon");
		$emailsent = $helper->sendEmailTemplate(
			$subscriber->getEmail(),
			$subscriber->getName(),
			$template,
			$data,
			$this->_getCustomerStoreId($customer),
			$sender
		);

		$translate->setTranslateInline(true);

		return $subscriber;
	}

	public function getCustomerIsSubscribed($customer) {
		return  $this->loadByCustomer($customer)->getSubscriberStatus() == 1 ? 1 : 0;
	}
}
