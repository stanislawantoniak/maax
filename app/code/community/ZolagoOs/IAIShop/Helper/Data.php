<?php

class ZolagoOs_IAIShop_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $_client;

    /**
     * @return ZolagoOs_IAIShop_Model_Client_Connector
     */
    public function getIAIShopConnector($vendorId)
    {
        if (!$this->_client) {
            $connector = Mage::getSingleton('zosiaishop/client_connector');
            $connector->setVendorId($vendorId);
            $this->_client = $connector;
        }
        return $this->_client;
    }

    public function getProducts($params)
    {
        foreach ($params as $vendorId => $orders) {
            // Init IAI-Shop client for the vendor
            $client = $this->getClient($vendorId);
            $client->getProducts($vendorId, $orders);
        }
    }

    /**
     * @param $params
     */
    public function addOrders($params)
    {
        foreach ($params as $vendorId => $orders) {
            // Init IAI-Shop client for the vendor
            $iaiShopConnector = $this->getIAIShopConnector($vendorId);
            $iaiShopConnector->addOrders($orders);
        }
    }

    /**
     * map payment method
     */
    public function getMappedPayment($payment) {
        //Sposób zapłaty za zamówienie.
        // Dopuszczalne wartości
        // "cash_on_delivery" - pobranie,
        // "prepaid" - przedplata,
        // "tradecredit" - kredytKupiecki.
        $paymentMethodsMapping = array(
                                     "online_bank_transfer" => "prepaid",
                                     "credit_card" => "prepaid",
                                     "bank_transfer" => "tradecredit",
                                     "cash_on_delivery" => "cash_on_delivery"
                                 );
        return isset($paymentMethodsMapping[$payment])? $paymentMethodsMapping[$payment]: 'unknown';
    }

    /**
     * return order operator name
     */
    public function getOrderOperator() {
        return 'MODAGO';
    }

    /**
     * papped counties
     */
    public function getMappedCountry($country) {
        $countries = array("PL" => "Polska");
        return isset($countries[$country])? $countries[$country]: 'unknown';
    }

    /**
     * mapped delivery
     */
    public function getMappedDelivery($delivery) {
        $deliveryMethodsMapping = array(
                                      "polish_post" => 6,
                                      "standard_courier" => 5,
                                      "inpost_parcel_locker" => 7
                                  );
        return isset($deliveryMethodsMapping[$delivery])? $deliveryMethodsMapping[$delivery]: 'unknown';
    }

    /**
     * @param $params
     */
    public function addPayments($params)
    {
        foreach ($params as $vendorId => $payments) {
            $iaiShopConnector = $this->getIAIShopConnector($vendorId);

            foreach ($payments as $payment) {
                $iaiShopConnector->addPayment($payment);
            }
        }
    }
    
    /**
     * create logs (database)
     */

     public function log($vendorId,$message) {
         $log = Mage::getModel('zosiaishop/log');
         $log->setVendorId($vendorId)
             ->setLog($message)
             ->save();
         return $log;
     }
     
    /**
     * create log (file in var/log);
     */
    public function fileLog($mess) {
        Mage::log($mess,null,'iaishop_log.log');
    }
}