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
     * mapped currency (now only PLN)
     */
     public function getMappedCurrency($currency) {
         $currencyList = array (
          'PLN' => 'PLN'
         );
         
        if (!isset($currencyList[$currency])) {
            $this->fileLog('wrong currency '.$currency);
        }
        return isset($currencyList[$currency])? $currencyList[$currency]:'unknown';
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
                                     "bank_transfer" => "prepaid",
                                     "cash_on_delivery" => "cash_on_delivery"
                                 );
        if (!isset($paymentMethodsMapping[$payment])) {
            $this->fileLog('wrong payment method');
        }
        return isset($paymentMethodsMapping[$payment])? $paymentMethodsMapping[$payment]: 'unknown';
    }

    /**
     * map payment method
     *
     * @todo make configuration in magento admin
     */
    public function getMappedPaymentForPayments($payment) {
        //Sposób zapłaty za zamówienie.
        // Dopuszczalne wartości
        // "cash_on_delivery" - pobranie,
        // "prepaid" - przedplata,
        // "tradecredit" - kredytKupiecki.
        $paymentMethodsMapping = array(
                                     "online_bank_transfer" => "Obsługa własna kart kredytowych",
                                     "credit_card" => "Obsługa własna kart kredytowych",
                                     "bank_transfer" => "Obsługa własna kart kredytowych",
                                     "cash_on_delivery" => "Pobranie"
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
                             84 => 'DHL',
                             80 => 'DHL',
                             18 => 'DHL',
                             81 => 'DHL',
                             82 => 'DHL',
                             83 => 'DHL',
                             100044 => 'DHL',
                             100056 => 'DHL',
                             17 => 'DHL',
                             19 => 'DHL',
                             10 => 'DPD',
                             6	=> 'DPD',
                             37 => 'DHL',
                             2 => 'UPS',
                             28 => 'UPS',
                             85 => 'GLS',
                             100039 => 'POCZTA POLSKA',
                             45 => 'INPOST',
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
                                      "polish_post" => 100039, // POCZTEX KURIER KRAJOWY
                                      "standard_courier" => 6, // STANDARD DPD
                                      "inpost_parcel_locker" => 45 //INPOST
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