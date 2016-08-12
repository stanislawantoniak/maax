<?php
/**
 * orders integrator for one vendor
 */
class ZolagoOs_IAIShop_Model_Integrator_Payment extends ZolagoOs_IAIShop_Model_Integrator_Ghapi {
    /**
     * prepare new orders list
     *
     * @return array
     */

    public function getGhApiVendorPayments()
    {
        return $this->getGhApiVendorMessages(GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_PAYMENT_DATA_CHANGED);
    }
    /**
     * process response from iaishop
     */
    public function processResponse($response, $orderId) {
        if (!$response->errors->faultCode) {
            if (!empty($response->result->payment_id)) {
                $po = Mage::getModel('udpo/po')->loadByIncrementId($orderId);
                if ($po) {
                    $this->addOrderToConfirmMessage($orderId);
                    $this->log($this->getHelper()->__('Payment for order %s was imported to IAI Shop with number %s',$orderId,$response->result->payment_id));
                } else {
                    $this->log($this->getHelper()->__('Order %s does not exists',$orderId));
                }
            } else {
                $this->getHelper()->fileLog($response->result);
                $this->log($this->getHelper()->__('IAI Api payment has not serial number for order %s',$orderId));
            }
        } else {
            $this->getHelper()->fileLog($response->errors);
            $this->log($this->getHelper()->__('IAI Api Error %d at order %s: %s',$response->errors->faultCode,$orderId,$response->errors->faultString));
        }

    }
    /**
     * sync orders
     */
    public function sync() {

        $payments = $this->getGhApiVendorPayments();
        $iaiConnector = Mage::getModel("zosiaishop/client_connector");
        $iaiConnector->setVendorId($this->getVendor()->getId());

        $paymentForms = $iaiConnector->getPaymentForms()->result->payment_forms;

        if ($payments->status) {
            foreach ($this->prepareOrderList($payments->list) as $item) {
                if (!empty($item->external_order_id) && floatval($item->order_due_amount) == 0) {

                    $item->payment_method_external_id = array_values(array_filter(
                                                            $paymentForms,
                    function ($e) use (&$item) {
                        return $e->name == $this->getHelper()->getMappedPaymentForPayments($item->payment_method);
                    }
                                                        ))[0]->id;

                    $response = $iaiConnector->addPayment($item);
                    if (!empty($response->result->payment_id)) {
                        $this->processResponse($response,$item->order_id);
                    }
                }
            }
            $this->confirmMessages($payments->list);
        }

    }


}