<?php

require Mage::getModuleDir('controllers', 'Zolago_Po' ) . DS . "PoController.php";

class Zolago_Rma_PoController extends Zolago_Po_PoController
{

    public function rmaAction() {
        /**
         * RMA List
         * @todo Implement
         */
        $this->_viewAction();
    }
    protected function _canViewRma(ZolagoOs_Rma_Model_Rma $rma)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($rma->getId() && $rma->getCustomerId() && ($rma->getCustomerId() == $customerId)) {
            return true;
        }
        return false;
    }

    public function printRmaAction() {
        $rmaId = $this->getRequest()->getParam('rma_id');
        $rma = Mage::getModel('urma/rma')->load($rmaId);
        if ($this->_canViewRma($rma)) {
            $ioAdapter = new Varien_Io_File();
            $tracks = $rma->getAllTracks();
            foreach ($tracks as $track) {
                if (!$dhlFile = $track->getLabelPic()) {
                    continue;
                }
                return $this->_prepareDownloadResponse(basename($dhlFile), @$ioAdapter->read($dhlFile), 'application/pdf');
            }
        }
        $this->_redirectReferer();
    }
    public function rmaListAction() {
        $activeNoRma = Mage::helper('zolagocommon')->isModuleActive('ZolagoOs_NoRma');
        if($activeNoRma) {
            return $this->_redirect('sales/order/process');
        }
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/rma/history');
        }

        $this->renderLayout();
    }
    public function historyAction()
    {
        /**
         * @todo Implement
         */
        $this->_viewAction();
    }

    public function newRmaAction()
    {
        if (!$this->_loadValidPo()) {
            return;
        }
        $session =   Mage::getSingleton('core/session');
        /* @var $session Mage_Core_Model_Session */

        // Rma data
        $rma = Mage::getModel("zolagorma/rma");
        /* @var $rma Zolago_Rma_Model_Rma */
        if($data=$session->getRma(true)) {
            $rma->setData($data);
        }
        Mage::register("current_rma", $rma);


        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/rma/history');
        }
        $this->renderLayout();
    }
    public function saveRmaAction()
    {
        $request = $this->getRequest();
        $session = Mage::getSingleton('core/session');
        try {
            /**
             * @todo Add processing carrier and insert tracking
             */
            $this->_saveRma();

            $rma = Mage::registry('current_rma');

            if(is_array($rma)) {
                $rma = current($rma);
            }
            if($rma && $rma instanceof Zolago_Rma_Model_Rma && $rma->getId()) {
                $session->setLastRmaId($rma->getId());
                $this->_redirect('sales/rma/success');
            } else {
                Mage::throwException("No rma created");
            }
        } catch (Exception $e) {
            $session->
            addError($e->getMessage())->
            setData("rma", $request->getParam('rma'));

            $this->_redirect('*/*/newrma', array('po_id'=>$this->getRequest()->getParam('po_id')));
        }
    }
    public function saveRmaCourierAction()
    {
        $request = $this->getRequest();
        $session = Mage::getSingleton('core/session');
        try {
            $this->_saveRmaDetails();
            $session->addSuccess($this->__("Courier data saved"));
            $this->_redirect('sales/rma/view', array('id'=>$this->getRequest()->getParam('rma_id')));
        } catch (Exception $e) {
            $session->
            addError($e->getMessage())->
            setData("rma", $request->getParam('rma'));

            $this->_redirect('sales/rma/courier', array('id'=>$this->getRequest()->getParam('rma_id')));
        }
    }

    protected function _saveRmaDetails()
    {
        $rma = $this->_initRma(true);

        $data = $this->getRequest()->getPost('rma');

        $data['send_email'] = true;

        if (empty($rma)) {
            Mage::throwException('Return could not be edited');
        }

        /* @var $rma Zolago_Rma_Model_Rma */
        $po = $rma->getPo();

        $this->_rmaSetOwnShippingAddress($data, $rma);
        // Send can throw exception so this need to be done with rma save
        $dhlRequest = $this->_getTrackignRequest($data);
        if ($dhlRequest) {
            $rma->setSendDlhRequestOnSave($dhlRequest);
        }
        $rma->save();
        // set tracking
        $this->_setTracking($dhlRequest, $rma, $data);
        if (!empty($data['send_email'])) {
            $rma->setEmailSent(true);
        }
        $po->setCustomerNoteNotify(!empty($data['send_email']));
        $po->setIsInProcess(true);

        $rma->getPo()->save();

        if ($rma->getCurrentTrack()) {
            Mage::dispatchEvent("zolagorma_rma_track_added", array(
                                    "rma" => $rma,
                                    "track" => $rma->getCurrentTrack()
                                ));
        }
        $rma->setRmaStatus(Zolago_Rma_Model_Rma_Status::STATUS_PENDING_PICKUP);
        $rma->save();

        // add comment
        $this->_addOrderNumberToComment($rma);
        Mage::helper('udropship')->processQueue();
    }


    /**
     * add order number from dhl (carrier order) to comment and notify customer
     */
    protected function _addOrderNumberToComment($rma) {
        $trackingParams = $rma->getDhlTrackingParams();
        if ($trackingParams) {
            // add comment to rma
            $model = $rma->createComment(Mage::helper('orbashipping')->__('Carrier was ordered. Your order number is %s. DHL phone number (42 634 53 45)',$trackingParams['orderNumber']),true,true);
            $rma->addComment($model);
            $rma->saveComments();
            Mage::dispatchEvent("zolagorma_rma_comment_added", array(
                                        "rma"          => $rma,
                                        "comment"      => $model,
                                        "notify"       => true,
                                    ));
        }

    }
    protected function _initRma($forSave=false)
    {
        $rma = false;
        $rmaId = $this->getRequest()->getParam('rma_id');
        $poId = $this->getRequest()->getParam('po_id');
        if ($rmaId) {
            $rma = Mage::getModel('urma/rma')->load($rmaId);
        }
        elseif ($poId) {
            $po = Mage::getModel('zolagopo/po')->load($poId);

            if (!$po->getId()) {
                Mage::throwException($this->__('The order no longer exists.'));
            }
            $data = $this->getRequest()->getParam('rma');
            if (!isset($data['items_single'])) {
                Mage::throwException($this->__('No items.'));
            }
            $rma = Mage::getModel('zolagorma/servicePo', $po)->prepareRmaForSave($data);
        }
        Mage::register('current_rma', $rma);
        return $rma;
    }

    protected function _saveRma()
    {
        $rmas = $this->_initRma(true);
        $data = $this->getRequest()->getPost('rma');
        $data['send_email'] = true;
        $comment = '';
        if (empty($rmas)) {
            Mage::throwException('Return could not be created');
        }

        // If pickup date and time is not set
        // It is a RETURN flow
        if(isset($data['carrier_date']) && $data['carrier_date'] != ""
                && isset($data['carrier_time_from'])  && $data['carrier_time_from'] != ""
                && isset($data['carrier_time_to']) && $data['carrier_time_to'] != "") {
            $dhlRequest = array (
                              'shipmentDate' => $data['carrier_date'],
                              'shipmentStartHour' => $data['carrier_time_from'],
                              'shipmentEndHour' => $data['carrier_time_to'],
                              'shippingPaymentType' => Orba_Shipping_Model_Carrier_Client_Dhl::PAYER_TYPE_RECEIVER, // chyba powinno być USER ale trzeba jeszcze sprawdzić to w DHL
                              'paymentType' => Orba_Shipping_Model_Carrier_Client_Dhl::PAYMENT_TYPE_CASH
                          );
        }
        else {
            $dhlRequest = NULL;
        }

        $config = Mage::getSingleton("shipping/config");
        /* @var $config Mage_Shipping_Model_Config */

        foreach ($rmas as $rma) {

            /* @var $rma Zolago_Rma_Model_Rma */
            $po = $rma->getPo();
            /* @var $po Zolago_Po_Model_Po */
            $rma->register();
            // set tracking
            if ($dhlRequest) {
                $track = Mage::getModel('urma/rma_track');
                $track->setTrackCreator(Zolago_Rma_Model_Rma_Track::CREATOR_TYPE_CUSTOMER);
                $track->setTrackType(GH_Statements_Model_Track::TRACK_TYPE_RMA_CLIENT);
                $rma->addTrack($track);
                $rma->setCurrentTrack($track);
                // Send can throw exception so this need to be done with rma save
                $rma->setSendDlhRequestOnSave($dhlRequest);
            }
        }
        if (!empty($data['comment_text'])) {
            foreach ($rmas as $rma) {
                $rma->setCommentText($data['comment_text']);
            }
            $comment = $data['comment_text'];
        }
        if (!empty($data['send_email'])) {
            foreach ($rmas as $rma) {
                $rma->setEmailSent(true);
            }
        }

        $rma->setRmaReason(@$data['rma_reason']);

        $po->setCustomerNoteNotify(!empty($data['send_email']));
        $po->setIsInProcess(true);
        /** @var Mage_Core_Model_Resource_Transaction $trans */
        $trans = Mage::getModel('core/resource_transaction');
        foreach ($rmas as $rma) {
            $rma->setIsCutomer(true);
            $trans->addObject($rma);

        }
        $trans->addObject($rma->getPo())->save();

        foreach ($rmas as $rma) {

            Mage::dispatchEvent("zolagorma_rma_created", array(
                                    "rma" => $rma
                                ));

            $rma->save();

            $trackingParams = $rma->getDhlTrackingParams();
            if ($dhlRequest && $trackingParams) {
                $track = $rma->getCurrentTrack();
                $carrier = Orba_Shipping_Model_Carrier_Dhl::CODE;
                $track->setTrackNumber($trackingParams['trackingNumber']);
                $track->setTitle($config->getCarrierInstance('orbadhl')->getConfigData('title'));
                $track->setCarrierCode($carrier);
                $track->setLabelPic($trackingParams['file']);

                $track->setData("gallery_shipping_source", isset($trackingParams["gallery_shipping_source"]) ? $trackingParams["gallery_shipping_source"] : 0);
                $track->setData("shipping_source_account", $trackingParams["account"]);
                $po = $rma->getPo();
                $weight = $po->getTracking()->getWeight();
                $type = Mage::helper('orbashipping/carrier_dhl')->getDhlParcelKeyByWeight($weight);
                $manager = Mage::helper('orbashipping')->getShippingManager($carrier);
                $manager->calculateCharge($track,$type,$po->getVendor(),$rma->getTotalValue(),0);
                $this->_addOrderNumberToComment($rma);
            }

            if($rma->getCurrentTrack()) {
                Mage::dispatchEvent("zolagorma_rma_track_added", array(
                                        "rma"		=> $rma,
                                        "track"		=> $rma->getCurrentTrack()
                                    ));
            }
            if(isset($data['customer_address_id'])) {
                // Duplicate Customer address to RMA address tored in Order Address
                $customerAddress = $this->_getCustomer()->getAddressById(
                                       $data['customer_address_id']
                                   );
                if($customerAddress && $customerAddress->getId()) {
                    $orderAddress = $rma->getShippingAddress();
                    $orderAddress = $this->_prepareShippingAddress($customerAddress, $orderAddress);
                    $rma->setOwnShippingAddress($orderAddress);
                }
            }
        }
        Mage::helper('udropship')->processQueue();
        Mage::getSingleton('core/session')->setRmaPrintId($rma->getId());
    }

    protected function _getTrackignRequest($data)
    {
        // If pickup date and time is not set
        // It is a RETURN flow
        if (isset($data['carrier_date']) && $data['carrier_date'] != ""
                && isset($data['carrier_time_from']) && $data['carrier_time_from'] != ""
                && isset($data['carrier_time_to']) && $data['carrier_time_to'] != ""
           ) {
            $dhlRequest = array(
                              'shipmentDate' => $data['carrier_date'],
                              'shipmentStartHour' => $data['carrier_time_from'],
                              'shipmentEndHour' => $data['carrier_time_to'],
                          );
        } else {
            $dhlRequest = NULL;
        }

        return $dhlRequest;
    }

    /**
     * @param $dhlRequest
     * @param $rma
     * @return $rma Zolago_Rma_Model_Rma
     */
    protected function _setTracking($dhlRequest, $rma, $data)
    {
        /* @var $config Mage_Shipping_Model_Config */
        $config = Mage::getSingleton("shipping/config");
        $trackingParams = $rma->getDhlTrackingParams();
        if ($dhlRequest && $trackingParams) {
            $track = Mage::getModel('urma/rma_track');
            $track->setTrackCreator(Zolago_Rma_Model_Rma_Track::CREATOR_TYPE_CUSTOMER);
            $track->setTrackType(GH_Statements_Model_Track::TRACK_TYPE_RMA_CLIENT);
            $track->setTrackNumber($trackingParams['trackingNumber']);
            $track->setTitle($config->getCarrierInstance('orbadhl')->getConfigData('title'));
            $track->setCarrierCode(Orba_Shipping_Model_Carrier_Dhl::CODE);
            $track->setLabelPic($trackingParams['file']);
            $rma->addTrack($track);
            $rma->setCurrentTrack($track);


            $rma->setCarrierDate($data['carrier_date']);
            $rma->setData("carrier_time_from",$data['carrier_time_from']);
            $rma->setData("carrier_time_to",$data['carrier_time_to']);
        }
        return $rma;
    }

    /**
     * @param $data
     * @param $rma Zolago_Rma_Model_Rma
     * @return $rma Zolago_Rma_Model_Rma
     */
    protected function _rmaSetOwnShippingAddress($data, $rma)
    {
        if (isset($data['customer_address_id'])) {
            // Duplicate Customer address to RMA address tored in Order Address
            $customerAddress = $this->_getCustomer()->getAddressById(
                                   $data['customer_address_id']
                               );
            if ($customerAddress && $customerAddress->getId()) {
                $orderAddress = $rma->getShippingAddress();
                $orderAddress = $this->_prepareShippingAddress($customerAddress, $orderAddress);
                $rma->setOwnShippingAddress($orderAddress);
                $rma->setCustomerAddressId($data['customer_address_id']);
            }
        }
        return $rma;
    }

    /**
     * Convert Customer Address to Order Addres to be bind to RMA
     * @param Mage_Customer_Model_Address $customerAddress
     * @param Mage_Sales_Model_Order_Address $orderAddress
     * @return Mage_Sales_Model_Order_Address
     */
    protected function _prepareShippingAddress(
        Mage_Customer_Model_Address $customerAddress,
        Mage_Sales_Model_Order_Address $orderAddress) {

        Mage::helper('core')->copyFieldset(
            'customer_address',
            'to_quote_address',
            $customerAddress,
            $orderAddress
        );

        // Clear billing data
        $orderAddress->setVatId(null);
        $orderAddress->setNeedInvoice(0);

        return $orderAddress;

    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer() {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
}