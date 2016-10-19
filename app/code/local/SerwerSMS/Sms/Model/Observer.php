<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_Model_Observer{
    
   public function wyslijsmsZamowienie(Varien_Event_Observer $observer){

        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();
        $numer_zamowienia = $order->getIncrementId();
        $shipping = $order->getShippingAddress();
        $kwota = number_format($order->getData('grand_total'),2,',','');
        $name = $shipping->getFirstname();
        $lastname = $shipping->getLastname();
        
        $modul_wlaczony = $this->getHelper()->wlaczonySerwerSMS();
        $powiadomienie_wlaczone = $this->getHelper()->powiadomienieZamowienie();
        $szablon = $this->getHelper()->szablonZamowienie();
        
        if($modul_wlaczony and $powiadomienie_wlaczone){

            $wysylka['odbiorcy'] = $this->getHelper()->numeryAdministratora();

            $szablon = str_replace("#IMIE#",$name,$szablon);
            $szablon = str_replace("#NAZWISKO#",$lastname, $szablon);
            $szablon = str_replace("#NUMER#",$numer_zamowienia,$szablon);
            $szablon = str_replace("#KWOTA#",$kwota,$szablon);
            
            $wysylka['tresc'] = $szablon;

            $this->getHelper()->wyslijSms($wysylka);
        }

    }
    
    public function wyslijsmsWysylka(Varien_Event_Observer $observer){
        
        $modul_wlaczony = $this->getHelper()->wlaczonySerwerSMS();
        $powiadomienie_wlaczone = $this->getHelper()->powiadomienieRealizacja();
        $szablon = $this->getHelper()->szablonRealizacja();
        
        if($modul_wlaczony and $powiadomienie_wlaczone){
            
            $shipment = $observer->getShipment();
            $order = $shipment->getOrder();
            $billingAddress = $order->getBillingAddress();

            $numer = $billingAddress->getTelephone();
            $numer = $this->getHelper()->korektaNumerow($numer);
            $wysylka['odbiorcy'] = implode(",",$numer);
            
            $numer_zamowienia = $order->getIncrementId();
            $shipping = $order->getShippingAddress();
            $name = $shipping->getFirstname();
            $lastname = $shipping->getLastname();
            $adres = $shipping->getStreetFull().", ".$shipping->getPostcode()." ".$shipping->getCity(); 
            $kwota = number_format($order->getData('grand_total'),2,',','');
            
            $szablon = str_replace("#IMIE#",$name,$szablon);
            $szablon = str_replace("#NAZWISKO#",$lastname, $szablon);
            $szablon = str_replace("#NUMER#",$numer_zamowienia,$szablon);
            $szablon = str_replace("#KWOTA#",$kwota,$szablon);
            $szablon = str_replace("#ADRES#",$adres,$szablon);
            
            $wysylka['tresc'] = $szablon;
            
            $this->getHelper()->wyslijSms($wysylka);
        }
    }
    
    public function wyslijsmsWstrzymanie(Varien_Event_Observer $observer){
        
        $order = $observer->getOrder();
        
        if ($order->getState() !== $order->getOrigData('state') && $order->getState() === Mage_Sales_Model_Order::STATE_HOLDED) {
            
            $orderId = $order->getId();
            $numer_zamowienia = $order->getIncrementId();
            $shipping = $order->getShippingAddress();
            $kwota = number_format($order->getData('grand_total'),2,',','');
            $name = $shipping->getFirstname();
            $lastname = $shipping->getLastname();

            $szablon = $this->getHelper()->szablonWstrzymanie();
            $billingAddress = $order->getBillingAddress();
            $numer = $billingAddress->getTelephone();
            $numer = $this->getHelper()->korektaNumerow($numer);
            $wysylka['odbiorcy'] = implode(",",$numer);
            
            if($this->getHelper()->wlaczonySerwerSMS() and $this->getHelper()->powiadomienieWstrzymanie()){

                $szablon = str_replace("#IMIE#",$name,$szablon);
                $szablon = str_replace("#NAZWISKO#",$lastname, $szablon);
                $szablon = str_replace("#NUMER#",$numer_zamowienia,$szablon);
                $szablon = str_replace("#KWOTA#",$kwota,$szablon);

                $wysylka['tresc'] = $szablon;

                $this->getHelper()->wyslijSms($wysylka);
            }
        }
    }
    
    public function wyslijsmsOdblokowanie(Varien_Event_Observer $observer){
        
        $order = $observer->getOrder();
        
        if ($order->getState() !== $order->getOrigData('state') && $order->getOrigData('state') === Mage_Sales_Model_Order::STATE_HOLDED) {
            
            $orderId = $order->getId();
            $numer_zamowienia = $order->getIncrementId();
            $shipping = $order->getShippingAddress();
            $kwota = number_format($order->getData('grand_total'),2,',','');
            $name = $shipping->getFirstname();
            $lastname = $shipping->getLastname();

            $szablon = $this->getHelper()->szablonOdblokowanie();
            $billingAddress = $order->getBillingAddress();
            $numer = $billingAddress->getTelephone();
            $numer = $this->getHelper()->korektaNumerow($numer);
            $wysylka['odbiorcy'] = implode(",",$numer);
            
            if($this->getHelper()->wlaczonySerwerSMS() and $this->getHelper()->powiadomienieWstrzymanie()){

                $szablon = str_replace("#IMIE#",$name,$szablon);
                $szablon = str_replace("#NAZWISKO#",$lastname, $szablon);
                $szablon = str_replace("#NUMER#",$numer_zamowienia,$szablon);
                $szablon = str_replace("#KWOTA#",$kwota,$szablon);

                $wysylka['tresc'] = $szablon;

                $this->getHelper()->wyslijSms($wysylka);
            }
        }
    }
    
    public function getHelper()
    {
        return Mage::helper('sms_helper/SerwerSMS');
    }
}
?>