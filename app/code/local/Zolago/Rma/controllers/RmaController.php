<?php

class Zolago_Rma_RmaController extends Mage_Core_Controller_Front_Action
{
	protected $_msgStores = array('catalog/session', 'customer/session', 'core/session', 'udqa/session');
	/**
	 * History action
	 * @return boolean
	 */
	public function historyAction() {
		$activeNoRma = Mage::helper('zolagocommon')->isModuleActive('ZolagoOs_NoRma');
		if($activeNoRma){
			return $this->_redirect('sales/order/process');
		}
		$session = Mage::getSingleton('customer/session');
		/* @var $session Mage_Customer_Model_Session */
		if(!$session->isLoggedIn()){
			return $this->_redirect('customer/account/login');
		}
        $this->loadLayout();
        $this->_initLayoutMessages($this->_msgStores);
		$this->_setNavigation();
        $this->renderLayout();
	}
	
	/**
	 * download pdf action
	 * @todo imlement
	 * @return boolean
	 */
	 public function pdfAction() {
		$session = Mage::getSingleton('customer/session');
		if(!$session->isLoggedIn()){
			return $this->_redirect('customer/account/login');
		}
		
		$customer = $session->getCustomer();
		 /** @var Zolago_Rma_Helper_Data $helperRma */
		$helperRma = Mage::helper('zolagorma');
		$helperTrack = Mage::helper('zolagorma/tracking');
		$helperDhl = Mage::helper('orbashipping/carrier_dhl');
		
		try{
			$rma = $this->_initRma();
			if ($rma->getRmaStatus() !== Zolago_Rma_Model_Rma_Status::STATUS_PENDING_PICKUP) {
			    Mage::throwException($helperRma->__("Wrong RMA status"));
			}
			$track = $helperTrack->getRmaTrackingForCustomer($rma, $customer);
			if($track && $track->getId()){
				$dhlFile = $helperRma->getRmaDocumentForCustomer($track);
				if(!file_exists($dhlFile)){
					Mage::throwException($helperRma->__("No RMA document"));
				}
				$ioAdapter = new Varien_Io_File();
				return $this->_prepareDownloadResponse(
					basename($dhlFile), 
					@$ioAdapter->read($dhlFile), 
					'application/pdf'
				);
			}else{
				throw new Exception($helperRma->__("No RMA tracking"));
			}
		}catch (Mage_Core_Exception $e){
			$session->addError($e->getMessage());
			return $this->_redirectReferer();
		}catch (Exception $e){
			$session->addError($helperRma->__("An error occured"));
			Mage::logException($e);
			return $this->_redirectReferer();
		}
    }

	 /**
	  * View action
	  * @return type
	  */
	public function viewAction() {
		$session = Mage::getSingleton('customer/session');
		/* @var $session Mage_Customer_Model_Session */
		if(!$session->isLoggedIn()){
			return $this->_redirect('customer/account/login');
		}
		// Current RMA can by set and forwarded by _initLastRma
		$rma = Mage::registry("current_rma");
		if(!$rma || !$rma->getId()){
			try{
				$rma =$this->_initRma();
				/* @var $rma Zolago_Rma_Model_Rma */
			
			}  catch (Mage_Core_Exception $e){
				$session->addError($e->getMessage());
				return $this->_redirect('sales/rma/history');
			}  catch (Exception $e){
				$session->addError(Mage::helper("zolagorma")->__("An error occured"));
				return $this->_redirect('sales/rma/history');
			}
		}
		$this->loadLayout();
        $this->_initLayoutMessages($this->_msgStores);
		$this->_setNavigation();
		$this->renderLayout();
	}

    public function courierAction()
    {

        $session = Mage::getSingleton('customer/session');
        /* @var $session Mage_Customer_Model_Session */
        if (!$session->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }
        try {
            $rma = $this->_initRma();
            /* @var $rma Zolago_Rma_Model_Rma */

            if ($rma->getRmaStatus() !== Zolago_Rma_Model_Rma_Status::STATUS_PENDING_COURIER) {
                $this->_redirect('sales/rma/view', array('id' => $this->getRequest()->getParam('id')));
            }

        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
            return $this->_redirect('sales/rma/history');
        } catch (Exception $e) {
            $session->addError(Mage::helper("zolagorma")->__("An error occurred"));
            return $this->_redirect('sales/rma/history');
        }
        $this->loadLayout();
        $this->_initLayoutMessages($this->_msgStores);
        $this->_setNavigation();
        $this->renderLayout();
    }

	/**
	 * Success action
	 * @return void
	 */
	public function successAction() {

		$session = Mage::getSingleton('customer/session');
		if(!$session->isLoggedIn()){
			$session->addError(Mage::helper("zolagorma")->__("You need to login"));
			return $this->_redirect('customer/account/login');
		}
		$this->_initLastRma();
		$this->_forward('view');
	}

    /**
     * Save courier data
     */
    public function saveCourierAction(){
        $this->_forward("saveRmaCourier",'po');
    }
    /**
     * Send Rma Detail Action
     * @return void
     */
    public function sendRmaDetailAction() {

        $session = Mage::getSingleton('customer/session');
        if (!$session->isLoggedIn()) {
            $session->addError(Mage::helper("zolagorma")->__("You need to login"));
            return $this->_redirect('customer/account/login');
        }

        $request = $this->getRequest();

        $commentText = trim($request->getParam("question_text", ""));
        $rmaId = (int)($request->getParam("rma_id", 0));
        if (!empty($commentText)) {
            try {
                $rma = Mage::getModel("urma/rma")->load($rmaId);
                if($rma->getId() && $rma->getCustomerId()== $session->getCustomerId()){

                    $customerId = $rma->getCustomerId();
                    $vendorId = $rma->getUdropshipVendor();

                    if ($customerId > 0) {
                        $author = Mage::getModel("customer/customer")->load($customerId);

                        //construct comment object
                        //comment
                        $notify = true;
                        $visibleOnFront = true;
                        $notifyVendor = false;
                        $visibleToVendor = true;
                        $comment = Mage::getModel('urma/rma_comment')
                            ->setComment($commentText)
                            ->setIsCustomerNotified($notify)
                            ->setIsVisibleOnFront($visibleOnFront)
                            ->setIsVendorNotified($notifyVendor)
                            ->setIsVendorId($vendorId)
                            ->setIsVisibleToVendor($visibleToVendor);
                        //comment

                        $ob = new Zolago_Rma_Model_Observer();
                        $ob->rmaCustomerSendDetail($rma, $comment, false, $author);


                        //After add new customer-author comment set RMA flag new customer comment to true
                        $rma->setNewCustomerQuestion(1);
                        $rma->save();
                        $session->addSuccess(Mage::helper("zolagorma")->__("Your message sent"));

						$store = $rma->getStore();

						/*Send Email to vendor agents of vendor*/
						$emailTemplateVariables = array();
						$emailTemplateVariables['vendor_name'] = $rma->getVendorName();
						$emailTemplateVariables['rma_increment_id'] = $rma->getIncrementId();
						$emailTemplateVariables['rma_url'] = Mage::getUrl("urma/vendor/edit", array("id" => $rma->getId()));
						$emailTemplateVariables['rma_status'] = $rma->getStatusLabel();
						$emailTemplateVariables['comment'] = nl2br($commentText);
						$emailTemplateVariables['store_name'] = $store->getName();
						$emailTemplateVariables['author'] = $author;


						$template = $store->getConfig('urma/general/zolagorma_comment_customer_email_template');
						$identity = $store->getConfig('udropship/vendor/vendor_email_identity');


						/* @var $helper Zolago_Common_Helper_Data */
						$helper = Mage::helper("zolagocommon");



						$vendorM = Mage::getResourceModel('udropship/vendor');
						$vendor = $rma->getVendor();
						$vendorAgents = $vendorM->getVendorAgentEmails($vendor->getId());
						if (!empty($vendorAgents)) {
							foreach ($vendorAgents as $email => $_) {
								$emailTemplateVariables['recepient'] = implode(' ', array($_['firstname'], $_['lastname']));
								$helper->sendEmailTemplate(
									$email,
									$vendor->getVendorName(),
									$template,
									$emailTemplateVariables,
									true,
									$identity
								);
							}
						}

						/*--Send Email to vendor agents of vendor*/


                        return $this->_redirect('sales/rma/view', array("id" => $rmaId));


                    }
                } else {
                    $session->addError($this->__('Unable to find RMA'));
                    return $this->_redirect('sales/rma/history');
                }


            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $session->addError(Mage::helper("zolagorma")->__("Other error. Check logs."));
            }
        }
        $session->addError($this->__('Unable to find a data to save'));
        return $this->_redirect('sales/rma/view', array("id" => $rmaId));

    }
	/**
	 * @param $customerName
	 * @param $customerEmail
	 * @param $template
	 * @param array $templateParams
	 * @param null $storeId
	 * @return Zolago_Common_Model_Core_Email_Template_Mailer
	 */
	protected function _sendEmailTemplate($customerName, $customerEmail,
										  $template, $templateParams = array(), $storeId = null)
	{
		$templateParams['use_attachments'] = true;

		$mailer = Mage::getModel('core/email_template_mailer');
		/* @var $mailer Zolago_Common_Model_Core_Email_Template_Mailer */
		$emailInfo = Mage::getModel('core/email_info');
		$emailInfo->addTo($customerEmail, $customerName);
		$mailer->addEmailInfo($emailInfo);

		// Set all required params and send emails
		$mailer->setSender(array(
			'name' => Mage::getStoreConfig('trans_email/ident_support/name', $storeId),
			'email' => Mage::getStoreConfig('trans_email/ident_support/email', $storeId)));
		$mailer->setStoreId($storeId);
		$mailer->setTemplateId(Mage::getStoreConfig($template, $storeId));
		$mailer->setTemplateParams($templateParams);

		return $mailer->send();
	}
	/**
	 * @param int $rmaId
	 * @return Zolago_Rma_Model_Rma
	 */
	protected function _initRma($rmaId=null) {
		if(is_null($rmaId)){
			$rmaId = $this->getRequest()->getParam("id");
		}
		$rma = Mage::getModel("urma/rma")->load($rmaId);
		if($rma->getId() && $rma->getCustomerId()==Mage::getSingleton('customer/session')->getCustomerId()){
				Mage::register("current_rma", $rma);
				return $rma;
		}
		Mage::throwException(Mage::helper("zolagorma")->__("RMA is not available"));
	}
	
	/**
	 * @return ZolagoOs_Rma_Model_Rma
	 */
	protected function _initLastRma() {
		if(!Mage::registry("current_rma")){
			$lastRmaId = Mage::getSingleton('core/session')->getLastRmaId();
			// Last id from session (set by PoController when created
			$item = Mage::getModel("urma/rma");
			if($lastRmaId){
				$item->load($lastRmaId);
			// If not use latest rma
			}else{
				$collection = Mage::getResourceModel('urma/rma_collection');
				/* @var $collection ZolagoOs_Rma_Model_Mysql4_Rma_Collection */
				$collection->addFieldToFilter("customer_id", Mage::getSingleton('customer/session')->getCustomerId());
				$collection->setOrder("created_at", "desc")->getSelect()->limit(1);

				if($collection->getFirstItem()){
					$item = $collection->getFirstItem();
				}
			}
			$item->setJustCreated(true);
			Mage::register("current_rma", $item);
		}
		return Mage::registry("current_rma");
	}
	
	/**
	 * Navigation helper
	 */
	protected function _setNavigation() {
		$navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/rma/history');
        }	
	}

}