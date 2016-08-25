<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action{
    
    protected function _initAction(){
        $this->loadLayout()->_setActiveMenu('serwersms/wyslane')
                ->_addBreadcrumb('sms Manager','sms Manager');
        return $this;
    }
    
    public function indexAction(){
        
        $this->_initAction();
        $this->renderLayout();
    }
    
    public function niewyslaneAction(){
        
        $this->loadLayout()->_setActiveMenu('serwersms/niewyslane')
                ->_addBreadcrumb('sms Manager','sms Manager');
        $this->renderLayout();
    }
    
    public function odpowiedziAction(){
        
        $this->loadLayout()->_setActiveMenu('serwersms/odpowiedzi')
                ->_addBreadcrumb('sms Manager','sms Manager');
        $this->renderLayout();
    }
    
    public function danekontaAction(){
        $this->loadLayout()->_setActiveMenu('serwersms/danekonta')
                ->_addBreadcrumb('sms Manager','sms Manager');
        $this->renderLayout();
    }
    
    public function fakturyAction(){
        $this->loadLayout()->_setActiveMenu('serwersms/faktury')
                ->_addBreadcrumb('sms Manager','sms Manager');
        $this->renderLayout();
    }
    
    public function dodajnazweAction(){
        $this->loadLayout()->_setActiveMenu('serwersms/danekonta')
                ->_addBreadcrumb('sms Manager','sms Manager');
        $this->renderLayout();
    }
    
    public function wyslijsmsAction(){
        $this->loadLayout()->_setActiveMenu('serwersms/wyslijsms')
                ->_addBreadcrumb('sms Manager','sms Manager');
        $this->renderLayout();
    }
    
    public function savenazwaAction(){
        
        if($this->getRequest()->getPost()){
                
                if($this->getRequest()->getParam('nazwa') != ''){
                    
                    $helper = Mage::helper('sms_helper/SerwerSMS');
                    $api['login'] = $helper->getApiLogin();
                    $api['haslo'] = $helper->getApiPassword();
                    
                    $api['nazwa'] = $this->getRequest()->getParam('nazwa');
                    
                    $xml = $helper->nazwa_nadawcy(array(login => $api['login'], haslo => $api['haslo'], operacja => "dodanie", nazwa => $api['nazwa']));
                    $dane = $helper->PrzetworzXML("nazwa_nadawcy",$xml);
                    
                    foreach($dane as $nazwa => $status){
                        $nadawca = $nazwa;
                        $komunikat = $status;
                    }
                    
                    if($status == "Dopisano"){
                        Mage::getSingleton('adminhtml/session')
                            ->addSuccess('Zapisano dane');
                    } else {
                        Mage::getSingleton('adminhtml/session')
                            ->addError('Błąd: '.$status);
                    }
                    
                } else {
                    Mage::getSingleton('adminhtml/session')
                        ->addError('Błąd: Nie podano nazwy nadawcy');
                }
                
        } else {
            Mage::getSingleton('adminhtml/session')
                        ->addError('Błąd: Nie podano nazwy nadawcy');
        }
        $this->_redirect('*/*/danekonta');
    }
    
    public function wysylkaformAction(){
        
        if($this->getRequest()->getPost()){
                
                if($this->getRequest()->getParam('wiadomosc') != ''){
                    
                    $helper = Mage::helper('sms_helper/SerwerSMS');
                    
                    if($helper->wlaczonySerwerSMS()){
                    
                        $typsms = $this->getRequest()->getParam('typ');
                        $odbiorca = $this->getRequest()->getParam('odbiorca');
                        
                        $wysylka['nadawca'] = ($typsms) ? $this->getRequest()->getParam('nadawca') : '';
                        $wysylka['typsms'] = ($typsms) ? 'FULL' : 'ECO';
                        $wysylka['tresc'] = $this->getRequest()->getParam('wiadomosc');

                        switch($odbiorca){
                            case '0':
                                $numery = $this->getRequest()->getParam('numer_reczny');
                                break;
                            case '1':
                                $numery = $this->getRequest()->getParam('numer_lista');
                                break;
                            case '2':
                                $grupa_id = $this->getRequest()->getParam('numer_grupa');

                                $collection = Mage::getModel('customer/customer')
                                        ->getCollection()
                                        ->addAttributeToSelect('*')
                                        ->addAttributeToFilter('group_id',$grupa_id);

                                foreach($collection as $customer){

                                    if($customer->getPrimaryBillingAddress()){
                                        $numery[] = $customer->getPrimaryBillingAddress()->getTelephone();
                                    }
                                }
                                break;
                        }

                        $numery = $helper->korektaNumerow($numery);
                        $liczba = count($numery);
                        
                        if($liczba > 500){
                            $ile_paczek = ceil($liczba/500);
                            for($i=1;$i<=$ile_paczek;$i++){
                                $indeks_koncowy = $i*500;
                                $indeks_poczatkowy = $indeks_koncowy-500;
                                for($j=$indeks_poczatkowy;$j<$indeks_koncowy;$j++){
                                    if(!empty($numery[$j])){
                                        $paczka[$i][] = $numery[$j];
                                    }
                                }
                            }
                            
                            foreach($paczka as $key => $num){
                                $odbiorcy[$key] = implode(",",$num);
                            }

                            foreach($odbiorcy as $odb){
                                $wysylka['odbiorcy'] = $odb;

                                if(!empty($wysylka['odbiorcy'])){

                                    if($helper->wyslijSms($wysylka)){
                                        $blad = false;
                                    } else {
                                        $blad = true;
                                    }
                                } else {
                                    Mage::getSingleton('adminhtml/session')
                                    ->addError('Błąd: Brak numeru');
                                    $blad = true;
                                }
                            }
                        } else {
                            $wysylka['odbiorcy'] = implode(",",$numery);
                            
                            if(!empty($wysylka['odbiorcy'])){

                                    if($helper->wyslijSms($wysylka)){
                                        $blad = false;
                                    } else {
                                        $blad = true;
                                    }
                                } else {
                                    Mage::getSingleton('adminhtml/session')
                                    ->addError('Błąd: Brak numeru');
                                    $blad = true;
                                }
                        }
                        
                        if(!$blad){
                                Mage::getSingleton('adminhtml/session')
                                ->addSuccess('Wiadomość została wysłana');
                            }
                        
                    } else {
                        Mage::getSingleton('adminhtml/session')
                        ->addError('Błąd: Moduł SerwerSMS jest wyłączony');
                    }
                    
                } else {
                    Mage::getSingleton('adminhtml/session')
                        ->addError('Błąd: Nie wpisano treści wiadomości');
                }
                
        } else {
            Mage::getSingleton('adminhtml/session')
                        ->addError('Błąd: Brak danych');
        }
        $this->_redirect('*/*/wyslijsms');
    }
    
    public function podgladfakturyAction(){
            
            if($this->getRequest()->getParam('faktura') != ''){
                
                $helper = Mage::helper('sms_helper/SerwerSMS');
                $api['login'] = $helper->getApiLogin();
                $api['haslo'] = $helper->getApiPassword();
                $api['faktura'] = $this->getRequest()->getParam('faktura');

                header('Content-type: application/pdf');
                echo $helper->faktury(array(login => $api['login'], haslo => $api['haslo'], faktura => $api['faktura']));
                exit();
                
            }
    }
}

?>