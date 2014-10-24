<?php

class Zolago_Rma_RmaController extends Mage_Core_Controller_Front_Action
{
	protected $_msgStores = array('catalog/session', 'customer/session', 'core/session', 'udqa/session');
	/**
	 * History action
	 * @return boolean
	 */
	public function historyAction() {
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
		$helperRma = Mage::helper('zolagorma');
		$helperTrack = Mage::helper('zolagorma/tracking');
		$helperDhl = Mage::helper('zolagodhl');
		
		try{
			$rma = $this->_initRma();
			$track = $helperTrack->getRmaTrackingForCustomer($rma, $customer);
			if($track && $track->getId()){
				$dhlFile = $helperDhl->getRmaDocument($track);
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
		if(!Mage::registry("current_rma")){
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
                if($rma->getId() && $rma->getCustomerId()==Mage::getSingleton('customer/session')->getCustomerId()){

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
                        $ob->rmaCustomerSendDetail($rma, $comment, null, $author);


                        //After add new customer-author comment set RMA flag new customer comment to true

                        $rma->setNewCustomerQuestion(1);
                        try {
                            $rma->save();
                            $session->addSuccess(Mage::helper("zolagorma")->__("Your message sent"));
                            return $this->_redirect('sales/rma/view', array("id" => $rmaId));
                        } catch (Exception $e) {
                            $session->addError($this->__('Unable to post the question.'));
                            return $this->_redirect('sales/rma/view', array("id" => $rmaId));
                        }

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
	 * @return Unirgy_Rma_Model_Rma
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
				/* @var $collection Unirgy_Rma_Model_Mysql4_Rma_Collection */
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