<?php
/**
 * paczkaPocztowaTypeBusiness
 */
class przesylkaBiznesowaType
{
	public $pobranie; // pobranieType
	public $urzadWydaniaEPrzesylki; // urzadWydaniaEPrzesylkiType
	public $subPrzesylka; // subPrzesylkaBiznesowaType
	public $ubezpieczenie; // ubezpieczenieType
	public $masa; // masaType
	public $gabaryt; // gabarytBiznesowaType
	public $wartosc; // wartoscType
	public $ostroznie; // boolean
}

class Orba_Shipping_Model_Post_Message_Pack_Business
{
    public function getObject() {
        return new przesylkaBiznesowaType();
    }
}
