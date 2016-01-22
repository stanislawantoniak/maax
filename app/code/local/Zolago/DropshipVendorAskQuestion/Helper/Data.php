<?php

/**
 * @category   Zolago
 * @package    Zolago_DropshipVendorAskQuestion
 */

class Zolago_DropshipVendorAskQuestion_Helper_Data extends Unirgy_DropshipVendorAskQuestion_Helper_Data
{
	const XML_PATH_EMAIL_CUSTOMER_CONFIRMATON = "udqa/general/customer_new_question_confirmation";
	const XML_PATH_EMAIL_CUSTOMER_CONFIRMATON_IDENTITY = "udqa/general/vendor_email_identity";
	const XML_PATH_EMAIL_CUSTOMER_REPLY = 'udqa/general/customer_email_template';
	
	/**
	 * @param Zolago_DropshipVendorAskQuestion_Model_Question $question
	 * @return type
	 */
	public function notifyCustomer($question)
    {
	    $store = Mage::helper('udqa')->getStore($question);
	    $storeId = $store->getId();
	    $vendor = Mage::helper("udropship")->getVendor($question->getVendorId());
	    $localVendorId = Mage::helper("udropship")->getLocalVendorId($storeId);
	    $identity = Mage::getStoreConfig(self::XML_PATH_EMAIL_CUSTOMER_CONFIRMATON_IDENTITY, $storeId);

	    Mage::helper('udropship')->setDesignStore($store);

		if($question->isObjectNew()){
			$template = Mage::getStoreConfig(self::XML_PATH_EMAIL_CUSTOMER_CONFIRMATON, $storeId);
		} elseif(Mage::helper('udqa')->isNotifyCustomer($question)) {
			$template = Mage::getStoreConfig(self::XML_PATH_EMAIL_CUSTOMER_REPLY);
		} else {
			$template = false;
		}

	    if($template) {
            $questionText = $question->getData('question_text');
            $question->setData('question_text',Mage::helper('zolagocommon')->nToBr($questionText));

            $answerText = $question->getData('answer_text');
            $question->setData('answer_text',Mage::helper('zolagocommon')->nToBr($answerText));
            
            $incrementId = '';
            if ($poId = $question->getPoId()) {
                $order = Mage::getModel('udropship/po')->load($poId);
                $incrementId = $order->getIncrementId();
            }

            $templateParams = array(
                'store' => $store,
                'store_name' => $store->getName(),
                'customer_name' => $question->getCustomerName(),
                'customer_email' => $question->getCustomerEmail(),
                "vendor" => $vendor,
                'vendor_name' => $question->getVendorName(),
                'vendor_email' => $question->getVendorEmail(),
                "local_vendor" => $localVendorId && $localVendorId==$vendor->getId(),
                'question' => $question,
                'show_customer_info' => Mage::getStoreConfigFlag('udqa/general/show_customer_info', $store),
                'show_vendor_info' => Mage::getStoreConfigFlag('udqa/general/show_vendor_info', $store),
                "use_attachments" => true,
                'increment_id' => $incrementId,
            );

		    $helper = Mage::helper("zolagocommon");
		    /* @var $helper Zolago_Common_Helper_Data */
		    $helper->sendEmailTemplate(
			    $question->getCustomerEmail(),
			    $question->getCustomerName(),
			    $template,
			    $templateParams,
			    $storeId,
			    $identity
		    );

		    if($template == Mage::getStoreConfig(self::XML_PATH_EMAIL_CUSTOMER_REPLY)) {
			    $question->setIsCustomerNotified(1);
			    Mage::getResourceSingleton('udropship/helper')->updateModelFields($question, array('is_customer_notified'));
		    }
	    }
	    Mage::helper('udropship')->setDesignStore();
    }


	/**
     * Notify vendor agents
     *
     * @param $question
     *
     * @return $this
     */
    public function notifyVendorAgent($question)
    {
        $store = Mage::helper('udqa')->getStore($question);

        if (self::isNotifyVendorAgents($question)) {
            Mage::helper('udropship')->setDesignStore($store);

            $vendorId = $question->getVendorId();

            $template = $store->getConfig('udqa/general/vendor_email_template');
            $identity = $store->getConfig('udqa/general/vendor_email_identity');
            $vendorM = Mage::getResourceModel('zolagoudqa/question');
            $superVendorAgents = $vendorM->getSuperVendorHelpdeskAgentEmails($vendorId);
            $vendorAgents = $vendorM->getVendorHelpdeskAgentEmails($vendorId);

            $incrementId = '';
            if ($poId = $question->getPoId()) {
                $order = Mage::getModel('udropship/po')->load($poId);
                $incrementId = $order->getIncrementId();
            }

            $emails = array();
            $emails += $superVendorAgents;
            $emails += $vendorAgents;
            unset($superVendorAgents);
            unset($vendorAgents);

            if (!empty($emails)) {
                $data = array(
                    'store'              => $store,
                    'store_name'         => $store->getName(),
                    'customer_name'      => $question->getCustomerName(),
                    'customer_email'     => $question->getCustomerEmail(),
                    'vendor_name'        => $question->getVendorName(),
                    'vendor_email'       => $question->getVendorEmail(),
                    'question'           => $question,
                    'show_customer_info' => Mage::getStoreConfigFlag('udqa/general/show_customer_info', $store),
                    'show_vendor_info'   => Mage::getStoreConfigFlag('udqa/general/show_vendor_info', $store),
	                'use_attachments'    => true,
	                'increment_id'		 => $incrementId,
	                'question_url'		 => Mage::getUrl('udqa/vendor/questionEdit',array('id'=>$question->getId())),
                );
	            /** @var Zolago_Common_Helper_Data $mailer */
	            $mailer = Mage::helper('zolagocommon');
                foreach ($emails as $email => $_) {
                    $data['vendor_name'] = implode(' ', array($_['firstname'], $_['lastname']));
	                $mailer->sendEmailTemplate(
		                $email,
		                $data['vendor_name'],
		                $template,
		                $data,
		                $store->getId(),
		                $identity
	                );
                }
                unset($email);
                unset($_);

                $question->setIsVendorAgentsNotified(1);
                Mage::getResourceSingleton('udropship/helper')->updateModelFields(
                    $question, array('is_vendor_agents_notified')
                );

            }
            Mage::helper('udropship')->setDesignStore();
        }
        return $this;
    }


    /**
     * Check if vendor agents notified
     *
     * @param $question
     *
     * @return bool
     */
    public function isNotifyVendorAgents($question)
    {
        $store = Mage::helper('udqa')->getStore($question);
        return !$question->getIsVendorAgentsNotified()
        && $question->getQuestionStatus() == Unirgy_DropshipVendorAskQuestion_Model_Source::UDQA_STATUS_APPROVED
        && Mage::getStoreConfigFlag(
            'udqa/general/send_vendor_agent_notifications', $store
        );
    }

}