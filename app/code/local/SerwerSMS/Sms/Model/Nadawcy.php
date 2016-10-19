<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_Model_Nadawcy extends Mage_Core_Model_Abstract {
    
    public function toOptionArray() {

        $helper = Mage::helper('sms_helper/SerwerSMS');
        $api['login'] = $helper->getApiLogin();
        $api['haslo'] = $helper->getApiPassword();
        
        $xml = $helper->ilosc_sms(array('login' => $api['login'], 'haslo' => $api['haslo']));
        $dane = $helper->PrzetworzXML("ilosc_sms",$xml);
        
        if(is_array($dane)){
            
            $xml2 = $helper->nazwa_nadawcy(array('login' => $api['login'], 'haslo' => $api['haslo'], operacja => 'lista'));
            $nazwy_nadawcy = $helper->PrzetworzXML("nazwa_nadawcy",$xml2);
            $xml3 = $helper->nazwa_nadawcy(array('login' => $api['login'], 'haslo' => $api['haslo'], operacja => 'lista', predefiniowane => 1));
            $predefiniowane = $helper->PrzetworzXML("nazwa_nadawcy",$xml3);

            $nadawcy['wlasne'] = $nazwy_nadawcy;
            $nadawcy['predefiniowane'] = $predefiniowane;
            
            $res = array(array('label' => '', 'value' => ''));
            
            foreach($nazwy_nadawcy as $nazwa => $status){
                if($status == 'Autoryzowano' or $status == '4005'){
                    $res[] = array('label' => $nazwa, 'value' => $nazwa);
                }
            }
            
            foreach($predefiniowane as $nazwa => $status){
                if($status == 'Autoryzowano' or $status == '4005'){
                    $res[] = array('label' => $nazwa, 'value' => $nazwa);
                }
            }
            
        } else {
            $res = array(array('label' => '', 'value' => ''));
        }

        //ksort($res);
        return $res;
    }
    
}