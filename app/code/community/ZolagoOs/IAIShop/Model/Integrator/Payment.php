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
                $this->addOrderToConfirmMessage($orderId);
                $this->log($this->getHelper()->__('Płatność do zamówienia %s została zaimportowana do IAI Shop z numerem %s',$orderId,$response->result->payment_id));
            } else {
                $this->getHelper()->fileLog($response->result);
                $this->log($this->getHelper()->__('Płatność w IAI Api nie ma identyfikatora dla zamówienia %s',$orderId));
            }
        } else {
            $this->getHelper()->fileLog($response->errors);
            $this->log($this->getHelper()->__('Błąd IAI-Shop Api %d dla zamówienia %s: %s',$response->errors->faultCode,$orderId,$response->errors->faultString));
        }

    }

    /**
     * process response from confirm payment
     */
    protected function _processConfirmResponse($response,$orderId) {
        if (!empty($response->errors->faultCode)) {
            $this->getHelper()->fileLog($response->errors);
            $this->log($this->getHelper()->__('Błąd IAI-Shop API: (%s) %s',$response->errors->faultCode,$response->errors->faultString));
        } else {
            $this->log($this->getHelper()->__('Potwierdzono płatność do zamówienia %s',$orderId));
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

                    $po = Mage::getModel('udpo/po')->loadByIncrementId($item->order_id);
                    if (!$po->getExternalPaymentId()) {
                        $response = $iaiConnector->addPayment($item);
                        if (!empty($response->result->payment_id)) {
                            $po->setExternalPaymentId($response->result->payment_id)
                            ->save();
                            $this->processResponse($response,$item->order_id);
                            $responseConfirm = $iaiConnector->confirmPayment($response->result->payment_id);
                            $this->_processConfirmResponse($responseConfirm,$item->order_id);
                        }
                    } else {
                        $this->addOrderToConfirmMessage($item->order_id);
                    }
                }
            }
            $this->confirmOrderMessages($payments->list);
        }

    }


}