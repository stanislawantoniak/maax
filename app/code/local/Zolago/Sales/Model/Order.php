<?php

class Zolago_Sales_Model_Order extends Mage_Sales_Model_Order
{
    function queueNewOrderEmail($forceMode = false) {
        $this->sendNewOrderEmail(); // no queue
    }
    /**
     * Send email with order data
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function sendNewOrderEmail()
    {
        $storeId = $this->getStore()->getId();

        if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $this;
        }

        $emailSentAttributeValue = $this->load($this->getId())->getData('email_sent');
        $this->setEmailSent((bool)$emailSentAttributeValue);
        if ($this->getEmailSent()) {
            return $this;
        }

        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

//        // Start store emulation process
//        $appEmulation = Mage::getSingleton('core/app_emulation');
//        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
//
//        try {
//            // Retrieve specified view block from appropriate design package (depends on emulated store)
//            $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())
//                ->setIsSecureMode(true);
//            $paymentBlock->getMethod()->setStore($storeId);
//            $paymentBlockHtml = $paymentBlock->toHtml();
//        } catch (Exception $exception) {
//            // Stop store emulation process
//            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
//            throw $exception;
//        }
//
//        // Stop store emulation process
//        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($this->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $this->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $this->getCustomerName();
        }

        /** @var Zolago_Common_Model_Core_Email_Template_Mailer $mailer */
        $mailer = Mage::getModel('zolagocommon/core_email_template_mailer');
        /** @var Mage_Core_Model_Email_Info $emailInfo */
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->getCustomerEmail(), $customerName);
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'        => $this,
                'billing'      => $this->getBillingAddress(),
//                'payment_html' => $paymentBlockHtml,
                'use_attachments'    => true,
                'customerIsGuest'     => $this->getCustomerIsGuest() ? true : false,
                'isMoreVendors'       => $this->isMoreVendors() ? true : false,
                'vendorNameList'     => $this->getVendorNameList(),
                "_ATTACHMENTS"        => Mage::helper("zolagopo")->getOrderImagesAsAttachments($this)
            )
        );

        $mailer->send();

        $this->setEmailSent(true);
        $this->_getResource()->saveAttribute($this, 'email_sent');

        return $this;
    }

    /**
     * @return ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Collection $poList
     */
    public function getPoListByOrder() {
        $collection = Mage::getResourceModel('udpo/po_collection');
        $collection->addFieldToFilter("order_id", $this->getId());
        return $collection;
    }

    protected function isMoreVendors() {
        return $this->getPoListByOrder($this)->getSize() >= 2 ? true : false;
    }

    protected function getVendorNameList() {
        $collection = $this->getPoListByOrder();
        $str = '';

        foreach ($collection as $po) {
            /** @var Zolago_Po_Model_Po $po */
            $str .= $po->getVendorName() . ', ';
        }

        return trim($str, " ,");
    }

    /**
     * @return Zolago_Po_Model_Po
     */
    public function firstPo() {
        return $this->getPoListByOrder()->getFirstItem();
    }

    /**
     * @return string
     */
    public function getFormattedGrandTotalInclTax() {
        $sum = 0;
        $collection =  $this->getPoListByOrder();
        foreach ($collection as $po) {
            /** @var Zolago_Po_Model_Po $po */
            $sum += $po->getGrandTotalInclTax();
        }

        return Mage::app()->getLocale()->currency(
            $this->getStore()->getCurrentCurrencyCode()
        )->toCurrency($sum);
    }
    
   /**
    * @todo implement
    * @return bool
    */
   public function isGatewayPayment() {
       if(!$this->getPayment()){
           return false;
       }
	   return $this->getPayment()->getMethod() == Zolago_Payment_Model_Gateway::PAYMENT_METHOD_CODE;
   }

   /**
    * @return bool
    */
   public function isPaymentCheckOnDelivery() {
       if(!$this->getPayment()){
           return false;
       }
       return $this->getPayment()->getMethod() == Mage::getSingleton("payment/method_cashondelivery")->getCode();
   }

    /**
     * return true if payment method is Banktransfer
     * if not return false
     *
     * @return bool
     */
    public function isPaymentBanktransfer() {
        if(!$this->getPayment()){
            return false;
        }
       return $this->getPayment()->getMethod() == Mage_Payment_Model_Method_Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE;
    }

    /**
     * return true if payment method is dotpay
     * if nor return false
     *
     * @return bool
     */
    public function isPaymentDotpay() {
        if(!$this->getPayment()){
            return false;
        }
       return $this->getPayment()->getMethod() == Zolago_Dotpay_Model_Client::PAYMENT_METHOD;
    }

    /**
     * @return bool
     */
    public function isCC() {
        if(!$this->getPayment()){
            return false;
        }
        $data = $this->getPayment()->getAdditionalInformation();
        return isset($data['is_cc']) ? true : false;
    }

    /**
     * @return bool
     */
    public function isGateway() {
        if(!$this->getPayment()){
            return false;
        }
        $data = $this->getPayment()->getAdditionalInformation();
        return isset($data['is_gateway']) ? true : false;
    }



    /**
     * Replace customer email with new email
     *
     * @param $newEmail
     * @param $customerId
     * @param $storeId
     */
    public function replaceEmailInOrders($newEmail, $customerId, $storeId)
    {
        if (empty($customerId)) {
            return;
        }
        $sameEmailCollection = $this->getCollection();

        $sameEmailCollection->addFieldToFilter("customer_id", $customerId);
        $sameEmailCollection->addFieldToFilter("store_id", $storeId);

        if ($sameEmailCollection->count()) {
            foreach ($sameEmailCollection as $order) {
                $order->setCustomerEmail($newEmail);
                $order->getResource()->saveAttribute($order, "customer_email");
            }
        }
    }


	public function assignToCustomer($customerId,$save=false) {
		$this
			->setCustomerIsGuest(0)
			->setCustomerId($customerId);
		if($save) {
			$this->save();
		}
		return $this;
	}

}