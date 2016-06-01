<?php
/**
 * adresType
 */
class adresType {
    public $nazwa; // nazwaType
    public $nazwa2; // nazwa2Type
    public $ulica; // ulicaType
    public $numerDomu; // numerDomuType
    public $numerLokalu; // numerLokaluType
    public $miejscowosc; // miejscowoscType
    public $kodPocztowy; // kodPocztowyType
    public $kraj; // krajType
    public $telefon; // telefonType
    public $email; // emailType
    public $mobile; // mobileType
    public $osobaKontaktowa; // string
    public $nip; // string
}
class Orba_Shipping_Model_Post_Message_Address
{
    public function getObject() {
        return new adresType();
    }
}
