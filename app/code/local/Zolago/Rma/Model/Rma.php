<?php
/**
 * @method ZolagoOs_OmniChannelPo_Model_Mysql4_Po getResource()
 * @method string getPaymentChannelOwner()
 * @method Zolago_Rma_Model_Rma setPaymentChannelOwner($owner)
 * @method string getIncrementId()
 * @method string getCreatedAt() TIMESTAMP
 */
class Zolago_Rma_Model_Rma extends ZolagoOs_Rma_Model_Rma
{

    const RMA_TYPE_STANDARD = '1';
    const RMA_TYPE_RETURN = '2';

    const TYPE_RMASHIPPING = "rmashipping";
    const TYPE_RMABILLING = "rmabilling";

    const FLOW_INSTANT = 1;
    const FLOW_ACKNOWLEDGED = 2;

    /**
     * @return boolean
     * @todo implement
     */
    public function getIsClaim() {
        return false;
    }

    /**
     * @return boolean
     * @todo implement
     */
    public function getIsReturn() {
        return true;
    }

    /**
     * @return string
     */
    public function getRmaStatusName() {
        return $this->getStatusObject()->getTitle();
    }

    /**
     * @return string
     */
    public function getRmaStatusCode() {
        return $this->getStatusObject()->getCode();
    }

    /**
     * @return Varien_Object
     */
    public function getStatusObject() {
        return $this->getStatusModel()->getStatusObject($this);
    }

    /**
     * @return bool
     */
    public function hasCustomerTracking() {
        foreach($this->getTracksCollection() as $track) {
            if($track->getTrackCreator()==Zolago_Rma_Model_Rma_Track::CREATOR_TYPE_CUSTOMER) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Zolago_Rma_Model_Resource_Rma_Track_Collection
     */
    public function getVendorTracksCollection() {
        return Mage::getResourceModel('urma/rma_track_collection')
               ->setRmaFilter($this->getId())
               ->addVendorFilter();
    }

    /**
     * @return Zolago_Rma_Model_Resource_Rma_Track_Collection
     */
    public function getCustomerTracksCollection() {
        return Mage::getResourceModel('urma/rma_track_collection')
               ->setRmaFilter($this->getId())
               ->addCustomerFilter();
    }
    /**
     * @return Zolago_Po_Model_Po
     */
    public function getPo() {
        if(!$this->hasData("po")) {
            $po = Mage::getModel("zolagopo/po");
            $po->load($this->getUdpoId());
            $this->setData("po", $po);
        }
        return $this->getData("po");
    }
    /**
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipment() {
        if(!$this->hasData("shipment")) {
            $shipment = Mage::getModel("sales/order_shipment");
            $shipment->load($this->getShipmentId());
            $this->setData("shipment", $shipment);
        }
        return $this->getData("shipment");
    }


    /**
     * @return Zolago_Rma_Model_Rma_Status
     */
    public function getStatusModel() {
        return Mage::getSingleton('zolagorma/rma_status');
    }

    /**
     * @return bool
     */
    public function isShippingSameAsPo() {
        return $this->getShippingAddress()->getId() == $this->getPo()->getShippingAddress()->getId();
    }

    /**
     * @return bool
     */
    public function isBillingSameAsPo() {
        return $this->getBillingAddress()->getId() == $this->getPo()->getBillingAddress()->getId();
    }
    public function getFormattedAddressForVendor() {
        $data = $this->getData();
        $addressId = $data['shipping_address_id'];
        $address = Mage::getModel('sales/order_address')->load($addressId)->getData();
        $out = array (
                   'name' 		=> (empty($address['company']))? ($address['firstname'].' '.$address['lastname']):$address['company'],
                   'city' 		=> $address['city'],
                   'postcode' 	=> $address['postcode'],
                   'street' 	=> $address['street'],
                   'personName' => $address['firstname'].' '.$address['lastname'],
                   'phone' 		=>$address['telephone'],
                   'email' 		=> $this->getOrder()->getCustomerEmail(),
                   'country'	=> $address['country_id'],
               );
        return $out;
    }
    public function getFormattedAddressForCustomer() {
        $data = $this->getData();
        $addressId = $data['customer_address_id'];
        $address = Mage::getModel('customer/address')->load($addressId)->getData();
        $out = array (
                   'name' 		=> (empty($address['company']))? ($address['firstname'].' '.$address['lastname']):$address['company'],
                   'city' 		=> $address['city'],
                   'postcode' 	=> $address['postcode'],
                   'street' 	=> $address['street'],
                   'personName' => $address['firstname'].' '.$address['lastname'],
                   'phone' 		=>$address['telephone'],
                   'email' 		=> $this->getOrder()->getCustomerEmail(),
                   'country'	=> $address['country_id'],
               );
        return $out;
    }

    /**
     * @return Mage_Sales_Model_Order_Address
     */
    public function getShippingAddress() {
        if($this->getShippingAddressId()) {
            $address = $this->getOrder()->getAddressById($this->getShippingAddressId());
            if($address->getId()) {
                return $address;
            }
        }
        return parent::getShippingAddress();
    }

    /**
     * @return Mage_Sales_Model_Order_Address
     */
    public function getBillingAddress() {
        if($this->getBillingAddressId()) {
            $address = $this->getOrder()->getAddressById($this->getBillingAddressId());
            if($address->getId()) {
                return $address;
            }
        }
        return parent::getBillingAddress();
    }


    /**
     * @param Mage_Sales_Model_Order_Address $address
     * @param bool $append
     * @return Zolago_Rma_Model_Rma
     */
    public function setOwnShippingAddress(Mage_Sales_Model_Order_Address $address, $append=false) {
        return $this->_setOwnAddress(self::TYPE_RMASHIPPING, $address, $append);
    }

    /**
     * @param Mage_Sales_Model_Order_Address $address
     * @param bool $append
     * @return Zolago_Rma_Model_Rma
     */
    public function setOwnBillingAddress(Mage_Sales_Model_Order_Address $address, $append=false) {
        return $this->_setOwnAddress(self::TYPE_RMABILLING, $address, $append);
    }

    /**
     * @param type $type
     * @param Mage_Sales_Model_Order_Address $address
     * @param type $append
     * @return Zolago_Rma_Model_Rma
     */
    protected function _setOwnAddress($type, Mage_Sales_Model_Order_Address $address, $append=false) {
        $address->setId(null);
        $address->setParentId($this->getOrder()->getId());
        $address->setAddressType($type);
        $address->save();
        if($type==self::TYPE_RMASHIPPING) {
            $this->setShippingAddressId($address->getId());
            $this->getResource()->saveAttribute($this, "shipping_address_id");
        } else {
            $this->setBillingAddressId($address->getId());
            $this->getResource()->saveAttribute($this, "billing_address_id");
        }
        // Remove not used addresses
        if(!$append) {
            $this->_cleanAddresses($type, array($address->getId()));
        }
        return $this;
    }

    /**
     * @return Zolago_Rma_Model_Rma
     */
    public function clearOwnShippingAddress() {
        if($this->isShippingSameAsPo()) {
            return $this;
        }
        $this->setShippingAddressId(
            $this->getPo()->getShippingAddress()->getId()
        );
        $this->getResource()->saveAttribute($this, "shipping_address_id");
        $this->_cleanAddresses(self::TYPE_RMASHIPPING);
        return $this;
    }

    /**
     * @return Zolago_Rma_Model_Rma
     */
    public function clearOwnBillingAddress() {
        if($this->isBillingSameAsPo()) {
            return $this;
        }
        $this->setBillingAddressId(
            $this->getPo()->getBillingAddress()->getId()
        );
        $this->getResource()->saveAttribute($this, "billing_address_id");
        $this->_cleanAddresses(self::TYPE_RMABILLING);
        return $this;
    }

    /**
     * @param string $type
     * @param array $exclude
     */
    protected function _cleanAddresses($type, $exclude=array()) {
        Mage::helper('zolagopo')->clearAddresses($this->getPo(), $type, $exclude);
    }

    /**
     * @param array $dhlParams
     * @return type
     */
    public function sendDhlRequest($dhlParams = array()) {
        /** @var Zolago_Rma_Model_Rma_Request $request */
        $request = Mage::getModel('zolagorma/rma_request');
        foreach ($dhlParams as $key=>$val) {
            $request->setParam($key,$val);
        }
        $return = $request->prepareRequest($this);
        return $return;
    }

    /**
     * @return flaot
     */
    public function getTotalValue() {
        $collection = $this->getItemsCollection();
        $price = 0;
        foreach ($collection as $item) {
            $price += $item->getPrice();
        }
        return $price;
    }

    /**
     * @return Zolago_Rma_Model_Rma
     */
    protected function _beforeSave() {
        if(!$this->getId()) {
            $this->getStatusModel()->processNewRmaStatus($this);
            $this->setIsNewFlag(true);
        }

        $this->_processPaymentChannelOwner();

        return parent::_beforeSave();
    }

    /**
     * @return $this|Mage_Core_Model_Abstract
     */
    protected function _afterSave() {
        /** @see Zolago_Rma_PoController::_saveRma */
        if ($dhlRequest = $this->getSendDlhRequestOnSave()) {
            $this->setDhlTrackingParams($this->sendDhlRequest($dhlRequest));
            $this->unsSendDlhRequestOnSave();
        }
        return parent::_afterSave();
    }

    /**
     * @param bool $force
     * @return Zolago_Rma_Model_Rma
     */
    public function _processPaymentChannelOwner($force = false) {
        if ($this->isObjectNew() || $force) {
            $paymentChannelOwner = $this->getPo()->getCurrentPaymentChannelOwner();
            $this->setPaymentChannelOwner($paymentChannelOwner);
        }
        return $this;
    }

    /**
     * generated pdf for customer
     * @return string
     */
    public function getRmaPdf() {
        $pdf = Mage::getModel('zolagorma/pdf');
        return $pdf->getPdfFile($this->getId());
    }

    /**
     * static pdf for customer
     * @return string
     */
    public function getCustomerPdf() {
        /** @var Zolago_Rma_Helper_Data $helper */
        $helper = Mage::helper('zolagorma');
        return $helper->getStaticCustomerPdf();
    }

    /**
     * return rma type name
     * @param int $id
     * @return string
     */
    public function getRmaTypeName() {
        $id = $this->getRmaType();
        $model = Mage::getModel('zolagorma/system_source_type');
        $val = $model->getTypeById($id);
        if ($val) {
            $helper = Mage::helper('zolagorma');
            return $helper->__($val);
        }
        return '';
    }
    
    /**
     * list of types which are used to refund
     * @param 
     * @return 
     */

    protected function _getRefundTransactionTypes() {
        $types = array (
                          Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND,
                          Zolago_Sales_Model_Order_Payment_Transaction::TYPE_DELIVERY_CHARGE
                      );
        return $types;
    }
    /**
     * @return float
     */
    public function getRmaRefundAmount() {
        if(!isset($this->_refundAmount)) {
            $_items = $this->getAllItems();
            $amount = 0;
            foreach ($_items as $item) {
                $amount += $item->getReturnedValue();
            }
            $this->_refundAmount = $amount;
        }
        return $this->_refundAmount;
    }

    /**
     * @return float
     */
    public function getRmaSimpleRefundAmount() {
        if(!isset($this->_refundSimpleAmount)) {

            $customerId = $this->getCustomerId();
            $order = $this->getOrder();
            $orderId = $order->getId();
            $paymentId = $this->getOrder()->getPayment()->getId();
            $types = $this->_getRefundTransactionTypes();
            $existTransactions = Mage::getModel('sales/order_payment_transaction')->getCollection()
                                 ->addFieldToFilter('order_id', $orderId)
                                 ->addFieldToFilter('customer_id', $customerId)
                                 ->addFieldToFilter('txn_type', $types)
                                 ->addFieldToFilter('payment_id', $paymentId);

            $amount = 0;
            foreach($existTransactions as $existTransaction) {
                $amount +=  -$existTransaction->getTxnAmount();
            }
            $this->_refundSimpleAmount = $amount;
        }
        return $this->_refundSimpleAmount;
    }

    public function getRmaRefundAmountMax() {
        if(!isset($this->_refundAmountMax)) {
            $_items = $this->getAllItems();
            $amount = 0;
            foreach($_items as $item) {
                if ($item->getPoItem()->getId()) {
                    $amount += $item->getPoItem()->getFinalItemPrice();
                } else {
                    $amount += $item->getPrice();
                }
            }
            $this->_refundAmountMax = $amount;
        }
        return $this->_refundAmountMax;
    }

    /**
     * @param int $poId
     * @return Zolago_Rma_Model_Resource_Rma_Collection
     */
    public function loadByPoId($poId) {
        $ids = $this->getCollection()
               ->addAttributeToFilter('udpo_id', $poId);

        return $ids;
    }

    public function isAlreadyReturned() {
        $poId = $this->getPo()->getId();

        /** @var Zolago_Payment_Model_Allocation $allocationsModel */
        $allocationsModel = Mage::getModel('zolagopayment/allocation');
        $refundsCollection = $allocationsModel->getCollection()
                             ->addFieldToFilter('po_id',$poId)
                             ->addFieldToFilter('allocation_type','refund');

        Mage::log((string)$refundsCollection->getSelect(),null,'refunds_query.log');

        $negativePaymentsCollection = $allocationsModel->getCollection()
                                      ->addFieldToFilter('po_id',$poId)
                                      ->addFieldToFilter('allocation_type','payment')
                                      ->addFieldToFilter('allocation_amount',array('lt'=>'0'));

        $sumRefunds = 0;
        foreach($refundsCollection as $refund) {
            $sumRefunds += abs($refund->getAllocationAmount());
        }

        $sumNegativePayments = 0;
        foreach($negativePaymentsCollection as $negativePayment) {
            $sumNegativePayments += abs($negativePayment->getAllocationAmount());
        }

        if($sumRefunds != $sumNegativePayments) {
            return false;
        } else {
            $txnIds = array();
            foreach($refundsCollection as $allocation) {
                $txnIds[] = $allocation->getRefundTransactionId();
            }
            if(count($txnIds)) {
                /** @var Mage_Sales_Model_Order_Payment_Transaction $transactions */
                $transactions = Mage::getModel("sales/order_payment_transaction")
                                ->getCollection()
                                ->addFieldToFilter('transaction_id',array('in'=>$txnIds))
                                ->addFieldToFilter('txn_status','3');
                if($transactions->getSize() == count($txnIds)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isAlreadySimpleReturned() {
        $customerId = $this->getCustomerId();
        $order = $this->getOrder();
        $orderId = $order->getId();
        $paymentId = $this->getOrder()->getPayment()->getId();

        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $existTransactions = Mage::getModel('sales/order_payment_transaction')->getCollection()
                             ->addFieldToFilter('order_id', $orderId)
                             ->addFieldToFilter('customer_id', $customerId)
                             ->addFieldToFilter('txn_type', $this->_getRefundTransactionTypes())
                             ->addFieldToFilter('payment_id', $paymentId);

        $sumRefunds = 0;
        foreach($existTransactions as $existTransaction) {
            $sumRefunds +=  abs($existTransaction->getTxnAmount());
        }

        if(round($sumRefunds,4) != round($this->getRmaRefundAmountMax(),4)) {
            return false;
        } else {
            $acceptedRefund = true;
            foreach($existTransactions as $existTransaction) {
                if($existTransaction->getTxnStatus() != Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED) {
                    $acceptedRefund = false;
                }
            }
            if($acceptedRefund == true) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * check if rma is delivery return 
     */

    public function isDeliveryReturn() {
        $reclamationIds = Mage::getStoreConfig('urma/general/zolagorma_reclamation_ids');
        if (!$reclamationIds) {
            return true;
        }
        $reclamationArray = explode(',',$reclamationIds);
        $isReturn = true;
        foreach ($this->getAllItems() as $item) {
            if ($condition = $item->getItemCondition()) {
                if (!in_array($condition,$reclamationArray)) {
                    return true;
                } else {
                    $isReturn = false; // at least one product as reclamation
                }
            }
        }
        return $isReturn;
    }
}
