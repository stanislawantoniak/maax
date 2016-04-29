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
                      'name' => $pos->getName(),
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
        $pack->addChild('id',$udpo->getIncrementId());
        $pack->addChild('senderEmail',$email);
        $pack->addChild('boxMachineName',$udpo->getInpostLockerName());
        $pack->addChild('packType',$settings['size']);
        $pack->addChild('addresseeEmail',$udpo->getCustomerEmail());
        $pack->addChild('phoneNum',$settings['phoneNumber']);
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

}