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
        Mage::log("subscribeCustomer was fired");
        $this->loadByCustomer($customer);

        if ($customer->getImportMode()) {
            $this->setImportMode(true);
        }


        $status = $this->getStatus();
        //handle situation when user was in newsletter subscribers list
        if($this->getId()) {
            //if customer wants to unsubscribe then unsubscribe him and send an unsubscription email
            if(!$customer->getIsSubscribed() && $status == self::STATUS_SUBSCRIBED) {
                $this->setStatus(self::STATUS_UNSUBSCRIBED);
            }
            //otherwise check if customer wants to subscribe
            elseif($customer->getIsSubscribed() && $status != self::STATUS_SUBSCRIBED) {
                //if he want to subscribe and he was subscribed before (right now is unsubscribed) just make him subscribed
                if($status == self::STATUS_UNSUBSCRIBED) {
                    $this->setStatus(self::STATUS_SUBSCRIBED);
                }
                //otherwise set his status to unconfirmed and send confirmation request email
                else {
                    $this->setStatus(self::STATUS_UNCONFIRMED);
                    $this->sendConfirmationRequestEmail();
                }
            }
            //do not replace old email in case when customer change account email
            //insert another one db row with the new email
            //on the /customer/account/edit page
            $m = clone $this;
            $m->setId(null);
            $m->setStoreId($customer->getStoreId())
                ->setEmail($customer->getEmail());
        }
        //and if he wasn't add it as NOT_ACTIVE if he didn't agree or as UNCONFIRMED if he agreed
        else {
            $newStatus = $customer->getIsSubscribed() ? self::STATUS_UNCONFIRMED : self::STATUS_NOT_ACTIVE;
            $this
                ->setCustomerId($customer->getId())
                ->setSubscriberConfirmCode($this->randomSequence())
                ->setEmail($customer->getEmail())
                ->setStatus($newStatus)
                ->setId(null);
            if($newStatus == self::STATUS_UNCONFIRMED) {
                $this->sendConfirmationRequestEmail();
            }
        }

        $this->save();
        return $this;
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
			Mage::app()->getStore(),
			$sender
		);

		$translate->setTranslateInline(true);

		return $subscriber;
	}




}
