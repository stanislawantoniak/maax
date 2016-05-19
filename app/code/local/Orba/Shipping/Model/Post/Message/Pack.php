<?php
/**
 * paczkaPocztowaType
 */
        class paczkaPocztowaType {
            public $posteRestante; // boolean
            public $iloscPotwierdzenOdbioru; // iloscPotwierdzenOdbioruType
            public $kategoria; // kategoriaType
            public $gabaryt; // gabarytType
            public $masa; // masaType
            public $wartosc; // wartoscType
            public $zwrotDoslanie; // boolean
            public $egzemplarzBiblioteczny; // boolean
            public $dlaOciemnialych; // boolean
        }
class Orba_Shipping_Model_Post_Message_Pack
{
    public function getObject() {
        return new paczkaPocztowaType();
    }
}
