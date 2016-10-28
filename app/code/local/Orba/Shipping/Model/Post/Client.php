<?php
/**
 * client poczta polska
 */
class Orba_Shipping_Model_Post_Client extends Orba_Shipping_Model_Client_Soap {


    protected $_default_params = array (
        'postOffice' => 0,
                                 );


    /**
     *
     */
    protected function _construct() {
        // include PP objects
        $tmp = Mage::getSingleton('orbashipping/post_client_wsdl');
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
        $classmap = array(
                        'addShipment' => 'addShipment',
                        'addShipmentResponse' => 'addShipmentResponse',
                        'przesylkaType' => 'przesylkaType',
                        'pocztexKrajowyType' => 'pocztexKrajowyType',
                        'umowaType' => 'umowaType',
                        'masaType' => 'masaType',
                        'numerNadaniaType' => 'numerNadaniaType',
                        'changePassword' => 'changePassword',
                        'changePasswordResponse' => 'changePasswordResponse',
                        'terminRodzajType' => 'terminRodzajType',
                        'uiszczaOplateType' => 'uiszczaOplateType',
                        'wartoscType' => 'wartoscType',
                        'kwotaPobraniaType' => 'kwotaPobraniaType',
                        'sposobPobraniaType' => 'sposobPobraniaType',
                        'sposobPrzekazaniaType' => 'sposobPrzekazaniaType',
                        'sposobDoreczeniaPotwierdzeniaType' => 'sposobDoreczeniaPotwierdzeniaType',
                        'iloscPotwierdzenOdbioruType' => 'iloscPotwierdzenOdbioruType',
                        'dataDlaDostarczeniaType' => 'dataDlaDostarczeniaType',
                        'razemType' => 'razemType',
                        'nazwaType' => 'nazwaType',
                        'nazwa2Type' => 'nazwa2Type',
                        'ulicaType' => 'ulicaType',
                        'numerDomuType' => 'numerDomuType',
                        'numerLokaluType' => 'numerLokaluType',
                        'miejscowoscType' => 'miejscowoscType',
                        'kodPocztowyType' => 'kodPocztowyType',
                        'paczkaPocztowaType' => 'paczkaPocztowaType',
                        'kategoriaType' => 'kategoriaType',
                        'gabarytType' => 'gabarytType',
                        'paczkaPocztowaPLUSType' => 'paczkaPocztowaPLUSType',
                        'przesylkaPobraniowaType' => 'przesylkaPobraniowaType',
                        'przesylkaNaWarunkachSzczegolnychType' => 'przesylkaNaWarunkachSzczegolnychType',
                        'przesylkaPoleconaKrajowaType' => 'przesylkaPoleconaKrajowaType',
                        'przesylkaHandlowaType' => 'przesylkaHandlowaType',
                        'przesylkaListowaZadeklarowanaWartoscType' => 'przesylkaListowaZadeklarowanaWartoscType',
                        'przesylkaFullType' => 'przesylkaFullType',
                        'errorType' => 'errorType',
                        'adresType' => 'adresType',
                        'sendEnvelope' => 'sendEnvelope',
                        'sendEnvelopeResponseType' => 'sendEnvelopeResponseType',
                        'urzadNadaniaType' => 'urzadNadaniaType',
                        'getUrzedyNadania' => 'getUrzedyNadania',
                        'getUrzedyNadaniaResponse' => 'getUrzedyNadaniaResponse',
                        'clearEnvelope' => 'clearEnvelope',
                        'clearEnvelopeResponse' => 'clearEnvelopeResponse',
                        'urzadNadaniaFullType' => 'urzadNadaniaFullType',
                        'guidType' => 'guidType',
                        'ePrzesylkaType' => 'ePrzesylkaType',
                        'eSposobPowiadomieniaType' => 'eSposobPowiadomieniaType',
                        'eKontaktType' => 'eKontaktType',
                        'urzadWydaniaEPrzesylkiType' => 'urzadWydaniaEPrzesylkiType',
                        'pobranieType' => 'pobranieType',
                        'anonymous52' => 'anonymous52',
                        'anonymous53' => 'anonymous53',
                        'przesylkaPoleconaZagranicznaType' => 'przesylkaPoleconaZagranicznaType',
                        'przesylkaZadeklarowanaWartoscZagranicznaType' => 'przesylkaZadeklarowanaWartoscZagranicznaType',
                        'krajType' => 'krajType',
                        'getUrzedyWydajaceEPrzesylki' => 'getUrzedyWydajaceEPrzesylki',
                        'getUrzedyWydajaceEPrzesylkiResponse' => 'getUrzedyWydajaceEPrzesylkiResponse',
                        'uploadIWDContent' => 'uploadIWDContent',
                        'getEnvelopeStatus' => 'getEnvelopeStatus',
                        'getEnvelopeStatusResponse' => 'getEnvelopeStatusResponse',
                        'envelopeStatusType' => 'envelopeStatusType',
                        'downloadIWDContent' => 'downloadIWDContent',
                        'downloadIWDContentResponse' => 'downloadIWDContentResponse',
                        'przesylkaShortType' => 'przesylkaShortType',
                        'addShipmentResponseItemType' => 'addShipmentResponseItemType',
                        'getKarty' => 'getKarty',
                        'getKartyResponse' => 'getKartyResponse',
                        'getPasswordExpiredDate' => 'getPasswordExpiredDate',
                        'getPasswordExpiredDateResponse' => 'getPasswordExpiredDateResponse',
                        'setAktywnaKarta' => 'setAktywnaKarta',
                        'setAktywnaKartaResponse' => 'setAktywnaKartaResponse',
                        'getEnvelopeContentFull' => 'getEnvelopeContentFull',
                        'getEnvelopeContentFullResponse' => 'getEnvelopeContentFullResponse',
                        'getEnvelopeContentShort' => 'getEnvelopeContentShort',
                        'getEnvelopeContentShortResponse' => 'getEnvelopeContentShortResponse',
                        'hello' => 'hello',
                        'helloResponse' => 'helloResponse',
                        'kartaType' => 'kartaType',
                        'telefonType' => 'telefonType',
                        'getAddressLabel' => 'getAddressLabel',
                        'getAddressLabelResponse' => 'getAddressLabelResponse',
                        'addressLabelContent' => 'addressLabelContent',
                        'getOutboxBook' => 'getOutboxBook',
                        'getOutboxBookResponse' => 'getOutboxBookResponse',
                        'getFirmowaPocztaBook' => 'getFirmowaPocztaBook',
                        'getFirmowaPocztaBookResponse' => 'getFirmowaPocztaBookResponse',
                        'getEnvelopeList' => 'getEnvelopeList',
                        'getEnvelopeListResponse' => 'getEnvelopeListResponse',
                        'envelopeInfoType' => 'envelopeInfoType',
                        'przesylkaZagranicznaType' => 'przesylkaZagranicznaType',
                        'przesylkaRejestrowanaType' => 'przesylkaRejestrowanaType',
                        'przesylkaNieRejestrowanaType' => 'przesylkaNieRejestrowanaType',
                        'anonymous94' => 'anonymous94',
                        'przesylkaBiznesowaType' => 'przesylkaBiznesowaType',
                        'gabarytBiznesowaType' => 'gabarytBiznesowaType',
                        'subPrzesylkaBiznesowaType' => 'subPrzesylkaBiznesowaType',
                        'subPrzesylkaBiznesowaPlusType' => 'subPrzesylkaBiznesowaPlusType',
                        'getAddresLabelByGuid' => 'getAddresLabelByGuid',
                        'getAddresLabelByGuidResponse' => 'getAddresLabelByGuidResponse',
                        'przesylkaBiznesowaPlusType' => 'przesylkaBiznesowaPlusType',
                        'opisType' => 'opisType',
                        'numerPrzesylkiKlientaType' => 'numerPrzesylkiKlientaType',
                        'pakietType' => 'pakietType',
                        'opakowanieType' => 'opakowanieType',
                        'typOpakowaniaType' => 'typOpakowaniaType',
                        'getPlacowkiPocztowe' => 'getPlacowkiPocztowe',
                        'getPlacowkiPocztoweResponse' => 'getPlacowkiPocztoweResponse',
                        'getGuid' => 'getGuid',
                        'getGuidResponse' => 'getGuidResponse',
                        'kierunekType' => 'kierunekType',
                        'getKierunki' => 'getKierunki',
                        'prefixKodPocztowy' => 'prefixKodPocztowy',
                        'getKierunkiResponse' => 'getKierunkiResponse',
                        'czynnoscUpustowaType' => 'czynnoscUpustowaType',
                        'miejsceOdbioruType' => 'miejsceOdbioruType',
                        'sposobNadaniaType' => 'sposobNadaniaType',
                        'getKierunkiInfo' => 'getKierunkiInfo',
                        'getKierunkiInfoResponse' => 'getKierunkiInfoResponse',
                        'kwotaTranzakcjiType' => 'kwotaTranzakcjiType',
                        'uslugiType' => 'uslugiType',
                        'idWojewodztwoType' => 'idWojewodztwoType',
                        'placowkaPocztowaType' => 'placowkaPocztowaType',
                        'anonymous124' => 'anonymous124',
                        'anonymous125' => 'anonymous125',
                        'punktWydaniaPrzesylkiBiznesowejPlus' => 'punktWydaniaPrzesylkiBiznesowejPlus',
                        'statusType' => 'statusType',
                        'terminRodzajPlusType' => 'terminRodzajPlusType',
                        'typOpakowanieType' => 'typOpakowanieType',
                        'getEnvelopeBufor' => 'getEnvelopeBufor',
                        'getEnvelopeBuforResponse' => 'getEnvelopeBuforResponse',
                        'clearEnvelopeByGuids' => 'clearEnvelopeByGuids',
                        'clearEnvelopeByGuidsResponse' => 'clearEnvelopeByGuidsResponse',
                        'zwrotDokumentowType' => 'zwrotDokumentowType',
                        'odbiorPrzesylkiOdNadawcyType' => 'odbiorPrzesylkiOdNadawcyType',
                        'potwierdzenieDoreczeniaType' => 'potwierdzenieDoreczeniaType',
                        'rodzajListType' => 'rodzajListType',
                        'potwierdzenieOdbioruType' => 'potwierdzenieOdbioruType',
                        'sposobPrzekazaniaPotwierdzeniaOdbioruType' => 'sposobPrzekazaniaPotwierdzeniaOdbioruType',
                        'doreczenieType' => 'doreczenieType',
                        'doreczenieUslugaPocztowaType' => 'doreczenieUslugaPocztowaType',
                        'doreczenieUslugaKurierskaType' => 'doreczenieUslugaKurierskaType',
                        'oczekiwanaGodzinaDoreczeniaType' => 'oczekiwanaGodzinaDoreczeniaType',
                        'oczekiwanaGodzinaDoreczeniaUslugiType' => 'oczekiwanaGodzinaDoreczeniaUslugiType',
                        'paczkaZagranicznaType' => 'paczkaZagranicznaType',
                        'setEnvelopeBuforDataNadania' => 'setEnvelopeBuforDataNadania',
                        'setEnvelopeBuforDataNadaniaResponse' => 'setEnvelopeBuforDataNadaniaResponse',
                        'lokalizacjaGeograficznaType' => 'lokalizacjaGeograficznaType',
                        'wspolrzednaGeograficznaType' => 'wspolrzednaGeograficznaType',
                        'zwrotType' => 'zwrotType',
                        'sposobZwrotuType' => 'sposobZwrotuType',
                        'listZwyklyType' => 'listZwyklyType',
                        'reklamowaType' => 'reklamowaType',
                        'getEPOStatus' => 'getEPOStatus',
                        'getEPOStatusResponse' => 'getEPOStatusResponse',
                        'statusEPOEnum' => 'statusEPOEnum',
                        'EPOType' => 'EPOType',
                        'EPOSimpleType' => 'EPOSimpleType',
                        'EPOExtendedType' => 'EPOExtendedType',
                        'zasadySpecjalneEnum' => 'zasadySpecjalneEnum',
                        'przesylkaEPOType' => 'przesylkaEPOType',
                        'przesylkaFirmowaPoleconaType' => 'przesylkaFirmowaPoleconaType',
                        'EPOInfoType' => 'EPOInfoType',
                        'awizoPrzesylkiType' => 'awizoPrzesylkiType',
                        'doreczeniePrzesylkiType' => 'doreczeniePrzesylkiType',
                        'zwrotPrzesylkiType' => 'zwrotPrzesylkiType',
                        'miejscaPozostawieniaAwizoEnum' => 'miejscaPozostawieniaAwizoEnum',
                        'podmiotDoreczeniaEnum' => 'podmiotDoreczeniaEnum',
                        'przyczynaZwrotuEnum' => 'przyczynaZwrotuEnum',
                        'getAddresLabelCompact' => 'getAddresLabelCompact',
                        'getAddresLabelCompactResponse' => 'getAddresLabelCompactResponse',
                        'getAddresLabelByGuidCompact' => 'getAddresLabelByGuidCompact',
                        'getAddresLabelByGuidCompactResponse' => 'getAddresLabelByGuidCompactResponse',
                        'ubezpieczenieType' => 'ubezpieczenieType',
                        'rodzajUbezpieczeniaType' => 'rodzajUbezpieczeniaType',
                        'kwotaUbezpieczeniaType' => 'kwotaUbezpieczeniaType',
                        'emailType' => 'emailType',
                        'mobileType' => 'mobileType',
                        'EMSType' => 'EMSType',
                        'EMSTypOpakowaniaType' => 'EMSTypOpakowaniaType',
                        'getEnvelopeBuforList' => 'getEnvelopeBuforList',
                        'getEnvelopeBuforListResponse' => 'getEnvelopeBuforListResponse',
                        'buforType' => 'buforType',
                        'createEnvelopeBufor' => 'createEnvelopeBufor',
                        'createEnvelopeBuforResponse' => 'createEnvelopeBuforResponse',
                        'moveShipments' => 'moveShipments',
                        'moveShipmentsResponse' => 'moveShipmentsResponse',
                        'updateEnvelopeBufor' => 'updateEnvelopeBufor',
                        'updateEnvelopeBuforResponse' => 'updateEnvelopeBuforResponse',
                        'getUbezpieczeniaInfo' => 'getUbezpieczeniaInfo',
                        'getUbezpieczeniaInfoResponse' => 'getUbezpieczeniaInfoResponse',
                        'ubezpieczeniaInfoType' => 'ubezpieczeniaInfoType',
                        'isMiejscowa' => 'isMiejscowa',
                        'isMiejscowaResponse' => 'isMiejscowaResponse',
                        'trasaRequestType' => 'trasaRequestType',
                        'trasaResponseType' => 'trasaResponseType',
                        'deklaracjaCelnaType' => 'deklaracjaCelnaType',
                        'szczegolyDeklaracjiCelnejType' => 'szczegolyDeklaracjiCelnejType',
                        'przesylkaPaletowaType' => 'przesylkaPaletowaType',
                        'rodzajPaletyType' => 'rodzajPaletyType',
                        'paletaType' => 'paletaType',
                        'platnikType' => 'platnikType',
                        'subPrzesylkaPaletowaType' => 'subPrzesylkaPaletowaType',
                        'getBlankietPobraniaByGuids' => 'getBlankietPobraniaByGuids',
                        'getBlankietPobraniaByGuidsResponse' => 'getBlankietPobraniaByGuidsResponse',
                        'updateAccount' => 'updateAccount',
                        'updateAccountResponse' => 'updateAccountResponse',
                        'accountType' => 'accountType',
                        'permisionType' => 'permisionType',
                        'getAccountList' => 'getAccountList',
                        'getAccountListResponse' => 'getAccountListResponse',
                        'profilType' => 'profilType',
                        'getProfilList' => 'getProfilList',
                        'getProfilListResponse' => 'getProfilListResponse',
                        'updateProfil' => 'updateProfil',
                        'updateProfilResponse' => 'updateProfilResponse',
                        'statusAccountType' => 'statusAccountType',
                        'uslugaPaczkowaType' => 'uslugaPaczkowaType',
                        'subUslugaPaczkowaType' => 'subUslugaPaczkowaType',
                        'terminPaczkowaType' => 'terminPaczkowaType',
                        'opakowaniePocztowaType' => 'opakowaniePocztowaType',
                        'uslugaKurierskaType' => 'uslugaKurierskaType',
                        'subUslugaKurierskaType' => 'subUslugaKurierskaType',
                        'createAccount' => 'createAccount',
                        'createAccountResponse' => 'createAccountResponse',
                        'createProfil' => 'createProfil',
                        'createProfilResponse' => 'createProfilResponse',
                        'terminKurierskaType' => 'terminKurierskaType',
                        'opakowanieKurierskaType' => 'opakowanieKurierskaType',
                        'zwrotDokumentowPaczkowaType' => 'zwrotDokumentowPaczkowaType',
                        'potwierdzenieOdbioruPaczkowaType' => 'potwierdzenieOdbioruPaczkowaType',
                        'sposobPrzekazaniaPotwierdzeniaOdbioruPocztowaType' => 'sposobPrzekazaniaPotwierdzeniaOdbioruPocztowaType',
                        'zwrotDokumentowKurierskaType' => 'zwrotDokumentowKurierskaType',
                        'terminZwrotDokumentowKurierskaType' => 'terminZwrotDokumentowKurierskaType',
                        'terminZwrotDokumentowPaczkowaType' => 'terminZwrotDokumentowPaczkowaType',
                        'potwierdzenieOdbioruKurierskaType' => 'potwierdzenieOdbioruKurierskaType',
                        'sposobPrzekazaniaPotwierdzeniaOdbioruKurierskaType' => 'sposobPrzekazaniaPotwierdzeniaOdbioruKurierskaType',
                        'addReklamacje' => 'addReklamacje',
                        'addReklamacjeResponse' => 'addReklamacjeResponse',
                        'getReklamacje' => 'getReklamacje',
                        'getReklamacjeResponse' => 'getReklamacjeResponse',
                        'getZapowiedziFaktur' => 'getZapowiedziFaktur',
                        'getZapowiedziFakturResponse' => 'getZapowiedziFakturResponse',
                        'addOdwolanieDoReklamacji' => 'addOdwolanieDoReklamacji',
                        'addOdwolanieDoReklamacjiResponse' => 'addOdwolanieDoReklamacjiResponse',
                        'addRozbieznoscDoZapowiedziFaktur' => 'addRozbieznoscDoZapowiedziFaktur',
                        'addRozbieznoscDoZapowiedziFakturResponse' => 'addRozbieznoscDoZapowiedziFakturResponse',
                        'reklamowanaPrzesylkaType' => 'reklamowanaPrzesylkaType',
                        'powodReklamacjiType' => 'powodReklamacjiType',
                        'reklamacjaRozpatrzonaType' => 'reklamacjaRozpatrzonaType',
                        'rozstrzygniecieType' => 'rozstrzygniecieType',
                        'getListaPowodowReklamacji' => 'getListaPowodowReklamacji',
                        'getListaPowodowReklamacjiResponse' => 'getListaPowodowReklamacjiResponse',
                        'powodSzczegolowyType' => 'powodSzczegolowyType',
                        'kategoriePowodowReklamacjiType' => 'kategoriePowodowReklamacjiType',
                        'listBiznesowyType' => 'listBiznesowyType',
                        'zamowKuriera' => 'zamowKuriera',
                        'zamowKurieraResponse' => 'zamowKurieraResponse',
                        'getEZDOList' => 'getEZDOList',
                        'getEZDOListResponse' => 'getEZDOListResponse',
                        'getEZDO' => 'getEZDO',
                        'getEZDOResponse' => 'getEZDOResponse',
                        'EZDOPakietType' => 'EZDOPakietType',
                        'EZDOPrzesylkaType' => 'EZDOPrzesylkaType',
                        'wplataCKPType' => 'wplataCKPType',
                        'getWplatyCKP' => 'getWplatyCKP',
                        'getWplatyCKPResponse' => 'getWplatyCKPResponse',
                        'globalExpresType' => 'globalExpresType',
                        'cancelReklamacja' => 'cancelReklamacja',
                        'cancelReklamacjaResponse' => 'cancelReklamacjaResponse',
                        'zalacznikDoReklamacjiType' => 'zalacznikDoReklamacjiType',
                        'addZalacznikDoReklamacji' => 'addZalacznikDoReklamacji',
                        'addZalacznikDoReklamacjiResponse' => 'addZalacznikDoReklamacjiResponse'
                    );

        $mode = array
                (
                    'classmap' => $classmap,
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
        $message = new clearEnvelope();
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
        $message = new addShipment();
        $data = new PrzesylkaBiznesowaType();
        $data->adres = $this->_prepareAddress();
        $data->gabaryt = $this->_settings['size'];
        $data->masa = $this->_settings['weight'];
        $data->guid = $this->_getGuid();
        $message->przesylki[] = $data;
        $result = $this->_sendMessage('addShipment',$message);
        return $this->_prepareResult($result);
    }

    /**
     * set Active card
     */

    public function setActiveCard($card) {
        $message = new setAktywnaKarta();
        $message->idKarta = $card;
        $card->aktywna = true;
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
        $address = new adresType();
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
            $cod->tytulem = sprintf(Mage::getStoreConfig('carriers/zolagopp/cod_transfer_title'),$this->_settings['orderId']);
        }
        return $cod;
    }

    /**
     * insurance values
     */
    protected function _prepareInsurance() {
        if (!$value = $this->_settings['insurance']) {
            return null; // no insurance
        }
        if ($value < 0) {
            $value = $this->_settings['value']*100; // order value
        }
        $insurance = new ubezpieczenieType();
        $insurance->rodzaj = rodzajUbezpieczeniaType::STANDARD;
        $insurance->kwota = $value;
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
        $data->termin = terminRodzajType::EKSPRES24;
        $data->numerPrzesylkiKlienta = $settings['orderId'];
        if ($insurance = $this->_prepareInsurance()) {
            $data->ubezpieczenie = $insurance;
        }
        if ($codValue = $this->_settings['cod']) {
            $data->pobranie = $this->_prepareCod($codValue);
        }

        $message->przesylki[] = $data;
        $result = $this->_sendMessage('addShipment',$message);
        return $this->_prepareResult($result);
    }

    /**
     * parse error message
     */
    protected function _parseErrorMessage($postResult) {
        if (!empty($postResult->error)) {
            if (!is_array($postResult->error)) {
                $postResult->error = array($postResult->error);
            }
            $message = array();
            foreach ($postResult->error as $error) {
                if (!empty($error->errorDesc)) {
                    $message[] = $error->errorDesc;
                }
                elseif (!empty($error->errorNumber)) {
                    $message[] = Mage::helper('orbashipping')->__('%s server error: Error number %s',
                                 'poczta-polska',
                                 $postResult->error->errorNumber);
                }
                else {
                    $message[] = Mage::helper('orbashipping')->__('%s server errror. Check logs',
                                 'poczta-polska');
                }
            }
            Mage::throwException(implode(PHP_EOL,$message));
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
        $out['data'] = $response->content->pdfContent;
        $numbers[$response->content->guid] = $response->content->nrNadania;
        $out['numbers'] = $numbers;
        // replace old number by new
        foreach ($tracking as $track) {        
            if ($newNumber = empty($numbers[$track->getNumber()])?null:$numbers[$track->getNumber()]) {
                $track->setNumber($newNumber);
                $track->save();
                Mage::app()->getRequest()->setParam('trackNumber',$newNumber);
            }            
        } 
        return $out;
    }


    /**
     * format results
     */
    public function processLabelsResult($method,$data) {
        $result = array (
                      'status' => true,
                      'labelData' => $data['data'],
                      'labelName' => implode('_',$data['numbers']).'.'.Orba_Shipping_Helper_Post::FILE_EXT,
                      'message' => 'Shipment ID: ' . implode(',',$data['numbers']),
                  );
        return $result;
    }

    /**
     * cancel package
     * @tod
     */
    public function cancelPack($number) {    
        $message = new clearEnvelopeByGuids();
        $message->guid = $number;
        $result = $this->_sendMessage('clearEnvelopeByGuids',$message);
        if (!empty($result->error)) {
            Mage::throwException($result->error->errorDesc);
        }
    }
    
    /**
     * send prepared envelope
     */
    public function sendEnvelope() {
        $message = new sendEnvelope();
        $message->urzadNadania = $this->getParam('postOffice');
        $result = $this->_sendMessage('sendEnvelope',$message);
        if (!empty($result->error)) {
            Mage::log($result);
            Mage::throwException($result->error->errorDesc);
        }
        if (empty($result)) {
            Mage::throwException(Mage::helper('orbashipping')->__('Empty content'));
        }
        return $result;
    }
    
    /**
     * post office list
     */
     public function getPostOfficeList() {
         $message = new getUrzedyNadania();
         $result = $this->_sendMessage('getUrzedyNadania',$message);
         if (empty($result->urzedyNadania)) {             
             throw new Orba_Shipping_Model_Post_Client_Exception_NoPostOffices(Mage::helper('orbashipping')->__('No assigned post offices'));
         }
         return $result->urzedyNadania;
     }
     
     protected function _getHelper() {
         return Mage::helper('orbashipping/post');
     }
     
    /**
     * aggregated
     */
     public function getOutboxBook($id) {
         $message = new getOutboxBook();
         $message->idEnvelope = $id;
         $result = $this->_sendMessage('getOutboxBook',$message);
        if (!empty($result->error)) {
            Mage::log($result);            
            Mage::throwException($result->error->errorDesc);
        }
        if (empty($result)) {
            Mage::throwException(Mage::helper('orbashipping')->__('Empty content'));
        }
        return $result;
     }

}

