<?php
class Zolago_Po_Helper_Data extends Unirgy_DropshipPo_Helper_Data
{
	const DEFAULT_PO_IMAGE_WIDTH = 53;
	const DEFAULT_PO_IMAGE_HEIGHT = 69;
	
	protected $_condJoined = false;

    /**
     * to have payment status updated when PO is changed
     *
     * @param Zolago_Po_Model_Po $po
     * @param bool $save
     * @throws Exception
     */
    public function updatePoStatusByAllocation(Zolago_Po_Model_Po $po, $save = true) {
        if ($po->getId()) {
            $newStatus = $po->getUdropshipStatus();

            $grandTotal = $po->getGrandTotalInclTax();
            /** @var Zolago_Payment_Model_Allocation $allocationModel */
            $allocationModel = Mage::getModel("zolagopayment/allocation");
            $sumAmount = $allocationModel->getSumOfAllocations($po->getId()); //sum of allocations amount

            //czeka na płatność
            if ($newStatus == Zolago_Po_Model_Po_Status::STATUS_PAYMENT && ($grandTotal <= $sumAmount)) {
                //rowny albo nadplata
                $po->setUdropshipStatus(Zolago_Po_Model_Po_Status::STATUS_PENDING);
                if ($save) {
                    $po->save();
                }
            } //czeka na spakowanie
            elseif ($newStatus == Zolago_Po_Model_Po_Status::STATUS_PENDING && ($grandTotal > $sumAmount) && !$po->isCod()) {
                //jest mniej niz potrzeba
                $po->setUdropshipStatus(Zolago_Po_Model_Po_Status::STATUS_PAYMENT);
                if ($save) {
                    $po->save();
                }
            } //czeka na rezerwacje
            elseif ($newStatus == Zolago_Po_Model_Po_Status::STATUS_BACKORDER && ($grandTotal <= $sumAmount)) {
                $po->setUdropshipStatus(Zolago_Po_Model_Po_Status::STATUS_PENDING);
                if ($save) {
                    $po->save();
                }
            }
        }
    }

    /**
     * @param Zolago_Po_Model_Po $po
     * @param $isVendorNotified
     * @param $isVisibleToVendor
     * @param $operator_id
     * @param $amount
     */
    public function addOverpayComment(Zolago_Po_Model_Po $po, $isVendorNotified, $isVisibleToVendor, $operator_id, $amount) {

        /** @var Zolago_Payment_Helper_Data $helperZP */
        $helperZP = Mage::helper("zolagopayment");
        /** @var Zolago_Operator_Model_Operator $modelOperator */
        $modelOperator = Mage::getModel("zolagooperator/operator")->load($operator_id);

        if ($modelOperator->getId()) {
            //if is operator
            $fullName = $modelOperator->getVendor()->getVendorName()." / ".$modelOperator->getEmail();
        } else {
            //if is vendor
            $fullName = Mage::getSingleton('udropship/session')->getVendor()->getVendorName();
        }
        if (empty($fullName)) {
            $fullName = $helperZP->__("Automat");
        }

        $_comment =
            "[$fullName] " .
            $helperZP->__("Created overpayment") .
            ": " .
            Mage::helper('core')->currency($amount, true, false)
        ;

        $po->addComment($_comment, $isVendorNotified, $isVisibleToVendor);
        if ($isVendorNotified) {
            Mage::helper('udpo')->sendPoCommentNotificationEmail($po, $_comment);
            Mage::helper('udropship')->processQueue();
        }
        $po->saveComments();
    }

    public function addAllocationComment(Zolago_Po_Model_Po $oldPo, Zolago_Po_Model_Po $newPo, $isVendorNotified, $isVisibleToVendor, $operator_id, $amount){
        /** @var Zolago_Payment_Helper_Data $helperZP */
        $helperZP = Mage::helper("zolagopayment");
        /** @var Zolago_Operator_Model_Operator $modelOperator */
        $modelOperator = Mage::getModel("zolagooperator/operator")->load($operator_id);

        if ($modelOperator->getId()) {
            //if is operator
            $fullName = $modelOperator->getVendor()->getVendorName()." / ".$modelOperator->getEmail();
        } else {
            //if is vendor
            $fullName = Mage::getSingleton('udropship/session')->getVendor()->getVendorName();
        }
        if (empty($fullName)) {
            $fullName = $helperZP->__("Automat");
        }

        $comment =
            "[$fullName] " .
            $helperZP->__("Overpayment moved from %s to %s. Amount: %s",
                $oldPo->getIncrementId(),
                $newPo->getIncrementId(),
                Mage::helper('core')->currency($amount, true, false));

        $newPo->addComment($comment, $isVendorNotified, $isVisibleToVendor);
        if ($isVendorNotified) {
            Mage::helper('udpo')->sendPoCommentNotificationEmail($newPo, $comment);
            Mage::helper('udropship')->processQueue();
        }
        $newPo->saveComments();

        $oldPo->addComment($comment, $isVendorNotified, $isVisibleToVendor);
        if ($isVendorNotified) {
            Mage::helper('udpo')->sendPoCommentNotificationEmail($oldPo, $comment);
            Mage::helper('udropship')->processQueue();
        }
        $oldPo->saveComments();
    }

	/**
	 * @param Zolago_Po_Model_Po_Item $item
	 * @return Mage_Catalog_Helper_Image
	 */
	public function getItemThumbnail(Zolago_Po_Model_Po_Item $item) { 
        return Mage::helper('catalog/image')->init($item->getProduct(), 'thumbnail');
	}
	
	/**
	 * @param Zolago_Po_Model_Po $po
	 * @return array
	 */
	public function getPoImagesAsAttachments(Zolago_Po_Model_Po $po, $options=array()) {
		$width = $this->getPoImageWidth($po);
		$height = $this->getPoImageHeight($po);
		$store = $po->getStore();
		$attachments = array();
		
		foreach($po->getItemsCollection() as $item){
			if(!$item->getParentItemId()){
				$imageUrl = (string)Mage::helper("catalog/image")->
					init($item->getProduct(), "thumbnail")->
					resize($width, $height);
				$attachments[] = array(
					"filename"		=> Mage::helper("zolagocommon")->getRelativePath($imageUrl, $store),
					"id"			=> basename($item->getId().".jpg"),
					"disposition"	=> "inline"
				);
			}
		}
		
		return $attachments;
	}

    /**
     * get all images attachments from order witch can have more
     * then 1 PO
     *
     * @param Zolago_Sales_Model_Order $order
     * @return array
     */
    public function getOrderImagesAsAttachments(Zolago_Sales_Model_Order $order) {

        $coll = $order->getPoListByOrder();
        $allAttachments = array();

        foreach ($coll as $po) {
            /** @var Zolago_Po_Model_Po $po */
            $attachments = $this->getPoImagesAsAttachments($po);
            foreach ($attachments as $att) {
                $allAttachments[] = $att;
            }
        }
        return $allAttachments;
    }
	
	/**
	 * @param Zolago_Po_Model_Po $po
	 * @return int
	 */
	public function getPoImageWidth(Zolago_Po_Model_Po $po) {
		return self::DEFAULT_PO_IMAGE_WIDTH;
	}
	
	/**
	 * @param Zolago_Po_Model_Po $po
	 * @return int
	 */
	public function getPoImageHeight(Zolago_Po_Model_Po $po) {
		return self::DEFAULT_PO_IMAGE_HEIGHT;
	}
	
	/**
	 * Clear not used addresses (trashes)
	 * @param Zolago_Po_Model_Po $po
	 * @param type $type
	 * @param type $exclude
	 */
	public function clearAddresses(Zolago_Po_Model_Po $po, $type, $exclude=array()) {
		
	    // Add this shippign id
		// $exclude[] = $po->getShippingAddressId();
	    $addressCollection = Mage::getResourceModel("sales/order_address_collection");
		/* @var $addressCollection Mage_Sales_Model_Resource_Order_Address_Collection */
		$addressCollection->addFieldToFilter("parent_id", $po->getOrder()->getId());
		$addressCollection->addFieldToFilter("address_type", $type);
		
		if($exclude){
			$addressCollection->addFieldToFilter("entity_id", array("nin"=>$exclude));
		}
		
		$select = $addressCollection->getSelect();
		
		if($type==$po::TYPE_POSHIPPING || $type==Zolago_Rma_Model_Rma::TYPE_RMASHIPPING){

			$subSelect = $select->getAdapter()->select();
			$subSelect->from( 
					array("shipment"=>$po->getResource()->getTable("sales/shipment")),
					array(new Zend_Db_Expr("COUNT(shipment.entity_id)"))
			);
			$subSelect->where("shipment.shipping_address_id=main_table.entity_id");

			$select->where("? < 1", $subSelect);
		}
		
		// Skip used addresses

		$subSelect = $select->getAdapter()->select();
		$subSelect->from( 
				array("self"=>$po->getResource()->getMainTable()),
				array(new Zend_Db_Expr("COUNT(self.entity_id)"))
		);
		$subSelect->where("self.shipping_address_id=main_table.entity_id OR self.billing_address_id=main_table.entity_id");

		$select->where("? < 1", $subSelect);
		
		// Skip RMA used addresse
		$subSelect = $select->getAdapter()->select();
		$subSelect->from( 
				array("rma"=>$po->getResource()->getTable("urma/rma")),
				array(new Zend_Db_Expr("COUNT(rma.entity_id)"))
		);
		$subSelect->where("rma.shipping_address_id=main_table.entity_id OR rma.billing_address_id=main_table.entity_id");

		$select->where("? < 1", $subSelect);
		
		foreach($addressCollection as $toDelete){
			$toDelete->delete();
		}
   
	}

    public function _sendNewPoNotificationEmail($po, $comment='')
    {
        $order = $po->getOrder();
        $store = $order->getStore();

        $vendor = $po->getVendor();

        $hlp = Mage::helper('udropship');
        $udpoHlp = Mage::helper('udpo');
        $data = array();

        if (!$po->getResendNotificationFlag()
            && ($store->getConfig('udropship/vendor/attach_packingslip') && $vendor->getAttachPackingslip()
                || $store->getConfig('udropship/vendor/attach_shippinglabel') && $vendor->getAttachShippinglabel() && $vendor->getLabelType())
        ) {
            $udpoHlp->createReturnAllShipments=true;
            if ($shipments = $udpoHlp->createShipmentFromPo($po, array(), true, true, true)) {
                foreach ($shipments as $shipment) {
                    $shipment->setNewShipmentFlag(true);
                    $shipment->setDeleteOnFailedLabelRequestFlag(true);
                }
            }
            $udpoHlp->createReturnAllShipments=false;
        }

        if ($po->getResendNotificationFlag()) {
            foreach ($po->getShipmentsCollection() as $_shipment) {
                if ($_shipment->getUdropshipStatus()!=Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED) {
                    $shipments[] = $_shipment;
                    break;
                }
            }
        }

        $adminTheme = explode('/', Mage::getStoreConfig('udropship/admin/interface_theme', 0));

        if ($store->getConfig('udropship/purchase_order/attach_po_pdf') && $vendor->getAttachPoPdf()) {
            Mage::getDesign()->setArea('adminhtml')
                ->setPackageName(!empty($adminTheme[0]) ? $adminTheme[0] : 'default')
                ->setTheme(!empty($adminTheme[1]) ? $adminTheme[1] : 'default');

            $orderShippingAmount = $order->getShippingAmount();
            $order->setShippingAmount($po->getShippingAmount());

            $pdf = Mage::helper('udpo')->getVendorPoMultiPdf(array($po));

            $order->setShippingAmount($orderShippingAmount);

            $data['_ATTACHMENTS'][] = array(
                'content'=>$pdf->render(),
                'filename'=>'purchase_order-'.$po->getIncrementId().'-'.$vendor->getId().'.pdf',
                'type'=>'application/x-pdf',
            );
        }

        if ($store->getConfig('udropship/vendor/attach_packingslip') && $vendor->getAttachPackingslip() && !empty($shipments)) {
            Mage::getDesign()->setArea('adminhtml')
                ->setPackageName(!empty($adminTheme[0]) ? $adminTheme[0] : 'default')
                ->setTheme(!empty($adminTheme[1]) ? $adminTheme[1] : 'default');

            foreach ($shipments as $shipment) {
                $orderShippingAmount = $order->getShippingAmount();
                $order->setShippingAmount($shipment->getShippingAmount());

                $pdf = Mage::helper('udropship')->getVendorShipmentsPdf(array($shipment));

                $order->setShippingAmount($orderShippingAmount);
                $shipment->setDeleteOnFailedLabelRequestFlag(false);

                $data['_ATTACHMENTS'][] = array(
                    'content'=>$pdf->render(),
                    'filename'=>'packingslip-'.$po->getIncrementId().'-'.$vendor->getId().'.pdf',
                    'type'=>'application/x-pdf',
                );
            }
        }

        if ($store->getConfig('udropship/vendor/attach_shippinglabel') && $vendor->getAttachShippinglabel()
            && $vendor->getLabelType() && !empty($shipments)
        ) {
            foreach ($shipments as $shipment) {
                try {
                    $hlp->unassignVendorSkus($shipment);
                    $hlp->unassignVendorSkus($po);
                    foreach ($shipment->getAllItems() as $sItem) {
                        $firstOrderItem = $sItem->getOrderItem();
                        break;
                    }
                    if (!isset($firstOrderItem) || !$firstOrderItem->getUdpompsManual()) {
                        if (!$po->getResendNotificationFlag()) {
                            $batch = Mage::getModel('udropship/label_batch')->setVendor($vendor)->processShipments(array($shipment));
                            if ($batch->getErrors()) {
                                if (Mage::app()->getRequest()->getRouteName()=='udropship') {
                                    Mage::throwException($batch->getErrorMessages());
                                } else {
                                    Mage::helper('udropship/error')->sendLabelRequestFailedNotification($shipment, $batch->getErrorMessages());
                                }
                            } else {
                                if ($batch->getShipmentCnt()>1) {
                                    $labelModel = Mage::helper('udropship')->getLabelTypeInstance($batch->getLabelType());
                                    $data['_ATTACHMENTS'][] = $labelModel->renderBatchContent($batch);
                                } else {
                                    $labelModel = $hlp->getLabelTypeInstance($batch->getLabelType());
                                    foreach ($shipment->getAllTracks() as $track) {
                                        $data['_ATTACHMENTS'][] = $labelModel->renderTrackContent($track);
                                    }
                                }
                            }
                        } else {
                            $batchIds = array();
                            foreach ($shipment->getAllTracks() as $track) {
                                $batchIds[$track->getBatchId()][] = $track;
                            }
                            foreach ($batchIds as $batchId => $tracks) {
                                $batch = Mage::getModel('udropship/label_batch')->load($batchId);
                                if (!$batch->getId()) continue;
                                if (count($tracks)>1) {
                                    $labelModel = Mage::helper('udropship')->getLabelTypeInstance($batch->getLabelType());
                                    $data['_ATTACHMENTS'][] = $labelModel->renderBatchContent($batch);
                                } else {
                                    reset($tracks);
                                    $labelModel = Mage::helper('udropship')->getLabelTypeInstance($batch->getLabelType());
                                    $data['_ATTACHMENTS'][] = $labelModel->renderTrackContent(current($tracks));
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    // ignore if failed
                }
            }
        }

        if (!empty($shipments)) {
            foreach ($shipments as $shipment) {
                if ($shipment->getNewShipmentFlag() && !$shipment->isDeleted()) {
                    $shipment->setNoInvoiceFlag(false);
                    $hlp->unassignVendorSkus($shipment);
                    $hlp->unassignVendorSkus($po);
                    $udpoHlp->invoiceShipment($shipment);
                }
            }
        }

        $hlp->setDesignStore($store);
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress) {
            $shippingAddress = $order->getBillingAddress();
        }
        $hlp->assignVendorSkus($po);
        $data += array(
            'po'              => $po,
            'order'           => $order,
            'vendor'          => $vendor,
            'comment'         => $comment,
            'store_name'      => $store->getName(),
            'vendor_name'     => $vendor->getVendorName(),
            'po_id'           => $po->getIncrementId(),
            'order_id'        => $order->getIncrementId(),
            'customer_info'   => Mage::helper('udropship')->formatCustomerAddress($shippingAddress, 'html', $vendor),
            'shipping_method' => $po->getUdropshipMethodDescription() ? $po->getUdropshipMethodDescription() : $vendor->getShippingMethodName($order->getShippingMethod(), true),
            'po_url'          => Mage::getUrl('udpo/vendor/', array('_query'=>'filter_po_id_from='.$po->getIncrementId().'&filter_po_id_to='.$po->getIncrementId())),
            'po_pdf_url'      => Mage::getUrl('udpo/vendor/udpoPdf', array('udpo_id'=>$po->getId())),
            'use_attachments' => true
        );

        $template = $vendor->getEmailTemplate();
        if (!$template) {
            $template = $store->getConfig('udropship/purchase_order/new_po_vendor_email_template');
        }
        $identity = $store->getConfig('udropship/vendor/vendor_email_identity');

        $data['_BCC'] = $vendor->getNewOrderCcEmails();
        if (($emailField = $store->getConfig('udropship/vendor/vendor_notification_field'))) {
            $email = $vendor->getData($emailField) ? $vendor->getData($emailField) : $vendor->getEmail();
        } else {
            $email = $vendor->getEmail();
        }
        Mage::log("Zolago: _sendNewPoNotificationEmail", null, "operator.log");
        Mage::log($email, null, "operator.log");

//        Mage::getModel('udropship/email')->sendTransactional($template, $identity, $email, $vendor->getVendorName(), $data);
        /* @var $helper Zolago_Common_Helper_Data */
        $helper = Mage::helper("zolagocommon");
        $helper->sendEmailTemplate(
            $email,
            $vendor->getVendorName(),
            $template,
            $data,
            true,
            $identity
        );

        $hlp->unassignVendorSkus($po);

        $hlp->setDesignStore();
    }

	public function sendNewPoNotificationEmail($po, $comment=''){
        Mage::log("Zolago: sendNewPoNotificationEmail", null, "operator.log");
		$vendor = $po->getVendor();
		/* @var $po Zolago_Po_Model_Po */
		$order = $po->getOrder();
        $store = $order->getStore();
		$pos = $po->getPos();

		$emailField = $store->getConfig('udropship/vendor/vendor_notification_field');

		if(!$emailField){
			$emailField = "email";
		}
		
		$oldEmail = $newEmail = $vendor->getData($emailField);
		
		if($pos && $pos->getId()){
			$newEmail = $pos->getEmail();
		}
        Mage::log($newEmail, null, "operator.log");
		// Replace vendor email to pos email & send mail & restore origin
		$vendor->setData($emailField, $newEmail);	
		$vendor->setData("po", $po);
		$this->_sendNewPoNotificationEmail($po, $comment);
		$vendor->setData($emailField, $oldEmail);
		$vendor->setData("po", null);

		// Porocess queue
		Mage::helper('udropship')->processQueue();
	}
	
	/**
	 * @param type $shipment
	 * @param type $save
	 * @return type
	 */
    public function cancelShipment($shipment, $save){
		Mage::dispatchEvent("zolagopo_shipment_cancel_before", array("shipment"=>$shipment));
		$return = parent::cancelShipment($shipment, $save);
		Mage::dispatchEvent("zolagopo_shipment_cancel_after", array("shipment"=>$shipment));
		return $return;
	}
	/**
	 * @param Unirgy_DropshipPo_Model_Mysql4_Po_Collection|array $collection
	 * @param Unirgy_Dropship_Model_Vendor | int $vendor
	 * @return boolean
	 * @throws Mage_Core_Exception
	 */
	public function createAggregated($collection, $vendor) {
		
		if($vendor instanceof Unirgy_Dropship_Model_Vendor){
			$vendor = $vendor->getId();
		}
		
		if(is_array($collection)){
			$collection = Mage::getResourceModel('udpo/po_collection');
			/* @var $collection Unirgy_DropshipPo_Model_Mysql4_Po_Collection */
			$collection->addFieldToFilter("entity_id", array("in"=>$collection));
		}
		
		if(!$collection->count()){
			throw new Mage_Core_Exception(Mage::helper('zolagopo')->__("Specify 1 or more PO"));
		}
		
		$poses = array();
		$carriers = array();
		$currentlyHas = array();
		foreach($collection as $po){
			if($po->getAggregatedId()){
				$currentlyHas[] = $po->getIncrementId();
			}
			$poses[$po->getDefaultPosId()] = $po->getDefaultPosId();
			$carriers[$po->getCurrentCarrier()] = true;
		}
		
		
		$count = count($currentlyHas);
		if($count){
			throw new Mage_Core_Exception(Mage::helper('zolagopo')->__(
					"Purchase Order(s): %s has currently dispatch ref.", 
					implode(",", $currentlyHas)
			));
		}
		
		if(count($poses)!=1){
			throw new Mage_Core_Exception(Mage::helper('zolagopo')->__("Purchase Order(s) have different POSes"));
		}
		if(count($carriers)!=1){
			throw new Mage_Core_Exception(Mage::helper('zolagopo')->__("Purchase Order(s) have different carriers"));
		}
		
		$aggregated = Mage::getModel("zolagopo/aggregated");
		$aggregated->setPosId(current($poses));
		$aggregated->setVendorId($vendor);
		$aggregated->generateName();
		$aggregated->save();
		
		foreach($collection as $po){
			$po->setAggregatedId($aggregated->getId());
			$po->getResource()->saveAttribute($po, "aggregated_id");
		}
		
		return $aggregated->getId();
	}
	
	public function setCondJoined($flag) {
		$this->_condJoined = $flag;
	}
	/**
	 * Add operator filter if session is in operator mode
	 * @return Unirgy_DropshipPo_Model_Mysql4_Po_Collection
	 */
	public function getVendorPoCollection() {
		$collection = parent::getVendorPoCollection();
		if($this->_condJoined){
			return $collection;
		}
		/* @var $collection Unirgy_DropshipPo_Model_Mysql4_Po_Collection */
		$session = Mage::getSingleton('udropship/session');
		/* @var $session Zolago_Dropship_Model_Session */
		if($session->isOperatorMode()){
			Mage::getResourceModel("zolagooperator/operator")->
				addOperatorFilterToPoCollection($collection, $session->getOperator());
		}
		$this->_condJoined = true;
		return $collection;
	}
	
	public function getDhlSettings($vendor, $posId) {
		$dhlSettings = false;
        $account = false;
        $posModel = Mage::getModel('zolagopos/pos')->load($posId);
        if ($posModel && $posModel->getId() && $posModel->getUseDhl()) {
            $account = $posModel->getDhlAccount();
            if ($posModel->getDhlLogin() && $posModel->getDhlPassword() && $posModel->getDhlAccount()) {
                $dhlSettings['login'] = $posModel->getDhlLogin();
                $dhlSettings['account'] = $posModel->getDhlAccount();
                $dhlSettings['password'] = $posModel->getDhlPassword();
            }
        } elseif ($vendor && $vendor->getId() && $vendor->getUseDhl()) {
            $account = $vendor->getDhlAccount();
            if ($vendor->getDhlLogin() && $vendor->getDhlPassword() && $vendor->getDhlAccount()) {
                $dhlSettings['login'] = $vendor->getDhlLogin();
                $dhlSettings['account'] = $vendor->getDhlAccount();
                $dhlSettings['password'] = $vendor->getDhlPassword();
            }
        }

        if($account && $vendor && $vendor->getId()){
            /* DHL client number be assigned to gallery or to vendor */
            /* @var $ghdhl GH_Dhl_Helper_Data */
            $ghdhl = Mage::helper("ghdhl");
            $galleryDHLAccountData = $ghdhl->getGalleryDHLAccountData($account, $vendor->getId());

            if (!empty($galleryDHLAccountData)) {
                $dhlSettings['account'] = $galleryDHLAccountData->getDhlAccount();
                $dhlSettings["login"] = $galleryDHLAccountData->getDhlLogin();
                $dhlSettings["password"] = $galleryDHLAccountData->getDhlPassword();
                $dhlSettings["gallery_shipping_source"] = 1;
            }
        }
		
		return $dhlSettings;
	}
	
	/**
	 * @todo handle configurable product
	 * @param Mage_Sales_Model_Order_Item $item
	 * @param Zolago_Po_Model_Po_Item $poItem
	 * @return \Zolago_Po_Helper_Data
	 */
	public function prepareOrderItemByPoItem(Mage_Sales_Model_Order_Item $item, 
		Zolago_Po_Model_Po_Item $poItem) {
		
		$po = $poItem->getPo();
		/* @var $po Zolago_Po_Model_Po */
		
		/**
		 * @todo Make admin way RMA creation available process
		 */
		if(!($po instanceof Zolago_Po_Model_Po)){
			return $this;
		}
		
		$order = $po->getOrder();
		/* @var $order Mage_Sales_Model_Order */
		$tmplItem = $order->getItemsCollection()->getFirstItem();
		/* @var $tmplItem Mage_Sales_Model_Order_Item */
		$store = $order->getStore();

		$product = Mage::getModel("catalog/product")->setStoreId($store->getId())
				->load($poItem->getProductId());
		/* @var $product Mage_Catalog_Model_Product */
		
		// Rates
		$globalCurrencyCode  = Mage::app()->getBaseCurrencyCode();
        $baseCurrency = $store->getBaseCurrency();
		
		
		// Taxes 
		$priceInclTax = $poItem->getPriceInclTax();
		
		$taxHelper = Mage::helper('tax');
		/* @var $taxHelper Mage_Tax_Helper_Data */
		$taxConfig = $taxHelper->getConfig();
		/* @var $taxConfig Mage_Tax_Model_Config */
		$taxCalculation = Mage::getSingleton('tax/calculation');
		/* @var $taxCalculation Mage_Tax_Model_Calculation */
		$customerGroup = Mage::getModel("customer/group")->load($po->getOrder()->getCustomerGroupId());
		/* @var $customerGroup Mage_Customer_Model_Group */
		
		$request = $taxCalculation->getRateRequest(
				$po->getShippingAddress(), 
				$po->getBillingAddress(), 
				$customerGroup->getTaxClassId(), 
				$store
		);
		
		$request->setProductClassId($product->getData('tax_class_id'));
		$taxAmount = 0;
		if ($taxRate = $taxCalculation->getRate($request)) {
			$taxAmount  = $store->roundPrice($priceInclTax * (1 - 1 / (($taxRate/100)+1)));
	    }
		$hiddenTaxAmount = 0;
		
		if($poItem->getDiscountPercent()){
			$originTax = $taxAmount;
			$taxAmount *= (1-$poItem->getDiscountPercent()/100);
			$hiddenTaxAmount = $originTax - $taxAmount;
		}
		// Stock item 
		$stockItem = $product->getStockItem();
		
		/**
		* @todo PO item if this has parent PO item use its ORDER item id 
		* as value of parent_item_id of new ORDER item
		*/
		
		$data = array(			
			"order_id"						=> $order->getId(),
			"parent_item_id"				=> null,
			"quote_item_id"					=> null, 
			"store_id"						=> $store->getId(),
			"created_at"					=> null, 
			"updated_at"					=> null,
			"product_id"					=> $product->getId(),
			"product_type"					=> $product->getTypeId(),
			"product_options"				=> null,
			"weight"						=> $poItem->getWeight(),
			"is_virtual"					=> $poItem->getIsVirtual(),
			"sku"							=> $poItem->getSku(),
			"name"							=> $poItem->getName(),
			"description"					=> null,
			"applied_rule_ids"				=> null,
			"additional_data"				=> null,
			"free_shipping"					=> 0,
			"is_qty_decimal"				=> $stockItem ? $stockItem->getIsQtyDecimal() : 0,
			"no_discount"					=> 0,
			"qty_backordered"				=> null,
			"qty_canceled"					=> 0,
			"qty_invoiced"					=> 0,
			"qty_ordered"					=> $poItem->getQty(),
			"qty_refunded"					=> 0,
			"qty_shipped"					=> 0,
			"base_cost"						=> $poItem->getBaseCost(),
			"price"							=> $poItem->getPrice(),
			"base_price"					=> $store->convertPrice($poItem->getPrice()),
			"original_price"				=> $product->getPrice(),
			"base_original_price"			=> $store->convertPrice($product->getPrice()),
			"tax_percent"					=> $taxRate,
			"tax_amount"					=> $taxAmount * $poItem->getQty(),
			"base_tax_amount"				=> $store->convertPrice($taxAmount * $poItem->getQty()),
			"tax_invoiced"					=> 0,
			"base_tax_invoiced"				=> 0,
			"discount_percent"				=> $poItem->getDiscountPercent(), 
			"discount_amount"				=> $poItem->getDiscountAmount(), 
			"base_discount_amount"			=> $store->convertPrice($poItem->getDiscountAmount()),
			"discount_invoiced"				=> 0,
			"base_discount_invoiced"		=> 0,
			"amount_refunded"				=> 0,
			"base_amount_refunded"			=> 0,
			"row_total"						=> $poItem->getRowTotal(),
			"base_row_total"				=> $store->convertPrice($poItem->getRowTotal()),
			"row_invoiced"					=> 0,
			"base_row_invoiced"				=> 0,
			"row_weight"					=> $poItem->getWeight() * $poItem->getQty(),
			"gift_message_id"				=> null,
			"gift_message_available"		=> null,
			"base_tax_before_discount"		=> null,
			"tax_before_discount"			=> null,
			"weee_tax_applied"				=> null,
			"weee_tax_applied_amount"		=> 0,
			"weee_tax_applied_row_amount"	=> 0,
			"base_weee_tax_applied_amount"	=> 0,
			"base_weee_tax_applied_row_amnt"=> 0,
			"weee_tax_disposition"			=> 0,
			"weee_tax_row_disposition"		=> 0,
			"base_weee_tax_disposition"		=> 0,
			"base_weee_tax_row_disposition"	=> 0,
			"ext_order_item_id"				=> null,
			"locked_do_invoice"				=> null,
			"locked_do_ship"				=> null,
			"price_incl_tax"				=> $priceInclTax,
			"base_price_incl_tax"			=> $store->convertPrice($priceInclTax),
			"row_total_incl_tax"			=> $poItem->getRowTotalInclTax(),
			"base_row_total_incl_tax"		=> $store->convertPrice($poItem->getRowTotalInclTax()),
			"hidden_tax_amount"				=> $hiddenTaxAmount * $poItem->getQty(),
			"base_hidden_tax_amount"		=> $store->convertPrice($hiddenTaxAmount * $poItem->getQty()),
			"hidden_tax_invoiced"			=> null,
			"base_hidden_tax_invoiced"		=> null,
			"hidden_tax_refunded"			=> null,
			"base_hidden_tax_refunded"		=> null,
			"is_nominal"					=> $poItem->getIsNominal(),
			"tax_canceled"					=> null,
			"hidden_tax_canceled"			=> null,
			"tax_refunded"					=> null,
			"base_tax_refunded"				=> null,
			"discount_refunded"				=> null,
			"base_discount_refunded"		=> null,
			"udropship_vendor"				=> $po->getUdropshipVendor(),
			"locked_do_udpo"				=> null,
			"qty_udpo"						=> $poItem->getQty(),
			"udpo_seq_number"				=> null,
			"udpo_qty_reverted"				=> null,
			"udpo_qty_used"					=> $poItem->getQty()
		);
		
		$item->addData($data);
		$item->setOrder($order);
		$order->addItem($item);
	}

    /**
     * Mass checking for PO collection is start packing available for
     * start packing process
     *
     * @param Zolago_Po_Model_Resource_Po_Collection $poCollection
     * @return array
     */
    public function massCheckIsStartPackingAvailable(Zolago_Po_Model_Resource_Po_Collection $poCollection) {
        $listNotValid = array();
        foreach ($poCollection as $po) {
            /** @var Zolago_Po_Model_Po $po */
            if(!$po->getStatusModel()->isStartPackingAvailable($po) 
                && !$po->getStatusModel()->isShippingAvailable($po)) {
                $listNotValid[] = $po->getIncrementId();
            }
        }
        return $listNotValid;
    }

    /**
     * Mass processing of start packing process
     *
     * @param Zolago_Po_Model_Resource_Po_Collection $poCollection
     * @return void
     */
    public function massProcessStartPacking(Zolago_Po_Model_Resource_Po_Collection $poCollection) {
        foreach ($poCollection as $po) {
            /** @var Zolago_Po_Model_Po $po */
            if($po->getStatusModel()->isStartPackingAvailable($po)) {
                $po->getStatusModel()->processStartPacking($po, false, false);
            }
        }
    }
}
