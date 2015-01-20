<?php

class Zolago_Payment_Model_Allocation extends Mage_Core_Model_Abstract {

    const ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT   = 'payment';
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY   = 'overpay'; // nadplata
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_UNDERPAID = 'underpaid'; // niedoplata

    protected function _construct() {
        $this->_init('zolagopayment/allocation');
    }

    public function allocationTransaction($transaction_id, $allocation_type, $operator_id = null, $comment = '') {
        $data = $this->getResource()->getDataAllocationForTransaction($transaction_id, $allocation_type, $operator_id, $comment);
        $this->getResource()->appendAllocations($data);

    }



//    POPRAWKI OD MACIEJ ABY NIE ZAPOMNIEC
//TODO:
//1.
//W zapisie transakcji wywoływany jest event
//Mage::dispatchEvent("zolagopayment_append_allocation" ...
//i to jest błąd. Event nie ma nic wpolnego z dodaniem alokacji.
//Jest to event importu transkacji więc powien nazywać się np zolagopayment_transaction_import_after, lub w ogóle można skorzystać z save after obiketu transkacji
//Dopiero w obsludze tego eventu dodajesz alkoacje. trzba rozroznic te dwie rzeczy.
//Event to nazwa zdarzenia które zaszło, a nie które czynności którą dopiero chesz wykonać
//
//2. Zolago_Payment_Model_Allocation powien mieć metodę importDataFromTransaction gdzie na podstawie transakcji zapełniasz model wymaganymi danymi.
//resztę dopisujesz recznie jeśli trzeba. potem robisz zwykly save bez odnoszenia sie do resource $this->getResource()->appendAllocations($data);
//W obecny sposób, gdyby chciec podpiąć sie pod event save after dla alokacji - nie ma możliowści - robi to resource bezpośrednio

}