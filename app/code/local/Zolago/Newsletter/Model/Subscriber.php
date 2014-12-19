<?php
class Zolago_Newsletter_Model_Subscriber extends Mage_Newsletter_Model_Subscriber
{
	/**
	 * Sends out confirmation success email by Subscriber ID
	 * @param int $sid
	 * @return Mage_Newsletter_Model_Subscriber
	 */
	public function sendConfirmationSuccessEmail($sid=null)
	{
		return $this->sendNewsletterEmail(
			$sid,
			Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE),
			Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY)
		);
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

	    $customerStoreId = $this->getCustomerStoreId($customer);
        $this->loadByCustomer($customer);

	    //if load by customer don't return valid object then try to load by it's email
	    $guestSubscriber = false;
	    if(!$this->getId()) {
		    $this->loadByEmail($customer->getEmail());
		    //Check if customer's email exists in database for his shop
		    if($this->getId() && $customer->getStore() == $customerStoreId) {
			    $guestSubscriber = true;
		    }
		    // if it exists in database but for other shop then treat it as new one
		    else {
			    $this->unsData();
		    }
	    }

	    $successMsg = false;
        $status = $this->getStatus();
        //handle situation when user was in newsletter subscribers list
        if($this->getId()) {
	        if(!is_null($customer->getIsSubscribedHasChanged())) {
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
				        $successMsg = $this->__("The subscription has been saved.");
			        } //otherwise set his status to unconfirmed and send confirmation request email
			        else {
				        $this->setStatus(self::STATUS_UNCONFIRMED);
				        $this->sendConfirmationRequestEmail();
				        $successMsg = $this->__("Your subscribtion has been saved.<br />To start receiving our newsletter you have to confirm your e-mail by clicking confirmation link in e-mail that we have just sent to you.<br />Newsletter setting in your account will be changed after e-mail confirmation.");
			        }
		        }
	        }
	        //handle situation when customer's email was in subscribers list as guest
	        //if it was then just assign customer to this email
	        if($guestSubscriber) {
		        $this->setCustomerId($customer->getId());
	        }


	        if(!is_null($customer->getIsEmailHasChanged())) {
		        //do not replace old email in case when customer change account email
		        //insert another one db row with the new email
		        //on the /customer/account/edit page
		        $m = clone $this;
		        $m->setId(null);
		        $m->setStoreId($customer->getStoreId())
			        ->setEmail($customer->getEmail());
	        }
        }
        //and if he wasn't add it as new one with status NOT_ACTIVE if he didn't agree or as UNCONFIRMED if he agreed
        else {
            $newStatus = $customer->getIsSubscribed() ? self::STATUS_UNCONFIRMED : null;
            $this
                ->setStoreId($customerStoreId)
                ->setCustomerId($customer->getId())
                ->setSubscriberConfirmCode($this->randomSequence())
                ->setEmail($customer->getEmail())
                ->setStatus($newStatus)
                ->setId(null);

			//if customer agreed to newsletter send him a confirmation email
            if($newStatus == self::STATUS_UNCONFIRMED) {
                $this->sendConfirmationRequestEmail();
	            //add msg?
            }
        }

        $this->save();

	    //check if any success msg was set during process and add it to session
	    if($successMsg) {
		    Mage::getSingleton('customer/session')->addSuccess($successMsg);
	    }

        return $this;
    }

	protected function getCustomerStoreId($customer) {
		return $customer->getStoreId() ? $customer->getStoreId() : Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
	}

	/**
	 * @param int|null $sid
	 * @return Mage_Core_Model_Abstract|Zolago_Newsletter_Model_Subscriber
	 */
	public function sendUnsubscriptionEmail($sid=null)
	{
		return $this->sendNewsletterEmail(
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
		return $this->sendNewsletterEmail(
			$sid,
			Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_TEMPLATE),
			Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_IDENTITY)
		);
	}


	protected function sendNewsletterEmail($sid=null,$template,$sender) {
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
		$customer = Mage::getModel("customer")->load($subscriber->getCustomerId());

		/** @var Zolago_Common_Helper_Data $helper */
		$helper = Mage::helper("zolagocommon");
		$helper->sendEmailTemplate(
			$subscriber->getEmail(),
			$subscriber->getName(),
			$template,
			array(
				'store_name' => Mage::app()->getStore()->getName(),
				'subscriber' => $subscriber,
				'use_attachments' => true
			),
			$this->getCustomerStoreId($customer),
			$sender
		);

		$translate->setTranslateInline(true);

		return $subscriber;
	}
}
