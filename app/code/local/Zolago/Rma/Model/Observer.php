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
			$type = Mage::helper('zolagorma')->__("shipping");
		}else{
			$type = Mage::helper('zolagorma')->__("billing");
		}

		$text = Mage::helper('zolagorma')->__("Origin %s address restored", $type);
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
				$type = $hlp->__("Shipping");
			}else{
				$type = $hlp->__("Billing");
			}
			$text = Mage::helper('zolagorma')->__("%s address changed (%s)", $type, implode(", " , $changeLog));
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
}
