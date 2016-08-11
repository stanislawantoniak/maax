<?php
/**
 * Class GH_Statements_Model_Track
 * @method int getId()
 * @method GH_Statements_Model_Track setId(int $id)
 * @method int getStatementId()
 * @method GH_Statements_Model_Track setStatementId(int $statement_id)
 * @method int getPoId()
 * @method GH_Statements_Model_Track setPoId(int $po_id)
 * @method string getPoIncrementId()
 * @method GH_Statements_Model_Track setPoIncrementId(string $po_increment_id)
 * @method int getRmaId()
 * @method GH_Statements_Model_Track setRmaId(int $rma_id)
 * @method string getRmaIncrementId()
 * @method GH_Statements_Model_Track setRmaIncrementId(string $rma_increment_id)
 * @method string getShippedDate()
 * @method GH_Statements_Model_Track setShippedDate(string $date)
 * @method string getTrackNumber()
 * @method GH_Statements_Model_Track setTrackNumber(string $track_number)
 * @method float getChargeShipment()
 * @method GH_Statements_Model_Track setChargeShipment(float $charge_shipment)
 * @method float getChargeFuel()
 * @method GH_Statements_Model_Track setChargeFuel(float $charge_fuel)
 * @method float getChargeInsurance()
 * @method GH_Statements_Model_Track setChargeInsurance(float $charge_insurance)
 * @method float getChargeCod()
 * @method GH_Statements_Model_Track setChargeCod(float $charge_cod)
 * @method float getChargeSubtotal()
 * @method GH_Statements_Model_Track setChargeSubtotal(float $charge_subtotal)
 * @method float getChargeTotal()
 * @method GH_Statements_Model_Track setChargeTotal(float $charge_total)
 */
class GH_Statements_Model_Track extends Mage_Core_Model_Abstract
{
	const TRACK_TYPE_ORDER = 0;
	const TRACK_TYPE_RMA_CLIENT = 1;
	const TRACK_TYPE_RMA_VENDOR = 2;
	const TRACK_TYPE_UNDELIVERED = 3;

    protected function _construct()
    {
        $this->_init('ghstatements/track');
    }

}