<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_Block_Danekonta extends Mage_Core_Block_Template{
    
    public function pokazDaneKonta(){
        
        $result = '';
        
        $helper = Mage::helper('sms_helper/SerwerSMS');
        $api['login'] = $helper->getApiLogin();
        $api['haslo'] = $helper->getApiPassword();
        
        $xml = $helper->ilosc_sms(array('login' => $api['login'], 'haslo' => $api['haslo'], 'pokaz_typ_konta' => 1));
        $dane = $helper->PrzetworzXML("ilosc_sms",$xml);
        
        if(is_array($dane)){
            
            $xml2 = $helper->nazwa_nadawcy(array('login' => $api['login'], 'haslo' => $api['haslo'], 'operacja' => 'lista'));
            $nazwy_nadawcy = $helper->PrzetworzXML("nazwa_nadawcy",$xml2);
            $xml3 = $helper->pomoc(array('login' => $api['login'], 'haslo' => $api['haslo']));
            $pomoc = $helper->PrzetworzXML("pomoc",$xml3);
            
            foreach($dane as $key => $value){
                if($value['konto']){
                    $result['konto'] = $value['konto'];
                } elseif($value['typ_sms'] == 'ECO'){
                    $result['ECO'] = $value['stan'];
                    $result['ECOlimit'] = $value['limit'];
                } elseif($value['typ_sms'] == 'FULL'){
                    $result['FULL'] = $value['stan'];
                    $result['FULLlimit'] = $value['limit'];
                }
            }
            $result['nadawcy'] = $nazwy_nadawcy;
            $result['pomoc'] = $pomoc;
        } else {
            $result = $dane;
        }
        
        //Mage::getSingleton('adminhtml/session')->addSuccess('Rekord dodany pomy�lnie!');
        return $result;
    }
    
    public function pokazFaktury(){
        
        $helper = Mage::helper('sms_helper/SerwerSMS');
        $api['login'] = $helper->getApiLogin();
        $api['haslo'] = $helper->getApiPassword();
        
        $xml = $helper->faktury(array('login' => $api['login'], 'haslo' => $api['haslo'], 'operacja' => "lista"));
        $dane = $helper->PrzetworzXML("faktury",$xml);
        
//        for($i=0;$i<15;$i++){
//            $dane[$i] = array('id' => $i, 'numer' => $i, 'rozliczono' => rand(1,60).','.rand(10,80), 'kwota' => rand(1,60).','.rand(10,80), 'termin' => '2012-'.rand(1,12).'-'.rand(1,30));
//        }
        
        return $dane;
        
    }
    
}

?>