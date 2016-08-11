<?php

/**
 * Class Zolago_Adminhtml_Sales_Order_ChangeController
 */
class Zolago_Adminhtml_Sales_Order_ChangeController extends Mage_Adminhtml_Controller_Action
{
    public function changeToCodAction()
    {
        $orderId = $this->getRequest()->getParam("order_id", NULL);
        if ($orderId == NULL) {
            $this->_getSession()->addError($this->__('Order not found'));
            $this->_redirectReferer();
            return;
        }
		/** @var Mage_Sales_Model_Order_Payment $paymentModel */
        $paymentModel = Mage::getModel("sales/order_payment");
        $paymentModel->load($orderId, "parent_id");

        if ($paymentModel->getId() == NULL) {
            $this->_getSession()->addError($this->__('Payment for order not found'));
            $this->_redirectReferer();
        }

        try {
            $paymentModel->setData('method', 'cashondelivery');
			// Additional Information must be reset because we change method
			// for now id DB there is provider => '' for COD (from our chechout)
			// but it have no sense so empty array
			$paymentModel->setAdditionalInformation(array());
            $paymentModel->save();
			
			// get list of PO for this order
			/** @var ZolagoOs_OmniChannel_Model_Mysql4_Po_Collection $collection */
			$collection = Mage::getResourceModel('udpo/po_collection');
			$collection->setOrderFilter($orderId);
			/** @var Zolago_Po_Model_Po $po */
			foreach ($collection as $po) {
				// for all set correct payment_channel_owner depend on current configuration
				$po->_processPaymentChannelOwner(true); // $force = true
				$po->save();
			}
			
            $this->_getSession()->addSuccess($this->__('Payment has been successfully changed to COD'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('An error occurred on change to COD action.'));
            Mage::logException($e);
        }

        $this->_redirectReferer();
    }


    public function changeEmailAction()
    {
        $orderId = $this->getRequest()->getParam("order_change_email_order", NULL);
        $email = $this->getRequest()->getParam("order_change_email_email", "");

        if ($orderId == NULL) {
            $this->_getSession()->addError($this->__('Order not found'));
            $this->_redirectReferer();
            return;
        }
        if (empty($email)) {
            $this->_getSession()->addError($this->__('Please enter email'));
            $this->_redirectReferer();
            return;
        }

        $order = Mage::getModel("sales/order");
        $order->load($orderId);
        if(!$order->getCustomerIsGuest()){
            $this->_getSession()->addError($this->__('This action allowed for GUEST orders only'));
            $this->_redirectReferer();
            return;
        }


        try {
            $order->setCustomerEmail($email);
            $order->save();
            $this->_getSession()->addSuccess($this->__('Customer email has been successfully changed.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('An error occurred on change customer email action.'));
            Mage::logException($e);
        }

        $this->_redirectReferer();
    }
    public function trackAction() {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        Mage::register('current_shipment',$shipment);
        $this->loadLayout();
        try {
            $helper = Mage::helper('orbashipping/carrier_tracking');
            $trackingManager = Mage::helper('orbashipping/carrier_manual');
            $helper->setHelper($trackingManager);
            $trackId = Mage::app()->getRequest()->getParam('id');
            $track = Mage::getModel('sales/order_shipment_track')->load($trackId);
            $helper->collectTracking(array(array($trackId=>$track)));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('An error occurred on change customer email action.'));
            Mage::logException($e);
        }
        $response = $this->getLayout()->getBlock('shipment_tracking')->toHtml();
        $this->getResponse()->setBody($response);
    }
}