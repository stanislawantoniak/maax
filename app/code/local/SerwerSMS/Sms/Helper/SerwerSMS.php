<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_Helper_SerwerSMS {

    public static $API_URL = 'https://api1.serwersms.pl/zdalnie/?';

    public static function wyslij_sms($Parametry) {
        return self::Zapytanie("wyslij_sms", $Parametry);
    }
    
    public static function ilosc_sms($Parametry) {
        return self::Zapytanie("ilosc_sms", $Parametry);
    }
    
    public static function nazwa_nadawcy($Parametry){
        return self::Zapytanie("nazwa_nadawcy",$Parametry);
    }
    
    public static function faktury($Parametry){
        return self::Zapytanie("faktury",$Parametry);
    }
    
    public static function pomoc($Parametry){
        return self::Zapytanie("pomoc",$Parametry);
    }
    
    private static function Zapytanie($akcja, $params) {

        $requestUrl = self::$API_URL;
		$params["akcja"] = $akcja;
        $postParams = array_merge($params);

        $curl = curl_init($requestUrl);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postParams));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl,CURLOPT_TIMEOUT,60);
        $answer = curl_exec($curl);
        
		if (curl_errno($curl)) {
		}
        
        curl_close($curl);

        return $answer;
    }
    
    public function xml_attribute($object, $attribute)
    {
            if(isset($object[$attribute]))
            return (string) $object[$attribute];
    }


    public function PrzetworzXML($akcja,$xml_file) {
	$dom = new domDocument;
	$dom->loadXML($xml_file);
	$xml = simplexml_import_dom($dom);
	
	if (isset($xml->Blad)) {
            
		$numer = empty($_POST['numer'])? 0:$_POST['numer'];
		$przyczyna = $xml->Blad;
		return $przyczyna;
                
	} elseif ($akcja=="wyslij_sms") {
            
		if(isset($xml->Odbiorcy->Skolejkowane)){
                    $i = 0;
			foreach($xml->Odbiorcy->Skolejkowane->SMS as $sms) {
				$wyslane[$i]['smsid'] = self::xml_attribute($sms, 'id');
                                $wyslane[$i]['numer'] = self::xml_attribute($sms, 'numer');
                                $wyslane[$i]['kolejka'] = self::xml_attribute($sms, 'godzina_skolejkowania');
                                $i++;
			}
		} 
		if (isset($xml->Odbiorcy->Niewyslane)) {
                    $i=0;
			foreach($xml->Odbiorcy->Niewyslane->SMS as $sms) {
				$niewyslane[$i]['smsid'] = self::xml_attribute($sms, 'id');
                                $niewyslane[$i]['numer'] = self::xml_attribute($sms, 'numer');
                                $niewyslane[$i]['przyczyna'] = self::xml_attribute($sms, 'przyczyna');
                                $i++;
			}
		}
                $wynik['wyslane'] = $wyslane;
                $wynik['niewyslane'] = $niewyslane;
                
                return $wynik;
                
	} elseif ($akcja=="ilosc_sms") {
		if(isset($xml->SMS)){
                        $i = 0;
			foreach($xml->SMS as $sms) {
				$limity[$i]['typ_sms'] = self::xml_attribute($sms, 'typ');
                                $limity[$i]['stan'] = $sms;
                                $limity[$i]['limit'] = self::xml_attribute($sms, 'limit_znakow');
                                $i++;
			}
		}
                if(isset($xml->KONTO)){
                    $limity[$i]['konto'] = $xml->KONTO;
                }
                return $limity;
                
	} elseif ($akcja=="nazwa_nadawcy") {
            if(isset($xml->NADAWCA)){
                foreach($xml->NADAWCA as $nadawca){
                    $lista[self::xml_attribute($nadawca,'nazwa')] = $nadawca;
                }
            }
            return $lista;
        } elseif ($akcja=="faktury"){
            if(isset($xml->FAKTURA)){
                $i = 0;
                foreach($xml->FAKTURA as $fak){
                    $faktury[$i]['id'] = self::xml_attribute($fak, 'id');
                    $faktury[$i]['numer'] = $fak->numer;
                    $faktury[$i]['rozliczono'] = $fak->rozliczono;
                    $faktury[$i]['kwota'] = $fak->kwota;
                    $faktury[$i]['termin'] = $fak->termin;
                }
            }
            return $faktury;
        } elseif ($akcja=="pomoc") {
            $pomoc['telefon'] = $xml->Telefon;
            $pomoc['infolinia'] = $xml->Infolinia;
            $pomoc['email'] = $xml->Email;
            $pomoc['formularz'] = $xml->Formularz;
            $pomoc['faq'] = $xml->Faq;
            $pomoc['opiekun'] = $xml->Opiekun->Nazwa;
            $pomoc['op_email'] = $xml->Opiekun->Email;
            $pomoc['op_telefon'] = $xml->Opiekun->Telefon;
            $pomoc['foto'] = $xml->Opiekun->Foto;
            return $pomoc;
        }
    }
    
    public function korektaNumerow($numery){
        if(is_array($numery)){
            foreach($numery as $numer){
                $numer = str_replace(" ","",$numer);
                $numer = str_replace("-","",$numer);
                $result[] = $numer;
            }
        } else {
            $numer = str_replace(" ","",$numery);
            $numer = str_replace("-","",$numer);
            $result[] = $numer;
        }
        return $result;
    }
    
    public function getApiLogin(){
        return Mage::getStoreConfig('smsconfig_section/konfiguracjasms/uzytkownik');
    }
    
    public function getApiPassword(){
        return Mage::getStoreConfig('smsconfig_section/konfiguracjasms/haslo');
    }
    
    public function wlaczonySerwerSMS(){
        return Mage::getStoreConfig('smsconfig_section/konfiguracjasms/wlaczony');
    }
    
    public function smsFull(){
        return Mage::getStoreConfig('smsconfig_section/ustawieniawiadomosci/typsms');
    }
    
    public function nazwaNadawcy(){
        return Mage::getStoreConfig('smsconfig_section/ustawieniawiadomosci/nadawca');
    }
    
    public function numeryAdministratora(){
        
        $numery_admin = Mage::getStoreConfig('smsconfig_section/konfiguracjasms/numery_admin');
        $odbiorcy = explode(",",$numery_admin);
        $odbiorcy = $this->korektaNumerow($odbiorcy);
        return implode(",",$odbiorcy);
    }
    
    public function powiadomienieZamowienie(){
        return Mage::getStoreConfig('smsconfig_section/powiadomienia/zamowienie');
    }
    
    public function szablonZamowienie(){
        return Mage::getStoreConfig('smsconfig_section/tekstysms/tekst_zamowienie');
    }
    
    public function powiadomienieRealizacja(){
        return Mage::getStoreConfig('smsconfig_section/powiadomienia/realizacja');
    }
    
    public function szablonRealizacja(){
        return Mage::getStoreConfig('smsconfig_section/tekstysms/tekst_realizacja');
    }
    
    public function powiadomienieWstrzymanie(){
        return Mage::getStoreConfig('smsconfig_section/powiadomienia/wstrzymanie');
    }
    
    public function szablonWstrzymanie(){
        return Mage::getStoreConfig('smsconfig_section/tekstysms/tekst_wstrzymanie');
    }
    
    public function szablonOdblokowanie(){
        return Mage::getStoreConfig('smsconfig_section/tekstysms/tekst_odblokowanie');
    }
    
    public function powiadomienieStanKonta(){
        return Mage::getStoreConfig('smsconfig_section/powiadomienia/stan_konta');
    }
    
    public function wyslijSms($parametry, $stanKonta = 1){
        
        if(!isset($parametry['nadawca'])) $parametry['nadawca'] = ($this->smsFull()) ? $this->nazwaNadawcy() : '';
        if(!isset($parametry['typsms'])) $parametry['typsms'] = ($this->smsFull() and $this->nazwaNadawcy()) ? "FULL" : "ECO";
        $parametry['login'] = $this->getApiLogin();
        $parametry['haslo'] = $this->getApiPassword();
        
        $xml = $this->wyslij_sms(array(login => $parametry['login'], haslo => $parametry['haslo'], numer => $parametry['odbiorcy'], wiadomosc => $parametry['tresc'], nadawca => $parametry['nadawca']));
        $dane = $this->PrzetworzXML("wyslij_sms",$xml);
        
        if($stanKonta){
            $this->sprawdzStanKonta();
        }

            if(!is_array($dane)){
                $baza[0]['data'] = date("Y-m-d H:i:s");
                $baza[0]['smsid'] = 'brak';
                $baza[0]['numer'] = $parametry['odbiorcy'];
                $baza[0]['nadawca'] = $parametry['nadawca'];
                $baza[0]['typ'] = $parametry['typsms'];
                $baza[0]['raport'] = 'brak';
                $baza[0]['status'] = 'err';
                $baza[0]['tresc'] = $parametry['tresc'];
                $baza[0]['powod'] = $dane;
            } else {
                $i = 0;
                if (!empty($dane['wyslane'])){
                    foreach($dane['wyslane'] as $sms){
                        $baza[$i]['data'] = $sms['kolejka'];
                        $baza[$i]['smsid'] = $sms['smsid'];
                        $baza[$i]['numer'] = $sms['numer'];
                        $baza[$i]['nadawca'] = $parametry['nadawca'];
                        $baza[$i]['typ'] = $parametry['typsms'];
                        $baza[$i]['raport'] = 'Oczekiwanie na raport';
                        $baza[$i]['status'] = 'ok';
                        $baza[$i]['tresc'] = $parametry['tresc'];
                        $baza[$i]['powod'] = '';
                        $i++;
                    }
                }

                if (!empty($dane['niewyslane'])){
                    foreach($dane['niewyslane'] as $sms){
                        $baza[$i]['data'] = date("Y-m-d H:i:s");
                        $baza[$i]['smsid'] = $sms['smsid'];
                        $baza[$i]['numer'] = $sms['numer'];
                        $baza[$i]['nadawca'] = $parametry['nadawca'];
                        $baza[$i]['typ'] = $parametry['typsms'];
                        $baza[$i]['raport'] = 'brak';
                        $baza[$i]['status'] = 'err';
                        $baza[$i]['tresc'] = $parametry['tresc'];
                        $baza[$i]['powod'] = $sms['przyczyna'];
                        $i++;
                    }
                }
            }

            try{
                foreach($baza as $rekord){
                    $message = Mage::getModel('serwersms_model/smsModel');
                    $message->setData('data',$rekord['data']);
                    $message->setSmsid($rekord['smsid']);
                    $message->setNumer($rekord['numer']);
                    $message->setNadawca($rekord['nadawca']);
                    $message->setTyp($rekord['typ']);
                    $message->setRaport($rekord['raport']);
                    $message->setStatus($rekord['status']);
                    $message->setTresc($rekord['tresc']);
                    $message->setPowod($rekord['powod']);
                    $message->save();
                }
                return true;
            } catch(Exception $e){
                return false;
            }
            
    }
    
    public function sprawdzStanKonta(){
        
        if($this->wlaczonySerwerSMS() and $this->powiadomienieStanKonta()){
                
            $parametry['login'] = $this->getApiLogin();
            $parametry['haslo'] = $this->getApiPassword();
            $parametry['odbiorcy'] = $this->numeryAdministratora();

            $xml = $this->ilosc_sms(array(login => $parametry['login'], haslo => $parametry['haslo']));
            $dane = $this->PrzetworzXML("ilosc_sms",$xml);

            foreach($dane as $sms){
                if($sms['typ_sms'] == 'ECO' and $sms['stan'] == 49){
                    $parametry['tresc'] = 'Stan Twojego konta SMS ECO spadl ponizej 50 wiadomosci';
                    $this->wyslijSms($parametry,0);
                }
                if($sms['typ_sms'] == 'FULL' and $sms['stan'] == 49){
                    $parametry['tresc'] = 'Stan Twojego konta SMS FULL spadl ponizej 50 wiadomosci';
                    $this->wyslijSms($parametry,0);
                }
            }  
        }
        
    }
    
    public function niezalogowanyWidok(){
        
        $key = Mage::getSingleton('adminhtml/url')->getSecretKey("system_config","edit");
        $konfig = Mage::helper("adminhtml")->getUrl("adminhtml/system_config/edit/",array("section" => 'smsconfig_section', "key" => $key));
        
        $widok = '<div style="text-align: center">';
        $widok .= '<strong>Błąd logowania: Nieprawidłowy login lub hasło.<br />
                    Prosimy o weryfikację danych w <a href="'.$konfig.'">konfiguracji</a>.</strong><br /><br />
                    Jeśli nie posiadasz konta w SerwerSMS prosimy o <a href="https://www.serwersms.pl/o,4-Rejestracja.html#rejestracja" target="_blank">zarejestrowanie się</a>.<br />
                    Po wypełnieniu formularza rejestracyjnego aktywuj swoje konto, zakup doładowanie SMS,<br />
                    lub skorzystaj z oferty abonamentowej.';
        $widok .= '</div>';
        
        return $widok;
        
    }
}

?>
