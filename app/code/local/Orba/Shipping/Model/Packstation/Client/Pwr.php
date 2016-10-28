<?php

class Orba_Shipping_Model_Packstation_Client_Pwr extends Orba_Shipping_Model_Client_Soap {

    protected $_returnAddress;

    protected function _construct() {
        $this->_init('orbashipping/packstation_client_pwr');
    }

    /**
     * WSDL url
     *
     * @return string
     */
    protected function _getWsdlUrl() {
        return $this->getHelper()->getApiWsdl();
    }

    /**
     * @return array
     */
    protected function _getSoapMode() {
        $mode = array (
                    'soap_version' => SOAP_1_1,  // use soap 1.1 client
                    'trace' => 1,
                );
        // for tests disable
        $testFlag = (bool)Mage::getConfig()->getNode('global/test_server');
        if ($testFlag) {
            // for tests servers skip ssl security
            $mode['stream_context'] = stream_context_create(
                                          array(
                                              'ssl' => array(
                                                  // set some SSL/TLS specific options
                                                  'verify_peer' => false,
                                                  'verify_peer_name' => false,
                                                  'allow_self_signed' => true
                                              )
                                          )
                                      );
        }
        return $mode;
    }

    /**
     * @return ZolagoOs_Pwr_Helper_Data
     */
    public function getHelper() {
        /** @var ZolagoOs_Pwr_Helper_Data $helper */
        $helper = Mage::helper("zospwr");
        return $helper;
    }
    
    public function setReturnAddress($address) {
      $this->_returnAddress = $address;
    }
     
    protected function _prepareSenderAddress(&$message) {
        $address = $this->_shipperAddress;
        $map = array(
         'SenderCompanyName' => 'company',
         'SenderStreetName' => 'street',
         'SenderCity' => 'city',
         'SenderEMail' => 'email',
         'SenderPostCode' => 'postcode',
         'SenderPhoneNumber' => 'phone',
       );
       foreach ($map as $key => $val) {
         $message->$key = $address[$val];
       }
       $message->SenderBuildingNumber = '.';
    }
    
    protected function _prepareReceiverAddress(&$message) {
        $address = $this->_receiverAddress;
        $map = array (
          'EMail' => 'email',          
          'FirstName' => 'firstname',
          'LastName' => 'lastname',
          'CompanyName' => 'company',
          'StreetName' => 'street',
          'City' => 'city',
          'PostCode' => 'postcode',
          'PhoneNumber' => 'telephone',
          
        );
         foreach ($map as $key => $val) {
           $message->$key = $address[$val];
         } 
    }
    protected function _prepareReturnAddress(&$message) {
        $address = $this->_shipperAddress; // zwracamy na adres nadawcy
        $map = array (
          'ReturnCompanyName' => 'company',
          'ReturnEMail'	      => 'email',
          'ReturnStreetName' => 'street',
          'ReturnCity' => 'city',
          'ReturnPostCode' => 'postcode',
          'ReturnPhoneNumber' => 'phone',
        );
        foreach ($map as $key => $val) {
           $message->$key = $address[$val];
        }
    }
    /**
     * override auth function
     */

    public function getAuth($param = null) {
      $auth = parent::getAuth($param);
      $message = new StdClass();
      $message->PartnerID = $auth->username;
      $message->PartnerKey = $auth->password;      
      return $message;
    }
    /**
     * create label
     */
     public function generateLabelBusinessPack() {
       $message = $this->getAuth();
       if ($this->_settings['boxSize'])  {
         $message->BoxSize = $this->_settings['boxSize'];
       }
       $message->DestinationCode = $this->_settings['destinationCode']; 
       $this->_prepareSenderAddress($message);       
       $this->_prepareReceiverAddress($message);
       $this->_prepareReturnAddress($message);
       $message->SenderOrders = $this->_settings['orderId'];
       $message->PrintAdress = 1;
       $message->PrintType = 1;
       if ($this->_settings['cod']) {
           $message->CashOnDelivery = 1;
           $message->AmountCashOnDelivery = $this->_settings['cod'];
           $message->TransferDescription = str_replace('-',' ',Mage::helper('zospwr')->getDescriptionTransfer($this->_settings['orderId']));
       }       
       $message->Insurance = (bool)$this->_settings['insurance'];       
       $message->PackValue = $this->_settings['value'];
       $data = $this->_sendMessage('GenerateLabelBusinessPack',$message);
       $result = $this->_prepareResult($data,'GenerateLabelBusinessPackResult','GenerateLabelBusinessPack');       
       $code = $result['NewDataSet']['GenerateLabelBusinessPack']['PackCode_RUCH'];
       // save pdf
       $fileName = sprintf('%s.%s',$code,Orba_Shipping_Helper_Packstation_Pwr::FILE_EXT);
       $this->_saveFile($fileName,$data->LabelData);
       return $code;
     }

    /**
     * There's location too
     * @return mixed|array
     */
    public function giveMeAllRUCH() {
        $message = new StdClass();
        $message->PartnerID = $this->getHelper()->getPartnerId();
        $message->PartnerKey = $this->getHelper()->getPartnerKey();
        $data = $this->_sendMessage("GiveMeAllRUCHZipcode", $message);
        $result = $this->_prepareResult($data,'GiveMeAllRUCHZipcodeResult','AllRUCHZipcode');
        return $result['NewDataSet']['AllRUCHZipcode'];
    }

    /**
     * tracking status
     *
     * @return array
     */
    public function getPackStatus($number) {
        $message = $this->getAuth();
        $message->PackCode = $number;
        $data = $this->_sendMessage('GiveMePackStatus',$message);
        $result = $this->_prepareResult($data,'GiveMePackStatusResult',null);
        return $result;
    }
    /**
     * Prepare answer
     *
     * @param $data
     * @return mixed|array
     */
    protected function _prepareResult($data,$param,$resultParam) {
        if (!isset($data->$param->any)) {
          Mage::throwException(Mage::helper('orbashipping')->__('%s server error: No valid answer','PWR'));
        }        
        $xml = simplexml_load_string($data->$param->any, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $result = json_decode($json,true);
        if ($resultParam) {
            if (!empty($result['NewDataSet'][$resultParam]['Err']) && ($result['NewDataSet'][$resultParam]['Err'] != '000')) {
               Mage::throwException(Mage::helper('orbashipping')->__('%s server error: %s','PWR',$result['NewDataSet'][$resultParam]['ErrDes']));
            }        
        }
        return $result;
    }
    
    /**
     * helper for orbashipping carrier
     *
     * @return Orba_Shipping_Helper_Carrier
     */
     protected function _getHelper() {
         return Mage::helper('orbashipping/packstation_pwr');
     }
}

