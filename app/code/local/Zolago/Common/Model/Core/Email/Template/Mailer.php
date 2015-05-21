<?php
class Zolago_Common_Model_Core_Email_Template_Mailer extends Mage_Core_Model_Email_Template_Mailer
{
	/**
	 * Append logo if attachments used
	 * @param array $templateParams
	 * @return type
	 */
	public function setTemplateParams(array $templateParams = array()) {
		// Append logo if needed
		if(isset($templateParams['use_attachments']) || isset($templateParams['_ATTACHMENTS'])){
			$templateParams['_ATTACHMENTS'][] = array(
				"filename"		=> Mage::helper("zolagocommon")->getDesignFileByStore(
					$this->getLogoFile(), $this->getStoreId()),
				"id"			=> "logo.png",
				"disposition"	=> "inline"
			);
		}
		return parent::setTemplateParams($templateParams);
	}

	/**
	 * @return string
	 */
	public function getLogoFile() {
		return "images/logo_email.png";
	}
	
    /**
	 * Override email template supported attachments
	 * 
     * Send all emails from email list
     * @see self::$_emailInfos
     *
     * @return Mage_Core_Model_Email_Template_Mailer
     */
    public function send()
    {
        /** @var Zolago_Common_Model_Core_Email_Template $emailTemplate */
        $emailTemplate = Mage::getModel('zolagocommon/core_email_template');
        // Send all emails from corresponding list
        while (!empty($this->_emailInfos)) {
            /** @var Mage_Core_Model_Email_Info $emailInfo */
            $emailInfo = array_pop($this->_emailInfos);
            // Handle "Bcc" recepients of the current email
            $emailTemplate->addBcc($emailInfo->getBccEmails());
            $emailTemplate->setReplyTo($emailinfo->getReplyTo());
            // Set required design parameters and delegate email sending to Mage_Core_Model_Email_Template
            $emailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $this->getStoreId()))
                ->sendTransactional(
                $this->getTemplateId(),
                $this->getSender(),
                $emailInfo->getToEmails(),
                $emailInfo->getToNames(),
                $this->getTemplateParams(),
                $this->getStoreId()
            );
        }
        return $this;
    }
}
