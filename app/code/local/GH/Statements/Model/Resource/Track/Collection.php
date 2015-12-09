<?php

/**
 * @method string getId()
 * @method string getStatementId()
 * @method string getPoId()
 * @method string getPoIncrementId()
 * @method string getRmaId()
 * @method string getRmaIncrementId()
 * @method string getShippedDate()
 * @method string getTrackNumber()
 * @method string getChargeShipment()
 * @method string getChargeFuel()
 * @method string getChargeInsurance()
 * @method string getChargeCod()
 * @method string getChargeSubtotal()
 * @method string getChargeTotal()
 * @method string getTrackType()
 * @method string getTitle()
 * @method string getCustomerId()
 * @method string getShippingSourceAccount()
 * @method string getSalesTrackId()
 * @method string getRmaTrackId()
 *
 * Class GH_Statements_Model_Resource_Track_Collection
 */
class GH_Statements_Model_Resource_Track_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/track');
    }

}