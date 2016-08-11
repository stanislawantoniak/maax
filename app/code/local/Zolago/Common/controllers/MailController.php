<?php
class Zolago_Common_MailController extends Mage_Core_Controller_Front_Action{
	
	public function sendAction() {
		$data = $this->getRequest()->getParams();
		try {
			if (isset($data['email']) && isset($data['template'])) {
				if (!isset($data['store'])) {
					$store = Mage::app()->getDefaultStoreView();
				} else {
					$store = Mage::app()->getStore($data['store']);
				}

				if(!$store->getId()) {
					Mage::throwException("Invalid store code");
				}

				$senderEmail = Mage::getStoreConfig('trans_email/ident_support/email',$store);
				$senderName = Mage::getStoreConfig('trans_email/ident_support/name',$store);

				if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
					$email = $data['email'];
				} else {
					Mage::throwException('Invalid recipient email');
				}

				if(!filter_var($senderEmail, FILTER_VALIDATE_EMAIL) || !$senderName) {
					Mage::throwException("Invalid sender data");
				} else {
					$sender = array('name' => $senderName, 'email' => $senderEmail);
				}

				if(is_numeric($data['template'])) {
					$emailTemplate = Mage::getModel('core/email_template')
						->load($data['template']);
					if($emailTemplate->getId() == $data['template']) {
						$emailTemplateId = $data['template'];
					} else {
						Mage::throwException("Invalid template provided");
					}
				} else {
					Mage::throwException("Invalid template provided");
				}

				$templateData = array(
					'custom_subject' => (isset($data['subject']) && $data['subject'] ? $data['subject'] : false)
				);


				/** @var Zolago_Common_Helper_Data $helper */
				$helper = Mage::helper("zolagocommon");
				$result = $helper->sendEmailTemplate($email,"",$emailTemplateId,$templateData,$store->getId(),$sender);
				if(!$result) {
					Mage::throwException("Email could not be sent");
				}
			} else {
				Mage::throwException("Incorrect data provided");
			}
		} catch(Exception $e) {
			Mage::logException($e);
			echo 'ERR'.(isset($data['debug']) ? ' ('.$e->getMessage().')' : '');
			return;
		}
		echo 'OK';
	}
}