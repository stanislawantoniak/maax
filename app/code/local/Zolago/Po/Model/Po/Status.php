<?php
class Zolago_Po_Model_Po_Status
{
	/**
	 * Dropship statuses
	 */
	
	// czeka na spakowanie
    const STATUS_PENDING    = Zolago_Po_Model_Source::UDPO_STATUS_PENDING; 
	// w trakcie pakowania
    const STATUS_EXPORTED   = Zolago_Po_Model_Source::UDPO_STATUS_EXPORTED;
	// czeka na potwierdzenie
    const STATUS_ACK        = Zolago_Po_Model_Source::UDPO_STATUS_ACK;
	// czeka na rezerwację
    const STATUS_BACKORDER  = Zolago_Po_Model_Source::UDPO_STATUS_BACKORDER;
	// problem
    const STATUS_ONHOLD     = Zolago_Po_Model_Source::UDPO_STATUS_ONHOLD;
	// spakowane
    const STATUS_READY      = Zolago_Po_Model_Source::UDPO_STATUS_READY;
	// N/O
    const STATUS_PARTIAL    = Zolago_Po_Model_Source::UDPO_STATUS_PARTIAL;
	// wysłane
    const STATUS_SHIPPED    = Zolago_Po_Model_Source::UDPO_STATUS_SHIPPED;
	// anulowane
    const STATUS_CANCELED   = Zolago_Po_Model_Source::UDPO_STATUS_CANCELED;
	// dostarczone
	const STATUS_DELIVERED  = Zolago_Po_Model_Source::UDPO_STATUS_DELIVERED;
	// zwrócone
    const STATUS_RETURNED   = Zolago_Po_Model_Source::UDPO_STATUS_RETURNED;
	// czeka na płatność
    const STATUS_PAYMENT    = Zolago_Po_Model_Source::UDPO_STATUS_PAYMENT; 


	public function canChange($old, $new) {
	   return true;
   }
   
}
