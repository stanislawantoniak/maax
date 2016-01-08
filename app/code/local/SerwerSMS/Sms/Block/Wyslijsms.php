<?php

/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_Block_Wyslijsms extends Mage_Core_Block_Template{
    
    public function nadawcyLista(){
        
        $result = '';
        
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

            $result['wlasne'] = $nazwy_nadawcy;
            $result['predefiniowane'] = $predefiniowane;
        } else {
            $result = $dane;
        }
        
        return $result;
    }
    
    public function kontaktyLista(){
        
        $collection = Mage::getModel('customer/customer')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->setOrder("lastname");
        $i=0;
        foreach($collection as $customer){
            
            if($customer->getPrimaryBillingAddress()){
                $result[$i]['name'] = $customer->getLastname().' '.$customer->getFirstname();
                $result[$i]['telefon'] = $customer->getPrimaryBillingAddress()->getTelephone();
                $i++;
            }
        }
        
        return $result;
    }
    
    public function grupyLista(){
        
        $collection = Mage::getModel('customer/group')
                ->getCollection()->getData();

        $i=0;
        foreach($collection as $group){
            
            $result[$i]['id'] = $group['customer_group_id'];
            $result[$i]['name'] = $group['customer_group_code'];
            $i++;
        }

        return $result;
    }
    
}

?>