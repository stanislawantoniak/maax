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
         $dhlFile = Mage::helper('zolagodhl')->getDhlFileDir() . $number . '.pdf';
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

        $session = $this->_getSession();

        try {
            $rma = $this->_registerRma();
            $statusModel = $rma->getStatusModel();
            $request = $this->getRequest();

            $comment = trim($request->getParam("comment", ''));
            $status = $request->getParam("status");
            $notify = $request->getParam("notify_customer", 0);

            $messages = array();

            // Process status
            if($status!=$rma->getRmaStatus()) {
                if(!$this->_isValidRmaStatus($rma, $status)) {
                    throw new Mage_Core_Exception(Mage::helper("zolagorma")->
                                                  __("Status code %s is not valid.", $status));
                }
                Mage::helper('zolagorma')->processSaveStatus($rma, $status);
                $messages[] = Mage::helper("zolagorma")->__("Status changed");
            }

            // Process comment
            if($comment) {
                if(!$statusModel->isVendorCommentAvailable($rma)) {
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
            $vendorSession = Mage::getSingleton('udropship/session');
            /* @var $vendorSession  Zolago_Dropship_Model_Session*/
            if($vendorSession->getVendorId() && $notify){
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

    /**
     * Send request to DHL
     * @return string
     * @throws Mage_Core_Exception
     */
    protected function _getTrackingDhlNumber() {
        $request = $this->getRequest();
        $width = (float)$request->getParam('specify_orbadhl_width');
        $height = (float)$request->getParam('specify_orbadhl_height');
        $length = (float)$request->getParam('specify_orbadhl_length');
        $date = $request->getParam('specify_orbadhl_shipping_date');
        $weight = ceil((float)$request->getParam('weight'));
        $type = $request->getParam('specify_orbadhl_type');
        switch ($type) {
            case 'PACKAGE':
                $dhlType = Zolago_Dhl_Model_Client::SHIPMENT_TYPE_PACKAGE;
            break;
            case 'ENVELOPE':
                $dhlType = Zolago_Dhl_Model_Client::SHIPMENT_TYPE_ENVELOPE;
            break;
            default:
                throw new Mage_Core_Exception(Mage::helper("zolagorma")->__("Unknown DHL package type"));
        }        
        $dhlParams = array (
            'width' => $width,
            'height' => $height,            
            'length' => $length,
            'shipmentDate' => $date,
            'weight' => ($weight>1)? $weight:1,
            'type' => $dhlType,
            'vendor' => true,
        );
        if (!$request->getParam('specify_orbadhl_custom_dim')) {
            $dhlParams['nonStandard'] = true;
        }
        $rma = $this->_registerRma();
        $trackingParams = $rma->sendDhlRequest($dhlParams);        
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
            // Override by dhl
            switch($carrier) {
            case "custom":
                // N.O.
                $trackingNumber = 'dev';
                break;
            case "ups":
                // N.O.
                $trackingNumber = 'dev';
                break;
            case Orba_Shipping_Model_Carrier_Dhl::CODE:
                $trackingNumber = $this->_getTrackingDhlNumber();
                break;
            case Orba_Shipping_Model_Carrier_Ups::CODE:
                $trackingNumber = $request->getParam('tracking_id');
                break;
            default:
                throw new Mage_Core_Exception(Mage::helper("zolagorma")->__("Unknown carrier"));
                break;
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
        if(!Mage::registry('current_rma')) {
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
