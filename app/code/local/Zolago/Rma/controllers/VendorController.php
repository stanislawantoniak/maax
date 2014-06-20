<?php

require_once Mage::getModuleDir('controllers', 'Unirgy_Rma') . "/VendorController.php";

/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Rma_VendorController extends Unirgy_Rma_VendorController
{
	/**
	 * Display edit form
	 * @return null
	 */
	public function editAction() {
		$render = false;
		try{
			$this->_registerRma();
			$render = true;
		}catch(Mage_Core_Exception $e){
			$this->_getSession()->addError($e->getMessage());
		}catch(Exception $e){
			Mage::logException($e);
			$this->_getSession()->addError(Mage::helper("zolagorma")->__("Other error. Check logs."));
		}
		
		if($render){
			return $this->_renderPage(null, 'urma');
		}
		
		return $this->_redirect("*/*");
	}
	
	/**
	 * Add comment
	 * @return null
	 */
	public function commentAction() {
		
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		/* @var $connection Varien_Db_Adapter_Interface */
		$connection->beginTransaction();
		
		$session = $this->_getSession();
		
		try{
			$rma = $this->_registerRma();
			$request = $this->getRequest();
			
			$comment = trim($request->getParam("comment", ''));
			$status = $request->getParam("status");
			$notify = $request->getParam("notify_customer", 0);
			
			$messages = array();
			
			// Process status
			if($status!=$rma->getRmaStatus()){
				if(!$this->_isValidTrackingStatus($status)){
					throw new Mage_Core_Exception(Mage::helper("zolagorma")->__("Status code %s is not valid.", $status));
				}
				$rma->setRmaStatus($status);
				$rma->getResource()->saveAttribute($rma, 'rma_status');
				$messages[] = Mage::helper("zolagorma")->__("Status changed");
			}
			
			// Process comment
			if($comment){
				$vendorId = $session->getVendorId();
				$operatorId = $session->getOperatorId();
				
				$data = array(
					"parent_id"				=> $rma->getId(), 
					"is_customer_notified"	=> $notify,
					"is_visible_on_front"	=> $notify,
					"comment"				=> $comment,
					"created_at"			=> Varien_Date::now(),
					"is_vendor_notified"	=> 1,
					"is_visible_to_vendor"	=> 1,
					"udropship_status"		=> null,
					"username"				=> null,
					"rma_status"			=> $rma->getUdropshipStatus(),
					"customer_id"			=> null,
					"operator_id"			=> $operatorId,
					"vendor_id"				=> $vendorId,
					"author_name"			=> null

				);
				
				$model = Mage::getModel("urma/rma_comment")->
					setRma($rma)->
					addData($data)->
					save();
				
				/* @var $model Unirgy_Rma_Model_Rma_Comment */
				
				$messages[] = Mage::helper("zolagorma")->__("Comment added");
			}
			
			$connection->commit();
			
			if($messages){
				if($notify){
					$messages[] =  Mage::helper("zolagorma")->__("Customer notified by email");
				}
				// Process flash msg
				foreach($messages as $message){
					$this->_getSession()->addSuccess($message);
				}
				// Send mail if needed
				$emailComment = $comment ? $session->getVendor()->getVendorName().': '.$comment : "";
				$rma->sendUpdateEmail($notify, $emailComment);
				
			}else{
				$this->_getSession()->addSuccess(Mage::helper("zolagorma")->__("No changes (empty comment and same status)"));
			}
		}catch(Mage_Core_Exception $e){
			$connection->rollBack();
			$this->_getSession()->addError($e->getMessage());
		}catch(Exception $e){
			$connection->rollBack();
			Mage::logException($e);
			$this->_getSession()->addError(Mage::helper("zolagorma")->__("Other error. Check logs."));
		}
		
		return $this->_redirectReferer();
	}
	
	/**
	 * Save tracking number
	 * @return type
	 */
	public function saveShippingAction() {
		try{
			$rma = $this->_registerRma();
			$items = $rma->getItemsCollection();
			
			$request = $this->getRequest();

			$trackingNumber = $request->getParam("tracking_id");
			$carrier = $request->getParam('carrier');
            $carrierTitle = $request->getParam('carrier_title');
		
			$calculedQty = $items->count();
			$finalPrice = null; // Calculate?
			$addressText = Mage::helper('udropship')->formatCustomerAddress(
					$rma->getShippingAddress(), 'text', $rma->getVendor());
			$addressText = preg_replace("/\n+/m", "\n", $addressText);
			
			$width = (float)$request->getParam('width');
			$height = (float)$request->getParam('height');
			$length = (float)$request->getParam('length');
			
			$autoTracking = false;
			// Override by dhl
			switch($carrier){
				case "custom":
					// N.O.
				break;
				case "ups":
					// N.O.
				break;
				case Zolago_Dhl_Model_Carrier::CODE:
					$width = (float)$request->getParam('specify_zolagodhl_width');
					$height = (float)$request->getParam('specify_zolagodhl_height');
					$length = (float)$request->getParam('specify_zolagodhl_length');
					$autoTracking = true;
				break;
				default:
					throw new Mage_Core_Exception(Mage::helper("zolagorma")->__("Unknown carrier"));
				break;
			}
			
			if($autoTracking){
				// @todo Implement
				// Process sippment & tracking. 
				// If not crated throw exception
				$trackingNumber = rand(10000,1000000); //DEV
			}
			
			
			$trackData = array(
				"parent_id"				=>  $rma->getId(),
				"weight"				=> (float)$request->getParam('weight'),
				"qty"					=> $calculedQty,
				"order_id"				=> $rma->getOrder()->getId(),
				"track_number" 			=> $trackingNumber,
				"description" 			=> $addressText, 
				"title"					=> $carrierTitle,
				"carrier_code"			=> $carrier,
				"created_at" 			=> Varien_Date::now(),
				"updated_at"			=> Varien_Date::now(),
				"batch_id"				=> null,
				"label_image"			=> null,
				"label_format" 			=> null,
				"label_pic"				=> null,
				"final_price"			=> $finalPrice,
				"value"					=> null, // what is this ?
				"length"				=> $length,
				"width"					=> $width,
				"height"				=> $height,
				"result_extra"			=> null, // what is this ?
				"pkg_num"				=> 1,
				"int_label_image"		=> null, // what is this ?
				"label_render_options"	=> null, // what is this ?
				"udropship_status"		=> Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING,
				"next_check"			=> null, // what is this ?
				"master_tracking_id"	=> null, // what is this ?
				"package_count"			=> null, // what is this ?
				"package_idx"			=> null, // what is this ?
			);
			
			$model = Mage::getModel('urma/rma_track')->
					addData($trackData)->
					save();
			
			$this->_getSession()->addSuccess(Mage::helper("zolagorma")->__("Shipping label added."));
		}catch(Mage_Core_Exception $e){
			$this->_getSession()->addError($e->getMessage());
		}catch(Exception $e){
			Mage::logException($e);
			$this->_getSession()->addError(Mage::helper("zolagorma")->__("Other error. Check logs."));
		}
		
		return $this->_redirectReferer();
	}
	
	/**
	 * Save address obejct
	 * @retur null
	 */
	public function saveAddressAction(){
		$req	=	$this->getRequest();
		$data	=	$req->getPost();
		$type	=	$req->getParam("type");
		
		$session = $this->_getSession();
		/* @var $session Zolago_Dropship_Model_Session */
		
		
		try{
			$rma = $this->_registerRma();
			
			if(isset($data['restore']) && $data['restore']==1){
				if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
					$rma->clearOwnShippingAddress();
				}else{
					$rma->clearOwnBillingAddress();
				}
				
				Mage::dispatchEvent("zolagorma_rma_address_restore", array(
					"rma"		=> $rma, 
					"type"		=> $type
				));
				
				$rma->save();
				$session->addSuccess(Mage::helper("zolagorma")->__("Address restored"));
				$response['content']['reload']=1;
			}elseif(isset($data['add_own']) && $data['add_own']==1){
				if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
					$orignAddress = $rma->getOrder()->getShippingAddress();
					$oldAddress = $rma->getShippingAddress();
				}else{
					$orignAddress = $rma->getOrder()->getBillingAddress();
					$oldAddress = $rma->getBillingAddress();
				}
				$newAddress = clone $orignAddress;
				$newAddress->addData($data);
				if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
					$rma->setOwnShippingAddress($newAddress);
				}else{
					$rma->setOwnBillingAddress($newAddress);
				}
				
				
				Mage::dispatchEvent("zolagorma_rma_address_change", array(
					"rma"			=> $rma, 
					"new_address"	=> $newAddress, 
					"old_address"	=> $oldAddress, 
					"type"			=> $type
				));
				
				$rma->save();
				
				$session->addSuccess(Mage::helper("zolagorma")->__("Address changed"));
			}
		}catch(Mage_Core_Exception $e){
			$session->addError($e->getMessage());
		}catch(Exception $e){
			Mage::logException($e);
			$session->addError(Mage::helper("zolagorma")->__("Some errors occure. Check logs."));
		}
		
		return $this->_redirectReferer();
	}
 
	/**
	 * @return Zolago_Rma_Model_Rma
	 * @throws Mage_Core_Exception
	 */
	protected function _registerRma() {
		if(!Mage::registry('current_rma')){
			$rma = Mage::getModel("urma/rma");
			if($this->getRequest()->getParam('id')){
				$rma->load($this->getRequest()->getParam('id'));
			}
			if(!$this->_validateRma($rma)){
				throw new Mage_Core_Exception(Mage::helper('zolagorma')->__('Rma not found'));
			}
			Mage::register('current_rma', $rma);
		}
		return Mage::registry('current_rma');
	}
	
	/**
	 * @return boolean
	 */
	protected function _validateRma(Zolago_Rma_Model_Rma $rma) {
		if(!$rma->getId()){
			return false;
		}
		if($rma->getVendor()->getId() != $this->_getSession()->getVendorId()){
			return false;
		}
		return true;
	}
	
	/**
	 * @param string $status
	 * @return bool
	 */
	public function _isValidTrackingStatus($status) {
		return array_key_exists($status, Mage::helper('zolagorma')->getVendorRmaStatuses());
	}
}
