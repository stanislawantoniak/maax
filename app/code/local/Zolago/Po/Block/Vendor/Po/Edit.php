<?php

class Zolago_Po_Block_Vendor_Po_Edit extends Zolago_Po_Block_Vendor_Po_Info
{
    public function __construct(array $args = array()) {
        parent::__construct($args);
        $this->_prepareShipments();
    }


    /**
     * @param Zolago_Po_Model_Po $po
     * @return array
     */
    public function getAlerts(Zolago_Po_Model_Po $po) {

        $alert = array();

        /** @var Zolago_Po_Model_Po_Alert $alertModel */
        $alertModel = Mage::getModel("zolagopo/po_alert");

        foreach($alertModel->getAllOptions() as $alertBit=>$text) {
            if($po->getAlert() & $alertBit) {
                $link = false;
                switch($alertBit) {
                case $alertModel::ALERT_SAME_EMAIL_PO:
                    $filter = "customer_fullname=" . $po->getData("customer_email") . "&udropship_status=";
                    $link = $this->getUrl("udpo/vendor/index", array("filter" => Mage::helper('core')->urlEncode($filter)));
                    break;
                }
                $alert[] = array(
                               "text"=>$this->__(
                                   $alertModel->getAlertText($alertBit),
                                   ($link ? '<a href="'.$link.'">' . $this->__("link") . '</a>' : null)
                               ),
                               "class" => "danger"
                           );
            }
        }



        if($po->getStatusModel()->isConfirmStockAvailable($po)) {
            if(!$po->getStockConfirm()) {
                $alert[] = array(
                               "text" => '<i class="icon-warning"></i> '  . $this->__("Product reservation not yet confirmed!"),
                               "class"=> "danger"
                           );
            } else {
                $alert[] = array(
                               "text" => '<i class="icon-barcode"></i> ' . $this->__("Items reserved"),
                               "class"=> "success"
                           );
            }
        } else {
            if($po->getStockConfirm()) {
                $alert[] = array(
                               "text" => '<i class="icon-barcode"></i> ' . $this->__("Items reserved"),
                               "class"=> "success"
                           );
            }
        }
        return $alert;
    }




    /**
     *
     * @param type $trackign
     * @param type $shipment
     * @return string
     */
    public function getLetterUrl(Mage_Sales_Model_Order_Shipment_Track $tracking, Zolago_Po_Model_Po $po) {
        if($this->isLetterable($tracking)) {
            if (!$url = Mage::helper('orbashipping')->getShippingManager($tracking->getCarrierCode())->getLetterUrl()) {
                Mage::throwException('Wrong carrier code');
            }
            return $this->getUrl($url, array(
                                     'trackId'		=> $tracking->getId(),
                                     'trackNumber'	=> $tracking->getNumber(),
                                     'vId'			=> $po->getVendor()->getId(),
                                     'posId'			=> $po->getDefaultPosId(),
                                     'udpoId'		=> $po->getId(),
                                     '_secure'		=>true
                                 ));
        }
        return null;
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment_Track $tracking
     * @return boolean
     */
    public function isLetterable(Mage_Sales_Model_Order_Shipment_Track $tracking) {
        $manager = Mage::helper('orbashipping')->getShippingManager($tracking->getCarrierCode());
        return $manager->isLetterable();
    }

    /**
     * @return Zolago_Po_Model_Po
     */
    public function getPo() {
        return parent::getPo();
    }

    /**
     * @param ZolagoOs_OmniChannelPo_Model_Po $po
     * @return string
     */
    public function getCurrentStatus(ZolagoOs_OmniChannelPo_Model_Po $po) {
        return Mage::helper("udpo")->getPoStatusName($this->getPo()->getUdropshipStatus());
    }

    public function isShippignLetterFile($trackingNo) {
        return Mage::helper("orbashipping/carrier_dhl")->getIsDhlFileAvailable($trackingNo);
    }

    public function getAllStatuses() {

    }

    /**
     * @param ZolagoOs_OmniChannelPo_Model_Po $po
     * @return Zolago_Pos_Model_Pos
     */
    public function getPos(ZolagoOs_OmniChannelPo_Model_Po $po) {
        return $po->getPos();
    }

    /**
     * @return array
     */
    public function getAllowedStatuses() {
        return Mage::helper('udpo')->getVendorUdpoStatuses();
    }

    /**
     * @param Zolago_Po_Model_Po $po
     * @return array
     */
    public function getAllowedStatusesForPo(Zolago_Po_Model_Po $po) {
        return $po->getStatusModel()->getAvailableStatus($po);
    }


    public function isManulaStatusAvailable(Zolago_Po_Model_Po $po) {
        return $po->getStatusModel()->isManulaStatusAvailable($po);
    }

    /**
     * @return bool
     */
    public function isRemovable() {
        if(!$this->hasData('is_removable')) {
            $removable = false;
            $i = 0;
            foreach($this->getPo()->getAllItems() as $item) {
                if(is_null($item->getOrderItem()->getParentItemId())) {
                    $i++;
                }
            }
            $this->setData('is_removable', $i>1);
        }
        return $this->getData('is_removable');
    }

    /**
     * @param ZolagoOs_OmniChannelPo_Model_Po_Item $item
     * @return Zolago_Po_Block_Vendor_Po_Item_Renderer_Abstract
     */
    public function	getItemRedener(ZolagoOs_OmniChannelPo_Model_Po_Item $item) {
        $orderItem = $item->getOrderItem();
        $type=$orderItem->getProductType();
        return $this->_getRendererByType($type)->
               setItem($item)->
               setParentBlock($this)->
               setIsRemovable($this->isRemovable())->
               setIsEditable($this->isEditable());

    }

    /**
     * @return void
     */
    protected function _prepareShipments() {
        $_po = $this->getPo();
        $_order = $_po->getOrder();
        $_vendor = $this->getVendor();
        $_hlp = Mage::helper('udropship');
        $shipping = $_hlp->getShippingMethods();
        $vShipping = $_vendor->getShippingMethods();

        $poShippingMethod = $_po->getUdropshipMethod();
        if (null == $poShippingMethod) {
            $poShippingMethod = $_order->getShippingMethod();
        }

        $uMethod = explode('_', $_order->getShippingMethod(), 2);
        if ($uMethod[0]=='udsplit') {
            $udMethod = Mage::helper('udropship')->mapSystemToUdropshipMethod(
                            $poShippingMethod,
                            $_vendor
                        );
            $uMethodCode = $udMethod->getShippingCode();
        } else {
            $uMethodCode = !empty($uMethod[1]) ? $uMethod[1] : '';
        }

        $method = explode('_', $poShippingMethod, 2);
        $carrierCode = !empty($method[0]) ? $method[0] : $_vendor->getCarrierCode();
        $curShipping = $shipping->getItemByColumnValue('shipping_code', $uMethodCode);
        $methodCode  = !empty($method[1]) ? $method[1] : '';

        $labelCarrierAllowAll = Mage::getStoreConfig('udropship/vendor/label_carrier_allow_all', $_order->getStoreId());
        $labelMethodAllowAll = Mage::getStoreConfig('udropship/vendor/label_method_allow_all', $_order->getStoreId());

        $availableMethods = array();
        if ($curShipping && $labelMethodAllowAll) {
            $curShipping->useProfile($_vendor);
            $_carriers = array($carrierCode=>0);
            if ($labelCarrierAllowAll) {
                $_carriers = array_merge($_carriers, $curShipping->getAllSystemMethods());
            }
            foreach ($_carriers as $_carrierCode=>$_dummy) {
                $_availableMethods = $_hlp->getCarrierMethods($_carrierCode, true);
                $carrierTitle = Mage::getStoreConfig("carriers/$_carrierCode/title", $_order->getStoreId());
                foreach ($_availableMethods as $mCode => $mLabel) {
                    $_amDesc = $carrierTitle.' - '.$mLabel;
                    $_amCode = $_carrierCode.'_'.$mCode;
                    $availableMethods[$_amCode] = $_amDesc;
                }
            }
            $curShipping->resetProfile();
        }
        elseif ($curShipping && isset($vShipping[$curShipping->getId()])) {
            $curShipping->useProfile($_vendor);
            $methodCode  = !empty($method[1]) ? $method[1] : $curShipping->getSystemMethods($vShipping[$curShipping->getId()]['carrier_code']);
            $availableMethods = array();
            if (!$labelCarrierAllowAll || Mage::helper('udropship')->isUdsprofileActive()) {
                foreach ($vShipping as $_sId => $__vs) {
                    foreach ($__vs as $_vs) {
                        if ($carrierCode != $_vs['carrier_code'] && !$labelCarrierAllowAll || !($_s = $shipping->getItemById($_sId)) || !($_vs['method_code'])) continue;
                        $_amCode = $_vs['carrier_code'].'_'.$_vs['method_code'];
                        $carrierMethods = Mage::helper('udropship')->getCarrierMethods($_vs['carrier_code']);
                        if (!isset($carrierMethods[$_vs['method_code']])) continue;
                        $_amDesc = Mage::getStoreConfig('carriers/'.$_vs['carrier_code'].'/title', $_order->getStoreId())
                                   .' - '.$carrierMethods[$_vs['method_code']];
                        $availableMethods[$_amCode] = $_amDesc;
                    }
                }
            } else {
                foreach ($vShipping as $_sId => $__vs) {
                    if (($_s = $shipping->getItemById($_sId))) {
                        $allSystemMethods = $_s->getAllSystemMethods();
                        foreach ($allSystemMethods as $_smCarrier => $__sm) {
                            foreach ($__sm as $_smMethod) {
                                $_amCode = $_smCarrier.'_'.$_smMethod;
                                $carrierMethods = Mage::helper('udropship')->getCarrierMethods($_smCarrier);
                                if (!isset($carrierMethods[$_smMethod])) continue;
                                $_amDesc = Mage::getStoreConfig('carriers/'.$_smCarrier.'/title', $_order->getStoreId())
                                           .' - '.$carrierMethods[$_smMethod];
                                $availableMethods[$_amCode] = $_amDesc;
                            }
                        }
                    }
                }
            }
            $curShipping->resetProfile();
        }

        $labelCarrierAllowAlways = Mage::getStoreConfig('udropship/vendor/label_carrier_allow_always', $_order->getStoreId());
        if (!is_array($labelCarrierAllowAlways)) {
            $labelCarrierAllowAlways = array_filter(explode(',', $labelCarrierAllowAlways));
        }
        foreach ($labelCarrierAllowAlways as $lcaaCode) {
            $lcaaCarrierMethods = Mage::helper('udropship')->getCarrierMethods($lcaaCode, true);
            foreach ($lcaaCarrierMethods as $lcaaMethodCode=>$lcaaMethodTitle) {
                $lcaaFullMethodCode = $lcaaCode.'_'.$lcaaMethodCode;
                $lcaaDesc = Mage::getStoreConfig('carriers/'.$lcaaCode.'/title', $_order->getStoreId())
                            .' - '.$lcaaMethodTitle;
                $availableMethods[$lcaaFullMethodCode] = $lcaaDesc;
            }
        }

        if (count($method)>1) {
            $_poCarrierMethods = Mage::helper('udropship')->getCarrierMethods($method[0]);
            if (isset($_poCarrierMethods[$method[1]])) {
                $availableMethods[$poShippingMethod] = Mage::getStoreConfig('carriers/'.$method[0].'/title', $_order->getStoreId())
                                                       .' - '.$_poCarrierMethods[$method[1]];
            }
        }
        $this->setAvailableMethods($availableMethods);
        $this->setCurrentShipping($curShipping);
        $this->setShippingMethod($poShippingMethod);

        $collection =  Mage::getResourceModel('sales/order_shipment_collection')->
                       addAttributeToFilter('udpo_id', $_po->getId())->
                       addAttributeToFilter("udropship_status",
                                            array("nin"=>array(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_CANCELED))
                                           );

        $collection->setOrder("created_at", "DESC");
        $this->setShipmentsCollection($collection);

        /** @var Mage_Sales_Model_Order_Shipment $customCurrentShipping */
        $customCurrentShipping = $collection->getFirstItem();
        $this->setCustomCurrentShipping($customCurrentShipping);
    }

    public function getCurrentTracking(Mage_Sales_Model_Order_Shipment $shipment = null) {
        if($shipment instanceof  Mage_Sales_Model_Order_Shipment && $shipment->getId()) {
            $collection = $shipment->getTracksCollection()->setOrder("created_at", "DESC");
            return $collection->getFirstItem();
        }
        return null;
    }

    public function canUseCarrier() {
        return Mage::helper('orbashipping')->canPosUseCarrier($this->getPo()->getDefaultPos()) ||
               Mage::helper('orbashipping')->canVendorUseCarrier($this->getPo()->getVendor());
    }

    public function canPosUseDhl() {
        return Mage::helper('orbashipping')->isDhlEnabledForPos($this->getPo()->getDefaultPos());
    }

    public function getMethodName($poShippingMethod) {
        foreach($this->getAvailableMethods() as $_amCode => $_amDesc) {
            if ($poShippingMethod==$_amCode) {
                return $_amDesc;
            }
        }
        return '';
    }

    public function getPoUrl($action, $params=array()) {
        $params += array(
                       "id"=> $this->getPo()->getId(),
                       "form_key" => Mage::getSingleton('core/session')->getFormKey()
                   );
        return $this->getUrl("*/*/$action", $params);
    }

    /**
     * Filter by email in column customer_name
     *
     * @param Zolago_Po_Model_Po $po
     * @return string
     */
    public function getHelpdeskUrl(Zolago_Po_Model_Po $po) {
        // overridden behaviour in Zolago_DropshipVendorAskQuestion_Block_Vendor_Question_Grid
        // grid -> column: customer_name
        // filter_condition_callback => filterByEmailIfValid
        return $this->getUrl("udqa/vendor/index", array(
                                 "filter"=> Mage::helper("core")->urlEncode("customer_name=" . urlencode($po->getOrder()->getCustomerEmail())))
                            );

    }

    /**
     * @param Zolago_Po_Model_Po $po
     * @return int
     */
    public function getAllMessagesCount(Zolago_Po_Model_Po $po) {
        if(!$this->hasData('all_messages_count')) {
            $collection = Mage::getResourceModel('udqa/question_collection');
            /* @var $collection ZolagoOs_OmniChannelVendorAskQuestion_Model_Mysql4_Question_Collection */
            $collection->addApprovedAnswersFilter();
            $collection->addVendorFilter($this->getVendor());
            $collection->addFieldToFilter("main_table.customer_email", array("like" => $po->getOrder()->getCustomerEmail()));
            $this->setData("all_messages_count", $collection->count());
        }
        return $this->getData("all_messages_count");
    }

    /**
     * @param Zolago_Po_Model_Po $po
     * @return int
     */
    public function getUnreadMessagesCount(Zolago_Po_Model_Po $po) {
        if(!$this->hasData('unread_messages_count')) {
            $collection = Mage::getResourceModel('udqa/question_collection');
            /* @var $collection ZolagoOs_OmniChannelVendorAskQuestion_Model_Mysql4_Question_Collection */
            $collection->addApprovedAnswersFilter();
            $collection->addVendorFilter($this->getVendor());
            $collection->addFieldToFilter("main_table.customer_email", array("like" => $po->getOrder()->getCustomerEmail()));
			$collection->addFieldToFilter("main_table.answer_text", array("null"=>true));
			$this->setData("unread_messages_count", $collection->count());
		}
		return $this->getData("unread_messages_count");
	}
	
	/**
	 * @return bool
	 */
	public function isEditable() {
		return $this->getPo()->getStatusModel()->isEditingAvailable($this->getPo());
	}
	
	/**
	 * @param type $type
	 * @return Zolago_Po_Block_Vendor_Po_Item_Renderer_Abstract
	 */
	protected function _getRendererByType($type) {
		$renderPath = "zolagopo/vendor_po_item_renderer_";
		switch ($type) {
			case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
			case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
				$renderPath.=$type;
				break;
			default:
				$renderPath.=Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
			break;
		}
		return $this->getLayout()->createBlock($renderPath);
	}

    public function getPaymentMethod(Zolago_Po_Model_Po $po) {
        $helper = Mage::helper("zolagopayment");
        if($po->isCod()) {
            return $helper->__("Cash on delivery");
        } else {
            $payment = $po->getOrder()->getPayment();
            $method = $payment->getMethod();
            $channelName = false;
            if($method == Zolago_Dotpay_Model_Client::PAYMENT_METHOD) {
                $channel = $payment->getAdditionalInformation('channel');
                $channelCode = $this->getMethodCodeByChannel($channel);
                $providerModel = Mage::getModel("zolagopayment/provider");
                $providerCollection = $providerModel->getCollection();
                $providerCollection->getSelect()->where("main_table.code = ?",$channelCode);
                $providerCollection->load();
                $provider = $providerCollection->getFirstItem();
                $channelName = $provider->getName();
            }
            return $helper->__($method).($channelName ? " (".$channelName.")" : "");
        }
    }

    private function getMethodCodeByChannel($channel) {
        $deXml = simplexml_load_file(Mage::getConfig()->getModuleDir('etc', 'Zolago_Payment').DS.'payment.xml');
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson,TRUE);
        $payments_array = array_merge($xml_array['global']['zolagopayment']['gateway'],$xml_array['global']['zolagopayment']['cc']);
        foreach($payments_array as $code=>$method) {
            if(isset($method['base']['additional_information']['channel'])) {
                if($method['base']['additional_information']['channel'] == $channel) {
                    return $code;
                }
            }
        }
    }

    
    /**
     * retrun true/false if can show payment change window
     */
    public function canShowPaymentChange() {
        $po = $this->getPo();
        $list = Mage::helper('zolagopayment')->getChangePaymentMethods($po);        
        if (count($list)<2) {
            return false;
        }
        $session = Mage::getSingleton('udropship/session');
        $isAllowed = $session->isAllowed(Zolago_Operator_Model_Acl::RES_PAYMENT_OPERATOR);
        if (!$isAllowed) return false;
        // check paid
        if ($po->getDebtAmount() >= 0) {
            return false;
        }
        // check statuses
        $status = Mage::getSingleton('zolagopo/po_status');
        return $status->isChangePaymentAvailable($po);
    }
    /**
     * Return true/false if button/modal window can be shown for payment method for current PO
     *
     * @return bool
     */
    public function canShowPaymentDetails() {
        $po = $this->getPo();
        if ($po->isCod()) {
            //Cash On Delivery Payment
            return (bool) Mage::getStoreConfig("payment/cashondelivery/p_details");
        }
        elseif($po->isPaymentBanktransfer()) {
            //Bank Transfer Payment
            return (bool) Mage::getStoreConfig("payment/banktransfer/p_details");
        }
        elseif ($po->isPaymentDotpay()) {
            //dotpay payment
            return (bool) Mage::getStoreConfig("payment/dotpay/p_details");
        }
        else {
            //for some new method not implemented yet
            return true;
        }
    }



    protected function _getShippingModal() {
        $po = $this->getPo();
        $shippingMethod = $po->getUdropshipMethod();
        $methodCode = Mage::helper('udtiership')->getCodeByPoMethod($shippingMethod);
        $block = Mage::helper('orbashipping')->getShippingManager($methodCode)->getShippingModal();
        $block->setParentBlock($this);
        return $block->toHtml();
    }


    /**
     * @return bool
     */
    public function isPickUpPaymentCanBeEntered()
    {
        // First check if we have access to payments manage by ACL
        /** @var Zolago_Dropship_Model_Session $session */
        $session = Mage::getSingleton('udropship/session');
        $isAllowed = $session->isAllowed(Zolago_Operator_Model_Acl::RES_PAYMENT_OPERATOR);
        if (!$isAllowed) return false;

        $po = $this->getPo();
        /** @var Zolago_Po_Helper_Data $hlp */
        $hlp = Mage::helper("zolagopo");
        return $hlp->isPickUpPaymentCanBeEntered($po);
    }
}
