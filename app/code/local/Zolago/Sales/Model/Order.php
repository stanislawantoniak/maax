<?php

class Zolago_Sales_Model_Order extends Mage_Sales_Model_Order
{
    /**
     * Send email with order data
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function sendNewOrderEmail()
    {
        Mage::log(__METHOD__ . '(' . __LINE__ . ')', null, 'mylog.log');

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
                'use_attachements'    => true,
                'customerIsGuest'     => $this->getCustomerIsGuest(),
                'isMoreVendors'       => $this->isMoreVendors(),
                'vendorNameList'     => $this->getVendorNameList(),
                "_ATTACHMENTS"        => Mage::helper("zolagopo")->getOrderImagesAsAttachments($this)
            )
        );


//

        /** @var Zolago_Dropship_Helper_Data $udropHlp */
//        $udropHlp = Mage::helper('udropship');
        /** @var Zolago_Po_Helper_Data $udpoHlp */
//        $udpoHlp = Mage::helper('udpo');

        /** @var Zolago_Po_Model_Po $po */
//        $udpo = Mage::getModel("zolagopo/po")->load($this->getId());

//        $vendor = $udropHlp->getVendor($udpo->getUdropshipVendor());


        $mailer->send();

        $this->setEmailSent(true);
        $this->_getResource()->saveAttribute($this, 'email_sent');

        return $this;
    }

    /**
     * @return Unirgy_DropshipPo_Model_Mysql4_Po_Collection $poList
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

}