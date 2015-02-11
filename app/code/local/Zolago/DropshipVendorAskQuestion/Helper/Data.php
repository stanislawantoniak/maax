<?php

/**
 * @category   Zolago
 * @package    Zolago_DropshipVendorAskQuestion
 */

class Zolago_DropshipVendorAskQuestion_Helper_Data extends Unirgy_DropshipVendorAskQuestion_Helper_Data
{
	const XML_PATH_EMAIL_CUSTOMER_CONFIRMATON = "udqa/general/customer_new_question_confirmation";
	const XML_PATH_EMAIL_CUSTOMER_CONFIRMATON_IDENTITY = "udqa/general/vendor_email_identity";
	const XML_PATH_EMAIL_CUTOMER_REPLY = 'udqa/general/admin_customer_email_template';
	
	/**
	 * @param Zolago_DropshipVendorAskQuestion_Model_Question $question
	 * @return type
	 */
	public function notifyCustomer($question)
    {
	    Mage::log("notifyCustomer",null,'question.log');
		if($question->isObjectNew()){
			/**
			 * @todo add store_id to question
			 */
			$storeId = Mage::app()->getStore()->getId();
			$vendor = Mage::helper("udropship")->getVendor($question->getVendorId());
			$localVendorId = Mage::helper("udropship")->getLocalVendorId($storeId);
			// Params 
			$templateParams = array(
				"question"			=> $question,
				"vendor"			=> $vendor,
				"local_vendor"		=> $localVendorId && $localVendorId==$vendor->getId(),
				"use_attachments"	=> true
			);
			
			$helper = Mage::helper("zolagocommon");
			/* @var $helper Zolago_Common_Helper_Data */
			$helper->sendEmailTemplate(
				$question->getCustomerEmail(), 
				$question->getCustomerName(), 
				Mage::getStoreConfig(self::XML_PATH_EMAIL_CUSTOMER_CONFIRMATON, $storeId),
				$templateParams, 
				$storeId,
				Mage::getStoreConfig(self::XML_PATH_EMAIL_CUSTOMER_CONFIRMATON_IDENTITY, $storeId)
			);
			
			return $this;
		}
        return parent::notifyCustomer($question);
    }

	/**
	 * @param Zolago_DropshipVendorAskQuestion_Model_Question $question
	 * @return Zolago_DropshipVendorAskQuestion_Helper_Data $this
	 */
	public function notifyAdminCustomer($question)
	{
		Mage::log("notifyAdminCustomer",null,'question.log');
		return parent::notifyAdminCustomer($question);

		$store = Mage::helper('udqa')->getStore($question);
		if (Mage::helper('udqa')->isNotifyAdminCustomer($question)) {
			Mage::helper('udropship')->setDesignStore($store);

			$storeId = $store->getId();
			$localVendorId = Mage::helper("udropship")->getLocalVendorId($storeId);
			$identity = $store->getConfig('udqa/general/vendor_email_identity');
			$vendor = Mage::helper("udropship")->getVendor($question->getVendorId());
			$template = $store->getConfig('udqa/general/admin_customer_email_template');
			$adminIdent = $store->getConfig('udqa/general/admin_email_identity');

			$data = array(
				'store' => $store,
				'store_name' => $store->getName(),
				'customer_name' => $question->getCustomerName(),
				'customer_email' => $question->getCustomerEmail(),
				'vendor' => Mage::helper("udropship")->getVendor($question->getVendorId()),
				'vendor_name' => $question->getVendorName(),
				'vendor_email' => $question->getVendorEmail(),
				'local_vendor'		=> $localVendorId && $localVendorId==$vendor->getId(),
				'question' => $question,
				'show_customer_info' => Mage::getStoreConfigFlag('udqa/general/show_customer_info', $store),
				'show_vendor_info' => Mage::getStoreConfigFlag('udqa/general/show_vendor_info', $store),
				'use_attachements' => true
			);

			$helper = Mage::helper("zolagocommon");
			/** @var $helper Zolago_Common_Helper_Data */
			$helper->sendEmailTemplate(
				Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/email', $store),
				Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/name', $store),
				$template,
				$data,
				$storeId,
				$identity
			);

			$question->setIsAdminAnswerNotified(1);
			Mage::getResourceSingleton('udropship/helper')->updateModelFields($question, array('is_admin_answer_notified'));

			Mage::helper('udropship')->setDesignStore();
		}
		return $this;
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
	    Mage::log("notifyVendorAgent",null,'question.log');
        $store = Mage::helper('udqa')->getStore($question);

        if (self::isNotifyVendorAgents($question)) {
            Mage::helper('udropship')->setDesignStore($store);
            $emails = array();

            $vendorId = $question->getVendorId();

            Mage::helper('udropship')->setDesignStore($store);

            $template = $store->getConfig('udqa/general/vendor_email_template');
            $identity = $store->getConfig('udqa/general/vendor_email_identity');
            $vendorM = Mage::getResourceModel('zolagoudqa/question');
            $superVendorAgents = $vendorM->getSuperVendorHelpdeskAgentEmails($vendorId);
            $vendorAgents = $vendorM->getVendorHelpdeskAgentEmails($vendorId);

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
	                'use_attachments'    => true
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