<?php
class Zolago_Rma_Model_Observer extends Zolago_Common_Model_Log_Abstract
{
	/**
	 * RMA Created
	 * @param type $observer
	 */
	public function rmaCreated($observer) {
		$rma = $observer->getEvent()->getData('rma');
		/* @var $rma Zolago_Rma_Model_Rma */
		$this->_logEvent(
				$rma,
				Mage::helper('zolagorma')->__("New RMA created"), 
				true
		);
	}
	
	/**
	 * RMA track status chnge
	 * @param type $observer
	 */
	public function rmaTrackStatusChange($observer) {
		$rma = $observer->getEvent()->getData('rma');
		/* @var $rma Zolago_Rma_Model_Rma */
		$track = $observer->getEvent()->getData('track');
		/* @var $rma Zolago_Rma_Model_Rma_Track */
		
		$newStatus = $observer->getEvent()->getData("new_status");
		$oldStatus = $observer->getEvent()->getData("old_status");
		
		$this->_logEvent($rma, Mage::helper('zolagorma')->
			__("Tracking %s status changed (%s&rarr;%s)", 
					$track->getTrackNumber(),
					$oldStatus,
					$newStatus
			)
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
		/* @var $track Zolago_Rma_Model_Rma_Track */
		
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
		$helper = Mage::helper("zolagorma");
		/* @var $rma Zolago_Rma_Model_Rma */
		$statusModel = $rma->getStatusModel();
		$this->_logEvent($rma, Mage::helper('zolagorma')->
			__("Status changed (%s&rarr;%s)", 
					$helper->__($statusModel->getStatusObject($oldStatus)->getTitle()), 
					$helper->__($statusModel->getStatusObject($newStatus)->getTitle())
			)
		);
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
		}elseif($author instanceof Unirgy_Dropship_Model_Vendor){
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
		
		/* @var $commentModel Zolago_Rma_Model_Rma_Comment */
		$commentModel->setRma($rma);
		$commentModel->addData($data);
		$commentModel->setAuthorName($commentModel->getAuthorName(false));
		$commentModel->save();
		
		// Send email
		if($doSendEmail){
			if($rma->getIsNewFlag()){
				$rma->sendEmail(true, $rma->getCommentText());
				Mage::helper('urma')->sendNewRmaNotificationEmail($rma, $rma->getCommentText());
				$rma->setIsNewFlag(false);
			}else{
				$rma->sendUpdateEmail(true, $commentModel->getComment());
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
					
					$vendor_return_reason->updateModelData($data);
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
}
