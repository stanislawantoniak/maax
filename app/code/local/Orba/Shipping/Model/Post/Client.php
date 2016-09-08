<?php
/**
 * client poczta polska
 */
class Orba_Shipping_Model_Post_Client extends Orba_Shipping_Model_Client_Soap {


    protected $_default_params = array (
                                 );


    /**
     *
     */
    protected function _construct() {
        $this->_init('orbashipping/post_client');
    }


    /**
     * pack number
     */
    protected function _getGuid() {
        mt_srand((double)microtime()*10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $retval = substr($charid, 0, 32);
        return $retval;
    }

    /**
     * wsdl url
     *
     * return string;
     */
    protected function _getWsdlUrl() {
        return Mage::getStoreConfig('carriers/zolagopp/gateway');
    }


    public static function useBusinessPackType() {
        return (bool)Mage::getStoreConfig('carriers/zolagopp/business_type');
    }
    /**
     * @return array
     */
    protected function _getSoapMode() {
        $login = Mage::getStoreConfig('carriers/zolagopp/id');
        $password = Mage::helper('core')->decrypt(Mage::getStoreConfig('carriers/zolagopp/password'));
        $mode = array
                (
                    'soap_version' => 'SOAP_1_1',  // use soap 1.1 client
                    'trace' => 1,
                    'login' => $login,
                    'password' => $password,
                );
        return $mode;
    }

    /**
     * clear envelope
     */
    public function clearEnvelope() {
        $message = Mage::getModel('orbashipping/post_message_clearEnvelope')->getObject();
        $result = $this->_sendMessage('clearEnvelope',$message);
        if (!empty($result->retval)) {
            return true;
        }
        $this->_checkResult($result);
        return false;
    }
    /**
     * normalize postcode
     */
    protected function _normalizePostcode($code) {
        return str_replace('-','',$code);
    }
    
    /**
     * create business pack
     */

    public function _createDeliveryPackBusiness($settings) {
        $message = Mage::getModel('orbashipping/post_message_pack_list')->getObject();
        $data = Mage::getModel('orbashipping/post_message_pack_business')->getObject();
        $data->adres = $this->_prepareAddress();
        $data->gabaryt = $this->_settings['size'];
        $data->masa = $this->_settings['weight'];
        $data->guid = $this->_getGuid();
        $message->przesylki[] = $data;
        Mage::log($message);
        $result = $this->_sendMessage('addShipment',$message);
        Mage::log($result);
        return $this->_prepareResult($result);
    }
    
    /**
     * set Active card
     */

    public function setActiveCard($card) {
        $message = Mage::getModel('orbashipping/post_message_card');
        $message->idKarta = $card;
        $card->aktywna = true;
        Mage::log($message);
        $result = $this->_sendMessage('setAktywnaKarta',$card);
        return $result;        
    }
    
    /**
     * get available cards
     */

    public function getCards() {
        $result = $this->_sendMessage('getKarty',null);
        return $result;
    }
    
    /**
     * password expired date
     */

    public function getPasswordExpiredDate() {
        $result = $this->_sendMessage('getPasswordExpiredDate',null);
        return $result;         
    }
    /**
     * creating packs // old version
     */
     /*
    public function createDeliveryPacks($settings) {
        if (!(int)$this->_settings['weight']) {
            Mage::throwException(Mage::helper('orbashipping')->__('No package weight'));
        }
        if (self::useBusinessPackType()) {
            return $this->_createDeliveryPackBusiness($settings);
        } else {
            return $this->_createDeliveryPackStandard($settings);
        }
    }

    */
    protected function _prepareReceiverAddress() {
        $address = new adresType();
        $receiver = $this->_receiverAddress;
        $address->nazwa = $receiver['firstname'].' '.$receiver['lastname'];
        $address->nazwa2 = $receiver['company'];
        $address->ulica = $receiver['street'];
        $address->miejscowosc = $receiver['city'];
        $address->kodPocztowy = $this->_normalizePostcode($receiver['postcode']);
        $address->kraj = $receiver['country_id'];
        $address->telefon = $receiver['telephone'];
        $address->osobaKontaktowa = $address->nazwa;
        return $address;
    }
    /**
     * prepare address
     */
    protected function _prepareAddress() {
        $address = Mage::getModel('orbashipping/post_message_address')->getObject();
        $sender = $this->_shipperAddress;
        $address->nazwa = $sender['name'];
        $address->ulica = $sender['street'];
        $address->miejscowosc = $sender['city'];
        $address->kodPocztowy = $this->_normalizePostcode($sender['postcode']);
        return $address;
    }
    
    /**
     * prepare data for COD
     */
    protected function _prepareCod($value) {
        $cod = new pobranieType();
        $cod->sposobPobrania = Mage::getStoreConfig('carriers/zolagopp/cod_settlement_type');
        $cod->kwotaPobrania = $value *100; // gr
        if ($cod->sposobPobrania == sposobPobraniaType::RACHUNEK_BANKOWY) {
            $cod->nrb = Mage::getStoreConfig('carriers/zolagopp/cod_account_number');
            $cod->tytulem = sprintf(Mage::getStoreConfig('carriers/zolagopp/cod_transfer_title'),$this->_settings['udpo']->getIncrementId());
        }
        return $cod;
    }
    
    /**
     * insurance values
     */
    protected function _prepareInsurance() {
        $insurance = new ubezpieczenieType();
        $insurance->rodzaj = rodzajUbezpieczeniaType::STANDARD;
        $insurance->kwota = $this->_settings['value']*100;
        return $insurance;
    }
    /**
     * create standard packs
     */

    public function createDeliveryPacks($settings) {
        $message = new addShipment();
        $data = new uslugaKurierskaType();
        $data->adres = $this->_prepareReceiverAddress();
        $data->guid = $this->_getGuid();

        $data->masa = $this->_settings['weight'];
        $data->wartosc = $this->_settings['value']*100; // gr
        $data->termin = terminRodzajType::EXPRES24;
//        $data->ubezpieczenie = $this->_prepareInsurance();
        
        if ($codValue = $this->_settings['cod']) {
            $data->pobranie = $this->_prepareCod($codValue);
        }

        $message->przesylki[] = $data;
        Mage::log($message);
        $result = $this->_sendMessage('addShipment',$message);
        Mage::log($result);
        return $this->_prepareResult($result);
    }

    /**
     * parse error message
     */
    protected function _parseErrorMessage($postResult) {
        if (!empty($postResult->error)) {
            if (!empty($postResult->error->errorDesc)) {
                Mage::throwException($postResult->error->errorDesc);
            }
            elseif (!empty($postResult->error->errorNumber)) {
                Mage::throwException(Mage::helper('orbashipping')->__('%s server error: Error number %s',
                                     'poczta-polska',
                                     $postResult->error->errorNumber));
            }
        }
    }

    /**
     * check if answer is right
     */
    protected function _checkResult($data) {
        if (is_array($data) && !empty($data['error'])) {
            Mage::throwException(Mage::helper('orbashipping')->__('%s server error: %s','poczta-polska',$data['error']));            
        }
        if (empty($data->retval)) {
            Mage::throwException(Mage::helper('orbashipping')->__('%s server error: No valid answer','poczta-polska'));
        }
        return true;
    }
    /**
     * prepare inpost answer
     */
    protected function _prepareResult($data) {
        $this->_checkResult($data);
        $result = $data->retval;
        $this->_parseErrorMessage($result);
        return $result;
    }


    /**
     * labels to print
     */
    public function getLabels($tracking) {
        throw new Exception('not implemented yet');
        if (empty($tracking)) {
            return false;
        }
        if (!is_array($tracking)) {
            $tracking = array($tracking);
        }
        $codes = array();
        foreach ($tracking as $track) {
            $codes[] = $track->getNumber();
        }
        $message = new getAddresLabelByGuid();
        $message->guid = $codes;
        $response = $this->_sendMessage('getAddresLabelByGuid',$message);
        if (!empty($response->error)) {	            
            Mage::throwException(Mage::helper('orbashipping')->__('Service %s get label error: %s','Poczta Polska',$response->error->errorDesc));
        }
        if (empty($response->content)) {
            Mage::throwException(Mage::helper('orbashipping')->__('Service %s get label error: %s','Poczta Polska',
                    Mage::helper('orbashipping')->__('Empty content')));
        }
        $out['data'] = '';
        foreach ($response->content as $content) {
            $out['data'] .= $content;
        }
        $out['numbers'] = $codes;
        return $out;
    }


    /**
     * format results
     */
    public function processLabelsResult($method,$data) {
        throw new Exception('not implemented yet');
        try {
            $xml = simplexml_load_string($data['data']);
        } catch (Exception $x) {
            // if there is no xml - means ok
            $xml = false;
        }
        if ($xml === false) { // ok
            $result = array (
                          'status' => true,
                          'labelData' => $data['data'],
                          'labelName' => implode('_',$data['numbers']).'.'.Orba_Shipping_Helper_Packstation_Inpost::FILE_EXT,
                          'message' => 'Shipment ID: ' . implode(',',$data['numbers']),
                      );
        } else {
            $tmp = $this->_prepareResult($data['data']);
            if (!empty($tmp['error'])) {
                $error = $tmp['error'];
            } else {
                $error = 'INPOST Service error '.implode(',',$data['numbers']);
            }
            $result = array(
                          'status' => false,
                          'message' => $error
                      );
        }
        return $result;
    }

    /**
     * cancel package
     * @tod
     */
    public function cancelPack($number) {
        throw new Exception('not implemented yet');
        $data = array (
                    'email' => $this->getAuth('username'),
                    'password' => $this->getAuth('password'),
                    'packcode' => $number
                );
        $result = $this->_sendMessage('cancelpack',$data,'POST');
        if ($result !== '1') {
            $result = $this->_prepareResult($result);
        }
        return $result;
    }
}

