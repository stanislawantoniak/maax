<?php
class Zolago_Po_Model_Observer extends Zolago_Common_Model_Log_Abstract {
    // Force UDPO shippping addres, no mage order shipping address
    public function poShipmentSaveBefore($observer) {
        $shipments = $observer->getEvent()->getShipments();
        $po = $observer->getEvent()->getUdpo();
        /* @var $po Zolago_Po_Model_Po */
        foreach($shipments as $shipment) {
            /* @var $shipment Mage_Sales_Model_Order_Shipment */
            if($shipment->getShippingAddressId()!=$po->getShippingAddressId()) {
                $shipment->setShippingAddressId($po->getShippingAddressId());
            }
        }
    }

    /**
     * Clear biling data from shipping address
     * @param type $observer
     */
    public function quoteAddressSaveBefore($observer) {
        $address = $observer->getEvent()->getDataObject();
        /* @var $address Mage_Sales_Model_Quote_Address */
        if($address instanceof Mage_Sales_Model_Quote_Address) {
            if($address->getAddressType()==Mage_Sales_Model_Quote_Address::TYPE_SHIPPING) {
                $address->setNeedInvoice(0);
                $address->setVatId(null);
            }
        }
    }
    /**
     * Delete aggregated based shipment-related po
     * Clear current carrier from PO
     * @param type $observer
     */
    public function shipmentCancelAfter($observer) {
        $shipment = $observer->getEvent()->getData('shipment');
        if($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            $po = Mage::getModel("zolagopo/po")->load($shipment->getUdpoId());
            /* @var $po Zolago_Po_Model_Po */
            $aggregated = $po->getAggregated();
            if($aggregated->getId()) {
                if (!in_array($po->getCurrentCarrier(),array(Orba_Shipping_Model_Post::CODE))) {
                    
                    $aggregated->delete();
                } else {
                    $list = $aggregated->getPoCollection();
                    if (count($list) <= 1) {
                        $aggregated->delete(); // usuwamy ostatnią
                    }
                }
                $po->setAggregatedId(null);
                $po->getResource()->saveAttribute($po,'aggregated_id');
            }
            // Clear tracking
            $trackList = $shipment->getAllTracks();
            foreach ($trackList as $track) {
                $manager = Mage::helper('orbashipping')->getShippingManager($track->getCarrierCode());
                $manager->cancelTrack($track);
            }
            // Clear current carrier
            $po->setCurrentCarrier(null);
            $po->getResource()->saveAttribute($po, "current_carrier");
        }
    }

    /**
     * Split PO
     * @param type $observer
     */
    public function poSplit($observer) {
        $po = $observer->getEvent()->getData('po');
        /* @var $po Zolago_Po_Model_Po */
        $newPo = $observer->getEvent()->getData('new_po');
        /* @var $oldPos Zolago_Po_Model_Po */
        $itemIds = $observer->getEvent()->getData('item_ids');
        /* @var $newPos array */

        $header = Mage::helper('zolagopo')->__("PO Split (#%s&rarr;#%s)", $po->getIncrementId(), $newPo->getIncrementId());
        $poInfo = Mage::helper('zolagopo')->__("Items of #%s:", $po->getIncrementId()) .
                  "\n" . $this->_getPoItemsText($po);
        $newPoInfo = Mage::helper('zolagopo')->__("Items of #%s:", $newPo->getIncrementId()) .
                     "\n" . $this->_getPoItemsText($newPo);

        $text = trim($header . "\n" . $poInfo . "\n" . $newPoInfo);

        $this->_logEvent($po, $text);
        $this->_logEvent($newPo, $text);
    }

    /**
     * Split payment from old po and allocate rest of money to new po
     */
    public function paymentSplit($observer) {
        /* @var $po Zolago_Po_Model_Po */
        /* @var $newPo Zolago_Po_Model_Po */

        $po = $observer->getEvent()->getData('po');
        $newPo = $observer->getEvent()->getData('new_po');

        /** @var Zolago_Payment_Model_Allocation $allocModel */
        $allocModel = Mage::getModel("zolagopayment/allocation");
        $allocModel->createOverpayment($po);

        /** @var Zolago_Payment_Model_Allocation $c */
        $coll = $allocModel->getPoOverpayments($newPo);
        foreach ($coll as $c) {
            $allocModel->allocateOverpayment($newPo, $c->getTransactionId());
        }
    }

    /**
     *
     * @param Zolago_Po_Model_Po $po
     * @return string
     */
    protected function _getPoItemsText(Zolago_Po_Model_Po $po) {
        $items = array();
        foreach($po->getAllItems() as $item) {
            /* @var $item Zolago_Po_Model_Po_Item */
            if(!$item->getParentItemId()) {
                $items[] = $item->getOneLineDesc();
            }
        }
        return implode("\n", $items);
    }


    /**
     * Change pos
     * @param type $observer
     */
    public function poChangePos($observer) {
        $po = $observer->getEvent()->getData('po');
        /* @var $po Zolago_Po_Model_Po */
        $oldPos = $observer->getEvent()->getData('old_pos');
        /* @var $oldPos Zolago_Pos_Model_Pos */
        $newPos = $observer->getEvent()->getData('new_pos');
        /* @var $newPos Zolago_Pos_Model_Pos */

        $text = Mage::helper('zolagopo')->__(
                    "Changed POS (%s&rarr;%s)", $oldPos->getExternalId(), $newPos->getExternalId());

        $this->_logEvent($po, $text);

        // Send email
        Mage::helper('udpo')->sendNewPoNotificationEmail($po);
        Mage::helper('udropship')->processQueue();
    }



    /**
     * send email
     */
    protected function _sendShippedEmail($po) {
        /** @var Zolago_Po_Helper_Data $helper */
        $helper = Mage::helper("zolagopo");
        // Register for use by email template block
        if (Mage::registry('current_po')) {
            Mage::unregister('current_po');
        }
        Mage::register('current_po', $po);
        // Do send email
        $tracking = $po->getTracking();
        $udropshipMethod = $po->getUdropshipMethod();
        $shippingMethod = Mage::helper("udropship")->getOmniChannelMethodInfoByMethod(0, $udropshipMethod);
        $deliveryCode = $shippingMethod->getDeliveryCode();
        $inpostLabel = $pwrLabel = 0;
        if($deliveryCode == GH_Inpost_Model_Carrier::CODE) {
            $inpostLabel = 1;
        }
        if($deliveryCode == Orba_Shipping_Model_Packstation_Pwr::CODE) {
            $pwrLabel = 1;
        }
        $params = array(
                      "tracking" => $tracking,
                      "track_url"=> $po->getTrackingUrl($tracking),
                      "contact_url"=> Mage::getUrl("help/contact/vendor", array(
                                  "vendor"=>$po->getVendor()->getId(),
                                  "po"=>$po->getId()
                              )),
                      "inpost_delivery" => $inpostLabel,
                      "pwr_delivery" => $pwrLabel,
                      "_ATTACHMENTS" => $helper->getPoImagesAsAttachments($po)
                  );
        $po->sendEmailTemplate(
            Zolago_Po_Model_Po::XML_PATH_UDROPSHIP_PURCHASE_ORDER_STATUS_CHANGED_SHIPPED,
            $params
        );

    }


    /**
     * automatic refund all payment (without allocations)
     */

    protected function _refundAll($po) {
        $amount = $po->getPaymentAmount();
        return $this->_refundPayment($po,$amount);

    }
    protected function _refundOverpayment($po) {
        $debtAmount = $po->getDebtAmount();
        return $this->_refundPayment($po,$debtAmount);
    }
    /**
     * automatic refund  on simple payments (without allocations)
     */
    protected function _refundPayment($po,$amount) {
        $store = $po->getStore();
        if (Mage::helper('zolagopayment')->getConfigUseAllocation($store)) {
            return;
        }
        if ($amount > 0) {
            // create refund
            $order = $po->getOrder();
            $customerId = $po->getCustomerId();

            try {
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                /* @var $connection Varien_Db_Adapter_Interface */
                $connection->beginTransaction();
                if ($result = Mage::helper('zolagosales/transaction')->createRefundTransaction($order,$customerId,-$amount)) {
                    $po->addComment(Mage::helper('zolagopayment')->__('[%s] Create overpayment refund on %s','Automat',$po->getCurrencyFormattedAmount($amount)),false,true);
                } else {
                    $po->addComment(Mage::helper('zolagopayment')->__('[%s] Refund not created','Automat'),false,true);
                }
                $connection->commit();
            } catch (Exception $xt) {
                Mage::logException($xt);
                $po->addComment(Mage::helper('zolagopayment')->__('[%s] Error at create overpayment refund. Please contact with administrators.','Automat'),false,true);
                $connection->rollback();
            }
            $po->saveComments();
        }

    }
    /**
     * Status change
     * @param Mage_Core_Model_Observer $observer
     */
    public function poChangeStatus($observer) {
        /* @var $po Zolago_Po_Model_Po */
        $po = $observer->getEvent()->getData('po');
        if($po instanceof Zolago_Po_Model_Po && $po->getId()) {
            $oldStatus = $observer->getEvent()->getOldStatus();
            $newStatus = $observer->getEvent()->getNewStatus();
            // Status changed to shipped
            if($oldStatus!=$newStatus) {
                switch ($newStatus) {
                case Zolago_Po_Model_Po_Status::STATUS_SHIPPED:
                case Zolago_Po_Model_Po_Status::STATUS_TO_PICK:
                    $this->_sendShippedEmail($po);
                    $this->_refundOverpayment($po);
                    break;
                case Zolago_Po_Model_Po_Status::STATUS_DELIVERED:
                    $this->_refundOverpayment($po);
                    break;
                case Zolago_Po_Model_Po_Status::STATUS_CANCELED:
                    $this->_refundAll($po);
                    break;
                case Zolago_Po_Model_Po_Status::STATUS_EXPORTED:
                    $this->_checkZipAlert($po);
                    break;
                default:
                    ;

                }
            }
        }
    }

    /**
     * read zip databaze one again
     */

    public function _checkZipAlert($po) {
        $alert = $po->getAlert();
        if ($alert | Zolago_Po_Model_Po_Alert::ALERT_DHL_ZIP_CHECKING) {
            $this->_zipAlertUpdate($po);
        }
    }
    //
    public function updatePoStatusByAllocation($observer) {
        /* @var $po Zolago_Po_Model_Po */
        $po = $observer->getEvent()->getData('po');
        if(!$po->getId()) {
            $po = $observer->getPo();
        }

        if($po->getId()) {
            /** @var Zolago_Po_Model_Po_Status $statusModel */
            $statusModel = Mage::getSingleton("zolagopo/po_status");
            $statusModel->updateStatusByAllocation($po);

        }
    }

    public function addAllocationComment($observer) {
        /** @var Zolago_Po_Helper_Data $hlp */
        $hlp = Mage::helper("zolagopo");

        /* @var $oldPo Zolago_Po_Model_Po */
        $oldPo = $observer->getEvent()->getData('oldPo');
        if(!$oldPo->getId()) {
            $oldPo = $observer->getPo();
        }
        /* @var $oldPo Zolago_Po_Model_Po */
        $newPo = $observer->getEvent()->getData('newPo');
        if(!$newPo->getId()) {
            $newPo = $observer->getPo();
        }

        if($newPo->getId()) {
            $hlp->addAllocationComment(
                $oldPo,
                $newPo,
                false,
                true,
                $observer->getEvent()->getData('operator_id'),
                $observer->getEvent()->getData('amount'));
        }
    }

    public function addOverpayComment($observer) {
        /** @var Zolago_Po_Helper_Data $hlp */
        $hlp = Mage::helper("zolagopo");

        /* @var $po Zolago_Po_Model_Po */
        $po = $observer->getEvent()->getData('po');
        if(!$po->getId()) {
            $po = $observer->getPo();
        }

        if($po->getId()) {
            $hlp->addOverpayComment($po, false, true, $observer->getEvent()->getData('operator_id'), $observer->getEvent()->getData('amount'));
        }
    }

    public function addPickUpPaymentComment($observer) {
        /** @var Zolago_Po_Helper_Data $hlp */
        $hlp = Mage::helper("zolagopo");
        /* @var $po Zolago_Po_Model_Po */
        $po = $observer->getEvent()->getData('po');
        $amount = $observer->getEvent()->getData('amount');

        if($po->getId()) {
            $hlp->addPickUpPaymentComment($po, $amount);
        }
    }

    /**
     * PO Compose
     * @param type $observer
     */
    public function poCompose($observer) {
        $po = $observer->getEvent()->getData('po');
        /* @var $po Zolago_Po_Model_Po */
        $message = $observer->getEvent()->getData('message');
        $recipient = $observer->getEvent()->getData('recipient');

        $text = Mage::helper('zolagopo')->__("Message send to %s: %s", $recipient, $message);

        $this->_logEvent($po, $text);
    }

    /**
     * PO Item edit
     * @param type $observer
     */
    public function poItemEdit($observer) {
        $po = $observer->getEvent()->getData('po');
        /* @var $po Zolago_Po_Model_Po */
        $oldItem = $observer->getEvent()->getData('old_item');
        /* @var $oldItem Zolago_Po_Model_Po_Item */
        $newItem = $observer->getEvent()->getData('new_item');
        /* @var $newItem Zolago_Po_Model_Po_Item */

        $changeLog = array();

        if($oldItem->getPriceInclTax()!=$newItem->getPriceInclTax()) {
            $changeLog[] = Mage::helper('zolagopo')->__("Price") . ": " .
                           $po->getStore()->formatPrice($oldItem->getPriceInclTax(), false) .
                           "&rarr;" .
                           $po->getStore()->formatPrice($newItem->getPriceInclTax(), false);
        }

        if($oldItem->getDiscountAmount()!=$newItem->getDiscountAmount()) {
            $changeLog[] = Mage::helper('zolagopo')->__("Discount") . ": " .
                           $po->getStore()->formatPrice($oldItem->getDiscountAmount(), false) .
                           "&rarr;" .
                           $po->getStore()->formatPrice($newItem->getDiscountAmount(), false);
        }

        if($oldItem->getQty()!=$newItem->getQty()) {
            $changeLog[] = Mage::helper('zolagopo')->__("Qty") . ": " .
                           (int)$oldItem->getQty() . "&rarr;" . (int)$newItem->getQty();
        }

        if($changeLog) {
            $text = Mage::helper('zolagopo')->__("Item changed %s (%s)", $newItem->getName(), implode(", ", $changeLog));
            $this->_logEvent($po, $text);
        }
    }

    /**
     * PO Item Add
     * @param type $observer
     */
    public function poItemAdd($observer) {
        $po = $observer->getEvent()->getData('po');
        /* @var $po Zolago_Po_Model_Po */
        $item = $observer->getEvent()->getData('item');
        /* @var $item Zolago_Po_Model_Po_Item */

        $text = Mage::helper('zolagopo')->__("Item added %s", $this->_getItemText($item));

        $this->_logEvent($po, $text);
    }

    /**
     * PO Item Remove
     * @param type $observer
     */
    public function poItemRemove($observer) {
        $po = $observer->getEvent()->getData('po');

        $item = $observer->getEvent()->getData('item');
        /* @var $item Zolago_Po_Model_Po_Item */

        $text = Mage::helper('zolagopo')->__("Item removed %s", $this->_getItemText($item, $po));

        $this->_logEvent($po, $text);
    }

    /**
     * PO Shipping Cost
     * @param type $observer
     */
    public function poShippingCost($observer) {
        $po = $observer->getEvent()->getData('po');
        /* @var $po Zolago_Po_Model_Po */
        $newPrice = $observer->getEvent()->getData('new_price');
        $oldPrice = $observer->getEvent()->getData('old_price');

        if((float)$newPrice!=(float)$oldPrice) {
            $text = Mage::helper('zolagopo')->__(
                        "Shipping cost changed (%s&rarr;%s)",
                        $po->getStore()->formatPrice($oldPrice,false),
                        $po->getStore()->formatPrice($newPrice,false)
                    );
            $this->_logEvent($po, $text);
        }
    }
    /**
     * PO Address Changed
     * @param type $observer
     */
    public function poAddressRestore($observer) {
        $po = $observer->getEvent()->getData('po');
        /* @var $po Zolago_Po_Model_Po */
        $type = $observer->getEvent()->getData('type');
        if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
            $type = Mage::helper('zolagopo')->__("shipping");
        } else {
            $type = Mage::helper('zolagopo')->__("billing");
        }

        $text = Mage::helper('zolagopo')->__("Origin %s address restored", $type);
        $this->_logEvent($po, $text);
    }

    /**
     * PO Shipping Method Changed
     * @param type $observer
     */
    public function poShippingMethodChange($observer) {
        $po = $observer->getEvent()->getData('po');
        /* @var $po Zolago_Po_Model_Po */

        $newUdropshipMethod = $observer->getEvent()->getData('new_udropship_method');
        $oldUdropshipMethod = $observer->getEvent()->getData('old_udropship_method');

        if(
            !empty($newUdropshipMethod)
            && ($newUdropshipMethod !== $oldUdropshipMethod)
        ) {
            $storeId =$po->getStoreId();
            $omniChannelMethodInfoByMethodNew = Mage::helper("udropship")
                                                ->getOmniChannelMethodInfoByMethod($storeId, $newUdropshipMethod, true);

            $omniChannelMethodInfoByMethodOld = Mage::helper("udropship")
                                                ->getOmniChannelMethodInfoByMethod($storeId, $oldUdropshipMethod, true);

            $message =  Mage::helper('zolagopo')->__('Shipping method changed: ') . $omniChannelMethodInfoByMethodOld['shipping_title'] . "&rarr;" . $omniChannelMethodInfoByMethodNew['shipping_title'];
            $this->_logEvent($po, $message, false);
        }

    }


    /**
     * PO Address Changed
     * @param type $observer
     */
    public function poAddressChange($observer) {
        $po = $observer->getEvent()->getData('po');
        /* @var $po Zolago_Po_Model_Po */

        $newAddress = $observer->getEvent()->getData('new_address');
        /* @var $newAddress Mage_Sales_Model_Order_Address */
        $oldAddress = $observer->getEvent()->getData('old_address');
        /* @var $oldAddress Mage_Sales_Model_Order_Address */

        $type =  $observer->getEvent()->getData('type');

        $hlp = Mage::helper("zolagopo");

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

        if($changeLog) {
            if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
                $text = Mage::helper('zolagopo')->__("Shipping address changed (%s)", implode(", " , $changeLog));
            } else {
                $text = Mage::helper('zolagopo')->__("Billing address changed (%s)", implode(", " , $changeLog));
            }
            $this->_logEvent($po, $text);
        }
    }

    public function poDeliveryAddressInpostChange($observer) {
        /** @var Zolago_Dropship_Helper_Data $udropshipHlp */
        $udropshipHlp = Mage::helper('udropship');
        /* @var $po Zolago_Po_Model_Po */
        $po = $observer->getEvent()->getData('po');
        $hlp = Mage::helper("zolagopo");
        $address = $udropshipHlp->formatCustomerAddressInpost($po, true, ', ');
        $text = $hlp->__("InPost shipping address changed (%s)", $address);
        $this->_logEvent($po, $text);
    }

    /**
     * PO Manually Create RMA
     * @param type $observer
     */
    public function poManuallyRma($observer) {
        $po = $observer->getEvent()->getData('po');
        /* @var $po Zolago_Po_Model_Po */

        $text = Mage::helper('zolagopo')->__("RMA created successfully");
        $this->_logEvent($po, $text);
    }

    /**
     * @param Zolago_Po_Model_Po_Item $item
     * @return string
     */
    protected function _getItemText(Zolago_Po_Model_Po_Item $item) {
        return $item->getOneLineDesc();
    }

    /**
     * @param ZolagoOs_OmniChannelPo_Model_Po $po
     * @param string $comment
     */
    protected function _logEvent($po, $comment) {
        $session = Mage::getSingleton('udropship/session');
        /* @var $session Zolago_Dropship_Model_Session */
        $vendor = $session->getVendor();
        $operator = $session->getOperator();

        if($session->isOperatorMode()) {
            $fullname = $vendor->getVendorName()  . " / " . $operator->getEmail();
        } else {
            $fullname = $vendor->getVendorName();
        }

        $po->addComment("[" . $fullname . "] "  . $comment, false, true);
        $po->saveComments();
    }


    /**
     * cancel orders with all po canceled
     */
    public function cronCancelOrders() {
        $order_collection = Mage::getModel('sales/order')->getCollection();
        $order_collection->addFieldToFilter('state',
                                            array('in'=>array (
                                                    Mage_Sales_Model_Order::STATE_NEW,
                                                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                                                    Mage_Sales_Model_Order::STATE_PROCESSING,
                                                    Mage_Sales_Model_Order::STATE_HOLDED,
                                                    Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
                                                    )
                                                 )
                                           );
        $time = Mage::getSingleton('core/date')->timestamp();
        $order_collection->addFieldToFilter("updated_at",array("lt"=>date('Y-m-d H:i:s',$time-24*3600)));
        $cancel = array();
        foreach ($order_collection as $order) {
            $collection = Mage::getResourceModel("udpo/po_collection");
            /* @var $collection ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Collection */
            $collection->addFieldToFilter("order_id", $order->getId());
            $collection->addFieldToFilter("udropship_status",
                                          array("nin"=>Zolago_Po_Model_Po_Status::STATUS_CANCELED)
                                         );
            if (!count($collection)) {
                // check date of canceled
                $collection = Mage::getResourceModel("udpo/po_collection");
                /* @var $collection ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Collection */
                $collection->addFieldToFilter("order_id", $order->getId());
                $collection->addFieldToFilter("updated_at",array('lt'=>date('Y-m-d H:i:s',$time-24*3600)));
                if (!count($collection)) {
                    $cancel[] = $order;
                }
            }
        }

        foreach ($cancel as $order) {
            $order->cancel()
            ->save();
        }
    }

    /**
     * Adding message NEW_ORDER to GH_API queue
     *
     * @param $observer
     */
    public function ghapiAddMessageNewOrder($observer) {
        $queue = Mage::getSingleton('ghapi/message');
        $po = $observer->getPo();
        if (!$po->getDefaultPosId()) {
            // no pos - no new order message
            return;
        }
        $vendor = $po->getVendor();
        $ghapiAccess = $vendor->getData('ghapi_vendor_access_allow');

        if ($ghapiAccess) {
            $queue->addMessage($po, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_NEW_ORDER);
        }
    }

    /**
     * Adding messages (CANCELLED_ORDER | STATUS_CHANGED) to GH_API queue
     *
     * @param $observer
     */
    public function ghapiAddMessageCancelledOrChanged($observer) {
        $queue = Mage::getSingleton('ghapi/message');
        $po = $observer->getPo();
        $oldStatus = $observer->getOldStatus();
        $newStatus = $observer->getNewStatus();
        $vendor = $po->getVendor();
        $ghapiAccess = $vendor->getData('ghapi_vendor_access_allow');

        /** @var Zolago_Po_Model_Po_Status $modelPoStatus */
        $modelPoStatus = Mage::getSingleton('zolagopo/po_status');
        $ghapiOldStatus = $modelPoStatus->ghapiOrderStatus($oldStatus);
        $ghapiNewStatus = $modelPoStatus->ghapiOrderStatus($newStatus);

        if ($ghapiNewStatus != $ghapiOldStatus) {
            // Inform only when status change for GH API getOrdersByID->order_status
            if ($ghapiAccess) {
                if ($newStatus == Zolago_Po_Model_Po_Status::STATUS_CANCELED) {
                    $msg = GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_CANCELLED_ORDER;
                } else {
                    $msg = GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_STATUS_CHANGED;
                }
                $queue->addMessage($po, $msg);
            }
        }
    }
    public function setOrderState($observer) {
        $po = $observer->getPo();
        Mage::getModel('udpo/po')
        ->setOrderState($po);
    }
    public function setOrderReservationOnSave($observer)
    {
        if(Mage::registry('GHAPI') === true) {
            return;
        }
        $po = $observer->getPo();
        $newStatus = (int)$po->getUdropshipStatus();
        $poOpenOrder = Mage::getStoreConfig('zolagocatalog/config/po_open_order');
        //Mage::log($newStatus, null, 'setOrderReservationOnSave.log');
        //Mage::log($poOpenOrder, null, 'setOrderReservationOnSave.log');
        if (in_array($newStatus, explode(',', $poOpenOrder))) {
            //set reservation=1
            $po->setReservation(0);
            $po->getResource()->saveAttribute($po, 'reservation');
        }

    }
    public function setOrderReservation($observer)
    {
        $po = $observer->getPo();

        $newStatus = $observer->getNewStatus();
        $poOpenOrder = Mage::getStoreConfig('zolagocatalog/config/po_open_order');
        if (in_array($newStatus, explode(',', $poOpenOrder))) {
            //set reservation=1
            $po->setReservation(0);
            $po->getResource()->saveAttribute($po, 'reservation');
        }

    }
    /**
     * Adding messages ITEMS_CHANGED to GH_API queue
     *
     * @param $observer
     */
    public function ghapiAddMessageItemsChanged($observer) {
        $queue = Mage::getSingleton('ghapi/message');
        $po    = $observer->getPo();
        $vendor = $po->getVendor();
        $ghapiAccess = $vendor->getData('ghapi_vendor_access_allow');

        if ($ghapiAccess) {
            $queue->addMessage($po, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_ITEMS_CHANGED);
        }
    }

    /**
     * Adding messages ( DELIVERY_DATA_CHANGED | INVOICE_ADDRESS_CHANGED ) to GH_API queue
     *
     * @param $observer
     */
    public function ghapiAddMessageDeliveryOrInvoice($observer) {
        $queue = Mage::getSingleton('ghapi/message');
        $po    = $observer->getPo();
        $type  = $observer->getType();
        $vendor = $po->getVendor();
        $ghapiAccess = $vendor->getData('ghapi_vendor_access_allow');

        if ($ghapiAccess) {
            if ($type == Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
                $queue->addMessage($po, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_DELIVERY_DATA_CHANGED);
            }
            elseif ($type == Mage_Sales_Model_Order_Address::TYPE_BILLING) {
                $queue->addMessage($po, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_INVOICE_ADDRESS_CHANGED);
            }
        }
    }

    /**
     * Adding messages PAYMENT_DATA_CHANGED to GH_API queue
     *
     * @param $observer
     */
    public function ghapiAddMessagePaymentDataChanged($observer) {
        $queue = Mage::getSingleton('ghapi/message');
        $po    = $observer->getPo();
        $oldPo = $observer->getOldPo();
        $newPo = $observer->getNewPo();

        if ($po) {
            $vendor = $po->getVendor();
            $ghapiAccess = $vendor->getData('ghapi_vendor_access_allow');
            if ($ghapiAccess) {
                $queue->addMessage($po, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_PAYMENT_DATA_CHANGED);
            }

        }
        elseif ($oldPo && $newPo) {
            // By allocateOverpayment

            // For vendor from old PO
            $vendorForOld = $oldPo->getVendor();
            $ghapiAccessForOld = $vendorForOld->getData('ghapi_vendor_access_allow');
            if ($ghapiAccessForOld) {
                $queue->addMessage($oldPo, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_PAYMENT_DATA_CHANGED);
            }

            // For vendor from new PO
            $vendorForNew = $newPo->getVendor();
            $ghapiAccessForNew = $vendorForNew->getData('ghapi_vendor_access_allow');
            if ($ghapiAccessForNew) {
                $queue->addMessage($newPo, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_PAYMENT_DATA_CHANGED);
            }
        }
    }


    /**
     * Dhl zip validation
     * @param $observer
     */
    public function poAlertUpdate($observer)
    {
        $po = $observer->getPo();
        $this->_zipAlertUpdate($po);
    }

    /**
     * dhl zip validation
     */

    protected function _zipAlertUpdate($po) {
        $alert = $po->getAlert();
        $shippingId = $po->getShippingAddressId();
        $address = Mage::getModel('sales/order_address')->load($shippingId);
        $dhlEnabled = Mage::helper('core')->isModuleEnabled('Zolago_Dhl');
        $dhlActive = Mage::helper('orbashipping/carrier_dhl')->isActive();
        if ($dhlEnabled && $dhlActive) {
            $dhlHelper = Mage::helper('orbashipping/carrier_dhl');
            $dhlValidZip = $dhlHelper
                           ->isDHLValidZip($address->getCountry(), $address->getPostcode());

            if (!$dhlValidZip) {
                $alert |= Zolago_Po_Model_Po_Alert::ALERT_DHL_ZIP_CHECKING;

            } else {
                $alert &= ~Zolago_Po_Model_Po_Alert::ALERT_DHL_ZIP_CHECKING;

            }

            $po->setAlert($alert);
            $po->getResource()->saveAttribute($po, "alert");

        }
    }
}
