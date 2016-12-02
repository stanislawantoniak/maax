<?php
/**
 * inpost xml message
 */
class Orba_Shipping_Model_Packstation_Inpost_Message extends Varien_Object {
    protected $_xmlMessage;

    public function _construct() {
        $this->_xmlMessage = simplexml_load_string('<paczkomaty></paczkomaty>');
    }

    /**
     * params for getDispatchPoint function
     */

    public function getDispatchPointMessage($email,$password,$name) {
        $data = array();
        $data['email'] = $email;
        $data['password'] = $password;
        $this->_xmlMessage->addChild('name',$name);
        $data['content'] = $this->_xmlMessage->asXML();
        return $data;
    }

    /**
     * params for createDispatchPoint function
     */
    public function getCreateDispatchPointMessage($email,$password,$pos) {
        $data = array();
        $data['email'] = $email;
        $data['password'] = $password;
        $point = $this->_xmlMessage->addChild('dispatchPoint');
        $params = array (
                      'name' => empty($pos->getExternalId())? $pos->getName():$pos->getExternalId(),
                      'postCode' => $pos->getPostcode(),
                      'street' => $pos->getStreet(),
                      'town' => $pos->getCity(),
                      'building' => '-',
                      'flat' => '',
                      'phoneNumber' => $pos->getPhone(),
                      'email' => $pos->getEmail(),
                      'comments' => '',
                      'availabilityHours' => $pos->getAvailabilityHours(),
                  );
        foreach ($params as $key => $val) {
            $point->addChild($key,$val);
        }
        $data['content'] = $this->_xmlMessage->asXML();
        return $data;
    }
    
    /**
     * params for create delivery packs
     */
     public function getCreateDeliveryPackMessage($email,$password,$settings) {
        $data = array();
        $data['email'] = $email;
        $data['password'] = $password;
        $this->_xmlMessage->addChild('autoLabels',false);
        $pack = $this->_xmlMessage->addChild('pack');    
        $udpo = $settings['udpo'];
        $pos = $settings['pos'];
        $pack->addChild('id',$udpo->getIncrementId());
        $pack->addChild('senderEmail',$email);
        $pack->addChild('boxMachineName',$udpo->getDeliveryPointName());
        $pack->addChild('packType',$settings['size']);
        $pack->addChild('insuranceAmount',$settings['insurance']);
        $pack->addChild('customerRef',$settings['orderId']);
        if ($settings['cod'] > 0) {
         $pack->addChild('onDeliveryAmount',$settings['cod']);
        }
        $pack->addChild('addresseeEmail',$udpo->getCustomerEmail());
        $pack->addChild('phoneNum',$settings['phoneNumber']);
        $pack->addChild('dispatchPointName',$settings['dispatchPointName']);
        $address = $pack->addChild('senderAddress');
        $address->addChild('name',empty($pos->getCompany())? $pos->getName():$pos->getCompany());
        $address->addChild('email',$pos->getEmail());
        $address->addChild('phoneNum',$pos->getPhone());
        $address->addChild('street',$pos->getStreet());
        $address->addChild('town',$pos->getCity());
        $address->addChild('zipCode',$pos->getPostcode());
        $data['content'] = $this->_xmlMessage->asXML();
        return $data;
     }
     
    /**
     * params for get stickers
     */
     public function getStickerMessage($email,$password,$packcodes,$labelType = '') {
         $data = array();
         $data['email'] = $email;
         $data['password'] = $password;
         foreach ($packcodes as $key => $code) {
             $data[sprintf('packcodes[%s]',$key)] = $code;
         }
         $data['labelType'] = $labelType;
         $data['labelFormat'] = '';
         return $data;
     }
     
    /**
     * confirm printout
     */
     public function getConfirmPrintoutMessage($email,$password,$packcodes) {
         $data = array();
         $data['email'] = $email;
         $data['password'] = $password;
         foreach ($packcodes as $code) {
             $pack = $this->_xmlMessage->addChild('pack');
             $pack->addChild('packcode',$code);
         }
         $data['content'] = $this->_xmlMessage->asXML();
         return $data; 
     }

}