<?php

require_once Mage::getModuleDir('controllers', 'Unirgy_Rma') . "/VendorController.php";

/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Rma_VendorController extends Unirgy_Rma_VendorController
{

    public function indexAction() {
        Mage::register('as_frontend', true);
        return parent::indexAction();
    }
    /**
     * Display edit form
     * @return null
     */
    public function editAction() {
        $render = false;
        try {
            $this->_registerRma();
            $render = true;
        } catch(Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch(Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagorma")->__("Other error. Check logs."));
        }

        if($render) {
            return $this->_renderPage(null, 'urma');
        }

        return $this->_redirect("*/*");
    }

	public function makeRefundAction() {
		$data = $this->getRequest()->getPost();
		/** @var Zolago_Rma_Helper_Data $rmaHelper */
		$hlp = Mage::helper("zolagorma");
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		/* @var $connection Varien_Db_Adapter_Interface */
		$connection->beginTransaction();

		try {
			$rma = $this->_registerRma();
			if($rma->getRmaStatusCode() == Zolago_Rma_Model_Rma_Status::STATUS_ACCEPTED) {
				$invalidItems = array();
				$validItems = array();
				$returnAmount = 0;
				$po = $rma->getPo();

				/** @var Zolago_Rma_Model_Rma $rmaModel */
				$rmaModel = Mage::getModel('zolagorma/rma');
				$rmas = $rmaModel->loadByPoId($po->getId());
				$alreadyReturnedAmount = 0;
				foreach($rmas as $rma) {
					$alreadyReturnedAmount += $rma->getReturnedValue();
				}

				foreach ($data['rmaItems'] as $id => $val) {
					/** @var Zolago_Rma_Model_Rma_Item $rmaItem */
					ob_start();
					var_dump($id);
					$result = ob_get_clean();
					Mage::log($result,null,'rma.log');
					Mage::log($rma->getData(),null,'rmaData.log');
					$rmaItem = $rma->getItemById($id);
					if (/*is_object($rmaItem) && */$rmaItem->getId()) {
						$maxValue = $rmaItem->getPoItem()->getFinalItemPrice();
						if (isset($data['returnValues'][$id]) &&
							$data['returnValues'][$id] <= $maxValue) {
							$validItems[] = $rmaItem->setReturnedValue($rmaItem->getReturnedValue() + $data['returnValues'][$id])->save();
							$returnAmount += $data['returnValues'][$id];
						} else {
							$invalidItems[] = $rmaItem->getName() . " (" . $rmaItem->getVendorSimpleSku() . ")";
						}
					} else {
						$invalidItems[] = $hlp->__("Invalid RMA item ID:") . " " . $id;
					}
				}

				if (count($validItems) && $returnAmount > 0) {

					if(($returnAmount + $alreadyReturnedAmount) <= $po->getGrandTotalInclTax()) {
						$rma->setReturnedValue($rma->getReturnedValue() + $returnAmount)->save();
					} else {
						$this->_throwRefundTooMuchAmountException();
					}

					if(!$po->isCod()) {
						/** @var Zolago_Payment_Model_Allocation $allocationModel */
						$allocationModel = Mage::getModel('zolagopayment/allocation');
						$result = $allocationModel->createOverpayment($po, "Moved to overpayment by RMA refund", "Created overpayment by RMA refund");
						if($result === false) {
							$this->_throwRefundTooMuchAmountException();
						}
					}
                    $_returnAmount = $po->getCurrencyFormattedAmount($returnAmount);
					$this->_getSession()->addSuccess($hlp->__("RMA refund successful! Amount refunded %s",$_returnAmount));
					$po->addComment($hlp->__("Created refund (RMA id: %s). Amount: %s",$rma->getIncrementId(),$_returnAmount),false,true);
					$po->saveComments();
					$rma->addComment($hlp->__("Created RMA refund. Amount: %s",$_returnAmount));
					$rma->saveComments();
				} elseif (count($invalidItems)) {
					Mage::throwException($hlp->__("There was an error while processing those items:") . "<br />" . implode('<br />', $invalidItems));
				} else {
					Mage::throwException($hlp->__("No items to refund"));
				}

				$connection->commit();
			} else {
				Mage::throwException($hlp->__("Cannot create RMA refund because of wrong RMA status"));
			}
		} catch(Mage_Core_Exception $e) {
			$connection->rollBack();
			$this->_getSession()->addError($e->getMessage());
		} catch(Exception $e) {
			$connection->rollBack();
			Mage::logException($e);
			$this->_getSession()->addError($hlp->__("Other error. Check logs."));
		}

		return $this->_redirectReferer();
	}

	protected function _throwRefundTooMuchAmountException() {
		Mage::throwException(Mage::helper("zolagorma")->__("Refund could not be created - not enough money left in PO"));
	}
    
    /**
     * Print DHL waybill
     */
     public function pdfAction() {
         $request = $this->getRequest();
         $number = $request->getParam('number');
         if (empty($number)) {
             Mage::throwException(Mage::helper('zolagorma')->__('No tracking number'));
         }
         $ioAdapter = new Varien_Io_File();
         $dhlFile = Mage::helper('orbashipping/carrier_dhl')->getDhlFileDir() . $number . '.pdf';
         return $this->_prepareDownloadResponse(basename($dhlFile), @$ioAdapter->read($dhlFile), 'application/pdf');
     }
    /**
     * Add comment
     * @return null
     */
    public function commentAction() {

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        /* @var $connection Varien_Db_Adapter_Interface */
        $connection->beginTransaction();

//        $session = $this->_getSession();

        try {
            $rma = $this->_registerRma();
            $statusModel = $rma->getStatusModel();
            $request = $this->getRequest();
            $oldStatus = $rma->getRmaStatus();

            $comment = trim($request->getParam("comment", ''));
            $status = $request->getParam("status");
            $notify = $request->getParam("notify_customer", 0);

            $notify_email = $statusModel->isNotifyEmailAvailable($status);

            //Email with info about status changed:
            //if flag notify by email is YES
            //then send email and notify
            //else
            //don't send email

            //Email with comment:
            //if flag notify by email is YES
            //then no meter
            //else
            //no meter

            $messages = array();

            // Process status
            if($status!=$oldStatus) {
                if(!$this->_isValidRmaStatus($rma, $status)) {
                    throw new Mage_Core_Exception(Mage::helper("zolagorma")->
                                                  __("Status code %s is not valid.", $status));
                }

                if($notify_email) {
                    $notify_status = 1;
                } else {
                    $notify_status = 0;                    
                }
                $messages[] = Mage::helper("zolagorma")->__("Status changed");
                Mage::helper('zolagorma')->processSaveStatus($rma, $status, (bool)$notify_status);

            }

            // Process comment
            if($comment) {
                if(!$statusModel->isVendorCommentAvailable($oldStatus)) {
                    throw new Mage_Core_Exception(Mage::helper("zolagorma")->
                                                  __("Cannot add comment in this status"));
                }

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
                            "rma_status"			=> $rma->getUdropshipStatus()
                        );
	
                $model = Mage::getModel("urma/rma_comment")->
                         setRma($rma)->
                         addData($data)->
                         setSkipSettingName(true)->
                         save();


                /* @var $model Unirgy_Rma_Model_Rma_Comment */

                Mage::dispatchEvent("zolagorma_rma_comment_added", array(
                                        "rma"		=> $rma,
                                        "comment"	=> $model,
                                        "notify"	=> (bool)$notify
                                    ));

                $messages[] = Mage::helper("zolagorma")->__("Comment added");
            }

            if($notify){
                Mage::getModel("urma/rma")
                    ->load($rma->getId())
                    ->setnewCustomerQuestion(0)
                    ->save();
            }

            $connection->commit();

            if($messages) {
                if($notify) {
                    $messages[] =  Mage::helper("zolagorma")->__("Customer notified by email");
                }
                // Process flash msg
                foreach($messages as $message) {
                    $this->_getSession()->addSuccess($message);
                }

            } else {
                $this->_getSession()->addNotice(Mage::helper("zolagorma")->
                                                __("No changes (empty comment and same status)"));
            }
        } catch(Mage_Core_Exception $e) {
            $connection->rollBack();
            $this->_getSession()->addError($e->getMessage());
        } catch(Exception $e) {
            $connection->rollBack();
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagorma")->__("Other error. Check logs."));
        }

        return $this->_redirectReferer();
    }
    protected function _getTrackingNumber($carrier) {
        $manager = Mage::helper('orbashipping')->getShippingManager($carrier);
        $request = $this->getRequest();
		$vendor = $this->_getSession()->getVendor();
        $rma = $this->_registerRma();
                        
        $manager->prepareRmaSettings($request,$vendor,$rma);
        
        $address = $rma->getFormattedAddressForVendor();
        $manager->setReceiverAddress($address);
        $address = $vendor->getRmaAddress();
        $manager->setSenderAddress($address);
        $trackingParams = $manager->createShipmentAtOnce();
        $session = Mage::getSingleton('core/session');
        $session->setPdfNumberPrintId($trackingParams['trackingNumber']);
        return $trackingParams['trackingNumber'];        
        
    }
    /**
     * Save tracking number
     * @return type
     */
    public function saveShippingAction() {
        try {
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
            
            $trackingNumber = $this->_getTrackingNumber($carrier);

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
                             "track_creator"			=> Zolago_Rma_Model_Rma_Track::CREATOR_TYPE_VENDOR
                         );


            $model = Mage::getModel('urma/rma_track')->
                     addData($trackData)->
                     save();

            Mage::dispatchEvent("zolagorma_rma_track_added", array(
                                    "rma"		=> $rma,
                                    "track"		=> $model
                                ));
            $rma->save();
            $this->_getSession()->addSuccess(Mage::helper("zolagorma")->__("Shipping label added."));
        } catch(Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch(Exception $e) {
            throw $e;
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagorma")->__("Other error. Check logs."));
        }

        return $this->_redirectReferer();
    }

    /**
     * Save address obejct
     * @retur null
     */
    public function saveAddressAction() {
        $req	=	$this->getRequest();
        $data	=	$req->getPost();
        $type	=	$req->getParam("type");

        $session = $this->_getSession();
        /* @var $session Zolago_Dropship_Model_Session */


        try {
            $rma = $this->_registerRma();

            if(!$rma->getStatusModel()->isEditingAddressAvailable($rma)) {
                throw new Mage_Core_Exception(Mage::helper("zolagorma")->
                                              __("Cannot edit shipping address in this status"));
            }

            if(isset($data['restore']) && $data['restore']==1) {
                if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
                    $rma->clearOwnShippingAddress();
                } else {
                    $rma->clearOwnBillingAddress();
                }

                Mage::dispatchEvent("zolagorma_rma_address_restore", array(
                                        "rma"		=> $rma,
                                        "type"		=> $type
                                    ));

                $rma->save();
                $session->addSuccess(Mage::helper("zolagorma")->__("Address restored"));
                $response['content']['reload']=1;
            }
            elseif(isset($data['add_own']) && $data['add_own']==1) {
                if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
                    $orignAddress = $rma->getOrder()->getShippingAddress();
                    $oldAddress = $rma->getShippingAddress();
                } else {
                    $orignAddress = $rma->getOrder()->getBillingAddress();
                    $oldAddress = $rma->getBillingAddress();
                }
                $newAddress = clone $orignAddress;
                $newAddress->addData($data);
                if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
                    $rma->setOwnShippingAddress($newAddress);
                } else {
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
        } catch(Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch(Exception $e) {
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
	    Mage::log($this->getRequest()->getParam('id'),null,'requestId.log');

	    if(is_object(Mage::registry('current_rma')) && Mage::registry('current_rma')->getId() != $this->getRequest()->getParam('id')) {
		    Mage::unregister('current_rma');
	    }

        if(!Mage::registry('current_rma')) {
	        Mage::log('getting rma from link',null,'rmaGet.log');
            $rma = Mage::getModel("urma/rma");
            if($this->getRequest()->getParam('id')) {
                $rma->load($this->getRequest()->getParam('id'));
            }
            if(!$this->_validateRma($rma)) {
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
        if(!$rma->getId()) {
            return false;
        }
		$vendor = $this->_getSession()->getVendor();
		$rmaVendorId = $rma->getVendor()->getId();
	
        if($rmaVendorId == $vendor->getId()) {
            return true;
        }
		
        return in_array($rmaVendorId, $vendor->getChildVendorIds());
    }

    /**
     * @param Zolago_Rma_Model_Rma $rma
     * @param string $status
     * @return bool
     */
    public function _isValidRmaStatus(Zolago_Rma_Model_Rma $rma, $status) {
        return array_key_exists(
                   $status,
                   $rma->getStatusModel()->getAvailableStatuses($rma)
               );
    }
}
