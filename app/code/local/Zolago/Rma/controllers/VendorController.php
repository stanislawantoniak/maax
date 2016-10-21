<?php

require_once Mage::getModuleDir('controllers', 'ZolagoOs_Rma') . "/VendorController.php";

/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Rma_VendorController extends ZolagoOs_Rma_VendorController
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
            $this->_getSession()->addError(Mage::helper("zolagorma")->__("There was a technical error. Please contact shop Administrator."));
        }

        if($render) {
            return $this->_renderPage(null, 'urma');
        }

        return $this->_redirect("*/*");
    }

    public function createNewRmaAction() {
        $hlp = Mage::helper("zolagorma");
        try {
            $this->_saveRmaManually();
            $this->_getSession()->addSuccess($hlp->__("RMA created successfully"));
        } catch(Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch(Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($hlp->__("There was a technical error. Please contact shop Administrator."));
        }
    }

    protected function _saveRmaManually() {
        $shippingCost = $this->getRequest()->getPost('rma_shipping_cost');
        $shippingCostStatus = false;
        if($shippingCost) {
            $shippingCostStatus = true;
        }
        $data = $this->getRequest()->getPost('rma');
        $poId = $this->getRequest()->getPost('po_id');
        $po = Mage::getModel('zolagopo/po')->load($poId);
        $rmas = Mage::getModel('zolagorma/servicePo', $po)->prepareRmaForSave($data, array(), $shippingCostStatus);
        foreach ($rmas as $rma) {
            $rma->setRmaType(Zolago_Rma_Model_Rma::RMA_TYPE_STANDARD);
            $rma->save();

            Mage::dispatchEvent("zolagorma_rma_created_manually", array(
                                    "po" => $po,
                                    "rma" => $rma
                                ));
        }
        $this->_redirect('udpo/vendor/edit', array('id'=>$poId));
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
            if(($rma->getRmaType() == Zolago_Rma_Model_Rma::RMA_TYPE_RETURN && $rma->getPo()->isCod())) {
                Mage::throwException($hlp->__("Refund is not possible because order was sent using COD and client didn't receive the package."));
            } else if($rma->getRmaStatusCode() == Zolago_Rma_Model_Rma_Status::STATUS_ACCEPTED) {
                $invalidItems = array();
                $validItems = array();
                $returnAmount = 0;
                $po = $rma->getPo();

                /** @var Zolago_Rma_Model_Rma $rmaModel */
                $rmaModel = Mage::getModel('zolagorma/rma');
                $rmas = $rmaModel->loadByPoId($po->getId());
                $alreadyReturnedAmount = 0;
                foreach($rmas as $singleRma) {
                    $alreadyReturnedAmount += $singleRma->getReturnedValue();
                }

                foreach ($data['rmaItems'] as $id => $val) {
                    /** @var Zolago_Rma_Model_Rma_Item $rmaItem */
                    $rmaItem = $rma->getItemById($id);
                    if (is_object($rmaItem) && $rmaItem->getId()) {
                        if($rmaItem->getPoItem()->getId()) {
                            $maxValue = $rmaItem->getPoItem()->getFinalItemPrice();
                        } else {
                            $maxValue = $rmaItem->getPrice();
                        }
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
                    $tmpValue = round(($returnAmount + $alreadyReturnedAmount),4);
                    if($tmpValue <= $po->getGrandTotalInclTax()) {
                        $rma->setReturnedValue($rma->getReturnedValue() + $returnAmount)->save();
                    } else {
                        $this->_throwRefundTooMuchAmountException();
                    }

                    if($po->isPaymentDotpay()) {
                        /** @var Zolago_Payment_Model_Allocation $allocationModel */
                        $allocationModel = Mage::getModel('zolagopayment/allocation');
                        $result = $allocationModel->createOverpayment($po, "Moved to overpayment by RMA refund", "Created overpayment by RMA refund",$rma->getId());
                        if($result === false) {
                            $this->_throwRefundTooMuchAmountException();
                        }
                    }
                    $_returnAmount = $po->getCurrencyFormattedAmount($returnAmount);
                    $this->_getSession()->addSuccess($hlp->__("RMA refund successful! Amount refunded %s",$_returnAmount));
                    $po->addComment($hlp->__("Created refund (RMA id: %s). Amount: %s",$rma->getIncrementId(),$_returnAmount),false,true);
                    /*$rma->addComment($hlp->__("Created RMA refund. Amount: %s",$_returnAmount),false,true);*/


                    $commentData = array(
                                       "parent_id" => $rma->getId(),
                                       "is_visible_on_front" => 0,
                                       "is_vendor_notified" => 0,
                                       "is_customer_notified" => 0,
                                       "is_visible_to_vendor" => 1
                                   );
                    /* @var $vendorSession  Zolago_Dropship_Model_Session*/
                    $vendorSession = Mage::getSingleton('udropship/session');
                    $commentData['vendor_id'] = $vendorSession->getVendorId();

                    /** @var GH_Statements_Model_Refund $refundStatementModel */
                    $refundStatementModel = Mage::getModel('ghstatements/refund');

                    if($vendorSession->isOperatorMode()) {
                        $operator = $vendorSession->getOperator();
                        $commentData['operator_id'] = $operator->getId();
                        $refundStatementModel
                        ->setOperatorId($operator->getId())
                        ->setOperatorName($operator->getFirstname()." ".$operator->getLastname()." (".$operator->getEmail().")");
                    }

                    if(!$po->isPaymentDotpay()) {
                        //refund confirm
                        $commentData['comment'] = $hlp->__("{{author_name}} has confirmed refund for this RMA, amount: %s",$_returnAmount);
                    } else {
                        //refund order
                        $commentData['comment'] = $hlp->__("{{author_name}} has ordered refund for this RMA, amount: %s",$_returnAmount);
                    }
                    $commentModel = Mage::getModel("zolagorma/rma_comment");
                    $commentModel->setRma($rma);
                    $commentModel->addData($commentData);
                    $commentModel->setAuthorName($commentModel->getAuthorName(false));
                    $commentModel->save();

                    //send emails to not transactional refunds
                    if(!$po->isPaymentDotpay()) {
                        /** @var Zolago_Payment_Helper_Data $paymentHelper */
                        $paymentHelper = Mage::helper('zolagopayment');
                        if($paymentHelper->sendRmaRefundEmail($rma->getOrder()->getCustomerEmail(),$rma,$_returnAmount)) {
                            $po->addComment($hlp->__("Email about RMA refund was sent to customer (RMA id: %s, amount: %s)", $rma->getIncrementId(), $_returnAmount), false, true);
                            $rma->addComment($hlp->__("Email about refund was sent to customer (Amount: %s)", $_returnAmount));
                        }
                    }

                    $refundStatementModel
                    ->setPoId($po->getId())
                    ->setPoIncrementId($po->getIncrementId())
                    ->setRmaId($rma->getId())
                    ->setRmaIncrementId($rma->getIncrementId())
                    ->setDate(Mage::getModel('core/date')->date('Y-m-d'))
                    ->setVendorId($po->getVendor()->getId())
                    ->setRegisteredValue($returnAmount);

                    if($rma->getPaymentChannelOwner()) {
                        $refundStatementModel->setValue($returnAmount);
                    }

                    $refundStatementModel->save();

                    $po->saveComments();
                    $rma->saveComments();
                }
                elseif (count($invalidItems)) {
                    Mage::throwException($hlp->__("There was an error while processing those items:") . "<br />" . implode('<br />', $invalidItems));
                }
                else {
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
            $this->_getSession()->addError($hlp->__("There was a technical error. Please contact shop Administrator."));
        }

        return $this->_redirectReferer();
    }

    public function makeSimpleRefundAction() {
        $rmaId = $this->getRequest()->getParam('id');
        $amount = $this->getRequest()->getParam('refund');
        $hlp = Mage::helper("zolagorma");
        try {
            $rma = Mage::getModel('urma/rma')->load($rmaId);
            $po = $rma->getPo();
            if(($rma->getRmaType() == Zolago_Rma_Model_Rma::RMA_TYPE_RETURN && $rma->getPo()->isCod())) {
                Mage::throwException($hlp->__("Refund is not possible because order was sent using COD and client didn't receive the package."));
            } else if($rma->getRmaStatusCode() == Zolago_Rma_Model_Rma_Status::STATUS_ACCEPTED) {
                $customerId = $rma->getCustomerId();
                $order = $rma->getOrder();
                $orderId = $order->getId();
                $paymentId = $rma->getOrder()->getPayment()->getId();
                $customerAccount = NULL;

                if($rma->getCustomerAccount()) {
                    $customerAccount = $rma->getCustomerAccount();
                }
                /* @var Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection $existTransactionCollection */
                $existTransactionCollection = Mage::getModel('sales/order_payment_transaction')->getCollection()
                    ->addFieldToFilter('order_id', $orderId)
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('txn_type', Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER)
                    ->addFieldToFilter('payment_id', $paymentId);
                /** @var Mage_Sales_Model_Order_Payment_Transaction $existTransaction */
                $existTransaction = $existTransactionCollection->getFirstItem();
                /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
                $transaction = Mage::getModel("sales/order_payment_transaction");
                $transaction->setOrderPaymentObject($order->getPayment());

                if($this->validateSimpleRefund($amount, $rma)){
                    if($existTransaction->getId()){
                        $transaction
                            ->setTxnId(uniqid())
                            ->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND)
                            ->setIsClosed(0)
                            ->setTxnAmount(-$amount)
                            ->setTxnStatus(Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW)
                            ->setParentId($existTransaction->getTransactionId())
                            ->setOrderId($orderId)
                            ->setParentTxnId($existTransaction->getTxnId(), $transaction->getTransactionId())
                            ->setCustomerId($customerId)
                            ->setBankAccount($customerAccount)
                            ->setRmaId($rma->getId())
                            ->setDotpayId($existTransaction->getDotpayId());
                    }else {
                        $transaction
                            ->setTxnId(uniqid())
                            ->setTransactionId($transaction->getTransactionId())
                            ->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND)
                            ->setIsClosed(0)
                            ->setTxnAmount(-$amount)
                            ->setTxnStatus(Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW)
                            ->setOrderId($orderId)
                            ->setCustomerId($customerId)
                            ->setBankAccount($customerAccount)
                            ->setRmaId($rma->getId())
                            ->setPaymentId($paymentId);
                    }

                    if($transaction->save()){
                        $commentData = array(
                            "parent_id" => $rma->getId(),
                            "is_visible_on_front" => 0,
                            "is_vendor_notified" => 0,
                            "is_customer_notified" => 0,
                            "is_visible_to_vendor" => 1
                        );
                        /* @var $vendorSession  Zolago_Dropship_Model_Session*/
                        $vendorSession = Mage::getSingleton('udropship/session');
                        $commentData['vendor_id'] = $vendorSession->getVendorId();
                        if(!$po->isPaymentDotpay()) {
                            //refund confirm
                            $commentData['comment'] = $hlp->__("{{author_name}} has confirmed refund for this RMA, amount: %s", $po->getCurrencyFormattedAmount($amount));
                        } else {
                            //refund order
                            $commentData['comment'] = $hlp->__("{{author_name}} has ordered refund for this RMA, amount: %s", $po->getCurrencyFormattedAmount($amount));
                        }
                        $commentModel = Mage::getModel("zolagorma/rma_comment");
                        $commentModel->setRma($rma);
                        $commentModel->addData($commentData);
                        $commentModel->setAuthorName($commentModel->getAuthorName(false));
                        $commentModel->save();

                        $this->_getSession()->addSuccess($hlp->__("RMA refund successful! Amount refunded %s", $po->getCurrencyFormattedAmount($amount)));
                    }
                } else {
                    $this->_getSession()->addError($hlp->__("An amount to refund can not be more than total order sum"));
                }
                $this->_redirect("urma/vendor/edit", array('id' => $rmaId));
            }
        } catch(Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch(Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($hlp->__("There was a technical error. Please contact shop Administrator."));
        }
    }

    /**
     * @param $amount
     * @param $rma
     * @return bool
     */
    protected function validateSimpleRefund($amount, $rma) {
        $customerId = $rma->getCustomerId();
        $orderId = $rma->getOrder()->getId();
        $paymentId = $rma->getOrder()->getPayment()->getId();
        $existRefunds = Mage::getModel('sales/order_payment_transaction')->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('txn_type', Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND)
            ->addFieldToFilter('payment_id', $paymentId);
        $refundSum = 0;
        foreach($existRefunds as $existRefund){
            $refundSum +=  abs($existRefund->getTxnAmount());
        }
        $newRefundSum = $refundSum + $amount;

        $items = $rma->getAllItems();
        $totalOrderSum = 0;
        foreach ($items as $item){
            $poItemId = $item->getPoItem()->getId();
            if($poItemId) {
                $paidNum = $item->getPoItem()->getFinalItemPrice();
            } else {
                $paidNum = $item->getPrice();
            }
            $totalOrderSum += floatval($paidNum);
        }
        if(round($newRefundSum,4) <= round($totalOrderSum,4)) {
            return true;
        }
        return false;
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
        $dhlFile = Mage::helper('orbashipping/carrier_dhl')->getFileDir() . $number . '.pdf';
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

            //don't allow to close rma if refund is still processing
            if($rma->getRmaRefundAmount() && $rma->getPo()->isPaymentDotpay() && !$rma->isAlreadyReturned() && $status == 'closed_accepted') {
                throw new Mage_Core_Exception(
                    Mage::helper("zolagorma")->__("You can't close RMA if refund is still processing. Please try again after refund completion.")
                );
            }

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


                /* @var $model ZolagoOs_Rma_Model_Rma_Comment */

                Mage::dispatchEvent("zolagorma_rma_comment_added", array(
                                        "rma"		=> $rma,
                                        "comment"	=> $model,
                                        "notify"	=> (bool)$notify
                                    ));

                $messages[] = Mage::helper("zolagorma")->__("Comment added");
            }

            if($notify) {
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
            $this->_getSession()->addError(Mage::helper("zolagorma")->__("There was a technical error. Please contact shop Administrator."));
        }

        return $this->_redirectReferer();
    }
    protected function _getTrackingNumber($carrier) {
        $manager = Mage::helper('orbashipping')->getShippingManager($carrier);
        $request = $this->getRequest();
        $vendor = $this->_getSession()->getVendor();
        $rma = $this->_registerRma();
        $settings = $manager->prepareRmaSettings($request,$vendor,$rma);
        if ($carrier == Orba_Shipping_Model_Carrier_Dhl::CODE) {
            $this->getRequest()->setParam("shipping_source_account", $settings["account"]);
            if (isset($settings["gallery_shipping_source"])
                    && ($settings["gallery_shipping_source"] == 1)
               ) {
                //Assign Client Number to Gallery Or To Vendor
                $this->getRequest()->setParam("gallery_shipping_source", 1);
            }

        }

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
                             "udropship_status"		=> ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_PENDING,
                             "next_check"			=> null, // what is this ?
                             "master_tracking_id"	=> null, // what is this ?
                             "package_count"			=> null, // what is this ?
                             "package_idx"			=> null, // what is this ?
                             "track_creator"			=> Zolago_Rma_Model_Rma_Track::CREATOR_TYPE_VENDOR,
                             "gallery_shipping_source" => $this->getRequest()->getParam("gallery_shipping_source", 0),
                             "shipping_source_account" => $this->getRequest()->getParam("shipping_source_account", 0),
                             "track_type" => GH_Statements_Model_Track::TRACK_TYPE_RMA_VENDOR
                         );


            $model = Mage::getModel('urma/rma_track')->
                     addData($trackData);
            $manager = Mage::helper('orbashipping')->getShippingManager($carrier);
            $type = $request->getParam('specify_orbadhl_rate_type',0);
            $manager->calculateCharge($model,$type,$this->_getSession()->getVendor(),$rma->getTotalValue(),0);

            $model->save();

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
            $this->_getSession()->addError(Mage::helper("zolagorma")->__("There was a technical error. Please contact shop Administrator."));
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
            $session->addError(Mage::helper("zolagorma")->__("There was a technical error. Please contact shop Administrator."));
        }

        return $this->_redirectReferer();
    }

    /**
     * @return Zolago_Rma_Model_Rma
     * @throws Mage_Core_Exception
     */
    protected function _registerRma() {
        $id = is_numeric($this->getRequest()->getParam('id')) ? $this->getRequest()->getParam('id') : false;

        if($id !== false &&
                   Mage::registry('current_rma') instanceof Zolago_Rma_Model_Rma &&
                   Mage::registry('current_rma')->getId() == $id) {
            return Mage::registry('current_rma');
        } else {
            Mage::unregister('current_rma');
        }

        /** @var Zolago_Rma_Model_Rma $rma */
        $rma = Mage::getModel("zolagorma/rma");
        $rma->load($id);

        if(!$rma->getId()) {
            throw new Mage_Core_Exception(Mage::helper('zolagorma')->__("This RMA does not exist."));
        }
        if(!$this->_validateRma($rma)) {
            throw new Mage_Core_Exception(Mage::helper('zolagorma')->__("This RMA is not yours."));
        } else {
            Mage::register('current_rma', $rma);
            return Mage::registry('current_rma');
        }
    }

    /**
     * Check if rma is valid. This means rma belongs to vendor or vendor children
     * @param Zolago_Rma_Model_Rma $rma
     * @return bool
     */
    protected function _validateRma(Zolago_Rma_Model_Rma $rma) {
        if(!$rma->getId()) {
            return false;
        }
        /** @var Zolago_Dropship_Model_Vendor $vendor */
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
