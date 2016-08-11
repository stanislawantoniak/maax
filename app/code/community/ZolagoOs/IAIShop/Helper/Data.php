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
     *
     * @todo make configuration in magento admin
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
        if (!isset($paymentMethodsMapping[$payment])) {
            $this->fileLog('wrong payment method');
        }
        return isset($paymentMethodsMapping[$payment])? $paymentMethodsMapping[$payment]: 'unknown';
    }

    /**
     * return order operator name
     */
    public function getOrderOperator() {
        return 'MODAGO';
    }

    
    /**
     * mapped carriers
     *
     * @todo make configuration in admin
     * @param int $carrierId
     * @return string
     */
    public function getMappedCarrier($carrierId) {
        $mappedCarrier = array(
            84 => 'dhl',
            80 => 'dhl',
            18 => 'dhl',
            81 => 'dhl',
            82 => 'dhl',
            83 => 'dhl',
            100044 => 'dhl',
            100056 => 'dhl',
            17 => 'dhl',
            19 => 'dhl',
            10 => 'dhl',
            37 => 'dhl',
            2 => 'ups',
            28 => 'ups',
            85 => 'gls',
        );
        if (!isset($mappedCarrier[$carrierId])) {
            $this->fileLog('Wrong carrier '.$carrierId);
        }
        return isset($mappedCarrier[$carrierId])? $mappedCarrier[$carrierId]: 'unknown';
    }
    /**
     * mapped countries
     *
     * @todo make configuration in magento admin
     */
    public function getMappedCountry($country) {
        $countries = array("PL" => "Polska");
        if (!isset($countries[$country])) {
            $this->fileLog('Wrong country '.$country);
        }
        return isset($countries[$country])? $countries[$country]: 'unknown';
    }

    /**
     * mapped delivery
     *
     * @todo make configuration in magento admin
     */
    public function getMappedDelivery($delivery) {
        $deliveryMethodsMapping = array(
                                      "polish_post" => 100039,
                                      "standard_courier" => 10,
                                      "inpost_parcel_locker" => 45
                                  );
        if (!isset($deliveryMethodsMapping[$delivery])) {
            $this->fileLog('Wrong delivery '.$delivery);
        }
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