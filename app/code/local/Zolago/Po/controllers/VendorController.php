<?php

class Zolago_Po_VendorController extends Zolago_Dropship_Controller_Vendor_Abstract {
	
	/**
	 * @return Zolago_Po_Model_Po
	 */
	protected function _registerPo() {
		if(!Mage::registry("current_po")){
			$poId = $this->getRequest()->getParam("id");
			$po = Mage::getModel("udpo/po")->load($poId);
			Mage::register("current_po", $po);
		}
		return Mage::registry("current_po");
	}
	
	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	protected function _getVendor() {
		return $this->_getSession()->getVendor();
	}
	
	public function indexAction() {
		// Override origin index
		Mage::register('as_frontend', true);// Tell block class to use regular URL's
		$this->_renderPage(array('default', 'formkey', 'adminhtml_head'), 'udpo');
	}
	
	public function editAction() {
		$this->_renderPage(null, 'udpo');
	}
	
	
	public function addCommentAction() {
		$_po = $this->_registerPo();
		$comment = $this->getRequest()->getParam("comment");
		
		if(empty($comment)){
			$this->_getSession()->addError(
				Mage::helper("zolagopo")->__("Enter some comment")
			);
			return $this->_redirectReferer();
		}
		
		if($this->_getVendor()){
			$comment = $this->_getVendor()->getVendorName() . ": " . $comment;
		}
		
		try{
			$_po->addComment($comment, false, true);
			$_po->saveComments();
			$this->_getSession()->addSuccess(
				Mage::helper("zolagopo")->__("Comment added")
			);
		}catch(Mage_Core_Exception $e){
			$this->_getSession()->addError($e->getMessage());
		}catch(Exception $e){
			$this->_getSession()->addError(
				Mage::helper("zolagopo")->__("Some error occure")
			);
			Mage::logException($e);
		}
		return $this->_redirectReferer(); //$this->_redirectUrl($this->_getAnchorEditUrl("comments"));
	}
	protected function _getAnchorEditUrl($anchor) {
		return Mage::getUrl("*/*/edit", array("id"=>$this->_registerPo()->getId()))."#".$anchor;
	}
	
	
	public function saveAddressAction(){
		$req	=	$this->getRequest();
		$data	=	$req->getPost();
		$type	=	$req->getParam("type");
		$isAjax =	$req->isAjax();
		
		$po = $this->_registerPo();
		/* @var $po Zolago_Po_Model_Po */
		$session = $this->_getSession();
		/* @var $session Zolago_Dropship_Model_Session */
		
		
		if(!$po->getId()){
			$this->getResponse()->setBody(Zend_Json::encode(array(
				"status"=>0, 
				"content"=>Mage::helper("zolagopo")->__("Wrong PO Id")
			)));
			return;
		}
		
		if($po->getVendor()->getId()!=$session->getVendor()->getId()){
			$this->getResponse()->setBody(Zend_Json::encode(array(
				"status"=>0, 
				"content"=>Mage::helper("zolagopo")->__("You have no access to this PO")
			)));
			return;
		}
		
		$response = array(
			"status"=>1,
			"content"=>array()
		);
		
		try{
			if(isset($data['restore']) && $data['restore']==1){
				if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
					$po->clearOwnShippingAddress();
				}else{
					$po->clearOwnBillingAddress();
				}
				$po->save();
				$session->addSuccess(Mage::helper("zolagopo")->__("Address restored"));
				$response['content']['reload']=1;
			}elseif(isset($data['add_own']) && $data['add_own']==1){
				if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
					$orignAddress = $po->getOrder()->getShippingAddress();
				}else{
					$orignAddress = $po->getOrder()->getBillingAddress();
				}
				$newAddress = clone $orignAddress;
				$newAddress->addData($data);
				if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
					$po->setOwnShippingAddress($newAddress);
				}else{
					$po->setOwnBillingAddress($newAddress);
				}
				$po->save();
				$session->addSuccess(Mage::helper("zolagopo")->__("Address changed"));
				$response['content']['reload']=1;
			}
		}catch(Exception $e){
			Mage::logException($e);
			$response = array(
				"status"=>0, 
				"content"=>Mage::helper("zolagopo")->__("Some errors occure. Check logs.")
			);
			if(!$isAjax){
				$session->addError(Mage::helper("zolagopo")->__("Some errors occure. Check logs."));
			}
		}
		if($isAjax){
			$this->getResponse()->setHeader("content-type", "application/json");
			$this->getResponse()->setBody(Zend_Json::encode($response));
		}else{
			$this->_redirectReferer();
		}
	}

	public function updatePosAction(){
		
		$poId = $this->getRequest()->getParam("id");
		$posId = $this->getRequest()->getParam("pos_id");
		
		$po = Mage::getModel("udpo/po")->load($poId);
		/* @var $po Unirgy_DropshipPo_Model_Po */
		$pos = Mage::getModel("zolagopos/pos")->load($posId);
		/* @var $pos Zolago_Pos_Model_Pos */
		$session = $this->_getSession();
		/* @var $session Zolago_Dropship_Model_Session */
		
		$reload = false;
		
		$this->getResponse()->setHeader("content-type", "application/json");
		
		if($po->getId() && $pos->getId() && 
				$po->getVendor()->getId()==$session->getVendor()->getId() &&
				$pos->isAssignedToVendor($session->getVendor())){
			
			$po->setDefaultPosId($pos->getId());
			$po->setDefaultPosName($pos->getName());
			if($session->isOperatorMode()){
				if(!in_array($pos->getId(), $session->getOperator()->getAllowedPos())){
					$reload = true;
				}
			}
			$po->save();
			$this->getResponse()->setBody(Zend_Json::encode(array(
				"status"=>1, 
				"reload"=>$reload, 
				"pos"=>$pos->getData()
			)));
			return;
		}
		
		$this->getResponse()->setBody(Zend_Json::encode(array("status"=>0, "message"=>"Some error occure")));
	}

    public function saveShippingAction()
    {
		
        $hlp = Mage::helper('udropship');
        $udpoHlp = Mage::helper('udpo');
        $r = $this->getRequest();
        $udpo = $this->_registerPo();
        $id = $udpo->getId();
		
        $vendor = $hlp->getVendor($udpo->getUdropshipVendor());
        $session = $this->_getSession();

        if (!$udpo->getId()) {
            return;
        }

        try {
            $store = $udpo->getOrder()->getStore();

            $track = null;
            $highlight = array();

            $partial = $r->getParam('partial_availability');
            $partialQty = $r->getParam('partial_qty');

            $printLabel = $r->getParam('print_label');
            $number = $r->getParam('tracking_id');

            $carrier = $r->getParam('carrier');
            $carrierTitle = $r->getParam('carrier_title');

            $notifyOn = Mage::getStoreConfig('udropship/customer/notify_on', $store);
            $pollTracking = Mage::getStoreConfig('udropship/customer/poll_tracking', $store);
            $poAutoComplete = Mage::getStoreConfig('udropship/vendor/auto_complete_po', $store);
            $autoComplete = Mage::getStoreConfig('udropship/vendor/auto_shipment_complete', $store);

            $poStatusShipped = Unirgy_DropshipPo_Model_Source::UDPO_STATUS_SHIPPED;
            $poStatusDelivered = Unirgy_DropshipPo_Model_Source::UDPO_STATUS_DELIVERED;
            $poStatusCanceled = Unirgy_DropshipPo_Model_Source::UDPO_STATUS_CANCELED;
            $poStatuses = Mage::getSingleton('udpo/source')->setPath('po_statuses')->toOptionHash();
            // if label was printed
            if ($printLabel) {
                $poStatus = $r->getParam('is_shipped') ? $poStatusShipped : Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL;
                $isShipped = $r->getParam('is_shipped') ? true : false;
            } else { // if status was set manually
                $poStatus = $r->getParam('status');
                $isShipped = $poStatus == $poStatusShipped || $poStatus==$poStatusDelivered || $autoComplete && ($poStatus==='' || is_null($poStatus));
            }
			
			
		

            //if ($printLabel || $number || ($partial=='ship' && $partialQty)) {
            $partialQty = $partialQty ? $partialQty : array();
            if ($r->getParam('use_label_shipping_amount')) {
                $udpo->setUseLabelShippingAmount(true);
            } elseif ($r->getParam('shipping_amount')) {
                $udpo->setShipmentShippingAmount($r->getParam('shipping_amount'));
            }
            $udpo->setUdpoNoSplitPoFlag(true);
            $shipment = $udpoHlp->createShipmentFromPo($udpo, $partialQty, true, true, true);
            if ($shipment) {
                $shipment->setNewShipmentFlag(true);
                $shipment->setDeleteOnFailedLabelRequestFlag(true);
                $shipment->setCreatedByVendorFlag(true);
            }
			
            //}
			
			/**
			 * DHL: Make a WebApi Call to get T&T Data
			 */
			$autoTracking = $r->getParam('auto-tracking');
			$dhlSettings = $udpoHlp->getDhlSettings($vendor, $udpo->getDefaultPosId());

			if (!$number && $carrier == Zolago_Dhl_Helper_Data::DHL_CARRIER_CODE && $autoTracking && $shipment && $dhlSettings) {
				
				$shipmentSettings = array(
					'type'			=> $r->getParam('specify_zolagodhl_type'),
					'width'			=> $r->getParam('specify_zolagodhl_width'),
					'height'		=> $r->getParam('specify_zolagodhl_height'),
					'length'		=> $r->getParam('specify_zolagodhl_length'),
					'weight'		=> ($shipment->getTotalWeight() ? ((int) ceil($shipment->getTotalWeight())) : Mage::helper('zolagodhl')->getDhlDefaultWeight()),
					'quantity'		=> Zolago_Dhl_Model_Client::SHIPMENT_QTY,
					'nonStandard'	=> $r->getParam('specify_zolagodhl_custom_dim'),
					'shipmentDate'  => $this->_porcessDhlDate($r->getParam('specify_zolagodhl_shipping_date')),
					'shippingAmount'=> $r->getParam('shipping_amount')
				);
				
				$number = $this->_createShipments($dhlSettings, $shipment, $shipmentSettings, $udpo);
				if (!$number) {
					$udpoHlp->cancelShipment($shipment, true);
					return $this->_redirectReferer();
				}
			}

            // if label to be printed
            if ($printLabel) {
                $data = array(
                    'weight'    => $r->getParam('weight'),
                    'value'     => $r->getParam('value'),
                    'length'    => $r->getParam('length'),
                    'width'     => $r->getParam('width'),
                    'height'    => $r->getParam('height'),
                    'reference' => $r->getParam('reference'),
                	'package_count' => $r->getParam('package_count'),
                );

                $extraLblInfo = $r->getParam('extra_label_info');
                $extraLblInfo = is_array($extraLblInfo) ? $extraLblInfo : array();
                $data = array_merge($data, $extraLblInfo);

                $oldUdropshipMethod = $shipment->getUdropshipMethod();
                $oldUdropshipMethodDesc = $shipment->getUdropshipMethodDescription();
                if ($r->getParam('use_method_code')) {
                    list($useCarrier, $useMethod) = explode('_', $r->getParam('use_method_code'), 2);
                    if (!empty($useCarrier) && !empty($useMethod)) {
                        $shipment->setUdropshipMethod($r->getParam('use_method_code'));
                        $carrierMethods = Mage::helper('udropship')->getCarrierMethods($useCarrier);
                        $shipment->setUdropshipMethodDescription(
                            Mage::getStoreConfig('carriers/'.$useCarrier.'/title', $shipment->getOrder()->getStoreId())
                            .' - '.$carrierMethods[$useMethod]
                        );
                    }
                }
                // generate label
                try {
	                $batch = Mage::getModel('udropship/label_batch')
	                    ->setVendor(Mage::getSingleton('udropship/session')->getVendor())
	                    ->processShipments(array($shipment), $data, array('mark_shipped'=>$isShipped));
                } catch (Exception $e) {
                    if ($r->getParam('use_method_code')) {
                        $shipment->setUdropshipMethod($oldUdropshipMethod);
                        $shipment->setUdropshipMethodDescription($oldUdropshipMethodDesc);
                        $shipment->getResource()->saveAttribute($shipment, 'udropship_method');
                        $shipment->getResource()->saveAttribute($shipment, 'udropship_method_description');
                    }
            		throw $e;
                }

                // if batch of 1 label is successfull
                if ($batch->getShipmentCnt()) {
                    $url = Mage::getUrl('udropship/vendor/reprintLabelBatch', array('batch_id'=>$batch->getId()));
                    Mage::register('udropship_download_url', $url);

                    if (($track = $batch->getLastTrack())) {
                        $session->addSuccess('Label was succesfully created');
                        Mage::helper('udropship')->addShipmentComment(
                            $shipment,
                            $this->__('%s printed label ID %s', $vendor->getVendorName(), $track->getNumber())
                        );
                        $shipment->save();
                        $highlight['tracking'] = true;
                    }
                } else {
                    if ($batch->getErrors()) {
                    	$batchError = '';
                        foreach ($batch->getErrors() as $error=>$cnt) {
                        	$batchError .= $hlp->__($error, $cnt)." \n";
                        }
                        if ($r->getParam('use_method_code')) {
                            $shipment->setUdropshipMethod($oldUdropshipMethod);
                            $shipment->setUdropshipMethodDescription($oldUdropshipMethodDesc);
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method');
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method_description');
                        }
	            		Mage::throwException($batchError);
                    } else {
                        if ($r->getParam('use_method_code')) {
                            $shipment->setUdropshipMethod($oldUdropshipMethod);
                            $shipment->setUdropshipMethodDescription($oldUdropshipMethodDesc);
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method');
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method_description');
                        }
	                    $batchError = 'No items are available for shipment';
	            		Mage::throwException($batchError);
                    }
                }

            } elseif ($number) { // if tracking id was added manually
                $method = explode('_', $shipment->getUdropshipMethod(), 2);
                $title = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
                $_carrier = $method[0];
                if (!empty($carrier) && !empty($carrierTitle)) {
                    $_carrier = $carrier;
                    $title = $carrierTitle;
                }
                $track = Mage::getModel('sales/order_shipment_track')
                    ->setNumber($number)
                    ->setCarrierCode($_carrier)
                    ->setTitle($title);

                $shipment->addTrack($track);

                Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);

                Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    $this->__('%s added tracking ID %s', $vendor->getVendorName(), $number)
                );
                $shipment->save();
                $session->addSuccess($this->__('Tracking ID has been added'));

                $highlight['tracking'] = true;
            }

            $udpoStatuses = false;
            if (Mage::getStoreConfig('udropship/vendor/is_restrict_udpo_status')) {
                $udpoStatuses = Mage::getStoreConfig('udropship/vendor/restrict_udpo_status');
                if (!is_array($udpoStatuses)) {
                    $udpoStatuses = explode(',', $udpoStatuses);
                }
            }

            if (!$printLabel && !is_null($poStatus) && $poStatus!=='' && $poStatus!=$udpo->getUdropshipStatus()
                && (!$udpoStatuses || (in_array($udpo->getUdropshipStatus(), $udpoStatuses) && in_array($poStatus, $udpoStatuses)))
            ) {
                $oldStatus = $udpo->getUdropshipStatus();
                $poStatusChanged = false;
                if ($r->getParam('force_status_change_flag')) {
                    $udpo->setForceStatusChangeFlag(true);
                }
                if ($oldStatus==$poStatusCanceled && !$udpo->getForceStatusChangeFlag()) {
                    Mage::throwException(Mage::helper('udpo')->__('Canceled purchase order cannot be reverted'));
                }
                if ($poStatus==$poStatusShipped || $poStatus==$poStatusDelivered) {
                    foreach ($udpo->getShipmentsCollection() as $_s) {
                        $hlp->completeShipment($_s, true, $poStatus==$poStatusDelivered);
                    }
                    if (isset($_s)) {
                        $hlp->completeOrderIfShipped($_s, true);
                    }
                    $poStatusChanged = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                } elseif ($poStatus == $poStatusCanceled) {
                    $udpo->setFullCancelFlag($r->getParam('full_cancel'));
                    $udpo->setNonshippedCancelFlag($r->getParam('nonshipped_cancel'));
                    Mage::helper('udpo')->cancelPo($udpo, true, $vendor);
                    $poStatusChanged = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                } else {
                    $poStatusChanged = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                }
                $udpo->getCommentsCollection()->save();
                if ($poStatusChanged) {
                    $session->addSuccess($this->__('Purchase order status has been changed'));
                } else {
                    $session->addError($this->__('Cannot change purchase order status'));
                }
            }

        	if (!empty($shipment) && $shipment->getNewShipmentFlag() && !$shipment->isDeleted()) {
        		$shipment->setNoInvoiceFlag(false);
            	$udpoHlp->invoiceShipment($shipment);
            }

            $comment = $r->getParam('comment');
            if ($comment || $partial=='inform' && $partialQty) {
                if ($partialQty) {
                    $comment .= "\n\nPartial Availability:\n";
                    foreach ($udpo->getAllItems() as $item) {
                        if (!array_key_exists($item->getId(), $partialQty) || '' === $partialQty[$item->getId()]) {
                            continue;
                        }
                        $comment .= $this->__('%s x [%s] %s', $partialQty[$item->getId()], $item->getName(), $item->getSku())."\n";
                    }
                }

                //$udpo->addComment($comment, false, true)->getCommentsCollection()->save();
                Mage::helper('udpo')->sendVendorComment($udpo, $comment);
                $session->addSuccess($this->__('Your comment has been sent to store administrator'));

                $highlight['comment'] = true;
            }

            $session->setHighlight($highlight);
        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }

        return $this->_redirectReferer();
    }
	public function cancelShippingAction() {
		$this->_getSession()->addSuccess("Shipping canceld");
		return $this->_redirectReferer();
	}
	
	protected function _porcessDhlDate($date) {
		$_date = explode("-", $date);
		if(count($_date)==3){
			if(count($_date[0])==4){
				return $date;
			}
			return $_date[2] . "-" . $_date[1] . "-" . $_date[0];
		}
	}
	
	protected function _createShipments($dhlSettings, $shipment, $shipmentSettings, $udpo) {
		$number		= false;
		$dhlClient	= Mage::helper('zolagodhl')->startDhlClient($dhlSettings);
		$posModel	= Mage::getModel('zolagopos/pos')->load($udpo->getDefaultPosId());
		$session = $this->_getSession();
		/* @var $session Zolago_Dropship_Model_Session */
		
		if ($posModel && $posModel->getId()) {
			$dhlClient->setPos($posModel);
			$dhlResult	= $dhlClient->createShipments($shipment, $shipmentSettings);
			$result		= $dhlClient->processDhlShipmentsResult('createShipments', $dhlResult);
			
			if ($result['shipmentId']) {
				$number = $result['shipmentId'];
			} else {
                Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    $result['message']
                );
                $shipment->save();
				Mage::helper('zolagodhl')->addUdpoComment($udpo, $result['message'], false, true, false);
                			
                $session->addError($this->__('DHL Service Error. Shipment Canceled. Please try again later.'));				
			}
		}
		
		return $number;
	}
}


