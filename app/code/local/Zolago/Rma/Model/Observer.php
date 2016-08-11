<?php
class Zolago_Rma_Model_Observer extends Zolago_Common_Model_Log_Abstract
{
	/**
	 * RMA Created
	 * @param type $observer
	 */
	public function rmaCreated($observer) {
		$rma = $observer->getEvent()->getData('rma');
		$author = null;
		$customerSession = Mage::getSingleton('customer/session');
		if($customerSession->isLoggedIn() && $customerSession->getCustomer()->getId()){
			$author = $customerSession->getCustomer();
		}
		
		
		/* @var $rma Zolago_Rma_Model_Rma */
		$this->_logEvent(
				$rma,
                $rma->getData('comment_text'),
				true,
				$author
		);
	}

    /**
     * RMA Customer Send Detail
     * @param type $observer
     */
    public function rmaCustomerSendDetail($rma, $comment, $sendEmail=null, Mage_Customer_Model_Customer $author) {
        /* @var $rma Zolago_Rma_Model_Rma */
        $this->_logEvent(
            $rma,
            $comment,
            $sendEmail,
            $author
        );
    }

	/**
	 * RMA track status chnge
	 * @param type $observer
	 */
	public function rmaTrackStatusChange($observer) {
		$rma   = $observer->getEvent()->getData('rma');
		/* @var $rma Zolago_Rma_Model_Rma */
		$track = $observer->getEvent()->getData('track');
		/* @var $rma Zolago_Rma_Model_Rma_Track */
		$notify = $observer->getEvent()->getData('notify');
		$newStatus = $observer->getEvent()->getData("new_status");
		$oldStatus = $observer->getEvent()->getData("old_status");		
		$this->_logEvent(
			$rma, 
			Mage::helper('zolagorma')->__(
				"Tracking %s status changed (%s&rarr;%s)", 
				$track->getTrackNumber(),
				$oldStatus,
				$newStatus),
			$notify 
		);
	}
	
	
	/**
	 * RMA comment created
	 * @param type $observer
	 */
	public function rmaCommentAdded($observer) {
		$rma = $observer->getEvent()->getData('rma');
		/* @var $rma Zolago_Rma_Model_Rma */
		$notify = $observer->getEvent()->getData('notify');
		$comment = $observer->getEvent()->getData('comment');
		/* @var $comment Zolago_Rma_Model_Rma_Comment */
		$this->_logEvent($rma, $comment, $notify);
	}
	
	
	/**
	 * RMA track created
	 * @param type $observer
	 */
	public function rmaTrackAdded($observer) {
		$rma = $observer->getEvent()->getData('rma');
		/* @var $rma Zolago_Rma_Model_Rma */
		$track = $observer->getEvent()->getData('track');
		$time = Mage::getSingleton('core/date')->timestamp();
		/* @var $track Zolago_Rma_Model_Rma_Track */
		$allowCarriers = Mage::helper('orbashipping/carrier_tracking')->getTrackingCarriersList();
		$carrierCode = $track->getCarrierCode () ;
		if (in_array($carrierCode,$allowCarriers) 
			&& Mage::getSingleton('shipping/config')->getCarrierInstance($carrierCode)->isTrackingAvailable()
			&& !$track->getWebApi()) {
				$track->setNextCheck(date('Y-m-d H:i:s', $time));
				$track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_PENDING);
				$track->save();
		    
		}
		$type = Mage::helper('zolagorma')->__(
				$track->getTrackCreator()==Zolago_Rma_Model_Rma_Track::CREATOR_TYPE_CUSTOMER ? "Customer" : "Vendor");
		
		$this->_logEvent($rma, 
				Mage::helper('zolagorma')->__("Tracking added (Creator: %s, Carrier: %s, Number: %s)", 
						$type, $track->getTitle(), $track->getTrackNumber()),
				false
		);
	}
	
	/**
	 * RMA Status change
	 * @param type $observer
	 */
	public function rmaStatusChanged($observer) {
		$rma = $observer->getEvent()->getData('rma');
		$newStatus = $observer->getEvent()->getData("new_status");
		$oldStatus = $observer->getEvent()->getData("old_status");
		$notify = $observer->getEvent()->getData("notify");

		// do not send emails with statuses for rma 
		if ($rma->getRmaType() == Zolago_Rma_Model_Rma::RMA_TYPE_RETURN) {
		    $notify = false;
		}

		$helper = Mage::helper("zolagorma");
		/* @var $rma Zolago_Rma_Model_Rma */
		$statusModel = $rma->getStatusModel();

        //Calculate response deadline
        if($response_deadline = Mage::helper('zolagoholidays/datecalculator')->calculateMaxRmaResponseDeadline($rma, $statusModel->getStatusObject($newStatus), true)){
            try{
                $rma->setResponseDeadline($response_deadline->toString('YYYY-MM-dd'));
                $rma->save();
            }
            catch(Exception $e){
                Mage::logException($e);
            }
        }
		
		$statusObject = $statusModel->getStatusObject($newStatus);
		
		$this->_logEvent(
			$rma, 
			Mage::helper('zolagorma')->__(
				"{{author_name}} changed status of this claim. New status: %s",
				$helper->__($statusObject->getCustomerNotes() ? 
						$statusObject->getCustomerNotes() : $statusObject->getTitle())),
			$notify
		);

		if ($rma->getRmaType() == Zolago_Rma_Model_Rma::RMA_TYPE_RETURN) {
		    $po = $rma->getPo();
		    $oldStatus = $po->getUdropshipStatus();

		    if ($oldStatus != Zolago_Po_Model_Po_Status::STATUS_RETURNED) {

		        $po->setUdropshipStatus(Zolago_Po_Model_Po_Status::STATUS_RETURNED);

		        $helper = Mage::helper('udpo');
                $_comment = $helper->__("[PO status changed from '%s' to '%s']",
                            $helper->getPoStatusName($oldStatus),
                            $helper->getPoStatusName(Zolago_Po_Model_Po_Status::STATUS_RETURNED)
                            );
		        $po->save();
		        $po->addComment($_comment,false,true);
		        $po->saveComments();

		    }
		}
		/*$this->_logEvent(
			$rma, 
			Mage::helper('zolagorma')->__("Status changed (%s&rarr;%s)", 
				$helper->__($statusModel->getStatusObject($oldStatus)->getTitle()), 
				$helper->__($statusModel->getStatusObject($newStatus)->getTitle())),
			$notify
		);*/
	}
	
	
	/**
	 * RMA Address Changed
	 * @param type $observer
	 */
	public function rmaAddressRestore($observer) {
		$rma = $observer->getEvent()->getData('rma');
		/* @var $rma Zolago_Rma_Model_Rma */
		$type = $observer->getEvent()->getData('type');
		if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
			$text = Mage::helper('zolagorma')->__("Origin shipping address restored");
		}else{
			$text = Mage::helper('zolagorma')->__("Origin billing address restored");
		}
		$this->_logEvent($rma, $text, false);
	}
	
	/**
	 * RMA Address Changed
	 * @param type $observer
	 */
	public function rmaAddressChange($observer) {
		$rma = $observer->getEvent()->getData('rma');
		/* @var $rma Zolago_Rma_Model_Rma */
		
		$newAddress = $observer->getEvent()->getData('new_address');
		/* @var $newAddress Mage_Sales_Model_Order_Address */
		$oldAddress = $observer->getEvent()->getData('old_address');
		/* @var $oldAddress Mage_Sales_Model_Order_Address */
		
		$type =  $observer->getEvent()->getData('type');
		
		$hlp = Mage::helper("zolagorma");
		
		$keysToCheck = array(
			"postcode"		=> $hlp->__("Postcode"),
			"lastname"		=> $hlp->__("Lastname"),
			"firstname"		=> $hlp->__("Firstname"),
			"street"		=> $hlp->__("Street"),
			"city"			=> $hlp->__("City"),
			"email"			=> $hlp->__("Email"),
			"telephone"		=> $hlp->__("Phone"),
			"country_id"	=> $hlp->__("Country"),
			"vat_id"		=> $hlp->__("NIP"),
			"company"		=> $hlp->__("Company"),
			"need_invoice"	=> $hlp->__("Invoice")	
		);
		
		$changeLog = $this->_prepareChangeLog($keysToCheck, $oldAddress, $newAddress);
		
		if($changeLog){
			if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
				$text = $hlp->__("Shipping address changed (%s)");
			}else{
				$text = $hlp->__("Billing address changed (%s)");
			}
			$text = Mage::helper('zolagorma')->__($text, implode(", " , $changeLog));
			$this->_logEvent($rma, $text, false);
		}
	}

	public function rmaCreatedManually($observer){
		$session = Mage::getSingleton('udropship/session');
		$rma = $observer->getEvent()->getData('rma');
		$vendor = $rma->getVendor();
		$operator = $rma->getOperator();
		if($session->isOperatorMode()){
			$author = $operator;
		}else{
			$author = $vendor;
		}
		$text = Mage::helper('zolagorma')->__("RMA created successfully");
		$this->_logEvent($rma, $text, false, $author);
	}
	
	/**
	 * @param Zolago_Rma_Model_Rma $rma
	 * @param Zolago_Rma_Model_Rma_Comment|string $comment 
	 * @parma bool $sendEmail
	 * @param string mixed|null
	 */
	protected function _logEvent($rma, $comment, $sendEmail=null, $author=null) {
		$notifyByStatus = (bool)$rma->getStatusObject()->getNotifyCustomer();
		
		$data  = array(
			"parent_id" => $rma->getId()
		);
		
		/* @var $rma Zolago_Rma_Model_Rma */
		
		// Identity author
		if(is_null($author)){
			$custSession = Mage::getSingleton('customer/session');
			/* @var $custSession Mage_Customer_Model_Session */
			$vendorSession = Mage::getSingleton('udropship/session');
			/* @var $vendorSession  Zolago_Dropship_Model_Session*/
			if($vendorSession->getVendorId()){
				$data['vendor_id'] = $vendorSession->getVendorId();
				if($vendorSession->isOperatorMode()){
					$data['operator_id'] = $vendorSession->getOperator()->getId();
				}
			}elseif($custSession->getCustomerId()){
				$data['customer_id'] = $custSession->getCustomerId();
			}
		}elseif($author instanceof ZolagoOs_OmniChannel_Model_Vendor){
			$data['vendor_id'] = $author->getId();
		}elseif($author instanceof Zolago_Operator_Model_Operator){
			$data['vendor_id'] = $author->getVendor()->getId();
			$data['operator_id'] = $author->getId();
		}elseif($author instanceof Mage_Customer_Model_Customer){
			$data['customer_id'] = $author->getId();
		}
		
		
		// default - author id system user
		
		if($comment instanceof Zolago_Rma_Model_Rma_Comment){
			$commentModel = $comment;
		}else{
			$data['comment'] = $comment;
			$commentModel = Mage::getModel("zolagorma/rma_comment");
		}

		$doSendEmail = $notifyByStatus;
		
		if($sendEmail===true || $sendEmail===false){
			$doSendEmail = $sendEmail;
		}
		
		// Set visiblity on front always if author is cutomer
		// Or when customer was notified
		if($author instanceof Mage_Customer_Model_Customer || $doSendEmail){
			$data['is_customer_notified'] = 1;
			$data['is_visible_on_front'] = 1;
		}
		
		/* @var $commentModel Zolago_Rma_Model_Rma_Comment */
		$commentModel->setRma($rma);
		$commentModel->addData($data);
		$commentModel->setAuthorName($commentModel->getAuthorName(false));
		$commentModel->save();
		
		// Send email
		if($doSendEmail){
			if($rma->getIsNewFlag()){
				$rma->sendEmail(true, $commentModel);
				Mage::helper('urma')->sendNewRmaNotificationEmail($rma, $commentModel);
				$rma->setIsNewFlag(false);
			}else{
				$rma->sendUpdateEmail(true, $commentModel);
			}
		}
	}

	/**
     * Add tab to vendor view
     *
     * @param $observer Varien_Event_Observer
	 * 
     * @return Zolago_Rma_Model_Observer
     */
	public function udropship_adminhtml_vendor_tabs_after($observer)
    {
        $block = $observer->getBlock();
        $block->addTab('return_reasons', array(
            'label'     => Mage::helper('zolagorma')->__('Return Reasons'),
            'content'   => Mage::app()->getLayout()->createBlock('zolagorma/adminhtml_dropship_edit_tab_returnreasons', 'vendor.returnreasons.form')
                ->toHtml()
        ));
        $block->addTabToSection('return_reasons','logistic',60);
    }
	
	/**
     * Save return reason for specific vendor
     *
     * @param $observer Varien_Event_Observer
	 * 
     * @return Zolago_Rma_Model_Observer
     */
	public function udropship_vendor_save_after($observer)
	{
		$front_controller = Mage::app()->getFrontController();
		
		if($front_controller->getRequest()->isPost()){
			
			$params = $front_controller->getRequest()->getParams();
			
			if(key_exists('return_reasons', $params)){
				
				$vendor_return_reasons = $params['return_reasons'];
				$mode = $params['submit_mode'];
				$vendor = $observer->getVendor();
				
				if(sizeof($vendor_return_reasons) > 0){
					
					foreach($vendor_return_reasons as $model_id => $data){
						
						$vendor_return_reason = Mage::getModel('zolagorma/rma_reason_vendor');
						
						if(!isset($data['use_default'])) $data['use_default'] = 0;
						
						// Edit mode
						if($mode == 'edit'){
							$vendor_return_reason = $vendor_return_reason->load($model_id);
							$vendor_return_reason->addData($data);
						}
						else{
							$data['vendor_id'] = (int) $vendor->getVendorId();
							$data['return_reason_id'] = (int) $model_id;
							$vendor_return_reason->setData($data);
						}
						
						try{
							$vendor_return_reason->save();
						}catch(Mage_Core_Exception $e){
				            Mage::logException($e);
				        }catch(Exception $e){
				            Mage::logException($e);
				        }
					}
				}
			}
		}
		
		return $this;
	}
	
	/**
     * Add Return Reason to all vendors
     *
     * @param $observer Varien_Event_Observer
	 * 
     * @return Zolago_Rma_Model_Observer
     */	
	public function zolagorma_global_return_reson_save_after(Varien_Event_Observer $observer)
	{
		$helper = Mage::helper('zolagorma');
		
		$return_reason = $observer->getModel();
		
		$all_vendors = Mage::getModel('udropship/vendor')->getCollection();
		$vendor_resource_resource = Mage::getResourceModel('zolagorma/rma_reason_vendor');
		/* @var $vendor_resons_collection Zolago_Rma_Model_Resource_Rma_Reason_Vendor */
		
		// Fix - adding filter to vendor withoout reson object
		$vendor_resource_resource->
				addUnbindRmaReasonFilterToVendorCollection($return_reason, $all_vendors);
		
		$vendors_count = $all_vendors->count();
		$ok_saved = 0;
		
		if($vendors_count > 0){
			
			// Add records for each vendor
			foreach($all_vendors as $vendor){
				
				try{
					$vendor_return_reason = Mage::getModel('zolagorma/rma_reason_vendor');
					
					$data = array(
						'return_reason_id' => $return_reason->getReturnReasonId(),
	                	'vendor_id' => $vendor->getVendorId(),
	                	'auto_days' => $return_reason->getAutoDays(),
	              		'allowed_days' => $return_reason->getAllowedDays(),
	                	'message' => $return_reason->getMessage()
					);
					
					$vendor_return_reason->setData($data);
					$vendor_return_reason->save();
					
					$ok_saved++;
				}catch(Mage_Core_Exception $e){
		            Mage::logException($e);
		        }catch(Exception $e){
		            Mage::logException($e);
		        }
				
			}
			
			$error_saved = $vendors_count - $ok_saved;
			
			if($ok_saved > 0){
				Mage::getSingleton('core/session')->addSuccess($helper->__("Records saved successfuly for {$ok_saved} vendors."));
			}
			if($error_saved > 0){
				Mage::getSingleton('core/session')->addError($helper->__("Records did not save successfuly for {$error_saved} vendors."));
			}
		}
		
		return $this;
	}
	
    /**
     * auto tracking rma
     */
    public function cronRmaTracking() {
        $helper = Mage::helper('zolagorma');
        $helper->rmaTracking();
    }
    
    /**
     * create rma for undelivered shipments
     *
     * @params Varien_Event_Observer $observer
     */
     public function createReturnRma(Varien_Event_Observer $observer) {
         $reason = Mage::getStoreConfig('urma/general/zolagorma_reason_for_returned_shipment');
         if (!$reason) {
             return;
         }
         $shipment = $observer->getEvent()->getData('shipment');
         $poId = $shipment->getUdpoId();
         $po = Mage::getModel('zolagopo/po')->load($poId);
         $items = $po->getItemsCollection();
         $list = Mage::helper('zolagorma')->getItemList($items);
         $out = array();
         foreach ($list as $pack) {
             foreach ($pack as $id=>$item) {
                 $out['items_condition_single'][$item['entityId']][$id] = $reason;
                 $out['items_single'][$item['entityId']][$id] = true;
             }
         }
         $rmas = Mage::getModel('zolagorma/servicePo', $po)->prepareRmaForSave($out);
         foreach ($rmas as $rma) {
             $rma->setRmaType(Zolago_Rma_Model_Rma::RMA_TYPE_RETURN);
             $rma->save();
         }
     }

}
